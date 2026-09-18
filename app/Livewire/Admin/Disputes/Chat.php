<?php

namespace App\Livewire\Admin\Disputes;

use App\Models\Chat as ChatModel;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public HelpCancelRequest $cancelRequest;
    public $activeTab = 'customer'; // 'customer', 'mitra', 'all'
    public $message = '';
    public $photo;

    public function mount(HelpCancelRequest $cancelRequest)
    {
        $this->cancelRequest = $cancelRequest->load([
            'help.user',
            'help.mitra',
            'help.city',
            'help.district',
            'requestedBy',
            'partner',
            'customer',
            'reviewedBy'
        ]);

        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $effectiveDistricts = $admin->getEffectiveAdminDistrictIds();
            $targetDistrictId = $this->cancelRequest->district_id ?? $this->cancelRequest->help?->district_id;
            if ($targetDistrictId && !in_array((int)$targetDistrictId, $effectiveDistricts, true)) {
                session()->flash('error', 'Anda tidak memiliki wewenang untuk meninjau pembatalan di luar wilayah kecamatan Anda.');
                return redirect()->route('admin.cancellations.index');
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
        $help = $this->cancelRequest->help;
        $customerId = $this->cancelRequest->customer_id ?? $help?->user_id;
        $partnerId = $this->cancelRequest->partner_id ?? $help?->mitra_id;

        $query = HelpCancelMessage::where('help_cancel_request_id', $this->cancelRequest->id)
            ->where('is_read', false)
            ->where('sender_id', '!=', auth()->id());

        if ($this->activeTab === 'customer' && $customerId) {
            $query->where('sender_id', $customerId);
        } elseif ($this->activeTab === 'mitra' && $partnerId) {
            $query->where('sender_id', $partnerId);
        }

        $query->update(['is_read' => true, 'read_at' => now()]);
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
        $lockKey = 'send_cancel_chat_' . auth()->id() . '_req_' . $this->cancelRequest->id . '_' . $this->activeTab . '_' . md5(($this->message ?? '') . '_' . $photoName);

        if (!Cache::add($lockKey, true, 2)) {
            return;
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('chat/cancellations', 'public');
        }

        HelpCancelMessage::create([
            'help_cancel_request_id' => $this->cancelRequest->id,
            'sender_id'              => auth()->id(),
            'recipient_type'         => $this->activeTab,
            'message'                => trim($this->message ?? ''),
            'photo'                  => $photoPath,
            'is_read'                => false,
        ]);

        $this->reset(['message', 'photo']);
        $this->dispatch('chat-scrolled');
    }

    public function render()
    {
        $help = $this->cancelRequest->help;
        $customer = $help?->user ?? $this->cancelRequest->customer;
        $partner = $help?->mitra ?? $this->cancelRequest->partner;

        $customerId = $customer?->id;
        $partnerId = $partner?->id;

        // Ambil semua pesan investigasi pembatalan
        $allCancelMessages = HelpCancelMessage::where('help_cancel_request_id', $this->cancelRequest->id)
            ->with('sender')
            ->oldest()
            ->get();

        // Ambil riwayat chat tugas terdahulu (jika ada)
        $historicalTaskChats = collect();
        if ($help) {
            $historicalTaskChats = ChatModel::where('help_id', $help->id)
                ->with(['mitra', 'customer'])
                ->oldest()
                ->get();
        }

        // Filter pesan berdasarkan tab
        $messages = $allCancelMessages->filter(function ($msg) use ($customerId, $partnerId) {
            if ($this->activeTab === 'all') {
                return true;
            }

            if ($this->activeTab === 'customer') {
                return $msg->recipient_type === 'customer'
                    || $msg->recipient_type === 'all'
                    || $msg->sender_id === $customerId;
            }

            if ($this->activeTab === 'mitra') {
                return $msg->recipient_type === 'mitra'
                    || $msg->recipient_type === 'all'
                    || $msg->sender_id === $partnerId;
            }

            return true;
        });

        $unreadCustomer = $allCancelMessages->where('sender_id', $customerId)->where('is_read', false)->count();
        $unreadMitra = $allCancelMessages->where('sender_id', $partnerId)->where('is_read', false)->count();

        $isSuperAdmin = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.disputes.chat', [
            'messages'            => $messages,
            'allCancelMessages'   => $allCancelMessages,
            'historicalTaskChats' => $historicalTaskChats,
            'customer'            => $customer,
            'partner'             => $partner,
            'help'                => $help,
            'unreadCustomer'      => $unreadCustomer,
            'unreadMitra'         => $unreadMitra,
            'isSuperAdmin'        => $isSuperAdmin,
        ])->layout($layout);
    }
}
