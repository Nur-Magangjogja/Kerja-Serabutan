<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AdminSecurityStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_active_admin_can_login_successfully()
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Aktif',
            'email'    => 'admin.aktif@sayabantu.com',
            'role'     => 'admin',
            'status'   => 'active',
            'verified' => true,
            'password' => Hash::make('password123'),
        ]);

        Volt::test('pages.auth.login')
            ->set('form.email', 'admin.aktif@sayabantu.com')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_inactive_admin_login_is_rejected_with_specific_error()
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Nonaktif',
            'email'    => 'admin.nonaktif@sayabantu.com',
            'role'     => 'admin',
            'status'   => 'inactive',
            'verified' => true,
            'password' => Hash::make('password123'),
        ]);

        Volt::test('pages.auth.login')
            ->set('form.email', 'admin.nonaktif@sayabantu.com')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasErrors(['form.email' => 'Akun Admin Wilayah Anda sedang dinonaktifkan. Silakan hubungi Super Admin.']);

        $this->assertGuest();
    }

    public function test_inactive_admin_accessing_admin_routes_is_logged_out_and_redirected()
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Sesi Putus',
            'email'    => 'admin.putus@sayabantu.com',
            'role'     => 'admin',
            'status'   => 'inactive',
            'verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.appearance'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Akun Admin Wilayah Anda sedang dinonaktifkan. Silakan hubungi Super Admin.');
        $this->assertGuest();
    }

    public function test_active_admin_can_access_admin_routes()
    {
        $city = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta']);
        $district = District::create(['city_id' => $city->id, 'name' => 'Danurejan']);
        $admin = User::factory()->create([
            'name'        => 'Admin Wilayah Aktif',
            'email'       => 'admin.ok@sayabantu.com',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $city->id,
            'district_id' => $district->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.appearance'));

        $response->assertOk();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_superadmin_is_not_affected_by_admin_inactive_rules()
    {
        $superAdmin = User::factory()->create([
            'name'     => 'Super Admin Utama',
            'email'    => 'superadmin@sayabantu.com',
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
            'password' => Hash::make('password123'),
        ]);

        // Login Super Admin berhasil
        Volt::test('pages.auth.login')
            ->set('form.email', 'superadmin@sayabantu.com')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('superadmin.dashboard', absolute: false));

        // Akses dashboard superadmin sukses
        $response = $this->actingAs($superAdmin)->get(route('superadmin.dashboard'));
        $response->assertOk();
    }

    public function test_admin_re_activated_must_login_again_and_old_session_invalidated()
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Reaktivasi',
            'email'    => 'admin.reaktif@sayabantu.com',
            'role'     => 'admin',
            'status'   => 'inactive',
            'verified' => true,
            'password' => Hash::make('password123'),
        ]);

        // 1. Sesi lama saat inactive mengakses rute -> ditolak & logout
        $response = $this->actingAs($admin)->get(route('admin.settings.appearance'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();

        // 2. Super Admin mengaktifkan kembali admin
        $admin->update(['status' => 'active']);

        // 3. Request berikutnya tanpa login ulang tetap harus diarahkan ke login (Guest)
        $responseAfterActive = $this->get(route('admin.settings.appearance'));
        $responseAfterActive->assertRedirect(route('login'));

        session()->forget('url.intended');

        // 4. Setelah login ulang dengan kredensial sah, akses kembali terbuka
        Volt::test('pages.auth.login')
            ->set('form.email', 'admin.reaktif@sayabantu.com')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_inactive_admin_subsequent_livewire_action_is_rejected_by_persistent_middleware()
    {
        $city = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta']);
        $district = District::create(['city_id' => $city->id, 'name' => 'Danurejan']);

        $admin = User::factory()->create([
            'name'        => 'Admin Wilayah Aktif',
            'email'       => 'admin.livewire@sayabantu.com',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $city->id,
            'district_id' => $district->id,
        ]);

        $customer = User::factory()->create([
            'role'        => 'customer',
            'city_id'     => $city->id,
            'district_id' => $district->id,
        ]);

        $help = Help::create([
            'user_id'       => $customer->id,
            'customer_id'   => $customer->id,
            'city_id'       => $city->id,
            'district_id'   => $district->id,
            'title'         => 'Bantuan Pengujian Keamanan',
            'description'   => 'Deskripsi uji coba',
            'amount'        => 50000,
            'total_amount'  => 50000,
            'status'        => 'menunggu_admin',
            'dispatch_mode' => 'pool',
        ]);

        // 1. Admin aktif memuat halaman Livewire Admin (GET route dengan middleware EnsureAdmin)
        $initialResponse = $this->actingAs($admin)->get(route('admin.helps'));
        $initialResponse->assertOk();

        // Ekstraksi snapshot Livewire dari halaman yang baru dimuat
        preg_match('/wire:snapshot="([^"]+)"/', $initialResponse->getContent(), $matches);
        $this->assertNotEmpty($matches, 'Snapshot Livewire harus ditemukan pada halaman render admin.');
        $snapshot = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);

        // 2. Status Admin di database diubah menjadi inactive oleh Super Admin
        $admin->update(['status' => 'inactive']);

        // 3. Admin melakukan subsequent Livewire action (POST /livewire/update) tanpa reload halaman
        $updateResponse = $this->postJson('/livewire/update', [
            'components' => [
                [
                    'snapshot' => $snapshot,
                    'updates'  => [],
                    'calls'    => [
                        [
                            'path'   => '',
                            'method' => 'approveHelp',
                            'params' => [$help->id],
                        ],
                    ],
                ],
            ],
        ], [
            'X-Livewire' => 'true',
        ]);

        // 4. Assert:
        // - EnsureAdmin persistent middleware menolak request dan meredirect ke login
        $updateResponse->assertRedirect(route('login'));

        // - Action bisnis tidak dieksekusi (status help tidak berubah)
        $help->refresh();
        $this->assertEquals('menunggu_admin', $help->status);

        // - Session authentication Admin telah berakhir (logged out)
        $this->assertGuest();

        // - Sesi lama tidak dapat digunakan lagi untuk request berikutnya
        $nextResponse = $this->get(route('admin.helps'));
        $nextResponse->assertRedirect(route('login'));
    }
}
