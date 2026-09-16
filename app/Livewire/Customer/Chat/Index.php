<?php

namespace App\Livewire\Customer\Chat;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $selected_partner_id = null; // ID Mitra yang sedang diajak chat atau 'admin'
    public $selected_partner = null;    // Objek User Mitra atau Objek Admin
    public $is_admin_chat = false;      // True jika sedang chat dengan Admin
    public $selected_report_id = null;  // ID Laporan terkait jika ada
    public $selected_report = null;     // Objek Laporan terkait
    public $active_help_id = null;      // ID Help terkini/terkait
    public $active_help = null;         // Objek Help terkini/terkait
    public $unassigned_help = null;     // Jika help belum diambil mitra
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
            $helpModel = Help::with(['mitra', 'user'])->find($help);

            if ($helpModel) {
                if ($helpModel->user_id !== Auth::id()) {
                    return redirect()->route('customer.chat');
                }

                if ($helpModel->mitra_id) {
                    $this->selectPartner($helpModel->mitra_id, $helpModel->id);
                } else {
                    $lastChat = ChatModel::where('help_id', $helpModel->id)->latest('created_at')->first();
                    if ($lastChat && $lastChat->mitra_id) {
                        $this->selectPartner($lastChat->mitra_id, $helpModel->id);
                    } else {
                        $this->unassigned_help = $helpModel;
                    }
                }
            } else {
                return redirect()->route('customer.chat');
            }
        }
    }

    public function getConversations()
    {
        $userId = Auth::id();

        // 1. Percakapan Khusus dengan Tim Admin SayaBantu
        $customerReports = PartnerReport::where('reporter_id', $userId)
            ->orWhereHas('reportedHelp', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->pluck('id');

        $lastAdminMsg = PartnerReportMessage::whereIn('partner_report_id', $customerReports)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('recipient_type', 'customer');
            })
            ->latest('created_at')
            ->first();

        $unreadAdminCount = PartnerReportMessage::whereIn('partner_report_id', $customerReports)
            ->where('recipient_type', 'customer')
            ->where('is_read', false)
            ->count();

        $latestCustomerReport = PartnerReport::whereIn('id', $customerReports)->latest()->first();

        $adminConversation = (object) [
            'partner' => (object) [
                'id'            => 'admin',
                'name'          => '🛡️ Tim Admin SayaBantu',
                'email'         => 'admin@sayabantu.com',
                'phone'         => 'Pusat Bantuan & Moderasi',
                'profile_photo' => null,
                'selfie_photo'  => null,
                'is_admin'      => true,
            ],
            'is_admin'     => true,
            'last_message' => $lastAdminMsg ? (object)[
                'message'    => $lastAdminMsg->message ?: '[Lampiran Foto Bukti]',
                'created_at' => $lastAdminMsg->created_at,
            ] : null,
            'unread_count' => $unreadAdminCount,
            'latest_help'  => $latestCustomerReport?->reportedHelp,
            'updated_at'   => $lastAdminMsg?->created_at ?? now(),
        ];

        // 2. Percakapan dengan Mitra
        $chatMitraIds = ChatModel::where('customer_id', $userId)
            ->whereNotNull('mitra_id')
            ->pluck('mitra_id');

        $helpMitraIds = Help::where('user_id', $userId)
            ->whereNotNull('mitra_id')
            ->pluck('mitra_id');

        $mitraIds = $chatMitraIds->merge($helpMitraIds)->unique()->values();

        $conversations = collect();

        if ($mitraIds->isNotEmpty()) {
            $mitrasQuery = User::whereIn('id', $mitraIds);

            if ($this->search) {
                $mitrasQuery->where('name', 'like', '%' . $this->search . '%');
            }

            $mitras = $mitrasQuery->get();
            $filteredMitraIds = $mitras->pluck('id');

            // Bulk eager load last messages using MAX(id) per mitra
            $latestChatIds = ChatModel::where('customer_id', $userId)
                ->whereIn('mitra_id', $filteredMitraIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('mitra_id')
                ->pluck('id');

            $lastMessages = ChatModel::whereIn('id', $latestChatIds)
                ->get()
                ->keyBy('mitra_id');

            // Bulk eager load unread counts in a single query
            $unreadCounts = ChatModel::where('customer_id', $userId)
                ->whereIn('mitra_id', $filteredMitraIds)
                ->whereIn('sender_type', ['mitra', 'system'])
                ->whereNull('read_at')
                ->selectRaw('mitra_id, count(*) as total')
                ->groupBy('mitra_id')
                ->pluck('total', 'mitra_id');

            // Bulk eager load latest helps using MAX(id) per mitra
            $latestHelpIds = Help::where('user_id', $userId)
                ->whereIn('mitra_id', $filteredMitraIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('mitra_id')
                ->pluck('id');

            $latestHelps = Help::whereIn('id', $latestHelpIds)
                ->get()
                ->keyBy('mitra_id');

            $conversations = $mitras->map(function ($mitra) use ($lastMessages, $unreadCounts, $latestHelps) {
                $lastMessage = $lastMessages->get($mitra->id);
                $unreadCount = (int) ($unreadCounts->get($mitra->id) ?? 0);
                $latestHelp = $latestHelps->get($mitra->id);

                return (object) [
                    'partner'      => $mitra,
                    'is_admin'     => false,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                    'latest_help'  => $latestHelp,
                    'updated_at'   => $lastMessage?->created_at ?? $latestHelp?->updated_at ?? $mitra->updated_at,
                ];
            })
            ->filter(fn($c) => $c->last_message !== null || $c->latest_help !== null)
            ->sortByDesc('updated_at')
            ->values();
        }

        // Tampilkan Admin di bagian atas percakapan jika pencarian cocok atau tanpa filter
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
            'id'            => 'admin',
            'name'          => '🛡️ Tim Admin SayaBantu',
            'email'         => 'admin@sayabantu.com',
            'phone'         => 'Pusat Bantuan & Moderasi Resmi',
            'profile_photo' => null,
            'selfie_photo'  => null,
            'is_admin'      => true,
        ];
        $this->unassigned_help     = null;

        $userId = Auth::id();
        $customerReports = PartnerReport::where('reporter_id', $userId)
            ->orWhereHas('reportedHelp', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->pluck('id');

        if ($reportId) {
            $this->selected_report_id = (int) $reportId;
            $this->selected_report    = PartnerReport::find($reportId);
            $this->active_help_id     = $this->selected_report?->reported_help_id;
            $this->active_help        = $this->selected_report?->reportedHelp;
        } else {
            $latestReport = PartnerReport::whereIn('id', $customerReports)->latest()->first();
            $this->selected_report_id = $latestReport?->id;
            $this->selected_report    = $latestReport;
            $this->active_help_id     = $latestReport?->reported_help_id;
            $this->active_help        = $latestReport?->reportedHelp;
        }

        // Tandai pesan dari admin sebagai terbaca
        PartnerReportMessage::whereIn('partner_report_id', $customerReports)
            ->where('sender_id', '!=', $userId)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->dispatch('scroll-chat-bottom');
    }

    public function selectPartner($mitraId, $helpId = null)
    {
        $this->selected_partner_id = (int) $mitraId;
        $this->is_admin_chat       = false;
        $this->selected_report_id  = null;
        $this->selected_report     = null;
        $this->selected_partner    = User::find($mitraId);
        $this->unassigned_help     = null;

        if ($helpId) {
            $this->active_help_id = (int) $helpId;
            $this->active_help    = Help::find($helpId);
        } else {
            $latestHelp = Help::where('user_id', Auth::id())
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();

            $this->active_help_id = $latestHelp?->id;
            $this->active_help    = $latestHelp;
        }

        ChatModel::where('customer_id', Auth::id())
            ->where('mitra_id', $mitraId)
            ->whereIn('sender_type', ['mitra', 'system'])
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->dispatch('chat-messages-read');
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
            $customerReports = PartnerReport::where('reporter_id', $userId)
                ->orWhereHas('reportedHelp', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->pluck('id');

            $query = PartnerReportMessage::whereIn('partner_report_id', $customerReports)
                ->where(function($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('recipient_type', 'customer');
                })
                ->with(['sender', 'report.reportedHelp'])
                ->orderBy('created_at', 'asc');

            return $query->get()->map(function($m) {
                return (object)[
                    'id'          => $m->id,
                    'message'     => $m->message,
                    'photo'       => $m->photo,
                    'sender_type' => $m->isFromAdmin() ? 'admin' : 'customer',
                    'sender_name' => $m->sender?->name ?? ($m->isFromAdmin() ? 'Tim Admin' : 'Customer'),
                    'created_at'  => $m->created_at,
                    'help_id'     => $m->report?->reported_help_id,
                    'help'        => $m->report?->reportedHelp,
                    'is_admin'    => $m->isFromAdmin(),
                    'is_read'     => (bool) $m->is_read,
                    'read_at'     => $m->read_at,
                ];
            });
        }

        $customerId = $userId;
        $mitraId    = $this->selected_partner_id;

        return ChatModel::where('customer_id', $customerId)
            ->where('mitra_id', $mitraId)
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
        $this->unassigned_help     = null;
        $this->message             = '';
        $this->photo               = null;
    }

    public function removePhoto()
    {
        $this->photo = null;
    }

    #[On('help-new-message')]
    #[On('refresh-chat')]
    public function handleNewIncomingMessage()
    {
        if ($this->selected_partner_id && !$this->is_admin_chat) {
            $updated = ChatModel::where('customer_id', Auth::id())
                ->where('mitra_id', $this->selected_partner_id)
                ->whereIn('sender_type', ['mitra', 'system'])
                ->whereNull('read_at')
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            if ($updated > 0) {
                $this->dispatch('chat-messages-read');
            }
            $this->dispatch('scroll-chat-bottom');
        }
    }

    public function sendMessage()
    {
        $this->validate();

        if (!$this->selected_partner_id) {
            $this->dispatch('error', 'Pilih percakapan terlebih dahulu');
            return;
        }

        $customerId = Auth::id();

        // Backend Duplicate Protection (2 detik)
        $convKey = $this->is_admin_chat ? ('admin_' . ($this->selected_report_id ?? 'new')) : ('partner_' . $this->selected_partner_id . '_' . ($this->active_help_id ?? 'any'));
        $photoName = $this->photo ? $this->photo->getClientOriginalName() : '';
        $lockKey = 'send_msg_' . $customerId . '_' . $convKey . '_' . md5(($this->message ?? '') . '_' . $photoName);

        if (!Cache::add($lockKey, true, 2)) {
            return;
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('chats/photos', 'public');
        }

        $msgText = trim($this->message) ?: ($photoPath ? '[Lampiran Foto]' : '');

        // JIKA CHAT DENGAN ADMIN
        if ($this->is_admin_chat) {
            $report = $this->selected_report;

            if (!$report) {
                $report = PartnerReport::where('reporter_id', $customerId)->latest()->first();
            }

            if (!$report) {
                $report = PartnerReport::create([
                    'reporter_id' => $customerId,
                    'category'    => 'dari_customer',
                    'report_type' => 'dukungan_umum',
                    'title'       => 'Pesan Bantuan / Diskusi dengan Tim Admin',
                    'message'     => $msgText,
                    'status'      => 'pending',
                ]);
                $this->selected_report    = $report;
                $this->selected_report_id = $report->id;
            }

            PartnerReportMessage::create([
                'partner_report_id' => $report->id,
                'sender_id'         => $customerId,
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

        // CHAT REGULER DENGAN MITRA
        $mitraId = $this->selected_partner_id;

        $helpId = $this->active_help_id;
        if (!$helpId) {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();
            $helpId = $latestHelp?->id;
        }

        if (!$helpId) {
            $anyChat = ChatModel::where('customer_id', $customerId)
                ->where('mitra_id', $mitraId)
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
            'sender_type' => 'customer',
            'is_read'     => false,
        ]);

        // Chat messages are saved in chats table and checked via realtime poll, no database notifications needed
        $this->message = '';
        $this->photo   = null;
        $this->dispatch('message-sent');
        $this->dispatch('scroll-chat-bottom');
    }

    public function render()
    {
        return view('livewire.customer.chat.index', [
            'conversations' => $this->getConversations(),
            'messages'      => $this->getChatMessages(),
        ])->layout('layouts.app');
    }
}
