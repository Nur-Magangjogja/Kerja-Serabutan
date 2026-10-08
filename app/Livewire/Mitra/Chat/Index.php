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
use Illuminate\Support\Facades\DB;
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
    public $admin_tab = 'support';      // Canonical default: 'support', 'cancellation', atau 'report'
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

    /**
     * Resolves an authenticated mitra's legitimate cancellation request (Fail-closed IDOR protection)
     */
    protected function resolveMitraCancellation(int $cancelRequestId): ?HelpCancelRequest
    {
        $mitraId = Auth::id();
        return HelpCancelRequest::where('id', $cancelRequestId)
            ->where(function ($q) use ($mitraId) {
                $q->where('partner_id', $mitraId)
                  ->orWhereHas('help', fn($h) => $h->where('mitra_id', $mitraId));
            })
            ->with(['help', 'partner', 'customer'])
            ->first();
    }

    /**
     * Resolves an authenticated mitra's legitimate report (Fail-closed IDOR protection)
     */
    protected function resolveMitraReport(int $reportId): ?PartnerReport
    {
        $mitraId = Auth::id();
        return PartnerReport::where('id', $reportId)
            ->where(function ($q) use ($mitraId) {
                $q->where('reporter_id', $mitraId)
                  ->orWhereHas('reportedHelp', fn($h) => $h->where('mitra_id', $mitraId));
            })
            ->with(['reportedHelp', 'reportedUser', 'reporter'])
            ->first();
    }

    /**
     * Resolves canonical active general support report (dukungan_umum, help_id = null)
     */
    protected function resolveMitraSupportReport(): ?PartnerReport
    {
        $mitraId = Auth::id();
        return PartnerReport::where('reporter_id', $mitraId)
            ->where('report_type', 'dukungan_umum')
            ->where('category', 'dari_mitra')
            ->first();
    }

    public function getConversations()
    {
        $mitraId = Auth::id();

        // 1. Percakapan Khusus dengan Tim Admin SayaBantu (Terpisah per-kanal, tanpa binding Help)
        $supportReports = PartnerReport::where('reporter_id', $mitraId)
            ->where('report_type', 'dukungan_umum')
            ->pluck('id');

        $investigationReports = PartnerReport::where(function($q) use ($mitraId) {
                $q->where('reporter_id', $mitraId)
                  ->orWhereHas('reportedHelp', fn($sub) => $sub->where('mitra_id', $mitraId));
            })
            ->where('report_type', '!=', 'dukungan_umum')
            ->pluck('id');

        $mitraCancels = HelpCancelRequest::where('partner_id', $mitraId)
            ->orWhereHas('help', fn($q) => $q->where('mitra_id', $mitraId))
            ->pluck('id');

        $lastSupportMsg = PartnerReportMessage::whereIn('partner_report_id', $supportReports)
            ->where(function($q) use ($mitraId) {
                $q->where('sender_id', $mitraId)
                  ->orWhereIn('recipient_type', ['mitra', 'all', 'both']);
            })
            ->latest('created_at')
            ->first();

        $lastInvestigationMsg = PartnerReportMessage::whereIn('partner_report_id', $investigationReports)
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

        $unreadSupportCount = PartnerReportMessage::whereIn('partner_report_id', $supportReports)
            ->where('sender_id', '!=', $mitraId)
            ->whereNull('mitra_read_at')
            ->whereIn('recipient_type', ['mitra', 'all', 'both'])
            ->count();

        $unreadInvestigationCount = PartnerReportMessage::whereIn('partner_report_id', $investigationReports)
            ->where('sender_id', '!=', $mitraId)
            ->whereNull('mitra_read_at')
            ->whereIn('recipient_type', ['mitra', 'all', 'both'])
            ->count();

        $unreadCancelCount = HelpCancelMessage::whereIn('help_cancel_request_id', $mitraCancels)
            ->where('sender_id', '!=', $mitraId)
            ->whereNull('mitra_read_at')
            ->whereIn('recipient_type', ['mitra', 'all', 'both'])
            ->count();

        $unreadAdminCount = $unreadSupportCount + $unreadInvestigationCount + $unreadCancelCount;

        // Tentukan pesan terakhir yang paling baru di antara semua kanal admin
        $allAdminMsgs = collect([$lastSupportMsg, $lastInvestigationMsg, $lastCancelMsg])->filter()->sortByDesc('created_at');
        $lastAdminMsg = $allAdminMsgs->first();

        $adminConversation = (object) [
            'partner' => (object) [
                'id'            => 'admin',
                'name'          => 'Tim Admin SayaBantu',
                'email'         => 'admin@email.com',
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
            'latest_help'  => null, // STRICT: No latest Help binding (CH1 Section 3)
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
            'name'          => 'Tim Admin SayaBantu',
            'email'         => 'admin@email.com',
            'phone'         => 'Pusat Bantuan & Moderasi Resmi',
            'profile_photo' => null,
            'selfie_photo'  => null,
            'is_admin'      => true,
        ];

        // 1. Explicit Cancellation
        if ($cancelRequestId) {
            $cancelReq = $this->resolveMitraCancellation((int) $cancelRequestId);
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
            $rep = $this->resolveMitraReport((int) $reportId);
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
                $supportRep = $this->resolveMitraSupportReport();
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

            $supportRep = $this->resolveMitraSupportReport();
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
            $supportRep = $this->resolveMitraSupportReport();
            $this->selected_report_id = $supportRep?->id;
            $this->selected_report    = $supportRep;
        }
    }

    public function markAdminMessagesAsRead()
    {
        $mitraId = Auth::id();
        $updated = false;

        if ($this->admin_tab === 'support' && $this->selected_report_id) {
            $updated = PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                ->whereNull('mitra_read_at')
                ->update(['mitra_read_at' => now()]) > 0;

            PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->where(fn($sub) => $sub->where('recipient_type', 'mitra')->whereNotNull('mitra_read_at'))
                      ->orWhere(fn($sub) => $sub->whereIn('recipient_type', ['all', 'both'])->whereNotNull('mitra_read_at'));
                })
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($this->admin_tab === 'cancellation' && $this->selected_cancel_request_id) {
            $updated = HelpCancelMessage::where('help_cancel_request_id', $this->selected_cancel_request_id)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                ->whereNull('mitra_read_at')
                ->update(['mitra_read_at' => now()]) > 0;

            HelpCancelMessage::where('help_cancel_request_id', $this->selected_cancel_request_id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->where(fn($sub) => $sub->where('recipient_type', 'mitra')->whereNotNull('mitra_read_at'))
                      ->orWhere(fn($sub) => $sub->whereIn('recipient_type', ['all', 'both'])->whereNotNull('customer_read_at')->whereNotNull('mitra_read_at'));
                })
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($this->admin_tab === 'report' && $this->selected_report_id) {
            $updated = PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                ->whereNull('mitra_read_at')
                ->update(['mitra_read_at' => now()]) > 0;

            PartnerReportMessage::where('partner_report_id', $this->selected_report_id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->where(fn($sub) => $sub->where('recipient_type', 'mitra')->whereNotNull('mitra_read_at'))
                      ->orWhere(fn($sub) => $sub->whereIn('recipient_type', ['all', 'both'])->whereNotNull('customer_read_at')->whereNotNull('mitra_read_at'));
                })
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        if ($updated) {
            $this->dispatch('chat-messages-read');
            $this->dispatch('refresh-chat-icon');
        }
    }

    public function selectPartner($customerId, $helpId = null)
    {
        $mitraId = Auth::id();
        $this->selected_partner_id        = (int) $customerId;
        $this->is_admin_chat              = false;
        $this->selected_cancel_request_id = null;
        $this->selected_cancel_request    = null;
        $this->selected_report_id         = null;
        $this->selected_report            = null;
        $this->selected_partner           = User::where('id', $customerId)->first();

        if ($helpId) {
            $helpModel = Help::where('id', $helpId)
                ->where('mitra_id', $mitraId)
                ->where('user_id', $customerId)
                ->first();

            $this->active_help_id = $helpModel?->id;
            $this->active_help    = $helpModel;
        } else {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();

            $this->active_help_id = $latestHelp?->id;
            $this->active_help    = $latestHelp;
        }

        ChatModel::where('mitra_id', $mitraId)
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
                                      ->whereIn('recipient_type', ['mitra', 'all', 'both']);
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
                            'sender_type' => $m->isFromAdmin() ? 'admin' : 'mitra',
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
                    $cancelReq = $this->resolveMitraCancellation((int) $this->selected_cancel_request_id);
                    if ($cancelReq) {
                        $cancelMsgs = HelpCancelMessage::where('help_cancel_request_id', $cancelReq->id)
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
                    $rep = $this->resolveMitraReport((int) $this->selected_report_id);
                    if ($rep && $rep->report_type !== 'dukungan_umum') {
                        $reportMsgs = PartnerReportMessage::where('partner_report_id', $rep->id)
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
            'sender_id'   => $mitraId,
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

    /**
     * Mode Support: Canonical general support (dukungan_umum, help_id = null)
     */
    protected function sendSupportMessage(string $msgText, ?string $photoPath): void
    {
        $mitraId = Auth::id();

        $report = DB::transaction(function () use ($mitraId, $msgText, $photoPath) {
            $lockedUser = User::where('id', $mitraId)->lockForUpdate()->firstOrFail();

            $activeReport = PartnerReport::where('reporter_id', $lockedUser->id)
                ->where('report_type', 'dukungan_umum')
                ->where('category', 'dari_mitra')
                ->first();

            if (!$activeReport) {
                $activeReport = PartnerReport::create([
                    'reporter_id' => $lockedUser->id,
                    'category'    => 'dari_mitra',
                    'report_type' => 'dukungan_umum',
                    'title'       => 'Pusat Bantuan / Konsultasi Rekan Jasa',
                    'message'     => $msgText,
                    'status'      => 'pending',
                ]);
            } else {
                $activeReport->update([
                    'status'     => 'pending',
                    'updated_at' => now(),
                ]);
            }

            PartnerReportMessage::create([
                'partner_report_id' => $activeReport->id,
                'sender_id'         => $lockedUser->id,
                'recipient_type'    => 'admin',
                'message'           => $msgText,
                'photo'             => $photoPath,
                'is_read'           => false,
                'mitra_read_at'     => now(),
            ]);
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
        $mitraId = Auth::id();

        if (!$this->selected_cancel_request_id) {
            $this->dispatch('error', 'Pilih pengajuan pembatalan yang ingin ditinjau terlebih dahulu.');
            return;
        }

        $cancelReq = $this->resolveMitraCancellation((int) $this->selected_cancel_request_id);

        if (!$cancelReq) {
            $this->dispatch('error', 'Pengajuan pembatalan tidak ditemukan atau tidak memiliki akses.');
            return;
        }

        HelpCancelMessage::create([
            'help_cancel_request_id' => $cancelReq->id,
            'sender_id'              => $mitraId,
            'recipient_type'         => 'admin',
            'message'                => $msgText,
            'photo'                  => $photoPath,
            'is_read'                => false,
            'mitra_read_at'          => now(),
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
        $mitraId = Auth::id();

        if (!$this->selected_report_id) {
            $this->dispatch('error', 'Pilih laporan aduan yang ingin Anda tanggapi terlebih dahulu.');
            return;
        }

        $report = $this->resolveMitraReport((int) $this->selected_report_id);

        if (!$report || $report->report_type === 'dukungan_umum') {
            $this->dispatch('error', 'Laporan aduan investigasi tidak valid atau Anda tidak memiliki akses.');
            return;
        }

        PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $mitraId,
            'recipient_type'    => 'admin',
            'message'           => $msgText,
            'photo'             => $photoPath,
            'is_read'           => false,
            'mitra_read_at'     => now(),
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
                        ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                        ->where('sender_id', '!=', $mitraId)
                        ->whereNull('mitra_read_at')
                        ->count();

                    $cReq->last_message = $lastMsg;
                    $cReq->unread_count = $unread;
                    return $cReq;
                });

            $userReports = PartnerReport::where(function ($q) use ($mitraId) {
                    $q->where('reporter_id', $mitraId)
                      ->orWhereHas('reportedHelp', fn($sub) => $sub->where('mitra_id', $mitraId));
                })
                ->where('report_type', '!=', 'dukungan_umum')
                ->with(['reportedHelp', 'reportedUser', 'reporter'])
                ->latest()
                ->get()
                ->map(function ($rep) use ($mitraId) {
                    $lastMsg = PartnerReportMessage::where('partner_report_id', $rep->id)
                        ->where(function($q) use ($mitraId) {
                            $q->where('sender_id', $mitraId)
                              ->orWhere('recipient_type', 'mitra')
                              ->orWhere('recipient_type', 'all')
                              ->orWhere('recipient_type', 'both');
                        })
                        ->latest('created_at')
                        ->first();

                    $unread = PartnerReportMessage::where('partner_report_id', $rep->id)
                        ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                        ->where('sender_id', '!=', $mitraId)
                        ->whereNull('mitra_read_at')
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
