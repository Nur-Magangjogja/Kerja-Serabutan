<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class BlockedPartnerController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        // Base query: users with role mitra or customer
        $baseQuery = User::whereIn('role', ['mitra', 'customer']);

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin') {
            $admin = auth()->user();
            $cityIds = collect([$admin->city_id])
                ->merge($admin->managedCities?->pluck('id') ?? [])
                ->merge(\App\Models\City::where('admin_id', $admin->id)->pluck('id'))
                ->filter()
                ->unique();

            if ($cityIds->isNotEmpty()) {
                $baseQuery->whereIn('city_id', $cityIds);
            }
        }

        // Counts for cards (fresh queries to avoid mutation)
        $totalCount = (clone $baseQuery)->count();
        $blockedCount = (clone $baseQuery)->where('status', 'blocked')->count();
        $activeCount = (clone $baseQuery)->where('status', 'active')->count();
        $mitraCount = (clone $baseQuery)->where('role', 'mitra')->count();
        $customerCount = (clone $baseQuery)->where('role', 'customer')->count();

        // Apply filters from request
        $query = User::whereIn('role', ['mitra', 'customer'])
            ->with('city')
            ->withMax('helps', 'updated_at')
            ->withMax('takenHelps', 'updated_at');

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin') {
            $admin = auth()->user();
            $cityIds = collect([$admin->city_id])
                ->merge($admin->managedCities?->pluck('id') ?? [])
                ->merge(\App\Models\City::where('admin_id', $admin->id)->pluck('id'))
                ->filter()
                ->unique();

            if ($cityIds->isNotEmpty()) {
                $query->whereIn('city_id', $cityIds);
            }
        }

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

        if ($status = $request->get('status')) {
            if (in_array($status, ['active', 'inactive', 'blocked'])) {
                $query->where('status', $status);
            }
        }

        $users = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        return view('livewire.admin.partners.blocked', [
            'blocked' => $users,
            'counts' => [
                'total' => $totalCount,
                'blocked' => $blockedCount,
                'active' => $activeCount,
                'mitra' => $mitraCount,
                'customer' => $customerCount,
            ],
        ]);
    }

    public function toggle($id, \Illuminate\Http\Request $request)
    {
        $request->validate([
            'admin_password' => ['required', 'string'],
        ], [
            'admin_password.required' => 'Kata sandi Admin wajib dimasukkan untuk mengonfirmasi tindakan ini.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'Kata sandi Admin yang Anda masukkan salah. Tindakan dibatalkan.'])->with('error', 'Kata sandi Admin salah. Tindakan dibatalkan.');
        }

        $user = User::findOrFail($id);
        // Toggle between 'blocked' and 'active' status
        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();

        try {
            \App\Models\PartnerActivity::create([
                'user_id' => $user->id,
                'activity_type' => $user->status === 'blocked' ? 'partner_blocked' : 'partner_unblocked',
                'description' => "Status akun diubah menjadi {$user->status} oleh Admin #" . auth()->id() . " (" . auth()->user()->name . ")",
            ]);
        } catch (\Throwable $e) {
            // Ignore activity logging errors
        }

        $label = $user->status === 'blocked' ? "Akun {$user->name} berhasil diblokir." : "Blokir akun {$user->name} berhasil dibuka.";
        return back()->with('success', $label);
    }
}
