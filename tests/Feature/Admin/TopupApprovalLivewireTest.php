<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Topup\Approval as AdminApproval;
use App\Livewire\SuperAdmin\Topup\Approval as SuperAdminApproval;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TopupApprovalLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();
        $this->city = City::create([
            'name'      => 'Sleman',
            'province'  => 'DI Yogyakarta',
            'latitude'  => -7.7156,
            'longitude' => 110.3556,
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_view_detail_and_approve_topup(): void
    {
        $superAdmin = User::factory()->create([
            'role'   => 'super_admin',
            'status' => 'active',
            'verified' => true,
        ]);

        $customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
            'status'  => 'active',
            'verified' => true,
        ]);

        $transaction = BalanceTransaction::create([
            'user_id' => $customer->id,
            'amount' => 50000,
            'type' => 'topup',
            'description' => 'Topup Saldo QRIS',
            'request_code' => 'TPU-TEST-001',
            'status' => 'waiting_approval',
        ]);

        Livewire::actingAs($superAdmin)
            ->test(SuperAdminApproval::class)
            ->call('viewDetail', $transaction->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedTransaction.id', $transaction->id)
            ->call('approve', $transaction->id)
            ->assertSet('showDetailModal', false)
            ->assertSee('berhasil disetujui');

        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals($superAdmin->id, $transaction->approved_by);

        $customerBalance = UserBalance::where('user_id', $customer->id)->first();
        $this->assertNotNull($customerBalance);
        $this->assertEquals(50000, $customerBalance->balance);
    }

    public function test_admin_can_view_detail_and_approve_topup_in_same_city(): void
    {
        $admin = User::factory()->create([
            'role'    => 'admin',
            'city_id' => $this->city->id,
            'status'  => 'active',
            'verified' => true,
        ]);

        $customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
            'status'  => 'active',
            'verified' => true,
        ]);

        $transaction = BalanceTransaction::create([
            'user_id' => $customer->id,
            'amount' => 75000,
            'type' => 'topup',
            'description' => 'Topup Saldo QRIS',
            'request_code' => 'TPU-TEST-002',
            'status' => 'waiting_approval',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminApproval::class)
            ->call('viewDetail', $transaction->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedTransaction.id', $transaction->id)
            ->call('approve', $transaction->id)
            ->assertSet('showDetailModal', false)
            ->assertSee('berhasil disetujui');

        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals($admin->id, $transaction->approved_by);

        $customerBalance = UserBalance::where('user_id', $customer->id)->first();
        $this->assertNotNull($customerBalance);
        $this->assertEquals(75000, $customerBalance->balance);
    }

    public function test_admin_can_reject_topup_with_reason(): void
    {
        $admin = User::factory()->create([
            'role'    => 'admin',
            'city_id' => $this->city->id,
            'status'  => 'active',
            'verified' => true,
        ]);

        $customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
            'status'  => 'active',
            'verified' => true,
        ]);

        $transaction = BalanceTransaction::create([
            'user_id' => $customer->id,
            'amount' => 100000,
            'type' => 'topup',
            'description' => 'Topup Saldo QRIS',
            'request_code' => 'TPU-TEST-003',
            'status' => 'waiting_approval',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminApproval::class)
            ->call('openRejectModal', $transaction->id)
            ->assertSet('showRejectModal', true)
            ->set('rejectionReason', 'Bukti transfer buram tidak terbaca')
            ->call('reject')
            ->assertSet('showRejectModal', false)
            ->assertSee('ditolak');

        $transaction->refresh();
        $this->assertEquals('rejected', $transaction->status);
        $this->assertEquals('Bukti transfer buram tidak terbaca', $transaction->rejection_reason);
    }
}
