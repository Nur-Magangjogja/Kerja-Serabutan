<?php

namespace App\Livewire\Customer\Dashboard;

use App\Models\Help;
use App\Models\UserBalance;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Index extends Component
{
    use WithPagination;
    public $activeTab = 'latest'; // latest, all, history
    public $selectedHelp = null;
    public $selectedHelpData = null;

    #[On('balance-updated')]
    public function refreshBalance()
    {
        $this->dispatch('$refresh');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function showHelp($id)
    {
        $help = Help::with(['user', 'city', 'district', 'mitra'])->find($id);
        if (!$help) {
            $this->selectedHelp = null;
            $this->selectedHelpData = null;
            return;
        }

        $this->selectedHelp = $help;
        $this->selectedHelpData = [
            'id' => $help->id,
            'title' => $help->title,
            'description' => $help->description,
            'amount' => $help->amount,
            'total_amount' => $help->total_amount > 0 ? $help->total_amount : $help->amount,
            'photo' => $help->photo,
            'location' => $help->location,
            'user_name' => $help->user?->name,
            'city_name' => $help->city?->name,
            'district_name' => $help->district?->name,
            'created_at_human' => $help->created_at?->diffForHumans(),
        ];
    }

    public function closeHelp()
    {
        $this->selectedHelp = null;
        $this->selectedHelpData = null;
    }

    public function render()
    {
        $user = auth()->user();

        $stats = [];

        // Get user balance
        $userBalance = UserBalance::where('user_id', $user->id)->first();
        $balance = $userBalance ? $userBalance->balance : 0;

        // Auto-cancel bantuan milik user yang sudah kadaluwarsa
        if ($user && $user->isCustomer()) {
            app(\App\Services\HelpCancellationService::class)->sweepAndAutoCancelExpiredHelps($user->id);
        }

        // Filter berdasarkan tab yang aktif
        if ($this->activeTab === 'latest') {
            // Ambil bantuan user sendiri (5 bantuan terakhir)
            $availableHelps = Help::where('user_id', $user->id)
                ->with(['user', 'city', 'district', 'mitra'])
                ->latest()
                ->take(5)
                ->get();
        } elseif ($this->activeTab === 'all') {
            // Ambil semua bantuan milik user sendiri (pakai pagination)
            $availableHelps = Help::where('user_id', $user->id)
                ->with(['user', 'city', 'district', 'mitra'])
                ->latest()
                ->paginate(10);
        } else { // history
            // Ambil riwayat transaksi user
            if ($user->isMitra()) {
                // Untuk mitra, tampilkan bantuan yang sudah dikerjakan
                $availableHelps = Help::where('mitra_id', $user->id)
                    ->with(['user', 'city', 'district'])
                    ->latest()
                    ->take(10)
                    ->get();
            } else {
                // Untuk customer, tampilkan bantuan yang sudah selesai atau dibatalkan
                $availableHelps = Help::where('user_id', $user->id)
                    ->whereIn('status', Help::terminalStatuses())
                    ->with(['mitra', 'city', 'district'])
                    ->latest()
                    ->take(10)
                    ->get();
            }
        }

        if ($user->isCustomer()) {
            // 1 query agregasi menggantikan 3 query COUNT terpisah
            $agg = Help::where('user_id', $user->id)
                ->selectRaw("COUNT(*) as total_helps")
                ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_helps", [Help::STATUS_MENUNGGU_MITRA])
                ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_helps", [Help::STATUS_SELESAI])
                ->first();

            $stats = [
                'total_helps' => (int) $agg->total_helps,
                'pending_helps' => (int) $agg->pending_helps,
                'completed_helps' => (int) $agg->completed_helps,
            ];

            // Reuse $availableHelps jika tab = 'latest' untuk menghindari duplikasi query
            $myHelps = ($this->activeTab === 'latest')
                ? $availableHelps
                : Help::where('user_id', $user->id)
                    ->with(['city', 'district', 'mitra'])
                    ->latest()
                    ->take(5)
                    ->get();
        } elseif ($user->isMitra()) {
            // 1 query agregasi menggantikan 3 query COUNT terpisah
            $aggMitra = Help::where('mitra_id', $user->id)
                ->selectRaw("COUNT(*) as total_helped")
                ->selectRaw("SUM(CASE WHEN status IN (" . implode(',', array_fill(0, count(Help::activeStatuses()), '?')) . ") THEN 1 ELSE 0 END) as in_progress", Help::activeStatuses())
                ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed", [Help::STATUS_SELESAI])
                ->first();

            $stats = [
                'total_helped' => (int) $aggMitra->total_helped,
                'in_progress' => (int) $aggMitra->in_progress,
                'completed' => (int) $aggMitra->completed,
            ];

            $myHelps = Help::where('mitra_id', $user->id)
                ->with(['user', 'city'])
                ->latest()
                ->take(5)
                ->get();
        } else {
            $myHelps = collect();
        }

        // Unread chats for customer (messages sent by mitra not yet read)
        $unreadChatCount = 0;
        try {
            $unreadChatCount = \App\Models\Chat::where('customer_id', $user->id)
                ->whereNull('read_at')
                ->whereIn('sender_type', ['mitra', 'system'])
                ->count();
        } catch (\Exception $e) {
            // ignore if Chat model or columns missing
        }

        return view('livewire.customer.dashboard.index', [
            'stats' => $stats,
            'myHelps' => $myHelps,
            'availableHelps' => $availableHelps,
            'balance' => $balance,
            'activeTab' => $this->activeTab,
            'unreadChatCount' => $unreadChatCount,
            'selectedHelp' => $this->selectedHelp,
            'selectedHelpData' => $this->selectedHelpData,
        ]);
    }
}
