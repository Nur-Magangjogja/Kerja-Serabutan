<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerActivity::with('user');

        // Filter by admin's city if user is admin
        // Admin hanya melihat aktivitas customer dan mitra (bukan admin lain)
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $query->whereHas('user', function ($q) {
                $q->where('city_id', auth()->user()->city_id)
                  ->whereIn('role', ['customer', 'mitra']); // Hanya customer dan mitra
            });
        }

        // Filter search: user name/email, description, IP
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filter activity type
        if ($type = $request->get('type')) {
            if ($type !== 'all') {
                $query->where('activity_type', $type);
            }
        }

        // Filter date range
        if ($start = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $start);
        }

        if ($end = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $end);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $selectedActivity = null;
        $recentActivities = collect();
        $suspicious = [
            'flag' => false,
            'reasons' => [],
        ];

        if ($activityId = $request->get('activity_id')) {
            $selectedActivity = PartnerActivity::with('user')->find($activityId);

            if ($selectedActivity) {
                $recentStart = $selectedActivity->created_at->copy()->subDay();
                $recentEnd = $selectedActivity->created_at->copy();

                $recentActivities = PartnerActivity::with('user')
                    ->where('user_id', $selectedActivity->user_id)
                    ->where('id', '<>', $selectedActivity->id)
                    ->whereBetween('created_at', [$recentStart, $recentEnd])
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                $suspicious = $this->detectSuspicious($selectedActivity, $recentActivities);
            }
        }

        $activityTypes = PartnerActivity::select('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        return view('admin.partners.activity', compact('activities', 'activityTypes', 'selectedActivity', 'recentActivities', 'suspicious'));
    }

    public function exportCsv(Request $request)
    {
        $filename = 'aktivitas_mitra_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['User', 'Aktivitas', 'Deskripsi', 'IP', 'User Agent', 'Waktu']);

            $this->buildFilteredQuery($request)
                ->orderBy('created_at', 'desc')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            optional($row->user)->name,
                            $row->activity_type,
                            $row->description,
                            $row->ip_address,
                            $row->user_agent,
                            optional($row->created_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filename = 'aktivitas_mitra_' . now()->format('Ymd_His') . '.xls';

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['User', 'Aktivitas', 'Deskripsi', 'IP', 'User Agent', 'Waktu'], "\t");

            $this->buildFilteredQuery($request)
                ->orderBy('created_at', 'desc')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            optional($row->user)->name,
                            $row->activity_type,
                            $row->description,
                            $row->ip_address,
                            $row->user_agent,
                            optional($row->created_at)->format('Y-m-d H:i:s'),
                        ], "\t");
                    }
                });

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function exportPrint(Request $request)
    {
        $activities = $this->buildFilteredQuery($request)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        return view('admin.partners.activity_print', compact('activities'));
    }

    protected function buildFilteredQuery(Request $request)
    {
        $query = PartnerActivity::with('user');

        // Filter by admin's city if user is admin
        // Admin hanya melihat aktivitas customer dan mitra (bukan admin lain)
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $query->whereHas('user', function ($q) {
                $q->where('city_id', auth()->user()->city_id)
                  ->whereIn('role', ['customer', 'mitra']); // Hanya customer dan mitra
            });
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            if ($type !== 'all') {
                $query->where('activity_type', $type);
            }
        }

        if ($start = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $start);
        }

        if ($end = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $end);
        }

        return $query;
    }

    public function resetSessions($userId)
    {
        DB::table('sessions')->where('user_id', $userId)->delete();

        return back()->with('success', 'Sesi login mitra telah direset.');
    }

    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);

        $newPassword = Str::random(12);
        $user->password = bcrypt($newPassword);
        $user->save();

        return back()->with('success', 'Password mitra telah direset. Password baru: ' . $newPassword);
    }

    protected function detectSuspicious(PartnerActivity $selectedActivity, $recentActivities): array
    {
        $reasons = [];

        $all = collect([$selectedActivity])->merge($recentActivities);

        $failedLogins = $all->where('activity_type', 'login_failed')
            ->where('created_at', '>=', now()->subMinutes(10));
        if ($failedLogins->count() >= 5) {
            $reasons[] = 'Banyak login gagal dalam 10 menit terakhir.';
        }

        $ips = $all->pluck('ip_address')->filter()->unique();
        if ($ips->count() >= 3) {
            $reasons[] = 'IP address berubah beberapa kali dalam 24 jam.';
        }

        if ($selectedActivity->activity_type === 'login') {
            $hour = (int) $selectedActivity->created_at->format('H');
            if ($hour <= 4 || $hour >= 23) {
                $reasons[] = 'Login pada jam tidak wajar.';
            }
        }

        return [
            'flag' => !empty($reasons),
            'reasons' => $reasons,
        ];
    }
}
