<?php

namespace App\Livewire\Mitra\Chat;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
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

    public ?int $userId = null;
    public $selected_partner_id = null; // ID Customer yang sedang diajak chat atau 'admin'
    public $selected_partner = null;    // Objek User Customer atau Objek Admin
    public $is_admin_chat = false;      // True jika sedang chat dengan Admin
    public $admin_tab = 'cancellation'; // 'cancellation' atau 'report'
    public $selected_cancel_request_id = null; // ID Pengajuan Pembatalan terkait jika ada
    public $selected_cancel_request = null;    // Objek Pengajuan Pembatalan terkait
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
        $this->userId = Auth::id();

        if (request()->query('cancel_request')) {
            $this->selectAdmin(null, (int) request()->query('cancel_request'));
            return;
        }

        if (request()->query('admin') || request()->query('report')) {
            $this->selectAdmin(request()->query('report') ? (int) request()->query('report') : null);
            return;
        }

        if (request()->query('customer')) {
            $this->selectPartner((int) request()->query('customer'), request()->query('help') ?? $help);
            return;
        }

        $helpId = $help ?? request()->query('help') ?? request()->route('help');

        if ($helpId) {
            $helpModel = Help::with(['user', 'mitra'])->find($helpId);

            if ($helpModel) {
                if ($helpModel->mitra_id !== Auth::id()) {
                    return redirect()->route('mitra.chat');
                }

                if ($helpModel->user_id) {
                    $this->selectPartner($helpModel->user_id, $helpModel->id);
                }
            } else {
                return redirect()->route('mitra.chat');
            }
        }
    }

    #[On('help-new-message')]
    public function onNewMessageReceived($event = null)
    {
        if ($this->selected_partner_id) {
            $this->markAsRead();
        } elseif ($this->is_admin_chat) {
            $this->markAdminMessagesAsRead();
        }

        $this->dispatch('$refresh');
        $this->dispatch('scroll-chat-bottom');
    }

    public function getConversations()
    {
        $mitraId = Auth::id();

        // 1. Percakapan Khusus dengan Tim Admin SayaBantu (Gabungan Klarifikasi Pembatalan & Laporan Aduan)
        $mitraReports = PartnerReport::where('reported_user_id', $mitraId)
            ->orWhere('reporter_id', $mitraId)
            ->orWhereHas('reportedHelp', function($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            })
            ->pluck('id');

        $mitraCancels = HelpCancelRequest::where('partner_id', $mitraId)
            ->orWhereHas('help', function($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            })
            ->pluck('id');

        $lastReportMsg = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
            ->where(function($q) use ($mitraId) {
                $q->where('sender_id', $mitraId)
                  ->orWhereIn('recipient_type', ['mitra', 'all', 'both']);
            })
            ->latest('created_at')
            ->first();

        $lastCancelMsg = HelpCancelMessage::whereIn('help_cancel_request_id', $mitraCancels)
            ->where(function($q) use ($mitraId) {
                $q->where('sender_id', $mitraId)
                  ->orWhereIn('recipient_type', ['mitra', 'all', 'both']);
            })
            ->latest('created_at')
            ->first();

        $unreadReportCount = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
            ->where('sender_id', '!=', $mitraId)
            ->where('is_read', false)
            ->whereIn('recipient_type', ['mitra', 'all', 'both'])
            ->count();

        $unreadCancelCount = HelpCancelMessage::whereIn('help_cancel_request_id', $mitraCancels)
            ->where('sender_id', '!=', $mitraId)
            ->where('is_read', false)
            ->whereIn('recipient_type', ['mitra', 'all', 'both'])
            ->count();

        $unreadAdminCount = $unreadReportCount + $unreadCancelCount;

        // Tentukan pesan terakhir yang paling baru
        $lastAdminMsg = null;
        if ($lastReportMsg && $lastCancelMsg) {
            $lastAdminMsg = $lastReportMsg->created_at->gt($lastCancelMsg->created_at) ? $lastReportMsg : $lastCancelMsg;
        } elseif ($lastReportMsg) {
            $lastAdminMsg = $lastReportMsg;
        } elseif ($lastCancelMsg) {
            $lastAdminMsg = $lastCancelMsg;
        }

        $latestMitraCancel = HelpCancelRequest::whereIn('id', $mitraCancels)->latest()->first();
        $latestMitraReport = PartnerReport::whereIn('id', $mitraReports)->latest()->first();
        $latestAdminHelp = $latestMitraCancel?->help ?? $latestMitraReport?->reportedHelp;

        $adminConversation = (object) [
            'partner' => (object) [
                'id'            => 'admin',
                'name'          => '🛡️ Tim Admin SayaBantu',
                'email'         => 'admin@sayabantu.com',
                'phone'         => 'Pusat Bantuan & Moderasi Resmi',
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
            'latest_help'  => $latestAdminHelp,
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
            $filteredCustomerIds = $customers->pluck('id');

            // Bulk eager load last messages using MAX(id) per customer
            $latestChatIds = ChatModel::where('mitra_id', $mitraId)
                ->whereIn('customer_id', $filteredCustomerIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('customer_id')
                ->pluck('id');

            $lastMessages = ChatModel::whereIn('id', $latestChatIds)
                ->get()
                ->keyBy('customer_id');

            // Bulk eager load unread counts in a single query
            $unreadCounts = ChatModel::where('mitra_id', $mitraId)
                ->whereIn('customer_id', $filteredCustomerIds)
                ->whereIn('sender_type', ['customer', 'system'])
                ->whereNull('read_at')
                ->selectRaw('customer_id, count(*) as total')
                ->groupBy('customer_id')
                ->pluck('total', 'customer_id');

            // Bulk eager load latest helps using MAX(id) per customer
            $latestHelpIds = Help::where('mitra_id', $mitraId)
                ->whereIn('user_id', $filteredCustomerIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('user_id')
                ->pluck('id');

            $latestHelps = Help::whereIn('id', $latestHelpIds)
                ->get()
                ->keyBy('user_id');

            $conversations = $customers->map(function ($customer) use ($lastMessages, $unreadCounts, $latestHelps) {
                $lastMessage = $lastMessages->get($customer->id);
                $unreadCount = (int) ($unreadCounts->get($customer->id) ?? 0);
                $latestHelp = $latestHelps->get($customer->id);

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
        if (!$this->search || str_contains(strtolower('admin tim pusat bantuan moderasi resmi'), strtolower($this->search))) {
            $conversations->prepend($adminConversation);
        }

        return $conversations;
    }

    public function selectAdmin($reportId = null, $cancelRequestId = null, $tab = null)
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

        $mitraId = Auth::id();
        $mitraReports = PartnerReport::where('reported_user_id', $mitraId)
            ->orWhere('reporter_id', $mitraId)
            ->orWhereHas('reportedHelp', fn($q) => $q->where('mitra_id', $mitraId))
            ->pluck('id');

        $mitraCancels = HelpCancelRequest::where('partner_id', $mitraId)
            ->orWhereHas('help', fn($q) => $q->where('mitra_id', $mitraId))
            ->pluck('id');

        if ($cancelRequestId) {
            $this->admin_tab                  = 'cancellation';
            $this->selected_cancel_request_id = (int) $cancelRequestId;
            $this->selected_cancel_request    = HelpCancelRequest::with(['help', 'partner', 'customer'])->find($cancelRequestId);
            $this->selected_report_id         = null;
            $this->selected_report            = null;
            $this->active_help_id             = $this->selected_cancel_request?->help_id;
            $this->active_help                = $this->selected_cancel_request?->help;
        } elseif ($reportId) {
            $this->admin_tab                  = 'report';
            $this->selected_report_id         = (int) $reportId;
            $this->selected_report            = PartnerReport::with(['reportedHelp', 'reportedUser', 'reporter'])->find($reportId);
            $this->selected_cancel_request_id = null;
            $this->selected_cancel_request    = null;
            $this->active_help_id             = $this->selected_report?->reported_help_id;
            $this->active_help                = $this->selected_report?->reportedHelp;
        } elseif ($tab) {
            $this->admin_tab                  = $tab;
            $this->selected_cancel_request_id = null;
            $this->selected_cancel_request    = null;
            $this->selected_report_id         = null;
            $this->selected_report            = null;
            $this->active_help_id             = null;
            $this->active_help                = null;
        } else {
            $this->selected_cancel_request_id = null;
            $this->selected_cancel_request    = null;
            $this->selected_report_id         = null;
            $this->selected_report            = null;
            $this->active_help_id             = null;
            $this->active_help                = null;
        }

        $this->markAdminMessagesAsRead();
        $this->dispatch('scroll-chat-bottom');
    }

    public function switchAdminTab(string $tab)
    {
        $this->selectAdmin(null, null, $tab);
    }

    public function selectAdminCancel(int $cancelId)
    {
        $this->selectAdmin(null, $cancelId);
    }

    public function selectAdminReport(int $reportId)
    {
        $this->selectAdmin($reportId, null);
    }

    public function unselectAdminIssue()
    {
        $this->selected_cancel_request_id = null;
        $this->selected_cancel_request    = null;
        $this->selected_report_id         = null;
        $this->selected_report            = null;
        $this->active_help_id             = null;
        $this->active_help                = null;
        $this->message                    = '';
        $this->photo                      = null;
    }

    public function markAdminMessagesAsRead()
    {
        $mitraId = Auth::id();
        if ($this->admin_tab === 'cancellation' && $this->selected_cancel_request_id) {
            HelpCancelMessage::where('help_cancel_request_id', $this->selected_cancel_request_id)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($this->admin_tab === 'report' && $this->selected_report_id) {
            PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function selectPartner($customerId, $helpId = null)
    {
        $this->selected_partner_id        = (int) $customerId;
        $this->is_admin_chat              = false;
        $this->selected_cancel_request_id = null;
        $this->selected_cancel_request    = null;
        $this->selected_report_id         = null;
        $this->selected_report            = null;
        $this->selected_partner           = User::find($customerId);

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
            ->whereIn('sender_type', ['customer', 'system'])
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

        // JIKA CHAT DENGAN ADMIN (RUANG 🛡️ TIM ADMIN SAYABANTU)
        if ($this->is_admin_chat) {
            $list = collect();

            if ($this->admin_tab === 'cancellation') {
                if ($this->selected_cancel_request_id) {
                    $cancelMsgs = HelpCancelMessage::where('help_cancel_request_id', $this->selected_cancel_request_id)
                        ->where(function ($q) use ($userId) {
                            $q->where('sender_id', $userId)
                              ->orWhere(function ($sub) use ($userId) {
                                  $sub->where('sender_id', '!=', $userId)
                                      ->whereIn('recipient_type', ['mitra', 'all', 'both']);
                              });
                        })
                        ->with(['sender', 'cancelRequest.help'])
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($cancelMsgs as $m) {
                        $list->push((object)[
                            'id'          => 'cancel_' . $m->id,
                            'raw_id'      => $m->id,
                            'topic_type'  => 'cancellation',
                            'message'     => $m->message,
                            'photo'       => $m->photo,
                            'sender_type' => $m->isFromAdmin() ? 'admin' : 'mitra',
                            'sender_name' => $m->isFromAdmin() ? '🛡️ Tim Admin SayaBantu' : ($m->sender?->name ?? 'Anda'),
                            'created_at'  => $m->created_at,
                            'help_id'     => $m->cancelRequest?->help_id,
                            'help'        => $m->cancelRequest?->help,
                            'is_admin'    => $m->isFromAdmin(),
                            'is_read'     => (bool) $m->is_read,
                            'read_at'     => $m->read_at,
                        ]);
                    }
                }
            } else {
                if ($this->selected_report_id) {
                    $reportMsgs = PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                        ->where(function ($q) use ($userId) {
                            $q->where('sender_id', $userId)
                              ->orWhere(function ($sub) use ($userId) {
                                  $sub->where('sender_id', '!=', $userId)
                                      ->whereIn('recipient_type', ['mitra', 'all', 'both']);
                              });
                        })
                        ->with(['sender', 'report.reportedHelp'])
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($reportMsgs as $m) {
                        $list->push((object)[
                            'id'          => 'report_' . $m->id,
                            'raw_id'      => $m->id,
                            'topic_type'  => 'report',
                            'message'     => $m->message,
                            'photo'       => $m->photo,
                            'sender_type' => $m->isFromAdmin() ? 'admin' : 'mitra',
                            'sender_name' => $m->isFromAdmin() ? '🛡️ Tim Admin SayaBantu' : ($m->sender?->name ?? 'Anda'),
                            'created_at'  => $m->created_at,
                            'help_id'     => $m->report?->reported_help_id,
                            'help'        => $m->report?->reportedHelp,
                            'is_admin'    => $m->isFromAdmin(),
                            'is_read'     => (bool) $m->is_read,
                            'read_at'     => $m->read_at,
                        ]);
                    }
                }
            }

            return $list->sortBy('created_at')->values();
        }

        // CHAT REGULER DENGAN CUSTOMER
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
        if ($this->is_admin_chat && ($this->selected_cancel_request_id || $this->selected_report_id)) {
            $this->unselectAdminIssue();
            return;
        }

        $this->selected_partner_id        = null;
        $this->selected_partner           = null;
        $this->is_admin_chat              = false;
        $this->selected_cancel_request_id = null;
        $this->selected_cancel_request    = null;
        $this->selected_report_id         = null;
        $this->selected_report            = null;
        $this->active_help_id             = null;
        $this->active_help                = null;
        $this->message                    = '';
        $this->photo                      = null;
    }

    public function removePhoto()
    {
        $this->photo = null;
    }

    #[On('echo-private:user.{userId},ChatMessageSent')]
    #[On('help-new-message')]
    #[On('refresh-chat')]
    public function handleNewIncomingMessage($event = null)
    {
        if ($this->selected_partner_id && !$this->is_admin_chat) {
            $updated = ChatModel::where('mitra_id', Auth::id())
                ->where('customer_id', $this->selected_partner_id)
                ->whereIn('sender_type', ['customer', 'system'])
                ->whereNull('read_at')
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            if ($updated > 0) {
                $this->dispatch('chat-messages-read');
            }
            $this->dispatch('scroll-chat-bottom');
        } elseif ($this->is_admin_chat) {
            $this->markAdminMessagesAsRead();
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

        $mitraId = Auth::id();

        // Backend Duplicate Protection (2 detik)
        $convKey = $this->is_admin_chat
            ? ('admin_mitra_' . $this->admin_tab . '_' . ($this->selected_cancel_request_id ?? '0') . '_' . ($this->selected_report_id ?? '0'))
            : ('partner_' . $this->selected_partner_id . '_' . ($this->active_help_id ?? 'any'));

        $photoName = $this->photo ? $this->photo->getClientOriginalName() : '';
        $lockKey = 'send_msg_' . $mitraId . '_' . $convKey . '_' . md5(($this->message ?? '') . '_' . $photoName);

        if (!Cache::add($lockKey, true, 2)) {
            return;
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('chats/photos', 'public');
        }

        $msgText = trim($this->message) ?: ($photoPath ? '[Lampiran Foto Bukti]' : '');

        // JIKA CHAT DENGAN ADMIN
        if ($this->is_admin_chat) {
            if ($this->admin_tab === 'cancellation') {
                $cancelReq = $this->selected_cancel_request ?? ($this->selected_cancel_request_id ? HelpCancelRequest::find($this->selected_cancel_request_id) : null);
                
                if (!$cancelReq) {
                    $cancelReq = HelpCancelRequest::where('partner_id', $mitraId)->where('status', 'pending')->latest()->first();
                }

                if ($cancelReq) {
                    HelpCancelMessage::create([
                        'help_cancel_request_id' => $cancelReq->id,
                        'sender_id'              => $mitraId,
                        'recipient_type'         => 'admin',
                        'message'                => $msgText,
                        'photo'                  => $photoPath,
                        'is_read'                => false,
                    ]);

                    $this->selected_cancel_request_id = $cancelReq->id;
                    $this->selected_cancel_request    = $cancelReq;
                    $this->message                    = '';
                    $this->photo                      = null;
                    $this->dispatch('message-sent');
                    $this->dispatch('scroll-chat-bottom');
                    return;
                } else {
                    $this->dispatch('error', 'Tidak ada pengajuan pembatalan aktif untuk dikirimi pesan.');
                    return;
                }
            } else {
                $report = $this->selected_report ?? ($this->selected_report_id ? PartnerReport::find($this->selected_report_id) : null);

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

                $this->selected_report_id = $report->id;
                $this->selected_report    = $report;
                $this->message            = '';
                $this->photo              = null;
                $this->dispatch('message-sent');
                $this->dispatch('scroll-chat-bottom');
                return;
            }
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
            $this->dispatch('error', 'Tidak dapat mengirim pesan: Pesanan bantuan terkait tidak ditemukan.');
            return;
        }

        $chat = ChatModel::create([
            'customer_id' => $customerId,
            'mitra_id'    => $mitraId,
            'help_id'     => $helpId,
            'sender_type' => 'mitra',
            'message'     => $msgText,
            'photo'       => $photoPath,
            'is_read'     => false,
        ]);

        $this->active_help_id = $helpId;

        $this->message = '';
        $this->photo   = null;

        $this->dispatch('message-sent');
        $this->dispatch('scroll-chat-bottom');
    }

    public function render()
    {
        $mitraId = Auth::id();
        $userCancelRequests = collect();
        $userReports = collect();

        if ($this->is_admin_chat) {
            $userCancelRequests = HelpCancelRequest::where('partner_id', $mitraId)
                ->orWhereHas('help', fn($q) => $q->where('mitra_id', $mitraId))
                ->with(['help', 'partner', 'customer'])
                ->latest()
                ->get()
                ->map(function ($cReq) use ($mitraId) {
                    $lastMsg = HelpCancelMessage::where('help_cancel_request_id', $cReq->id)
                        ->where(function($q) use ($mitraId) {
                            $q->where('sender_id', $mitraId)
                              ->orWhere('recipient_type', 'mitra')
                              ->orWhere('recipient_type', 'all');
                        })
                        ->latest('created_at')
                        ->first();

                    $unread = HelpCancelMessage::where('help_cancel_request_id', $cReq->id)
                        ->whereIn('recipient_type', ['mitra', 'all'])
                        ->where('sender_id', '!=', $mitraId)
                        ->where('is_read', false)
                        ->count();

                    $cReq->last_message = $lastMsg;
                    $cReq->unread_count = $unread;
                    return $cReq;
                });

            $userReports = PartnerReport::where('reported_user_id', $mitraId)
                ->orWhere('reporter_id', $mitraId)
                ->orWhereHas('reportedHelp', fn($q) => $q->where('mitra_id', $mitraId))
                ->with(['reportedHelp', 'reportedUser', 'reporter'])
                ->latest()
                ->get()
                ->map(function ($rep) use ($mitraId) {
                    $lastMsg = PartnerReportMessage::where('partner_report_id', $rep->id)
                        ->where(function($q) use ($mitraId) {
                            $q->where('sender_id', $mitraId)
                              ->orWhere('recipient_type', 'mitra')
                              ->orWhere('recipient_type', 'all');
                        })
                        ->latest('created_at')
                        ->first();

                    $unread = PartnerReportMessage::where('partner_report_id', $rep->id)
                        ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                        ->where('sender_id', '!=', $mitraId)
                        ->where('is_read', false)
                        ->count();

                    $rep->last_message = $lastMsg;
                    $rep->unread_count = $unread;
                    return $rep;
                });
        }

        return view('livewire.mitra.chat.index', [
            'conversations'       => $this->getConversations(),
            'messages'            => $this->getChatMessages(),
            'userCancelRequests'  => $userCancelRequests,
            'userReports'         => $userReports,
        ])->layout('layouts.mitra');
    }
}
