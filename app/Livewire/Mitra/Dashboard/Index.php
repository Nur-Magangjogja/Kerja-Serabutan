<?php

namespace App\Livewire\Mitra\Dashboard;

use App\Models\Help;
use App\Models\UserBalance;
use App\Services\LocationTrackingService;
use App\Notifications\HelpTakenNotification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.mitra')]
class Index extends Component
{
    use WithPagination;

    public $activeTab = 'tersedia'; // tersedia, semua, diproses, selesai

    public function mount()
    {
        // Check if tab parameter is in the query string
        $tab = request()->query('tab');
        if ($tab && in_array($tab, ['tersedia', 'semua', 'diproses', 'selesai'])) {
            $this->activeTab = $tab;
        }
    }

    #[On('balance-updated')]
    public function refreshBalance()
    {
        $this->dispatch('$refresh');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function takeHelp($helpId, $latitude = null, $longitude = null)
    {
        $help = Help::findOrFail($helpId);

        try {
            app(\App\Services\HelpTransactionService::class)->takeHelp(
                $help,
                auth()->user(),
                $latitude ? (float) $latitude : null,
                $longitude ? (float) $longitude : null
            );

            session()->flash('message', 'Bantuan berhasil diambil! GPS tracking aktif. Segera menuju lokasi customer.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        } catch (\Throwable $e) {
            \Log::error('[Mitra/Dashboard] takeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat mengambil bantuan.');
            return;
        }
        
        // Emit event untuk mulai GPS tracking
        $this->dispatch('start-gps-tracking', helpId: $helpId);
        
        $this->setTab('diproses');

        // Redirect mitra to the help detail page so they can see full info and navigation
        return $this->redirectRoute('mitra.helps.detail', ['id' => $helpId]);
    }

    public function completeHelp($helpId)
    {
        $help = Help::where('id', $helpId)
            ->where('mitra_id', auth()->id())
            ->firstOrFail();

        $help->update([
            'status' => 'selesai',
            'completed_at' => now(),
        ]);

        session()->flash('message', 'Bantuan berhasil diselesaikan! Terima kasih atas kebaikan Anda.');
        $this->setTab('selesai');
    }

    public function render()
    {
        $user = auth()->user();
        $userBalance = UserBalance::where('user_id', $user->id)->first();
        $balance = $userBalance ? $userBalance->balance : 0;

        // Statistik bantuan
        $availableHelpsCount = Help::where('status', 'menunggu_mitra')
            ->whereNull('mitra_id')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->count();

        $inProgressCount = Help::where('mitra_id', $user->id)
            ->whereIn('status', ['memperoleh_mitra', 'taken', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation'])
            ->count();

        $completedCount = Help::where('mitra_id', $user->id)
            ->whereIn('status', ['selesai', 'completed'])
            ->count();

        $userCityId = optional($user)->city_id;

        // Data berdasarkan tab
        if ($this->activeTab === 'tersedia' || $this->activeTab === 'semua') {
            $helpsQuery = Help::where('status', 'menunggu_mitra')
                ->whereNull('mitra_id')
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
                })
                ->with(['user', 'city']);

            if ($userCityId) {
                $helpsQuery->orderByRaw("(city_id = ?) DESC", [$userCityId])->latest();
            } else {
                $helpsQuery->latest();
            }

            $helps = $helpsQuery->paginate(10);
        } elseif ($this->activeTab === 'diproses') {
            $helps = Help::where('mitra_id', $user->id)
                ->whereIn('status', ['memperoleh_mitra', 'taken', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation'])
                ->with(['user', 'city'])
                ->latest()
                ->paginate(10);
        } else { // selesai
            $helps = Help::where('mitra_id', $user->id)
                ->whereIn('status', ['selesai', 'completed'])
                ->with(['user', 'city'])
                ->latest()
                ->paginate(10);
        }

        // Additional curated lists for dashboard sections
        $relations = ['user', 'city'];
        if (Schema::hasColumn('helps', 'category_id')) {
            $relations[] = 'category';
        }

        $recommendedQuery = Help::where('status', 'menunggu_mitra')
            ->whereNull('mitra_id')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->with($relations);
        if ($userCityId) {
            $recommendedQuery->orderByRaw("(city_id = ?) DESC", [$userCityId])->latest();
        } else {
            $recommendedQuery->latest();
        }
        $recommendedHelps = $recommendedQuery->take(6)->get();

        // Terbaru: order by created_at desc
        $latestQuery = Help::where('status', 'menunggu_mitra')
            ->whereNull('mitra_id')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->with($relations);
        $latestHelps = $latestQuery->latest()->take(6)->get();

        // Terdekat: prioritize user's city
        $nearbyQuery = Help::where('status', 'menunggu_mitra')
            ->whereNull('mitra_id')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->with($relations);
        if ($userCityId) {
            $nearbyQuery->orderByRaw("(city_id = ?) DESC", [$userCityId])->latest();
        } else {
            $nearbyQuery->latest();
        }
        $nearbyHelps = $nearbyQuery->take(6)->get();

        // Unread chat count for mitra (messages sent by customers not yet read)
        $unreadChatCount = 0;
        try {
            $unreadChatCount = \App\Models\Chat::where('mitra_id', $user->id)
                ->whereNull('read_at')
                ->where('sender_type', 'customer')
                ->count();
        } catch (\Throwable $e) {
            // ignore if Chat table or column is missing
        }

        // Active task checking
        $activeTask = $user ? Help::where('mitra_id', $user->id)->active()->first() : null;

        return view('livewire.mitra.dashboard.index', [
            'helps' => $helps,
            'balance' => $balance,
            'availableHelpsCount' => $availableHelpsCount,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'user' => $user,
            'recommendedHelps' => $recommendedHelps,
            'latestHelps' => $latestHelps,
            'nearbyHelps' => $nearbyHelps,
            'unreadChatCount' => $unreadChatCount,
            'activeTask' => $activeTask,
        ]);
    }
}
