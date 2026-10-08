<?php

namespace Tests\Feature\Customer;

use App\Livewire\Customer\Topup\TopupRequest;
use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TopupSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        AppSetting::set('topup_qris_image', 'images/payment/custom_qris.png');
        AppSetting::set('topup_qris_merchant_name', 'PT SayaBantu');
        AppSetting::set('topup_qris_nmid', 'ID1234567890');
    }

    public function test_single_topup_submission_creates_one_transaction(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
        ]);

        $file = UploadedFile::fake()->image('bukti.png', 300, 300);

        Livewire::actingAs($user)
            ->test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('customerPhone', '081234567890')
            ->call('nextStep') // to step 2
            ->call('nextStep') // to step 3
            ->set('proofOfPayment', $file)
            ->call('submitRequest')
            ->assertRedirect(route('customer.transactions.index'));

        $this->assertDatabaseCount('balance_transactions', 1);
        $this->assertDatabaseHas('balance_transactions', [
            'user_id' => $user->id,
            'amount' => 50000,
            'type' => 'topup',
            'status' => 'waiting_approval',
        ]);
    }

    public function test_rapid_consecutive_submission_does_not_create_duplicate(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
        ]);

        $file = UploadedFile::fake()->image('bukti.png', 300, 300);

        $component = Livewire::actingAs($user)
            ->test(TopupRequest::class)
            ->set('amount', 100000)
            ->set('customerPhone', '081234567890')
            ->call('nextStep')
            ->call('nextStep')
            ->set('proofOfPayment', $file);

        // First submit
        $component->call('submitRequest');

        // Immediate second submit (simulating double click)
        $component->call('submitRequest');

        $this->assertDatabaseCount('balance_transactions', 1);
        $this->assertEquals(1, BalanceTransaction::where('user_id', $user->id)->where('type', 'topup')->count());
    }
}
