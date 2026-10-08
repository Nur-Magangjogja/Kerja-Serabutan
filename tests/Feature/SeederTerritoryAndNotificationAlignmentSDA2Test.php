<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Carbon\Carbon;
use Database\Seeders\ActivityLogsSeeder;
use Database\Seeders\AdminCitySeeder;
use Database\Seeders\AdminNotificationSeeder;
use Database\Seeders\AppSettingsSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\HelpsSeeder;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\RegistrationsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\UserBalancesSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeederTerritoryAndNotificationAlignmentSDA2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * 1. Seeded User Profile Territory is valid, matches database hierarchy, and district belongs to city.
     */
    public function test_seeded_users_retain_valid_profile_territory(): void
    {
        $users = User::whereIn('role', ['admin', 'mitra', 'customer'])->get();
        $this->assertNotEmpty($users);

        foreach ($users as $user) {
            $this->assertNotNull($user->city_id, "User {$user->email} harus memiliki city_id.");
            $this->assertNotNull($user->district_id, "User {$user->email} harus memiliki district_id.");

            $district = District::find($user->district_id);
            $this->assertNotNull($district, "District id {$user->district_id} harus ada di database.");
            $this->assertSame((int) $user->city_id, (int) $district->city_id, "District {$district->name} harus milik City id {$user->city_id}.");
        }
    }

    /**
     * 2. Admin authority comes from canonical pivots (`admin_district` and `admin_city`), not inferred solely from user profile.
     */
    public function test_admin_pivots_provide_territorial_authority(): void
    {
        $authService = app(AdminTerritoryAuthorizationService::class);

        $adminSleman = User::where('email', 'admin.sleman@sayabantu.com')->firstOrFail();
        $adminDIY = User::where('email', 'admin@sayabantu.com')->firstOrFail();

        $slemanCity = City::where('code', '3404')->firstOrFail();
        $ngaglik = District::where('city_id', $slemanCity->id)->where('name', 'like', '%Ngaglik%')->firstOrFail();

        $jogjaCity = City::where('code', '3471')->firstOrFail();
        $gondomanan = District::where('city_id', $jogjaCity->id)->where('name', 'like', '%Gondomanan%')->firstOrFail();

        // Admin Sleman has authority in Ngaglik, Sleman
        $this->assertTrue($authService->canAccessTerritory($adminSleman, $ngaglik->id, $slemanCity->id));
        // Admin Sleman DOES NOT have authority in Gondomanan, Yogyakarta
        $this->assertFalse($authService->canAccessTerritory($adminSleman, $gondomanan->id, $jogjaCity->id));

        // Admin DIY has authority in Gondomanan, Yogyakarta
        $this->assertTrue($authService->canAccessTerritory($adminDIY, $gondomanan->id, $jogjaCity->id));
        // Admin DIY DOES NOT have authority in Ngaglik, Sleman
        $this->assertFalse($authService->canAccessTerritory($adminDIY, $ngaglik->id, $slemanCity->id));
    }

    /**
     * 3. Deterministic Cross-Territory Help exists where Help location != Customer Profile territory.
     */
    public function test_deterministic_cross_territory_help_exists_and_isolates_case_authority(): void
    {
        $authService = app(AdminTerritoryAuthorizationService::class);

        $customerSleman = User::where('email', 'customer@sayabantu.com')->firstOrFail();
        $slemanCity = City::where('code', '3404')->firstOrFail();
        $ngaglik = District::where('city_id', $slemanCity->id)->where('name', 'like', '%Ngaglik%')->firstOrFail();

        // Customer Profile Territory is Sleman / Ngaglik
        $this->assertSame((int) $slemanCity->id, (int) $customerSleman->city_id);
        $this->assertSame((int) $ngaglik->id, (int) $customerSleman->district_id);

        // Find the cross-territory job
        $crossHelp = Help::where('order_id', 'HLP-SEED-0002')->firstOrFail();

        $jogjaCity = City::where('code', '3471')->firstOrFail();
        $gondomanan = District::where('city_id', $jogjaCity->id)->where('name', 'like', '%Gondomanan%')->firstOrFail();

        // Verify Help belongs to Customer Sleman, but Help Territory is Gondomanan, Yogyakarta
        $this->assertSame((int) $customerSleman->id, (int) $crossHelp->user_id);
        $this->assertSame((int) $jogjaCity->id, (int) $crossHelp->city_id);
        $this->assertSame((int) $gondomanan->id, (int) $crossHelp->district_id);

        $adminSleman = User::where('email', 'admin.sleman@sayabantu.com')->firstOrFail();
        $adminDIY = User::where('email', 'admin@sayabantu.com')->firstOrFail();

        // Case Admin (Admin DIY) has authority over the Help
        $this->assertTrue($authService->canAccessHelp($adminDIY, $crossHelp));

        // Profile Admin (Admin Sleman) DOES NOT have case authority over the foreign Help
        $this->assertFalse($authService->canAccessHelp($adminSleman, $crossHelp));
    }

    /**
     * 4. Help statuses and lifecycle timestamps are mutually consistent with canonical state machine.
     */
    public function test_help_status_and_timestamps_consistency(): void
    {
        $helps = Help::all();
        $this->assertGreaterThanOrEqual(7, $helps->count());

        foreach ($helps as $help) {
            $this->assertNotNull($help->city_id);
            $this->assertNotNull($help->district_id);

            $dist = District::find($help->district_id);
            $this->assertNotNull($dist);
            $this->assertSame((int) $help->city_id, (int) $dist->city_id);

            if ($help->status === 'menunggu_mitra') {
                $this->assertNull($help->mitra_id);
                $this->assertSame('pool', $help->dispatch_mode);
            }

            if (in_array($help->status, ['partner_on_the_way', 'in_progress', 'waiting_customer_confirmation', 'selesai', 'disputed'], true)) {
                $this->assertNotNull($help->mitra_id, "Help {$help->order_id} dengan status {$help->status} harus memiliki mitra_id.");
                $this->assertSame('assigned', $help->dispatch_mode);
            }

            if ($help->status === 'selesai') {
                $this->assertNotNull($help->completed_at, "Help {$help->order_id} selesai harus memiliki completed_at.");
                $this->assertSame('released', $help->escrow_status);
            }

            if ($help->status === 'disputed') {
                $this->assertNotNull($help->disputed_at, "Help {$help->order_id} dispute harus memiliki disputed_at.");
                $this->assertSame('locked', $help->escrow_status);
            }
        }
    }

    /**
     * 5. Multi-period Help data spans current month, previous month, older month, and previous year.
     */
    public function test_multi_period_helps_cover_multiple_months_and_years(): void
    {
        $now = now();
        $currentYear = (int) $now->year;
        $currentMonth = (int) $now->month;

        $hasCurrentMonth = Help::whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)->exists();
        $this->assertTrue($hasCurrentMonth, 'Harus ada seeded Help di bulan berjalan.');

        $prevMonthDt = $now->copy()->subMonth();
        $hasPrevMonth = Help::whereYear('created_at', (int) $prevMonthDt->year)->whereMonth('created_at', (int) $prevMonthDt->month)->exists();
        $this->assertTrue($hasPrevMonth, 'Harus ada seeded Help di bulan sebelumnya.');

        $olderMonthDt = $now->copy()->subMonths(3);
        $hasOlderMonth = Help::whereYear('created_at', (int) $olderMonthDt->year)->whereMonth('created_at', (int) $olderMonthDt->month)->exists();
        $this->assertTrue($hasOlderMonth, 'Harus ada seeded Help di 3 bulan lalu.');

        $prevYearDt = $now->copy()->subYear()->subMonths(2);
        $hasPrevYear = Help::whereYear('created_at', (int) $prevYearDt->year)->exists();
        $this->assertTrue($hasPrevYear, 'Harus ada seeded Help di tahun sebelumnya.');
    }

    /**
     * 6. Admin Account notifications are delivered strictly to Admins matching Profile Territory.
     */
    public function test_admin_notification_routes_account_notifications_to_profile_territory_admins_only(): void
    {
        $adminSleman = User::where('email', 'admin.sleman@sayabantu.com')->firstOrFail();
        $adminDIY = User::where('email', 'admin@sayabantu.com')->firstOrFail();
        $adminSolo = User::where('email', 'admin.solo@sayabantu.com')->firstOrFail();

        // Registration notification (registrant in Sleman)
        $slemanRegNotifCount = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $adminSleman->id)
            ->where('data', 'like', '%Pendaftaran Mitra Baru Perlu Verifikasi%')
            ->count();
        $this->assertSame(1, $slemanRegNotifCount, 'Admin Sleman harus menerima notifikasi pendaftaran mitra Sleman.');

        $diyRegNotifCount = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $adminDIY->id)
            ->where('data', 'like', '%Pendaftaran Mitra Baru Perlu Verifikasi%')
            ->count();
        $this->assertSame(0, $diyRegNotifCount, 'Admin DIY TIDAK boleh menerima notifikasi pendaftaran mitra Sleman.');

        $soloRegNotifCount = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $adminSolo->id)
            ->where('data', 'like', '%Pendaftaran Mitra Baru Perlu Verifikasi%')
            ->count();
        $this->assertSame(0, $soloRegNotifCount, 'Admin Solo TIDAK boleh menerima notifikasi pendaftaran mitra Sleman.');
    }

    /**
     * 7. Admin Job notifications are delivered strictly to Admins matching Help Territory.
     */
    public function test_admin_notification_routes_job_notifications_to_case_territory_admins_only(): void
    {
        $adminSleman = User::where('email', 'admin.sleman@sayabantu.com')->firstOrFail();
        $adminDIY = User::where('email', 'admin@sayabantu.com')->firstOrFail();

        // Dispute in Sleman
        $slemanDisputeNotifCount = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $adminSleman->id)
            ->where('data', 'like', '%Sengketa Pesanan Baru Perlu Ditinjau%')
            ->count();
        $this->assertSame(1, $slemanDisputeNotifCount);

        $diyDisputeNotifCount = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $adminDIY->id)
            ->where('data', 'like', '%Sengketa Pesanan Baru Perlu Ditinjau%')
            ->count();
        $this->assertSame(0, $diyDisputeNotifCount);
    }

    /**
     * 8. User notifications have referential integrity without fake IDs.
     */
    public function test_user_notifications_have_valid_referential_integrity_without_fake_ids(): void
    {
        $customer = User::where('email', 'customer@sayabantu.com')->firstOrFail();
        $mitra = User::where('email', 'mitra@sayabantu.com')->firstOrFail();

        $custNotifs = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $customer->id)
            ->get();

        foreach ($custNotifs as $n) {
            $data = json_decode($n->data, true);
            if (!empty($data['help_id'])) {
                $help = Help::find($data['help_id']);
                $this->assertNotNull($help, "Help id {$data['help_id']} pada notifikasi customer harus ada di database.");
                $this->assertSame((int) $customer->id, (int) $help->user_id, "Help {$help->id} harus milik customer.");
            }
        }

        $mitraNotifs = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $mitra->id)
            ->get();

        foreach ($mitraNotifs as $n) {
            $data = json_decode($n->data, true);
            if (!empty($data['help_id'])) {
                $help = Help::find($data['help_id']);
                $this->assertNotNull($help, "Help id {$data['help_id']} pada notifikasi mitra harus ada di database.");
                $this->assertSame((int) $mitra->id, (int) $help->mitra_id, "Help {$help->id} harus ditugaskan ke mitra.");
            }
        }
    }

    /**
     * 9. Rerunning seeders is completely idempotent without creating duplicate logical records.
     */
    public function test_notification_and_activity_log_seeders_are_strictly_idempotent(): void
    {
        $initialNotifCount = DB::table('notifications')->count();
        $initialActivityLogCount = ActivityLog::count();
        $initialHelpCount = Help::count();

        // Run seeders a second time
        $this->seed(HelpsSeeder::class);
        $this->seed(AdminNotificationSeeder::class);
        $this->seed(NotificationSeeder::class);
        $this->seed(ActivityLogsSeeder::class);

        $this->assertSame($initialHelpCount, Help::count(), 'Jumlah Help tidak boleh bertambah saat seeder dijalankan ulang.');
        $this->assertSame($initialNotifCount, DB::table('notifications')->count(), 'Jumlah Notifikasi tidak boleh bertambah saat seeder dijalankan ulang.');
        $this->assertSame($initialActivityLogCount, ActivityLog::count(), 'Jumlah ActivityLog tidak boleh bertambah saat seeder dijalankan ulang.');
    }
}
