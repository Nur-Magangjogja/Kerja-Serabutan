<?php

namespace App\Livewire\Mitra\Chat;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $selected_partner_id = null; // ID Customer yang sedang diajak chat atau 'admin'
    public $selected_partner = null;    // Objek User Customer atau Objek Admin
    public $is_admin_chat = false;      // True jika sedang chat dengan Admin
    public $selected_report_id = null;  // ID Laporan terkait jika ada
    public $selected_report = null;     // Objek Laporan terkait
    public $active_help_id = null;      // ID Help terkini/terkait
    public $active_help = null;         // Objek Help terkini/terkait
    public $message = '';
    public $photo = null;
    public $search = '';

    protected function rules()
    {
        return [
            'message' => 'required_without:photo|nullable|string|max:2000',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function mount($help = null)
    {
        if (request()->query('admin') || request()->query('report')) {
            $this->selectAdmin(request()->query('report'));
            return;
        }

        if ($help) {
            $helpModel = Help::with(['user', 'mitra'])->find($help);

            if ($helpModel && $helpModel->user_id) {
                $this->selectPartner($helpModel->user_id, $helpModel->id);
            }
        }
    }

    public function getConversations()
    {
        $mitraId = Auth::id();

        // 1. Percakapan Khusus dengan Tim Admin SayaBantu
        $mitraReports = PartnerReport::where('reported_user_id', $mitraId)
            ->orWhereHas('reportedHelp', function($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            })
            ->pluck('id');

        $lastAdminMsg = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
            ->where(function($q) use ($mitraId) {
                $q->where('sender_id', $mitraId)
                  ->orWhere('recipient_type', 'mitra');
            })
            ->latest('created_at')
            ->first();

        $unreadAdminCount = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
            ->where('recipient_type', 'mitra')
            ->where('is_read', false)
            ->count();

        $latestMitraReport = PartnerReport::whereIn('id', $mitraReports)->latest()->first();

        $adminConversation = (object) [
            'partner' => (object) [
                'id'           => 'admin',
                'name'         => '🛡️ Tim Admin SayaBantu',
                'email'        => 'admin@sayabantu.com',
                'phone'        => 'Pusat Bantuan & Moderasi Mitra',
                'selfie_photo' => null,
                'is_admin'     => true,
            ],
            'is_admin'     => true,
            'last_message' => $lastAdminMsg ? (object)[
                'message'    => $lastAdminMsg->message ?: '[Lampiran Foto Bukti]',
                'created_at' => $lastAdminMsg->created_at,
            ] : null,
            'unread_count' => $unreadAdminCount,
            'latest_help'  => $latestMitraReport?->reportedHelp,
            'updated_at'   => $lastAdminMsg?->created_at ?? now(),
        ];

        // 2. Percakapan dengan Customer
        $chatCustomerIds = ChatModel::where('mitra_id', $mitraId)
            ->whereNotNull('customer_id')
            ->pluck('customer_id');

        $helpCustomerIds = Help::where('mitra_id', $mitraId)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $customerIds = $chatCustomerIds->merge($helpCustomerIds)->unique()->values();

        $conversations = collect();

        if ($customerIds->isNotEmpty()) {
            $customersQuery = User::whereIn('id', $customerIds);

            if ($this->search) {
                $customersQuery->where('name', 'like', '%' . $this->search . '%');
            }

            $customers = $customersQuery->get();

            $conversations = $customers->map(function ($customer) use ($mitraId) {
                $lastMessage = ChatModel::where('mitra_id', $mitraId)
                    ->where('customer_id', $customer->id)
                    ->latest('created_at')
                    ->first();

                $unreadCount = ChatModel::where('mitra_id', $mitraId)
                    ->where('customer_id', $customer->id)
                    ->where('sender_type', 'customer')
                    ->whereNull('read_at')
                    ->count();

                $latestHelp = Help::where('user_id', $customer->id)
                    ->where('mitra_id', $mitraId)
                    ->latest('updated_at')
                    ->first();

                return (object) [
                    'partner'      => $customer,
                    'is_admin'     => false,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                    'latest_help'  => $latestHelp,
                    'updated_at'   => $lastMessage?->created_at ?? $latestHelp?->updated_at ?? $customer->updated_at,
                ];
            })
            ->filter(fn($c) => $c->last_message !== null || $c->latest_help !== null)
            ->sortByDesc('updated_at')
            ->values();
        }

        // Tampilkan Admin di paling atas percakapan jika pencarian cocok atau tanpa filter
        if (!$this->search || str_contains(strtolower('admin tim pusat bantuan moderasi'), strtolower($this->search))) {
            $conversations->prepend($adminConversation);
        }

        return $conversations;
    }

    public function selectAdmin($reportId = null)
    {
        $this->selected_partner_id = 'admin';
        $this->is_admin_chat       = true;
        $this->selected_partner    = (object) [
            'id'           => 'admin',
            'name'         => '🛡️ Tim Admin SayaBantu',
            'email'        => 'admin@sayabantu.com',
            'phone'        => 'Pusat Bantuan & Moderasi Resmi',
            'selfie_photo' => null,
            'is_admin'     => true,
        ];

        $mitraId = Auth::id();
        $mitraReports = PartnerReport::where('reported_user_id', $mitraId)
            ->orWhereHas('reportedHelp', function($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            })
            ->pluck('id');

        if ($reportId) {
            $this->selected_report_id = (int) $reportId;
            $this->selected_report    = PartnerReport::find($reportId);
            $this->active_help_id     = $this->selected_report?->reported_help_id;
            $this->active_help        = $this->selected_report?->reportedHelp;
        } else {
            $latestReport = PartnerReport::whereIn('id', $mitraReports)->latest()->first();
            $this->selected_report_id = $latestReport?->id;
            $this->selected_report    = $latestReport;
            $this->active_help_id     = $latestReport?->reported_help_id;
            $this->active_help        = $latestReport?->reportedHelp;
        }

        // Tandai pesan dari admin sebagai terbaca
        PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
            ->where('sender_id', '!=', $mitraId)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->dispatch('scroll-chat-bottom');
    }

    public function selectPartner($customerId, $helpId = null)
    {
        $this->selected_partner_id = (int) $customerId;
        $this->is_admin_chat       = false;
        $this->selected_report_id  = null;
        $this->selected_report     = null;
        $this->selected_partner    = User::find($customerId);

        if ($helpId) {
            $this->active_help_id = (int) $helpId;
            $this->active_help    = Help::find($helpId);
        } else {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', Auth::id())
                ->latest('updated_at')
                ->first();

            $this->active_help_id = $latestHelp?->id;
            $this->active_help    = $latestHelp;
        }

        ChatModel::where('mitra_id', Auth::id())
            ->where('customer_id', $customerId)
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->dispatch('scroll-chat-bottom');
    }

    public function getChatMessages()
    {
        if (!$this->selected_partner_id) {
            return collect();
        }

        $userId = Auth::id();

        // Jika chat dengan Admin
        if ($this->is_admin_chat) {
            $mitraReports = PartnerReport::where('reported_user_id', $userId)
                ->orWhereHas('reportedHelp', function($q) use ($userId) {
                    $q->where('mitra_id', $userId);
                })
                ->pluck('id');

            $query = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
                ->where(function($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('recipient_type', 'mitra');
                })
                ->with(['sender', 'report.reportedHelp'])
                ->orderBy('created_at', 'asc');

            return $query->get()->map(function($m) {
                return (object)[
                    'id'          => $m->id,
                    'message'     => $m->message,
                    'photo'       => $m->photo,
                    'sender_type' => $m->isFromAdmin() ? 'admin' : 'mitra',
                    'sender_name' => $m->sender?->name ?? ($m->isFromAdmin() ? 'Tim Admin' : 'Mitra'),
                    'created_at'  => $m->created_at,
                    'help_id'     => $m->report?->reported_help_id,
                    'help'        => $m->report?->reportedHelp,
                    'is_admin'    => $m->isFromAdmin(),
                ];
            });
        }

        $mitraId    = $userId;
        $customerId = $this->selected_partner_id;

        return ChatModel::where('mitra_id', $mitraId)
            ->where('customer_id', $customerId)
            ->with('help')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function closeChat()
    {
        $this->selected_partner_id = null;
        $this->selected_partner    = null;
        $this->is_admin_chat       = false;
        $this->selected_report_id  = null;
        $this->selected_report     = null;
        $this->active_help_id      = null;
        $this->active_help         = null;
        $this->message             = '';
        $this->photo               = null;
    }

    public function removePhoto()
    {
        $this->photo = null;
    }

    public function sendMessage()
    {
        $this->validate();

        if (!$this->selected_partner_id) {
            $this->dispatch('error', 'Pilih percakapan terlebih dahulu');
            return;
        }

        $mitraId = Auth::id();

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('chats/photos', 'public');
        }

        $msgText = trim($this->message) ?: ($photoPath ? '[Lampiran Foto Bukti]' : '');

        // JIKA CHAT DENGAN ADMIN
        if ($this->is_admin_chat) {
            $report = $this->selected_report;

            if (!$report) {
                $report = PartnerReport::where('reported_user_id', $mitraId)->latest()->first();
            }

            if (!$report) {
                $report = PartnerReport::create([
                    'reported_user_id' => $mitraId,
                    'category'         => 'dari_mitra',
                    'report_type'      => 'dukungan_umum',
                    'title'            => 'Klarifikasi / Diskusi Mitra dengan Tim Admin',
                    'message'          => $msgText,
                    'status'           => 'pending',
                ]);
                $this->selected_report    = $report;
                $this->selected_report_id = $report->id;
            }

            PartnerReportMessage::create([
                'partner_report_id' => $report->id,
                'sender_id'         => $mitraId,
                'recipient_type'    => 'admin',
                'message'           => $msgText,
                'photo'             => $photoPath,
                'is_read'           => false,
            ]);

            if ($report->status === 'pending') {
                $report->update(['status' => 'in_progress']);
            }

            $this->message = '';
            $this->photo   = null;
            $this->dispatch('message-sent');
            $this->dispatch('scroll-chat-bottom');
            return;
        }

        // CHAT REGULER DENGAN CUSTOMER
        $customerId = $this->selected_partner_id;

        $helpId = $this->active_help_id;
        if (!$helpId) {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();
            $helpId = $latestHelp?->id;
        }

        if (!$helpId) {
            $anyChat = ChatModel::where('mitra_id', $mitraId)
                ->where('customer_id', $customerId)
                ->whereNotNull('help_id')
                ->latest('created_at')
                ->first();
            $helpId = $anyChat?->help_id;
        }

        if (!$helpId) {
            $this->dispatch('error', 'Tidak dapat mengirim pesan tanpa konteks bantuan.');
            return;
        }

        $chat = ChatModel::create([
            'help_id'     => $helpId,
            'mitra_id'    => $mitraId,
            'customer_id' => $customerId,
            'message'     => $msgText,
            'photo'       => $photoPath,
            'sender_type' => 'mitra',
            'is_read'     => false,
        ]);

        if ($this->selected_partner && !($this->selected_partner->is_admin ?? false)) {
            try {
                $this->selected_partner->notify(
                    new ChatMessageNotification($helpId, Str::limit($msgText, 150), $mitraId, Auth::user()->name)
                );
            } catch (\Throwable $e) {
                \Log::warning('[MitraChat] Failed to notify customer: ' . $e->getMessage());
            }
        }

        $this->message = '';
        $this->photo   = null;
        $this->dispatch('message-sent');
        $this->dispatch('scroll-chat-bottom');
    }

    public function render()
    {
        return view('livewire.mitra.chat.index', [
            'conversations' => $this->getConversations(),
            'messages'      => $this->getChatMessages(),
        ])->layout('layouts.mitra');
    }
}
