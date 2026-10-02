<?php

namespace Tests\Feature;

use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyPaymentGatewayCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected City $city;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'      => 'Kota Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'latitude'  => -7.7930,
            'longitude' => 110.3700,
        ]);

        $this->user = User::factory()->create([
            'name'        => 'Mitra User',
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        UserBalance::create([
            'user_id' => $this->user->id,
            'balance' => 50000,
        ]);
    }

    public function test_gateway_callback_endpoint_is_hard_rejected_as_disabled(): void
    {
        $response = $this->postJson('/gateway/callback', [
            'external_id' => 'gw_test1234567890',
            'status'      => 'success',
        ]);

        $response->assertStatus(503);
        $response->assertJson([
            'error' => 'gateway_callback_disabled',
        ]);
    }

    public function test_external_callback_does_not_mutate_balance_status_or_ledger_on_success(): void
    {
        $withdraw = WithdrawRequest::create([
            'user_id'        => $this->user->id,
            'amount'         => 50000,
            'admin_fee'      => 2500,
            'net_amount'     => 47500,
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra User',
            'status'         => WithdrawRequest::STATUS_PENDING,
            'external_id'    => 'gw_ext_99999',
        ]);

        $initialBalance = (float) UserBalance::where('user_id', $this->user->id)->value('balance');
        $initialTxCount = BalanceTransaction::count();

        // Send simulated success callback
        $response = $this->postJson('/gateway/callback', [
            'external_id' => 'gw_ext_99999',
            'status'      => 'success',
        ]);

        $response->assertStatus(503);

        // Assert: No status mutation
        $withdraw->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdraw->status);

        // Assert: No balance mutation
        $currentBalance = (float) UserBalance::where('user_id', $this->user->id)->value('balance');
        $this->assertEquals($initialBalance, $currentBalance);

        // Assert: No ledger created
        $this->assertEquals($initialTxCount, BalanceTransaction::count());
    }

    public function test_external_callback_does_not_mutate_balance_status_or_ledger_on_failure(): void
    {
        $withdraw = WithdrawRequest::create([
            'user_id'        => $this->user->id,
            'amount'         => 50000,
            'admin_fee'      => 2500,
            'net_amount'     => 47500,
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra User',
            'status'         => WithdrawRequest::STATUS_PENDING,
            'external_id'    => 'gw_ext_88888',
        ]);

        $initialBalance = (float) UserBalance::where('user_id', $this->user->id)->value('balance');
        $initialTxCount = BalanceTransaction::count();

        // Send simulated failure callback multiple times
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/gateway/callback', [
                'external_id' => 'gw_ext_88888',
                'status'      => 'failed',
            ]);
            $response->assertStatus(503);
        }

        // Assert: No status mutation
        $withdraw->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdraw->status);

        // Assert: No balance mutation (no refund / double refund)
        $currentBalance = (float) UserBalance::where('user_id', $this->user->id)->value('balance');
        $this->assertEquals($initialBalance, $currentBalance);

        // Assert: No ledger created
        $this->assertEquals($initialTxCount, BalanceTransaction::count());
    }

    public function test_dormant_payment_gateway_service_ignores_callbacks_without_side_effects(): void
    {
        $withdraw = WithdrawRequest::create([
            'user_id'        => $this->user->id,
            'amount'         => 50000,
            'admin_fee'      => 2500,
            'net_amount'     => 47500,
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra User',
            'status'         => WithdrawRequest::STATUS_PENDING,
            'external_id'    => 'gw_ext_77777',
        ]);

        $initialBalance = (float) UserBalance::where('user_id', $this->user->id)->value('balance');

        $service = app(PaymentGatewayService::class);
        $service->handleGatewayCallback([
            'external_id' => 'gw_ext_77777',
            'status'      => 'failed',
        ]);

        // Balances and status remain completely untouched
        $this->assertEquals($initialBalance, (float) UserBalance::where('user_id', $this->user->id)->value('balance'));
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdraw->fresh()->status);
    }
}
