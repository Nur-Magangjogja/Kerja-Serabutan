<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->user();

        $cityIds = City::where('admin_id', $admin->id)->pluck('id');

        $query = User::with('city')
            ->withCount('helps')
            ->withCount('partnerReports')
            // load both mitra/customer rating aggregates so we can display correct values per role
            ->withCount([
                'mitraRatings as mitra_ratings_count',
                'customerRatings as customer_ratings_count',
            ])
            ->withAvg('mitraRatings as mitra_average_rating', 'rating')
            ->withAvg('customerRatings as customer_average_rating', 'rating');

        // Apply city scoping only when admin has linked cities
        if ($cityIds->isNotEmpty()) {
            $query->whereIn('city_id', $cityIds);
        }

        // Search by name or email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $role = $request->get('role');
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        } else {
            // By default, show mitra and customer/kustomer roles to focus admin listing
            $query->whereIn('role', ['mitra', 'kustomer', 'customer']);
        }

        // Filter by account status
        if ($status = $request->get('account_status')) {
            if ($status === 'blocked') {
                $query->where('status', 'blocked');
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive');
            } elseif ($status === 'active') {
                $query->where('status', 'active');
            }
        }

        // Filter by KTP status
        if ($ktpStatus = $request->get('ktp_status')) {
            if ($ktpStatus === 'uploaded') {
                $query->whereNotNull('ktp_path');
            } elseif ($ktpStatus === 'missing') {
                $query->whereNull('ktp_path');
            }
        }

        $users = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Ensure we have a city_name property for display (fallback to lookup by city_id)
        foreach ($users as $user) {
            $user->city_name = optional($user->city)->name ?? optional(City::find($user->city_id))->name;

            // Normalize rating/count fields for the view so the template can show rating for both Mitra and Customer
            if ($user->isMitra()) {
                $user->ratings_count = $user->mitra_ratings_count ?? 0;
                $user->average_rating = $user->mitra_average_rating !== null ? round($user->mitra_average_rating, 2) : null;
            } else {
                $user->ratings_count = $user->customer_ratings_count ?? 0;
                $user->average_rating = $user->customer_average_rating !== null ? round($user->customer_average_rating, 2) : null;
            }
        }

        return view('admin.users.index', compact('users'));
    }

    public function show(\Illuminate\Http\Request $request, User $user)
    {
        $admin = auth()->user();

        $cityIds = City::where('admin_id', $admin->id)->pluck('id');

        // Only restrict access when the admin is linked to one or more cities.
        // If the admin has no city assignments, allow viewing any user.
        if ($cityIds->isNotEmpty() && !$cityIds->contains($user->city_id)) {
            abort(404);
        }

        // If the request is AJAX, return only the partial HTML suitable for a modal.
        if ($request->ajax()) {
            $user->load('city');
            $user->city_name = optional($user->city)->name ?? optional(City::find($user->city_id))->name;
            return view('admin.users.partials.show', compact('user'));
        }

        $user->load('city');

        return view('admin.users.show', compact('user'));
    }
}
