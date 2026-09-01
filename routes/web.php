<?php

use Illuminate\Support\Facades\Route;

// Landing / Login route - Unified entrance
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return redirect()->route('dashboard');
        }
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        if (empty($user->nik) || (empty($user->ktp_photo) && empty($user->ktp_path))) {
            return redirect()->route('register.step1');
        }
        if ($user->status !== 'active' || !$user->verified) {
            return redirect()->route('registration.success');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Alias welcome page
Route::get('/welcome', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return redirect()->route('dashboard');
        }
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        if (empty($user->nik) || (empty($user->ktp_photo) && empty($user->ktp_path))) {
            return redirect()->route('register.step1');
        }
        if ($user->status !== 'active' || !$user->verified) {
            return redirect()->route('registration.success');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('welcome');

// Rejected registration page (public)
Route::get('/rejected/{registration}', [\App\Http\Controllers\Auth\RejectedController::class, 'show'])->name('auth.rejected');

// Authenticated and fully approved routes (Protected against unverified / incomplete / unvalidated users)
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    // Main Dashboard route - redirects based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'mitra') {
            return redirect()->route('mitra.dashboard');
        } elseif ($user->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Default: redirect to customer dashboard
        return redirect()->route('customer.dashboard');
    })->name('dashboard');

    // Chat shortcut routes (open a specific conversation)
    Route::get('/chat/start', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat/{help}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');

    // Lightweight AJAX endpoints for authenticated users
    Route::get('/ajax/cities', [\App\Http\Controllers\Api\CityController::class, 'search'])->name('ajax.cities');

    // ========================================
    // CUSTOMER ROUTES (Customer/Penerima Bantuan)
    // ========================================
    Route::prefix('customer')->name('customer.')->middleware('customer')->group(function () {
        // Dashboard
        Route::get('/dashboard', \App\Livewire\Customer\Dashboard\Index::class)->name('dashboard');

        // Helps Management (specific routes BEFORE general routes)
        Route::get('/helps/create', \App\Livewire\Customer\Helps\Create::class)->name('helps.create');
        Route::get('/helps/history', \App\Livewire\Customer\Helps\History::class)->name('helps.history');
        Route::get('/helps/{id}/detail', \App\Livewire\Customer\Helps\Detail::class)->name('helps.detail');
        Route::get('/helps', \App\Livewire\Customer\Helps\Index::class)->name('helps.index');

        // Notifications
        Route::get('/notifications', \App\Livewire\Customer\Notifications\Index::class)->name('notifications.index');
        Route::post('/notifications/cleanup-on-exit', function () {
            if (auth()->check()) {
                $user = auth()->user();
                $settings = $user->notification_settings ?? [];
                if (!empty($settings['auto_cleanup_read'])) {
                    $user->notifications()->whereNotNull('read_at')->delete();
                }
            }
            return response()->json(['success' => true]);
        })->name('notifications.cleanup-on-exit');

        // Balance & Transactions
        Route::get('/transactions', \App\Livewire\Customer\Transactions\Index::class)->name('transactions.index');

        // Top Up Saldo - Approval system
        Route::get('/topup/request', \App\Livewire\Customer\Topup\TopupRequest::class)->name('topup.request');
        Route::get('/topup/history', \App\Livewire\Customer\Topup\History::class)->name('topup.history');

        // Withdraw (Customer) - Full Livewire 3
        Route::get('/withdraw', \App\Livewire\Customer\Withdraw\WithdrawForm::class)->name('withdraw.form');
        Route::get('/withdraw/history', \App\Livewire\Customer\Withdraw\WithdrawHistory::class)->name('withdraw.history');

        // Top Up Saldo (Old Midtrans - kept for backward compatibility)
        Route::get('/top-up', \App\Livewire\Customer\Topup\Index::class)->name('topup');

        // Chat (optional help id for opening detail directly)
        Route::get('/chat/{help?}', \App\Livewire\Customer\Chat\Index::class)->name('chat');

        // Ratings (customer receives ratings from mitra)
        Route::get('/ratings', \App\Livewire\Customer\Ratings\Index::class)->name('ratings');

        // Help & Support
        Route::view('/help-support', 'customer.help-support')->name('help-support');

        // Lightweight endpoint to fetch current tracking coordinates (used by client-side polling)
        Route::get('/helps/{id}/tracking', function ($id) {
            $help = \App\Models\Help::find($id);
            if (! $help) {
                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json([
                'partnerLat' => $help->partner_current_lat,
                'partnerLng' => $help->partner_current_lng,
                'customerLat' => $help->latitude,
                'customerLng' => $help->longitude,
                'partnerName' => $help->mitra?->name ?? null,
                'updated_at' => $help->updated_at?->toDateTimeString(),
            ]);
        })->name('customer.helps.tracking');

        // Lightweight JSON endpoint to fetch help details
        Route::get('/helps/{id}/json', function ($id) {
            $help = \App\Models\Help::with(['city','mitra','user'])->find($id);
            if (! $help) return response()->json(['error' => 'Not found'], 404);

            return response()->json([
                'id' => $help->id,
                'scheduled_at' => $help->scheduled_at?->toDateTimeString(),
                'title' => $help->title,
                'amount' => $help->amount,
            ]);
        })->name('helps.json');

        // Reports
        Route::get('/reports/create', \App\Livewire\Customer\Reports\Create::class)->name('reports.create');
        Route::get('/reports/create/user/{user_id}', \App\Livewire\Customer\Reports\Create::class)->name('reports.create.user');
        Route::get('/reports/create/help/{help_id}', \App\Livewire\Customer\Reports\Create::class)->name('reports.create.help');
    });

    // ========================================
    // MITRA ROUTES (Volunteer/Pemberi Bantuan)
    // ========================================
    Route::prefix('mitra')->name('mitra.')->middleware('mitra')->group(function () {
        // Dashboard
        Route::get('/dashboard', \App\Livewire\Mitra\Dashboard\Index::class)->name('dashboard');

        // Helps Management
        Route::get('/helps', \App\Livewire\Mitra\Helps\AllHelps::class)->name('helps.all');
        Route::get('/helps/completed', \App\Livewire\Mitra\Helps\CompletedHelps::class)->name('helps.completed');
        Route::get('/helps/{id}/detail', \App\Livewire\Mitra\Helps\HelpDetail::class)->name('helps.detail');

        // Profile
        Route::get('/profile', \App\Livewire\Mitra\Profile\Index::class)->name('profile');
        Route::get('/profile/edit', \App\Livewire\Mitra\Profile\EditPage::class)->name('profile.edit');

        // Chat (optional help id for opening detail directly)
        Route::get('/chat/{help?}', \App\Livewire\Mitra\Chat\Index::class)->name('chat');

        // Notifications (Mitra)
        Route::get('/notifications', \App\Livewire\Mitra\Notifications\Index::class)->name('notifications.index');
        Route::post('/notifications/cleanup-on-exit', function () {
            if (auth()->check()) {
                $user = auth()->user();
                $settings = $user->notification_settings ?? [];
                if (!empty($settings['auto_cleanup_read'])) {
                    $user->notifications()->whereNotNull('read_at')->delete();
                }
            }
            return response()->json(['success' => true]);
        })->name('notifications.cleanup-on-exit');

        // Reports
        Route::get('/reports/create', \App\Livewire\Mitra\Reports\Create::class)->name('reports.create');
        Route::get('/reports/create/user/{user_id}', \App\Livewire\Mitra\Reports\Create::class)->name('reports.create.user');
        Route::get('/reports/create/help/{help_id}', \App\Livewire\Mitra\Reports\Create::class)->name('reports.create.help');

        // Processing helps (in-progress)
        Route::get('/helps/processing', \App\Livewire\Mitra\Helps\ProcessingHelps::class)->name('helps.processing');

        // Ratings
        Route::get('/ratings', \App\Livewire\Mitra\Ratings\Index::class)->name('ratings');

        // Settings (Mitra)
        Route::view('/settings', 'mitra.settings')->name('settings');
        Route::view('/settings/notifications', 'mitra.settings.notifications')->name('settings.notifications');
        Route::view('/settings/password', 'mitra.settings.password')->name('settings.password');

        // Help & Support
        Route::view('/help-support', 'mitra.help-support')->name('help-support');

        // Balance & Transactions (Mitra)
        Route::get('/transactions', \App\Livewire\Mitra\Transactions\Index::class)->name('transactions.index');

        // Withdraw (Mitra) - Full Livewire 3
        Route::get('/withdraw', \App\Livewire\Mitra\Withdraw\WithdrawForm::class)->name('withdraw.form');
        Route::get('/withdraw/history', \App\Livewire\Mitra\Withdraw\WithdrawHistory::class)->name('withdraw.history');
    });

    // ========================================
    // PROFILE & SETTINGS ROUTES (Role-Guarded)
    // ========================================
    Route::get('/profile', function () {
        $user = auth()->user();
        if ($user?->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }
        if ($user?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user?->role === 'mitra') {
            return redirect()->route('mitra.profile');
        }
        return view('profile');
    })->name('profile');

    Route::get('/profile/edit', function () {
        $user = auth()->user();
        if ($user?->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }
        if ($user?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user?->role === 'mitra') {
            return redirect()->route('mitra.profile.edit');
        }
        return view('profile.edit');
    })->name('profile.edit');

    Route::get('/profile/settings', function () {
        $user = auth()->user();
        if ($user?->role === 'super_admin') {
            return redirect()->route('superadmin.settings.appearance');
        }
        if ($user?->role === 'admin') {
            return redirect()->route('admin.settings.appearance');
        }
        if ($user?->role === 'mitra') {
            return redirect()->route('mitra.settings');
        }
        return view('profile.settings');
    })->name('profile.settings');

    Route::get('/profile/settings/notifications', function () {
        $user = auth()->user();
        if ($user?->role === 'super_admin') {
            return redirect()->route('superadmin.notifications.index');
        }
        if ($user?->role === 'admin') {
            return redirect()->route('admin.notifications.index');
        }
        if ($user?->role === 'mitra') {
            return redirect()->route('mitra.settings.notifications');
        }
        return view('profile.settings.notifications');
    })->name('profile.settings.notifications');

    Route::get('/profile/settings/password', function () {
        $user = auth()->user();
        if ($user?->role === 'super_admin') {
            return redirect()->route('superadmin.settings.appearance');
        }
        if ($user?->role === 'admin') {
            return redirect()->route('admin.settings.appearance');
        }
        if ($user?->role === 'mitra') {
            return redirect()->route('mitra.settings.password');
        }
        return view('profile.settings.password');
    })->name('profile.settings.password');

    Route::put('/profile/password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password)
        ]);

        return back()->with('status', 'Password updated successfully!');
    })->name('profile.password.update');

});

// ========================================
// SUPER ADMIN ROUTES (Full Livewire 3)
// ========================================
Route::middleware(['auth', 'verified', 'super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\SuperAdmin\Dashboard\Index::class)->name('dashboard');
    Route::get('/users', \App\Livewire\SuperAdmin\Users\Index::class)->name('users');
    Route::get('/cities', \App\Livewire\SuperAdmin\Cities\Index::class)->name('cities');
    Route::get('/notifications', \App\Livewire\SuperAdmin\Notifications\Index::class)->name('notifications.index');
    Route::get('/activity-logs', \App\Livewire\SuperAdmin\ActivityLogs\Index::class)->name('activity.logs');
    Route::get('/transactions/logs', \App\Livewire\SuperAdmin\Transactions\Log::class)->name('transactions.log');
    Route::get('/helps/approved', \App\Livewire\SuperAdmin\Helps\Approved::class)->name('helps.approved');
    Route::get('/settings/identity', \App\Livewire\SuperAdmin\Settings\IdentitySettings::class)->name('settings.identity');
    Route::get('/settings/help', \App\Livewire\SuperAdmin\Settings\HelpSettings::class)->name('settings.help');
    Route::get('/settings/withdraw', \App\Livewire\SuperAdmin\Settings\WithdrawSettings::class)->name('settings.withdraw');
    Route::get('/settings/banners', \App\Livewire\SuperAdmin\Banners\Index::class)->name('settings.banners');
    Route::view('/settings/transactions', 'superadmin.transactions')->name('settings.transactions');
    Route::get('/topup/approvals', \App\Livewire\SuperAdmin\Topup\Approval::class)->name('topup.approvals');
    Route::get('/verifications', \App\Livewire\Admin\Verifications\Index::class)->name('verifications');
    Route::get('/admin-users', \App\Livewire\SuperAdmin\Users\AdminUsers::class)->name('admin.users');

    // Withdraw Management (Full Livewire)
    Route::get('/withdraws', \App\Livewire\Admin\Withdraws\Index::class)->name('withdraws.index');

    // Moderasi & Laporan Mitra (Full Livewire)
    Route::get('/partners/activity', \App\Livewire\Admin\Partners\Activity::class)->name('partners.activity');
    Route::get('/partners/report', \App\Livewire\Admin\Partners\Reports\Index::class)->name('partners.report');
    Route::get('/partners/reports', \App\Livewire\Admin\Partners\Reports\Index::class)->name('partners.reports');
    Route::get('/partners/reports/{report}', \App\Livewire\Admin\Partners\Reports\Show::class)->name('partners.reports.show');
    Route::get('/partners/reports/{report}/chat', \App\Livewire\Admin\Partners\Reports\Chat::class)->name('partners.reports.chat');
    Route::get('/partners/blocked', \App\Livewire\Admin\Partners\Blocked::class)->name('partners.blocked');
    Route::get('/partners/greylist', \App\Livewire\Admin\Partners\Greylist::class)->name('partners.greylist');
    Route::get('/disputes', \App\Livewire\Admin\Disputes\Index::class)->name('disputes.index');

    Route::view('/settings/appearance', 'superadmin.settings.appearance')->name('settings.appearance');
    Route::get('/settings', function () {
        return redirect()->route('superadmin.settings.identity');
    });
});

// ========================================
// ADMIN ROUTES (Full Livewire 3)
// ========================================
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard\Index::class)->name('dashboard');
    Route::view('/settings/appearance', 'livewire.admin.settings.appearance')->name('settings.appearance');
    Route::get('/helps', \App\Livewire\Admin\Helps\Index::class)->name('helps');
    Route::get('/helps/approved', \App\Livewire\Admin\Helps\Approved::class)->name('helps.approved');
    Route::get('/verifications', \App\Livewire\Admin\Verifications\Index::class)->name('verifications');
    Route::get('/users', \App\Livewire\SuperAdmin\Users\Index::class)->name('users.index');

    // Withdraw Management (Full Livewire)
    Route::get('/withdraws', \App\Livewire\Admin\Withdraws\Index::class)->name('withdraws.index');

    // Moderasi & Laporan Mitra (Full Livewire)
    Route::get('/partners/activity', \App\Livewire\Admin\Partners\Activity::class)->name('partners.activity');
    Route::get('/partners/report', \App\Livewire\Admin\Partners\Reports\Index::class)->name('partners.report');
    Route::get('/partners/reports', \App\Livewire\Admin\Partners\Reports\Index::class)->name('partners.reports');
    Route::get('/partners/reports/{report}', \App\Livewire\Admin\Partners\Reports\Show::class)->name('partners.reports.show');
    Route::get('/partners/reports/{report}/chat', \App\Livewire\Admin\Partners\Reports\Chat::class)->name('partners.reports.chat');
    Route::get('/partners/blocked', \App\Livewire\Admin\Partners\Blocked::class)->name('partners.blocked');
    Route::get('/partners/greylist', \App\Livewire\Admin\Partners\Greylist::class)->name('partners.greylist');
    Route::get('/disputes', \App\Livewire\Admin\Disputes\Index::class)->name('disputes.index');
    Route::get('/topup/approvals', \App\Livewire\Admin\Topup\Approval::class)->name('topup.approvals');
});

// ========================================
// MIDTRANS PAYMENT ROUTES (Public - No Auth)
// ========================================
Route::prefix('topup')->name('topup.')->group(function () {
    Route::get('/finish', [\App\Http\Controllers\TopupController::class, 'finish'])->name('finish');
    Route::get('/unfinish', [\App\Http\Controllers\TopupController::class, 'unfinish'])->name('unfinish');
    Route::get('/error', [\App\Http\Controllers\TopupController::class, 'error'])->name('error');
    Route::get('/success', [\App\Http\Controllers\TopupController::class, 'success'])->name('success');
    Route::post('/notification', [\App\Http\Controllers\TopupController::class, 'notification'])->name('notification');
    Route::post('/client-callback', [\App\Http\Controllers\TopupController::class, 'clientCallback'])->name('client-callback');
});

// Public callback endpoint used by payment gateway integrations for withdraw disbursements
Route::post('/gateway/callback', [\App\Http\Controllers\WithdrawController::class, 'gatewayCallback'])->name('gateway.callback');

require __DIR__ . '/auth.php';