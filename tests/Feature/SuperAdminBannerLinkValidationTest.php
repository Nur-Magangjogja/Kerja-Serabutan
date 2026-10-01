<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Banners\Index;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminBannerLinkValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_valid_full_urls_pass_validation()
    {
        $superAdmin = $this->createSuperAdmin();

        AppSetting::set('banner_customer', json_encode([
            ['image' => 'banners/cust1.jpg', 'link' => 'https://sayabantu.com/promo'],
        ]));
        AppSetting::set('banner_mitra', json_encode([
            ['image' => 'banners/mitra1.jpg', 'link' => 'https://sayabantu.com/mitra/helps'],
        ]));

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('customerBanners.0.link', 'https://sayabantu.com/new-promo')
            ->set('mitraBanners.0.link', 'http://partner.sayabantu.com/info')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('bannersSaved')
            ->assertSee('Konfigurasi gambar banner dan link tujuan berhasil disimpan.');

        $savedCust = json_decode((string) AppSetting::get('banner_customer'), true);
        $this->assertEquals('https://sayabantu.com/new-promo', $savedCust[0]['link']);

        $savedMitra = json_decode((string) AppSetting::get('banner_mitra'), true);
        $this->assertEquals('http://partner.sayabantu.com/info', $savedMitra[0]['link']);
    }

    public function test_empty_or_whitespace_links_pass_validation_since_link_is_optional()
    {
        $superAdmin = $this->createSuperAdmin();

        AppSetting::set('banner_customer', json_encode([
            ['image' => 'banners/cust1.jpg', 'link' => 'https://sayabantu.com/promo'],
        ]));

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('customerBanners.0.link', '')
            ->call('save')
            ->assertHasNoErrors();

        $savedCust = json_decode((string) AppSetting::get('banner_customer'), true);
        $this->assertEquals('', $savedCust[0]['link']);
    }

    public function test_internal_relative_path_fails_validation()
    {
        $superAdmin = $this->createSuperAdmin();

        AppSetting::set('banner_customer', json_encode([
            ['image' => 'banners/cust1.jpg', 'link' => ''],
        ]));

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('customerBanners.0.link', '/customer/helps')
            ->call('save')
            ->assertHasErrors(['customerBanners.0.link'])
            ->assertSee('Format link tujuan tidak boleh menggunakan path internal (contoh: /customer/helps)');
    }

    public function test_invalid_link_fails_validation_with_error_message()
    {
        $superAdmin = $this->createSuperAdmin();

        AppSetting::set('banner_customer', json_encode([
            ['image' => 'banners/cust1.jpg', 'link' => ''],
        ]));
        AppSetting::set('banner_mitra', json_encode([
            ['image' => 'banners/mitra1.jpg', 'link' => ''],
        ]));

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('customerBanners.0.link', 'invalid-link-without-protocol-or-slash')
            ->set('mitraBanners.0.link', 'htp:/typo-url')
            ->call('save')
            ->assertHasErrors(['customerBanners.0.link', 'mitraBanners.0.link'])
            ->assertSee('Terdapat kesalahan format pada link atau file banner:')
            ->assertSee('Format link tujuan tidak valid. Masukkan alamat URL lengkap');
    }

    public function test_dangerous_javascript_scheme_fails_validation()
    {
        $superAdmin = $this->createSuperAdmin();

        AppSetting::set('banner_customer', json_encode([
            ['image' => 'banners/cust1.jpg', 'link' => ''],
        ]));

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('customerBanners.0.link', 'javascript:alert("hacked")')
            ->call('save')
            ->assertHasErrors(['customerBanners.0.link'])
            ->assertSee('Format link tujuan tidak aman.');
    }

    public function test_invalid_new_upload_link_fails_validation()
    {
        $superAdmin = $this->createSuperAdmin();

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('customerNewLinks.0', 'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==')
            ->set('mitraNewLinks.0', 'httpp://typo-new-link')
            ->call('save')
            ->assertHasErrors(['customerNewLinks.0', 'mitraNewLinks.0'])
            ->assertSee('Format link tujuan tidak aman.')
            ->assertSee('Format link tujuan tidak valid. Masukkan alamat URL lengkap');
    }
}
