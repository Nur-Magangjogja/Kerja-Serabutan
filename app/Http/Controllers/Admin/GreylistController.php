<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use App\Models\UserGreylistLog;
use App\Notifications\ChatMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GreylistController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::user();

        // Base query for stats (users who are greylisted, shadowbanned, or have active warnings)
        $baseQuery = User::whereIn('role', ['mitra', 'customer'])
            ->where(function ($q) {
                $q->where('is_greylisted', true)
                  ->orWhere('is_shadow_banned', true)
                  ->orWhere('warning_level', '>', 0);
            });

        // Filter by admin city if admin
        if ($admin && $admin->role === 'admin') {
            $cityIds = collect([$admin->city_id])
                ->merge($admin->managedCities?->pluck('id') ?? [])
                ->merge(City::where('admin_id', $admin->id)->pluck('id'))
                ->filter()
                ->unique();

            if ($cityIds->isNotEmpty()) {
                $baseQuery->whereIn('city_id', $cityIds);
            }
        }

        $totalGreylist    = (clone $baseQuery)->where('is_greylisted', true)->count();
        $totalShadowBanned = (clone $baseQuery)->where('is_shadow_banned', true)->count();
        $totalWarning      = (clone $baseQuery)->where('warning_level', '>', 0)->count();
        $totalMitra        = (clone $baseQuery)->where('role', 'mitra')->count();
        $totalCustomer     = (clone $baseQuery)->where('role', 'customer')->count();

        // Main Query
        $query = (clone $baseQuery)->with(['city', 'greylistLogs.admin']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            if (in_array($role, ['mitra', 'customer'])) {
                $query->where('role', $role);
            }
        }

        if ($filter = $request->get('filter')) {
            if ($filter === 'shadow_banned') {
                $query->where('is_shadow_banned', true);
            } elseif ($filter === 'warning') {
                $query->where('warning_level', '>', 0);
            } elseif ($filter === 'greylisted') {
                $query->where('is_greylisted', true);
            }
        }

        $users = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        // Available candidate users for Add to Greylist modal (not yet greylisted)
        $candidatesQuery = User::whereIn('role', ['mitra', 'customer'])
            ->where('is_greylisted', false)
            ->where('is_shadow_banned', false)
            ->where('warning_level', 0);

        if ($admin && $admin->role === 'admin' && isset($cityIds) && $cityIds->isNotEmpty()) {
            $candidatesQuery->whereIn('city_id', $cityIds);
        }

        $candidateUsers = $candidatesQuery->orderBy('name')->take(100)->get(['id', 'name', 'email', 'role', 'phone']);

        return view('livewire.admin.partners.greylist', [
            'users' => $users,
            'candidateUsers' => $candidateUsers,
            'counts' => [
                'total_greylist'    => $totalGreylist,
                'total_shadow_banned' => $totalShadowBanned,
                'total_warning'      => $totalWarning,
                'total_mitra'        => $totalMitra,
                'total_customer'     => $totalCustomer,
            ],
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'reason'          => 'required|string|max:1000',
            'warning_level'   => 'nullable|integer|between:0,3',
            'warning_message' => 'nullable|string|max:1000',
            'shadow_ban'      => 'nullable|boolean',
        ]);

        $user = User::findOrFail($request->user_id);
        $adminId = Auth::id();

        $warningLevel = (int) ($request->warning_level ?? 0);
        $isShadowBanned = (bool) $request->shadow_ban;

        $user->update([
            'is_greylisted'          => true,
            'greylisted_at'          => now(),
            'greylist_reason'        => $request->reason,
            'is_shadow_banned'       => $isShadowBanned,
            'shadow_banned_at'       => $isShadowBanned ? now() : null,
            'warning_level'          => $warningLevel,
            'latest_warning_message' => $request->warning_message ?: ($warningLevel > 0 ? $request->reason : null),
            'latest_warning_at'      => $warningLevel > 0 ? now() : null,
        ]);

        UserGreylistLog::create([
            'user_id'       => $user->id,
            'admin_id'      => $adminId,
            'action'        => 'greylist_add',
            'warning_level' => $warningLevel,
            'reason'        => $request->reason,
            'message'       => $request->warning_message,
        ]);

        if ($warningLevel > 0) {
            try {
                $user->notify(new ChatMessageNotification(
                    null,
                    "⚠️ Peringatan Resmi (SP {$warningLevel}): " . ($request->warning_message ?: $request->reason),
                    $adminId,
                    'Tim Moderasi & Kepatuhan SayaBantu'
                ));
            } catch (\Throwable $e) {}
        }

        return back()->with('success', "User {$user->name} ({$user->role}) berhasil dimasukkan ke Daftar Abu-Abu.");
    }

    public function issueWarning(User $user, Request $request)
    {
        $request->validate([
            'warning_level'   => 'required|integer|between:1,3',
            'warning_message' => 'required|string|max:1000',
        ]);

        $adminId = Auth::id();
        $warningLevel = (int) $request->warning_level;

        $user->update([
            'is_greylisted'          => true,
            'greylisted_at'          => $user->greylisted_at ?? now(),
            'warning_level'          => $warningLevel,
            'latest_warning_message' => $request->warning_message,
            'latest_warning_at'      => now(),
        ]);

        UserGreylistLog::create([
            'user_id'       => $user->id,
            'admin_id'      => $adminId,
            'action'        => 'warning_issued',
            'warning_level' => $warningLevel,
            'reason'        => 'Pemberian Surat Peringatan SP ' . $warningLevel,
            'message'       => $request->warning_message,
        ]);

        try {
            $user->notify(new ChatMessageNotification(
                null,
                "⚠️ Surat Peringatan Resmi SP {$warningLevel}: " . $request->warning_message,
                $adminId,
                'Tim Moderasi & Kepatuhan SayaBantu'
            ));
        } catch (\Throwable $e) {}

        return back()->with('success', "Surat Peringatan (SP {$warningLevel}) berhasil diterbitkan kepada {$user->name}.");
    }

    public function toggleShadowBan(User $user, Request $request)
    {
        $adminId = Auth::id();
        $newStatus = !$user->is_shadow_banned;

        $user->update([
            'is_greylisted'    => true,
            'greylisted_at'    => $user->greylisted_at ?? now(),
            'is_shadow_banned' => $newStatus,
            'shadow_banned_at' => $newStatus ? now() : null,
        ]);

        UserGreylistLog::create([
            'user_id'  => $user->id,
            'admin_id' => $adminId,
            'action'   => $newStatus ? 'shadow_ban_enabled' : 'shadow_ban_disabled',
            'reason'   => $request->input('reason', $newStatus ? 'Aktivasi Shadow Ban oleh Admin' : 'Pencabutan Shadow Ban oleh Admin'),
        ]);

        $statusMsg = $newStatus 
            ? "Shadow Ban berhasil diaktifkan untuk {$user->name}. User tidak dapat membuat/melihat bantuan."
            : "Shadow Ban berhasil dicabut untuk {$user->name}. Akses bantuan kembali normal.";

        return back()->with('success', $statusMsg);
    }

    public function remove(User $user, Request $request)
    {
        $adminId = Auth::id();

        $user->update([
            'is_greylisted'          => false,
            'greylisted_at'          => null,
            'greylist_reason'        => null,
            'is_shadow_banned'       => false,
            'shadow_banned_at'       => null,
            'warning_level'          => 0,
            'latest_warning_message' => null,
            'latest_warning_at'      => null,
        ]);

        UserGreylistLog::create([
            'user_id'  => $user->id,
            'admin_id' => $adminId,
            'action'   => 'greylist_remove',
            'reason'   => $request->input('reason', 'Pemulihan akun dan penghapusan dari Daftar Abu-Abu oleh Admin'),
        ]);

        return back()->with('success', "User {$user->name} berhasil dipulihkan dan dihapus dari Daftar Abu-Abu.");
    }
}
