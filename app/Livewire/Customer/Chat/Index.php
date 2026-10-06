<?php

namespace App\Livewire\Customer\Chat;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public ?int $userId = null;
    public $selected_partner_id = null; // ID Mitra yang sedang diajak chat atau 'admin'
    public $selected_partner = null;    // Objek User Mitra atau Objek Admin
    public $is_admin_chat = false;      // True jika sedang chat dengan Admin
    public $admin_tab = 'support';      // Canonical default: 'support', 'cancellation', atau 'report'
    public $selected_cancel_request_id = null; // ID Pengajuan Pembatalan terkait jika ada
    public $selected_cancel_request = null;    // Objek Pengajuan Pembatalan terkait
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
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:1536',
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

    #[On('help-new-message')]
    public function onNewMessageReceived($event = null)
    {
        if ($this->selected_partner_id && !$this->is_admin_chat) {
            $this->markAsRead();
        } elseif ($this->is_admin_chat) {
            $this->markAdminMessagesAsRead();
        }

        $this->dispatch('$refresh');
        $this->dispatch('scroll-chat-bottom');
    }

    /**
     * Resolves an authenticated customer's legitimate cancellation request (Fail-closed IDOR protection)
     */
    protected function resolveCustomerCancellation(int $cancelRequestId): ?HelpCancelRequest
    {
        $userId = Auth::id();
        return HelpCancelRequest::where('id', $cancelRequestId)
            ->where(function ($q) use ($userId) {
                $q->where('customer_id', $userId)
                  ->orWhereHas('help', fn($h) => $h->where('user_id', $userId));
            })
            ->with(['help', 'partner', 'customer'])
            ->first();
    }

    /**
     * Resolves an authenticated customer's legitimate report (Fail-closed IDOR protection)
     */
    protected function resolveCustomerReport(int $reportId): ?PartnerReport
    {
        $userId = Auth::id();
        return PartnerReport::where('id', $reportId)
            ->where(function ($q) use ($userId) {
                $q->where('reporter_id', $userId)
                  ->orWhereHas('reportedHelp', fn($h) => $h->where('user_id', $userId));
            })
            ->with(['reportedHelp', 'reportedUser', 'reporter'])
            ->first();
    }

    /**
     * Resolves canonical active general support report (dukungan_umum, help_id = null)
     */
    protected function resolveCustomerSupportReport(): ?PartnerReport
    {
        $userId = Auth::id();
        return PartnerReport::where('reporter_id', $userId)
            ->where('report_type', 'dukungan_umum')
            ->where('category', 'dari_customer')
            ->whereIn('status', ['pending', 'in_progress', 'under_review', 'investigating', 'proses'])
            ->latest()
            ->first();
    }

    public function getConversations()
    {
        $userId = Auth::id();

        // 1. Percakapan Khusus dengan Tim Admin SayaBantu (Terpisah per-kanal, tanpa binding Help)
        $supportReports = PartnerReport::where('reporter_id', $userId)
            ->where('report_type', 'dukungan_umum')
            ->pluck('id');

        $investigationReports = PartnerReport::where(function($q) use ($userId) {
                $q->where('reporter_id', $userId)
                  ->orWhereHas('reportedHelp', fn($sub) => $sub->where('user_id', $userId));
            })
            ->where('report_type', '!=', 'dukungan_umum')
            ->pluck('id');

        $customerCancels = HelpCancelRequest::where('customer_id', $userId)
            ->orWhereHas('help', fn($q) => $q->where('user_id', $userId))
            ->pluck('id');

        $lastSupportMsg = PartnerReportMessage::whereIn('partner_report_id', $supportReports)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhereIn('recipient_type', ['customer', 'all', 'both']);
            })
            ->latest('created_at')
            ->first();

        $lastReportMsg = PartnerReportMessage::whereIn('partner_report_id', $investigationReports)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhereIn('recipient_type', ['customer', 'all', 'both']);
            })
            ->latest('created_at')
            ->first();

        $lastCancelMsg = HelpCancelMessage::whereIn('help_cancel_request_id', $customerCancels)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhereIn('recipient_type', ['customer', 'all', 'both']);
            })
            ->latest('created_at')
            ->first();

        $unreadSupportCount = PartnerReportMessage::whereIn('partner_report_id', $supportReports)
            ->where('sender_id', '!=', $userId)
            ->whereNull('customer_read_at')
            ->whereIn('recipient_type', ['customer', 'all', 'both'])
            ->count();

        $unreadReportCount = PartnerReportMessage::whereIn('partner_report_id', $investigationReports)
            ->where('sender_id', '!=', $userId)
            ->whereNull('customer_read_at')
            ->whereIn('recipient_type', ['customer', 'all', 'both'])
            ->count();

        $unreadCancelCount = HelpCancelMessage::whereIn('help_cancel_request_id', $customerCancels)
            ->where('sender_id', '!=', $userId)
            ->whereNull('customer_read_at')
            ->whereIn('recipient_type', ['customer', 'all', 'both'])
            ->count();

        $unreadAdminCount = $unreadSupportCount + $unreadReportCount + $unreadCancelCount;

        // Tentukan pesan terakhir yang paling baru di antara semua kanal admin
        $allAdminMsgs = collect([$lastSupportMsg, $lastReportMsg, $lastCancelMsg])->filter()->sortByDesc('created_at');
        $lastAdminMsg = $allAdminMsgs->first();

        $adminConversation = (object) [
            'partner' => (object) [
                'id'            => 'admin',
                'name'          => 'Tim Admin SayaBantu',
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
            'latest_help'  => null, // STRICTLY DECOUPLED FROM ANY HELP (CH1 Section 3)
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

            $latestChatIds = ChatModel::where('customer_id', $userId)
                ->whereIn('mitra_id', $filteredMitraIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('mitra_id')
                ->pluck('id');

            $lastMessages = ChatModel::whereIn('id', $latestChatIds)
                ->get()
                ->keyBy('mitra_id');

            $unreadCounts = ChatModel::where('customer_id', $userId)
                ->whereIn('mitra_id', $filteredMitraIds)
                ->whereIn('sender_type', ['mitra', 'system'])
                ->whereNull('read_at')
                ->selectRaw('mitra_id, count(*) as total')
                ->groupBy('mitra_id')
                ->pluck('total', 'mitra_id');

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
            'name'          => 'Tim Admin SayaBantu',
            'email'         => 'admin@sayabantu.com',
            'phone'         => 'Pusat Bantuan & Moderasi Resmi',
            'profile_photo' => null,
            'selfie_photo'  => null,
            'is_admin'      => true,
        ];
        $this->unassigned_help     = null;

        // 1. Explicit Cancellation
        if ($cancelRequestId) {
            $cancelReq = $this->resolveCustomerCancellation((int) $cancelRequestId);
            if ($cancelReq) {
                $this->admin_tab                  = 'cancellation';
                $this->selected_cancel_request_id = $cancelReq->id;
                $this->selected_cancel_request    = $cancelReq;
                $this->selected_report_id         = null;
                $this->selected_report            = null;
                $this->active_help_id             = $cancelReq->help_id;
                $this->active_help                = $cancelReq->help;
            } else {
                // Fail closed
                $this->admin_tab                  = 'cancellation';
                $this->selected_cancel_request_id = null;
                $this->selected_cancel_request    = null;
                $this->selected_report_id         = null;
                $this->selected_report            = null;
                $this->active_help_id             = null;
                $this->active_help                = null;
            }
        }
        // 2. Explicit Report
        elseif ($reportId) {
            $rep = $this->resolveCustomerReport((int) $reportId);
            if ($rep) {
                if ($rep->report_type === 'dukungan_umum') {
                    $this->admin_tab                  = 'support';
                    $this->selected_report_id         = $rep->id;
                    $this->selected_report            = $rep;
                    $this->selected_cancel_request_id = null;
                    $this->selected_cancel_request    = null;
                    $this->active_help_id             = null;
                    $this->active_help                = null;
                } else {
                    $this->admin_tab                  = 'report';
                    $this->selected_report_id         = $rep->id;
                    $this->selected_report            = $rep;
                    $this->selected_cancel_request_id = null;
                    $this->selected_cancel_request    = null;
                    $this->active_help_id             = $rep->reported_help_id;
                    $this->active_help                = $rep->reportedHelp;
                }
            } else {
                // Fail closed
                $this->admin_tab                  = 'report';
                $this->selected_report_id         = null;
                $this->selected_report            = null;
                $this->selected_cancel_request_id = null;
                $this->selected_cancel_request    = null;
                $this->active_help_id             = null;
                $this->active_help                = null;
            }
        }
        // 3. Tab Switch
        elseif ($tab) {
            $this->admin_tab                  = in_array($tab, ['support', 'cancellation', 'report'], true) ? $tab : 'support';
            $this->selected_cancel_request_id = null;
            $this->selected_cancel_request    = null;
            $this->active_help_id             = null;
            $this->active_help                = null;

            if ($this->admin_tab === 'support') {
                $supportRep = $this->resolveCustomerSupportReport();
                $this->selected_report_id = $supportRep?->id;
                $this->selected_report    = $supportRep;
            } else {
                $this->selected_report_id = null;
                $this->selected_report    = null;
            }
        }
        // 4. No arguments -> Default to 'support' (CH1 Section 8)
        else {
            $this->admin_tab                  = 'support';
            $this->selected_cancel_request_id = null;
            $this->selected_cancel_request    = null;
            $this->active_help_id             = null;
            $this->active_help                = null;

            $supportRep = $this->resolveCustomerSupportReport();
            $this->selected_report_id = $supportRep?->id;
            $this->selected_report    = $supportRep;
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

        if ($this->admin_tab === 'support') {
            $supportRep = $this->resolveCustomerSupportReport();
            $this->selected_report_id = $supportRep?->id;
            $this->selected_report    = $supportRep;
        }
    }

    public function markAdminMessagesAsRead()
    {
        $userId = Auth::id();
        $updated = false;

        if ($this->admin_tab === 'cancellation' && $this->selected_cancel_request_id) {
            $updated = HelpCancelMessage::where('help_cancel_request_id', $this->selected_cancel_request_id)
                ->where('sender_id', '!=', $userId)
                ->whereIn('recipient_type', ['customer', 'all', 'both'])
                ->whereNull('customer_read_at')
                ->update(['customer_read_at' => now()]) > 0;

            HelpCancelMessage::where('help_cancel_request_id', $this->selected_cancel_request_id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->where(fn($sub) => $sub->where('recipient_type', 'customer')->whereNotNull('customer_read_at'))
                      ->orWhere(fn($sub) => $sub->whereIn('recipient_type', ['all', 'both'])->whereNotNull('customer_read_at')->whereNotNull('mitra_read_at'));
                })
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($this->admin_tab === 'report' && $this->selected_report_id) {
            $updated = PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('sender_id', '!=', $userId)
                ->whereIn('recipient_type', ['customer', 'all', 'both'])
                ->whereNull('customer_read_at')
                ->update(['customer_read_at' => now()]) > 0;

            PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->where(fn($sub) => $sub->where('recipient_type', 'customer')->whereNotNull('customer_read_at'))
                      ->orWhere(fn($sub) => $sub->whereIn('recipient_type', ['all', 'both'])->whereNotNull('customer_read_at')->whereNotNull('mitra_read_at'));
                })
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($this->admin_tab === 'support' && $this->selected_report_id) {
            $updated = PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('sender_id', '!=', $userId)
                ->whereIn('recipient_type', ['customer', 'all', 'both'])
                ->whereNull('customer_read_at')
                ->update(['customer_read_at' => now()]) > 0;

            PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->where(fn($sub) => $sub->where('recipient_type', 'customer')->whereNotNull('customer_read_at'))
                      ->orWhere(fn($sub) => $sub->whereIn('recipient_type', ['all', 'both'])->whereNotNull('customer_read_at')->whereNotNull('mitra_read_at'));
                })
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        if ($updated) {
            $this->dispatch('chat-messages-read');
            $this->dispatch('refresh-chat-icon');
        }
    }

    public function selectPartner($mitraId, $helpId = null)
    {
        $userId = Auth::id();
        $this->selected_partner_id        = (int) $mitraId;
        $this->is_admin_chat              = false;
        $this->selected_cancel_request_id = null;
        $this->selected_cancel_request    = null;
        $this->selected_report_id         = null;
        $this->selected_report            = null;
        $this->selected_partner           = User::where('id', $mitraId)->first();
        $this->unassigned_help            = null;

        if ($helpId) {
            $helpModel = Help::where('id', $helpId)
                ->where('user_id', $userId)
                ->where('mitra_id', $mitraId)
                ->first();

            $this->active_help_id = $helpModel?->id;
            $this->active_help    = $helpModel;
        } else {
            $latestHelp = Help::where('user_id', $userId)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();

            $this->active_help_id = $latestHelp?->id;
            $this->active_help    = $latestHelp;
        }

        ChatModel::where('customer_id', $userId)
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

        // JIKA CHAT DENGAN ADMIN (RUANG TIM ADMIN SAYABANTU - STRICT CHANNELS)
        if ($this->is_admin_chat) {
            $list = collect();

            if ($this->admin_tab === 'support') {
                if ($this->selected_report_id) {
                    $supportMsgs = PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                        ->where(function ($q) use ($userId) {
                            $q->where('sender_id', $userId)
                              ->orWhere(function ($sub) use ($userId) {
                                  $sub->where('sender_id', '!=', $userId)
                                      ->whereIn('recipient_type', ['customer', 'all', 'both']);
                              });
                        })
                        ->with(['sender'])
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($supportMsgs as $m) {
                        $list->push((object)[
                            'id'          => 'support_' . $m->id,
                            'raw_id'      => $m->id,
                            'topic_type'  => 'support',
                            'message'     => $m->message,
                            'photo'       => $m->photo,
                            'sender_type' => $m->isFromAdmin() ? 'admin' : 'customer',
                            'sender_name' => $m->isFromAdmin() ? 'Tim Admin SayaBantu' : ($m->sender?->name ?? 'Anda'),
                            'created_at'  => $m->created_at,
                            'help_id'     => null,
                            'help'        => null,
                            'is_admin'    => $m->isFromAdmin(),
                            'is_read'     => (bool) $m->is_read,
                            'read_at'     => $m->read_at,
                        ]);
                    }
                }
            } elseif ($this->admin_tab === 'cancellation') {
                if ($this->selected_cancel_request_id) {
                    $cancelReq = $this->resolveCustomerCancellation((int) $this->selected_cancel_request_id);
                    if ($cancelReq) {
                        $cancelMsgs = HelpCancelMessage::where('help_cancel_request_id', $cancelReq->id)
                            ->where(function ($q) use ($userId) {
                                $q->where('sender_id', $userId)
                                  ->orWhere(function ($sub) use ($userId) {
                                      $sub->where('sender_id', '!=', $userId)
                                          ->whereIn('recipient_type', ['customer', 'all', 'both']);
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
                                'sender_type' => $m->isFromAdmin() ? 'admin' : 'customer',
                                'sender_name' => $m->isFromAdmin() ? 'Tim Admin SayaBantu' : ($m->sender?->name ?? 'Anda'),
                                'created_at'  => $m->created_at,
                                'help_id'     => $m->cancelRequest?->help_id,
                                'help'        => $m->cancelRequest?->help,
                                'is_admin'    => $m->isFromAdmin(),
                                'is_read'     => (bool) $m->is_read,
                                'read_at'     => $m->read_at,
                            ]);
                        }
                    }
                }
            } elseif ($this->admin_tab === 'report') {
                if ($this->selected_report_id) {
                    $rep = $this->resolveCustomerReport((int) $this->selected_report_id);
                    if ($rep && $rep->report_type !== 'dukungan_umum') {
                        $reportMsgs = PartnerReportMessage::where('partner_report_id', $rep->id)
                            ->where(function ($q) use ($userId) {
                                $q->where('sender_id', $userId)
                                  ->orWhere(function ($sub) use ($userId) {
                                      $sub->where('sender_id', '!=', $userId)
                                          ->whereIn('recipient_type', ['customer', 'all', 'both']);
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
                                'sender_type' => $m->isFromAdmin() ? 'admin' : 'customer',
                                'sender_name' => $m->isFromAdmin() ? 'Tim Admin SayaBantu' : ($m->sender?->name ?? 'Anda'),
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
            }

            return $list->sortBy('created_at')->values();
        }

        // CHAT REGULER DENGAN MITRA
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
        $this->unassigned_help            = null;
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

        $customerId = Auth::id();

        // Backend Duplicate Protection (2 detik)
        $convKey = $this->is_admin_chat
            ? ('admin_' . $this->admin_tab . '_' . ($this->selected_cancel_request_id ?? '0') . '_' . ($this->selected_report_id ?? '0'))
            : ('partner_' . $this->selected_partner_id . '_' . ($this->active_help_id ?? 'any'));

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

        // JIKA CHAT DENGAN ADMIN (STRICT FIRST-LEVEL CHANNEL BRANCHING - CH1 Section 9)
        if ($this->is_admin_chat) {
            switch ($this->admin_tab) {
                case 'support':
                    $this->sendSupportMessage($msgText, $photoPath);
                    break;

                case 'cancellation':
                    $this->sendCancellationMessage($msgText, $photoPath);
                    break;

                case 'report':
                    $this->sendInvestigationMessage($msgText, $photoPath);
                    break;

                default:
                    $this->dispatch('error', 'Kanal percakapan admin tidak valid.');
                    break;
            }
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
            $this->dispatch('error', 'Tidak dapat mengirim pesan: Pesanan bantuan terkait tidak ditemukan.');
            return;
        }

        ChatModel::create([
            'customer_id' => $customerId,
            'mitra_id'    => $mitraId,
            'sender_id'   => $customerId,
            'help_id'     => $helpId,
            'sender_type' => 'customer',
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

    /**
     * Mode Support: Canonical general support (dukungan_umum, help_id = null)
     */
    protected function sendSupportMessage(string $msgText, ?string $photoPath): void
    {
        $customerId = Auth::id();

        $report = DB::transaction(function () use ($customerId, $msgText, $photoPath) {
            $lockedUser = User::where('id', $customerId)->lockForUpdate()->firstOrFail();

            $activeReport = PartnerReport::where('reporter_id', $lockedUser->id)
                ->where('report_type', 'dukungan_umum')
                ->where('category', 'dari_customer')
                ->whereIn('status', ['pending', 'in_progress', 'under_review', 'investigating', 'proses'])
                ->latest()
                ->first();

            if (!$activeReport) {
                $activeReport = PartnerReport::create([
                    'reporter_id' => $lockedUser->id,
                    'category'    => 'dari_customer',
                    'report_type' => 'dukungan_umum',
                    'title'       => 'Pusat Bantuan / Konsultasi Pelanggan',
                    'message'     => $msgText,
                    'status'      => 'pending',
                ]);
            }

            PartnerReportMessage::create([
                'partner_report_id' => $activeReport->id,
                'sender_id'         => $lockedUser->id,
                'recipient_type'    => 'admin',
                'message'           => $msgText,
                'photo'             => $photoPath,
                'is_read'           => false,
                'customer_read_at'  => now(),
            ]);

            if ($activeReport->status === 'pending') {
                $activeReport->update(['status' => 'in_progress']);
            }

            return $activeReport;
        });

        $this->selected_report_id = $report->id;
        $this->selected_report    = $report;
        $this->message            = '';
        $this->photo              = null;

        $this->dispatch('message-sent');
        $this->dispatch('scroll-chat-bottom');
    }

    /**
     * Mode Cancellation: Requires explicit valid cancellation selection (NO fallback!)
     */
    protected function sendCancellationMessage(string $msgText, ?string $photoPath): void
    {
        $customerId = Auth::id();

        if (!$this->selected_cancel_request_id) {
            $this->dispatch('error', 'Pilih pengajuan pembatalan yang ingin ditinjau terlebih dahulu.');
            return;
        }

        $cancelReq = $this->resolveCustomerCancellation((int) $this->selected_cancel_request_id);

        if (!$cancelReq) {
            $this->dispatch('error', 'Pengajuan pembatalan tidak ditemukan atau tidak memiliki akses.');
            return;
        }

        HelpCancelMessage::create([
            'help_cancel_request_id' => $cancelReq->id,
            'sender_id'              => $customerId,
            'recipient_type'         => 'admin',
            'message'                => $msgText,
            'photo'                  => $photoPath,
            'is_read'                => false,
            'customer_read_at'       => now(),
        ]);

        $this->selected_cancel_request_id = $cancelReq->id;
        $this->selected_cancel_request    = $cancelReq;
        $this->message                    = '';
        $this->photo                      = null;

        $this->dispatch('message-sent');
        $this->dispatch('scroll-chat-bottom');
    }

    /**
     * Mode Investigation: Keeps exact same investigation report (NO dukungan_umum fallback!)
     */
    protected function sendInvestigationMessage(string $msgText, ?string $photoPath): void
    {
        $customerId = Auth::id();

        if (!$this->selected_report_id) {
            $this->dispatch('error', 'Pilih laporan aduan yang ingin Anda tanggapi terlebih dahulu.');
            return;
        }

        $report = $this->resolveCustomerReport((int) $this->selected_report_id);

        if (!$report || $report->report_type === 'dukungan_umum') {
            $this->dispatch('error', 'Laporan aduan investigasi tidak valid atau Anda tidak memiliki akses.');
            return;
        }

        PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $customerId,
            'recipient_type'    => 'admin',
            'message'           => $msgText,
            'photo'             => $photoPath,
            'is_read'           => false,
            'customer_read_at'  => now(),
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
    }

    public function render()
    {
        $userId = Auth::id();
        $userCancelRequests = collect();
        $userReports = collect();

        if ($this->is_admin_chat) {
            $userCancelRequests = HelpCancelRequest::where('customer_id', $userId)
                ->orWhereHas('help', fn($q) => $q->where('user_id', $userId))
                ->with(['help', 'partner', 'customer'])
                ->latest()
                ->get()
                ->map(function ($cReq) use ($userId) {
                    $lastMsg = HelpCancelMessage::where('help_cancel_request_id', $cReq->id)
                        ->where(function($q) use ($userId) {
                            $q->where('sender_id', $userId)
                              ->orWhere('recipient_type', 'customer')
                              ->orWhere('recipient_type', 'all');
                        })
                        ->latest('created_at')
                        ->first();

                    $unread = HelpCancelMessage::where('help_cancel_request_id', $cReq->id)
                        ->whereIn('recipient_type', ['customer', 'all', 'both'])
                        ->where('sender_id', '!=', $userId)
                        ->whereNull('customer_read_at')
                        ->count();

                    $cReq->last_message = $lastMsg;
                    $cReq->unread_count = $unread;
                    return $cReq;
                });

            $userReports = PartnerReport::where(function ($q) use ($userId) {
                    $q->where('reporter_id', $userId)
                      ->orWhereHas('reportedHelp', fn($sub) => $sub->where('user_id', $userId));
                })
                ->where('report_type', '!=', 'dukungan_umum')
                ->with(['reportedHelp', 'reportedUser', 'reporter'])
                ->latest()
                ->get()
                ->map(function ($rep) use ($userId) {
                    $lastMsg = PartnerReportMessage::where('partner_report_id', $rep->id)
                        ->where(function($q) use ($userId) {
                            $q->where('sender_id', $userId)
                              ->orWhere('recipient_type', 'customer')
                              ->orWhere('recipient_type', 'all')
                              ->orWhere('recipient_type', 'both');
                        })
                        ->latest('created_at')
                        ->first();

                    $unread = PartnerReportMessage::where('partner_report_id', $rep->id)
                        ->whereIn('recipient_type', ['customer', 'all', 'both'])
                        ->where('sender_id', '!=', $userId)
                        ->whereNull('customer_read_at')
                        ->count();

                    $rep->last_message = $lastMsg;
                    $rep->unread_count = $unread;
                    return $rep;
                });
        }

        return view('livewire.customer.chat.index', [
            'conversations'       => $this->getConversations(),
            'messages'            => $this->getChatMessages(),
            'userCancelRequests'  => $userCancelRequests,
            'userReports'         => $userReports,
        ])->layout('layouts.app');
    }
}
