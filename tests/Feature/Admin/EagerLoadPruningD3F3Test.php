<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Disputes\Index;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EagerLoadPruningD3F3Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $partner1;
    protected User $partner2;
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

        $this->admin = User::factory()->create([
            'name'        => 'Admin Danurejan',
            'role'        => 'admin',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        $this->admin->managedDistricts()->sync([$this->district->id]);

        $this->customer = User::factory()->create([
            'name'        => 'Customer John',
            'role'        => 'customer',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        $this->partner1 = User::factory()->create([
            'name'        => 'Mitra Budi',
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        $this->partner2 = User::factory()->create([
            'name'        => 'Mitra Agus',
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
    }

    protected function createCancelRequest(array $overrides = []): HelpCancelRequest
    {
        return HelpCancelRequest::create(array_merge([
            'previous_status' => 'taken',
            'action_type'     => 'partner_incident',
            'status'          => 'pending',
            'district_id'     => $this->district->id,
        ], $overrides));
    }

    public function test_cancellation_listing_eager_loads_clean_graph_without_sibling_cancel_requests(): void
    {
        $this->actingAs($this->admin);

        // Create a Help with 3 cancellation requests (historical + pending)
        $help = Help::create([
            'user_id'      => $this->customer->id,
            'mitra_id'     => $this->partner2->id,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'title'        => 'Perbaikan Pipa Air',
            'description'  => 'Pipa bocor di dapur',
            'amount'       => 100000,
            'total_amount' => 105000,
            'status'       => Help::STATUS_TAKEN,
        ]);

        // Historical cancel 1 by partner 1
        $this->createCancelRequest([
            'help_id'        => $help->id,
            'requested_by'   => $this->partner1->id,
            'requester_type' => 'partner',
            'partner_id'     => $this->partner1->id,
            'customer_id'    => $this->customer->id,
            'reason'         => 'Kendala motor mogok',
            'status'         => 'approved',
            'settlement_type'=> 'full_refund',
            'reviewed_by'    => $this->admin->id,
            'reviewed_at'    => now()->subDays(2),
        ]);

        // Historical cancel 2 by customer
        $this->createCancelRequest([
            'help_id'        => $help->id,
            'requested_by'   => $this->customer->id,
            'requester_type' => 'customer',
            'action_type'    => 'customer_withdraw',
            'partner_id'     => $this->partner1->id,
            'customer_id'    => $this->customer->id,
            'reason'         => 'Ingin ganti jadwal',
            'status'         => 'rejected',
            'reviewed_by'    => $this->admin->id,
            'reviewed_at'    => now()->subDay(),
        ]);

        // Pending cancel 3 by partner 2
        $pendingCancel = $this->createCancelRequest([
            'help_id'        => $help->id,
            'requested_by'   => $this->partner2->id,
            'requester_type' => 'partner',
            'partner_id'     => $this->partner2->id,
            'customer_id'    => $this->customer->id,
            'reason'         => 'Hujan lebat tidak bisa jalan',
            'status'         => 'pending',
        ]);

        // Render Livewire component
        $testable = Livewire::test(Index::class, ['activeTab' => 'cancellations'])
            ->set('activeTab', 'cancellations')
            ->set('status', 'all')
            ->assertOk()
            ->assertSee('Perbaikan Pipa Air')
            ->assertSee('Customer John')
            ->assertSee('Mitra Agus')
            ->assertSee('3x Aktivitas Pembatalan');

        // Verify the cancellation collection model did NOT hydrate cancelRequests relation on help
        $cancellations = $testable->viewData('cancellations');
        $this->assertNotEmpty($cancellations);
        $firstItem = $cancellations->first();
        $this->assertEquals(3, $firstItem->help->cancel_requests_count);
        $this->assertFalse($firstItem->help->relationLoaded('cancelRequests'), 'help.cancelRequests should NOT be eagerly loaded in listing');
    }

    public function test_expanding_row_loads_cancellation_history_on_demand(): void
    {
        $this->actingAs($this->admin);

        $help = Help::create([
            'user_id'      => $this->customer->id,
            'mitra_id'     => $this->partner1->id,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'title'        => 'Bersihkan Halaman',
            'description'  => 'Potong rumput',
            'amount'       => 50000,
            'status'       => Help::STATUS_TAKEN,
        ]);

        $this->createCancelRequest([
            'help_id'        => $help->id,
            'requested_by'   => $this->partner1->id,
            'requester_type' => 'partner',
            'partner_id'     => $this->partner1->id,
            'customer_id'    => $this->customer->id,
            'reason'         => 'Alat potong rusak di jalan',
            'status'         => 'pending',
        ]);

        $this->createCancelRequest([
            'help_id'        => $help->id,
            'requested_by'   => $this->customer->id,
            'requester_type' => 'customer',
            'action_type'    => 'customer_withdraw',
            'partner_id'     => $this->partner1->id,
            'customer_id'    => $this->customer->id,
            'reason'         => 'Perlu bantuan lain',
            'status'         => 'approved',
            'reviewed_by'    => $this->admin->id,
        ]);

        $component = Livewire::test(Index::class, ['activeTab' => 'cancellations'])
            ->set('status', 'all')
            ->assertDontSee('Kronologi Pembatalan Tugas Bantuan')
            ->call('toggleHelpLogs', $help->id)
            ->assertSee('Kronologi Pembatalan Tugas Bantuan')
            ->assertSee('Alat potong rusak di jalan')
            ->assertSee('Perlu bantuan lain');

        $this->assertTrue(in_array($help->id, $component->get('expandedHelpIds')));

        // Toggle back collapses the sub-row
        $component->call('toggleHelpLogs', $help->id)
            ->assertDontSee('Kronologi Pembatalan Tugas Bantuan');
        $this->assertFalse(in_array($help->id, $component->get('expandedHelpIds')));
    }

    public function test_cancellation_review_modal_loads_full_history_and_functions(): void
    {
        $this->actingAs($this->admin);

        $help = Help::create([
            'user_id'      => $this->customer->id,
            'mitra_id'     => $this->partner1->id,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'title'        => 'Cuci AC Ruang Tamu',
            'description'  => 'AC tidak dingin',
            'amount'       => 80000,
            'total_amount' => 82000,
            'status'       => Help::STATUS_TAKEN,
        ]);

        $cancelReq = $this->createCancelRequest([
            'help_id'        => $help->id,
            'requested_by'   => $this->partner1->id,
            'requester_type' => 'partner',
            'partner_id'     => $this->partner1->id,
            'customer_id'    => $this->customer->id,
            'reason'         => 'Freon habis tidak bawa alat cukup',
            'status'         => 'pending',
        ]);

        Livewire::test(Index::class, ['activeTab' => 'cancellations'])
            ->call('openCancelReviewModal', $cancelReq->id)
            ->assertSet('selectedCancelRequestId', $cancelReq->id)
            ->assertSet('showCancelReviewModal', true)
            ->assertSee('Freon habis tidak bawa alat cukup')
            ->set('cancelDecision', 'approved')
            ->set('settlementType', 'full_refund')
            ->set('cancelAdminNotes', 'Disetujui pembatalan karena kendala teknis')
            ->call('executeCancelReview')
            ->assertSet('showCancelReviewModal', false);

        $cancelReq->refresh();
        $this->assertEquals('approved', $cancelReq->status);
        $this->assertEquals('Disetujui pembatalan karena kendala teknis', $cancelReq->admin_notes);
    }

    public function test_cancellation_listing_has_no_n_plus_one_query_growth(): void
    {
        $this->actingAs($this->admin);

        // 1. Create 2 Helps with cancel requests
        for ($i = 1; $i <= 2; $i++) {
            $h = Help::create([
                'user_id'      => $this->customer->id,
                'mitra_id'     => $this->partner1->id,
                'city_id'      => $this->city->id,
                'district_id'  => $this->district->id,
                'title'        => "Pekerjaan {$i}",
                'description'  => "Deskripsi {$i}",
                'amount'       => 50000 + ($i * 1000),
                'status'       => Help::STATUS_TAKEN,
            ]);

            $this->createCancelRequest([
                'help_id'        => $h->id,
                'requested_by'   => $this->partner1->id,
                'requester_type' => 'partner',
                'partner_id'     => $this->partner1->id,
                'customer_id'    => $this->customer->id,
                'reason'         => "Alasan pembatalan {$i}",
                'status'         => 'pending',
            ]);
        }

        // Warm up component/service static initializers
        $warmup = new Index();
        $warmup->activeTab = 'cancellations';
        $warmup->status = 'all';
        $warmup->render();

        // Count queries for single render of 2 rows
        $component1 = new Index();
        $component1->activeTab = 'cancellations';
        $component1->status = 'all';

        DB::flushQueryLog();
        DB::enableQueryLog();
        $component1->render();
        $queries2Rows = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 2. Add 8 more Helps (total 10 rows)
        for ($i = 3; $i <= 10; $i++) {
            $h = Help::create([
                'user_id'      => $this->customer->id,
                'mitra_id'     => $this->partner1->id,
                'city_id'      => $this->city->id,
                'district_id'  => $this->district->id,
                'title'        => "Pekerjaan {$i}",
                'description'  => "Deskripsi {$i}",
                'amount'       => 50000 + ($i * 1000),
                'status'       => Help::STATUS_TAKEN,
            ]);

            $this->createCancelRequest([
                'help_id'        => $h->id,
                'requested_by'   => $this->partner1->id,
                'requester_type' => 'partner',
                'partner_id'     => $this->partner1->id,
                'customer_id'    => $this->customer->id,
                'reason'         => "Alasan pembatalan {$i}",
                'status'         => 'pending',
            ]);
        }

        // Count queries for single render of 10 rows
        $component2 = new Index();
        $component2->activeTab = 'cancellations';
        $component2->status = 'all';

        DB::flushQueryLog();
        DB::enableQueryLog();
        $component2->render();
        $queries10Rows = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Query count for 10 rows should equal query count for 2 rows (Zero N+1 linear increase)
        $this->assertEquals($queries2Rows, $queries10Rows, "Query count for 10 rows ({$queries10Rows}) differed from 2 rows ({$queries2Rows}), indicating N+1 queries.");
        $this->assertLessThanOrEqual(15, $queries10Rows);
    }

    public function test_territory_authorization_enforced_on_cancellation_listing_and_modal(): void
    {
        $otherDistrict = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Umbulharjo',
            'latitude'  => -7.8100,
            'longitude' => 110.3800,
        ]);

        $foreignHelp = Help::create([
            'user_id'      => $this->customer->id,
            'mitra_id'     => $this->partner1->id,
            'city_id'      => $this->city->id,
            'district_id'  => $otherDistrict->id,
            'title'        => 'Pekerjaan Umbulharjo',
            'description'  => 'Di luar wilayah Danurejan',
            'amount'       => 75000,
            'status'       => Help::STATUS_TAKEN,
        ]);

        $foreignCancel = $this->createCancelRequest([
            'help_id'        => $foreignHelp->id,
            'requested_by'   => $this->partner1->id,
            'requester_type' => 'partner',
            'partner_id'     => $this->partner1->id,
            'customer_id'    => $this->customer->id,
            'district_id'    => $otherDistrict->id,
            'reason'         => 'Alasan pembatalan luar wilayah',
            'status'         => 'pending',
        ]);

        $this->actingAs($this->admin);

        // Danurejan admin should NOT see Umbulharjo cancel request in listing
        Livewire::test(Index::class, ['activeTab' => 'cancellations'])
            ->set('activeTab', 'cancellations')
            ->set('status', 'all')
            ->assertDontSee('Pekerjaan Umbulharjo')
            ->assertDontSee('Alasan pembatalan luar wilayah');

        // Danurejan admin attempting to open review modal for Umbulharjo cancel request receives unauthorized flash error
        Livewire::test(Index::class, ['activeTab' => 'cancellations'])
            ->call('openCancelReviewModal', $foreignCancel->id)
            ->assertSet('selectedCancelRequestId', null)
            ->assertSet('showCancelReviewModal', false);
    }
}
