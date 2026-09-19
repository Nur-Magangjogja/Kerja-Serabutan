<?php

namespace App\Livewire\Admin\Partners\Reports;

use App\Models\Chat as ChatModel;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public PartnerReport $report;
    public $activeTab = 'customer'; // 'customer', 'mitra', 'all', 'task_log'
    public $message = '';
    public $photo;

    public function mount(PartnerReport $report)
    {
        $this->report = $report->load(['reporter', 'reportedUser', 'reportedHelp.user', 'reportedHelp.mitra']);
        
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $effectiveDistricts = $admin->getEffectiveAdminDistrictIds();
            $targetDistrictId = $this->report->district_id ?? $this->report->reportedHelp?->district_id ?? $this->report->reporter?->district_id ?? $this->report->reportedUser?->district_id;
            if ($targetDistrictId && !in_array((int)$targetDistrictId, $effectiveDistricts, true)) {
                session()->flash('error', 'Anda tidak memiliki wewenang untuk meninjau laporan di luar wilayah kecamatan Anda.');
                return redirect()->route('admin.partners.reports');
            }
        }

        if (request()->has('tab') && in_array(request('tab'), ['customer', 'mitra', 'all', 'task_log'], true)) {
            $this->activeTab = request('tab');
        }
        $this->markAsRead();
    }

    public function selectTab(string $tab)
    {
        $this->activeTab = in_array($tab, ['customer', 'mitra', 'all', 'task_log'], true) ? $tab : 'customer';
        $this->markAsRead();
    }

    public function markAsRead()
    {
        if ($this->activeTab === 'task_log') {
            return;
        }

        $customer = ($this->report->reporter && $this->report->reporter->role === 'customer') ? $this->report->reporter : (($this->report->reportedUser && $this->report->reportedUser->role === 'customer') ? $this->report->reportedUser : $this->report->reportedHelp?->user);
        $mitra = ($this->report->reporter && $this->report->reporter->role === 'mitra') ? $this->report->reporter : (($this->report->reportedUser && $this->report->reportedUser->role === 'mitra') ? $this->report->reportedUser : $this->report->reportedHelp?->mitra);

        $query = PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false);

        if ($this->activeTab === 'customer' && $customer) {
            $query->where(function($q) use ($customer) {
                $q->where('sender_id', $customer->id)
                  ->orWhere('recipient_type', 'customer');
            });
        } elseif ($this->activeTab === 'mitra' && $mitra) {
            $query->where(function($q) use ($mitra) {
                $q->where('sender_id', $mitra->id)
                  ->orWhere('recipient_type', 'mitra');
            });
        }

        $query->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function sendMessage()
    {
        if ($this->activeTab === 'task_log') {
            return;
        }

        $this->validate([
            'message' => 'required_without:photo|nullable|string|max:2000',
            'photo'   => 'nullable|image|max:5120',
        ]);

        $photoName = $this->photo ? $this->photo->getClientOriginalName() : '';
        $lockKey = 'send_msg_' . auth()->id() . '_report_' . $this->report->id . '_' . $this->activeTab . '_' . md5(($this->message ?? '') . '_' . $photoName);

        if (!Cache::add($lockKey, true, 2)) {
            return;
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('reports/messages', 'public');
        }

        $customerReadAt = $this->activeTab === 'mitra' ? now() : null;
        $mitraReadAt    = $this->activeTab === 'customer' ? now() : null;

        PartnerReportMessage::create([
            'partner_report_id' => $this->report->id,
            'sender_id'         => auth()->id(),
            'recipient_type'    => $this->activeTab,
            'message'           => trim($this->message ?? ''),
            'photo'             => $photoPath,
            'is_read'           => false,
            'customer_read_at'  => $customerReadAt,
            'mitra_read_at'     => $mitraReadAt,
        ]);

        $this->reset(['message', 'photo']);
        $this->dispatch('chat-scrolled');
    }

    public function render()
    {
        $customer = ($this->report->reporter && $this->report->reporter->role === 'customer') ? $this->report->reporter : (($this->report->reportedUser && $this->report->reportedUser->role === 'customer') ? $this->report->reportedUser : $this->report->reportedHelp?->user);
        $mitra = ($this->report->reporter && $this->report->reporter->role === 'mitra') ? $this->report->reporter : (($this->report->reportedUser && $this->report->reportedUser->role === 'mitra') ? $this->report->reportedUser : $this->report->reportedHelp?->mitra);

        $customerId = $customer?->id;
        $mitraId = $mitra?->id;

        // Ambil semua pesan aduan
        $allReportMessages = PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->with('sender')
            ->oldest()
            ->get();

        // Ambil riwayat chat pesanan terkait jika laporan memiliki reportedHelp (Read Only Log)
        $historicalTaskChats = collect();
        if ($this->report->reported_help_id || $this->report->reportedHelp) {
            $helpId = $this->report->reported_help_id ?? $this->report->reportedHelp->id;
            $historicalTaskChats = ChatModel::where('help_id', $helpId)
                ->with(['mitra', 'customer'])
                ->oldest()
                ->get();
        }

        // Filter pesan berdasarkan tab
        $messages = $allReportMessages->filter(function ($msg) use ($customerId, $mitraId) {
            if ($this->activeTab === 'all') {
                return true;
            }

            if ($this->activeTab === 'customer') {
                return $msg->recipient_type === 'customer'
                    || $msg->recipient_type === 'all'
                    || $msg->recipient_type === 'both'
                    || ($customerId && $msg->sender_id === $customerId);
            }

            if ($this->activeTab === 'mitra') {
                return $msg->recipient_type === 'mitra'
                    || $msg->recipient_type === 'all'
                    || $msg->recipient_type === 'both'
                    || ($mitraId && $msg->sender_id === $mitraId);
            }

            return true;
        });

        $unreadCustomer = $allReportMessages->filter(function($msg) use ($customerId) {
            return $msg->sender_id != auth()->id() && !$msg->is_read && (
                ($customerId && $msg->sender_id === $customerId) || $msg->recipient_type === 'customer'
            );
        })->count();

        $unreadMitra = $allReportMessages->filter(function($msg) use ($mitraId) {
            return $msg->sender_id != auth()->id() && !$msg->is_read && (
                ($mitraId && $msg->sender_id === $mitraId) || $msg->recipient_type === 'mitra'
            );
        })->count();

        $isSuperAdmin = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.reports.chat', [
            'messages'            => $messages,
            'historicalTaskChats' => $historicalTaskChats,
            'customer'            => $customer,
            'mitra'               => $mitra,
            'unreadCustomer'      => $unreadCustomer,
            'unreadMitra'         => $unreadMitra,
            'isSuperAdmin'        => $isSuperAdmin,
        ])->layout($layout);
    }
}
