<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Detail as CustomerHelpDetail;
use App\Livewire\Customer\Helps\Index as CustomerHelpsIndex;
use App\Models\City;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerPickupDeliveryVehicleInfoTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $mitra;
    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'      => 'Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->customer = User::factory()->create([
            'role'     => 'customer',
            'city_id'  => $this->city->id,
            'verified' => true,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 500000]);

        $this->mitra = User::factory()->create([
            'name'                        => 'Budi Prakoso',
            'role'                        => 'mitra',
            'city_id'                     => $this->city->id,
            'verified'                    => true,
            'status'                      => 'active',
            'phone'                       => '081234567890',
            'vehicle_plate_number'        => 'AB 1234 CD',
            'vehicle_brand'               => 'Honda',
            'vehicle_model'               => 'Vario 160',
            'vehicle_color'               => 'Hitam',
            'vehicle_sim_number'          => '998877665544',
            'vehicle_sim_photo'           => 'vehicles/sim/photo.jpg',
            'vehicle_stnk_number'         => 'STNK-123456',
            'vehicle_stnk_photo'          => 'vehicles/stnk/photo.jpg',
            'vehicle_verification_status' => 'verified',
            'vehicle_verified'            => true,
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 50000]);
    }

    public function test_customer_detail_shows_partner_name_plate_and_vehicle_type_for_pickup_delivery(): void
    {
        $pickupHelp = Help::create([
            'user_id'          => $this->customer->id,
            'mitra_id'         => $this->mitra->id,
            'city_id'          => $this->city->id,
            'title'            => 'Antar Paket Makanan',
            'description'      => 'Mohon diantar segera ke alamat tujuan',
            'amount'           => 30000,
            'admin_fee'        => 3000,
            'total_amount'     => 33000,
            'status'           => Help::STATUS_TAKEN,
            'service_type'     => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category' => 'goods_document',
            'dispatch_mode'    => Help::DISPATCH_MODE_POOL,
        ]);

        $this->actingAs($this->customer);

        Livewire::test(CustomerHelpDetail::class, ['id' => $pickupHelp->id])
            ->assertSee('Budi Prakoso')
            ->assertSee('AB 1234 CD')
            ->assertSee('Honda Vario 160 (Hitam)')
            ->assertSee('Kendaraan Mitra');
    }

    public function test_customer_detail_does_not_show_vehicle_plate_for_regular_on_site_job(): void
    {
        $onSiteHelp = Help::create([
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'title'         => 'Bantu Rapikan Taman',
            'description'   => 'Kerja serabutan biasa on-site',
            'amount'        => 50000,
            'admin_fee'     => 5000,
            'total_amount'  => 55000,
            'status'        => Help::STATUS_TAKEN,
            'service_type'  => Help::SERVICE_TYPE_ON_SITE,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
        ]);

        $this->actingAs($this->customer);

        Livewire::test(CustomerHelpDetail::class, ['id' => $onSiteHelp->id])
            ->assertSee('Budi Prakoso')
            ->assertDontSee('Kendaraan Mitra')
            ->assertDontSee('AB 1234 CD');
    }

    public function test_customer_helps_index_displays_partner_vehicle_details(): void
    {
        $pickupHelp = Help::create([
            'user_id'          => $this->customer->id,
            'mitra_id'         => $this->mitra->id,
            'city_id'          => $this->city->id,
            'title'            => 'Antar Kue Ulang Tahun',
            'description'      => 'Antar kue',
            'amount'           => 35000,
            'admin_fee'        => 3500,
            'total_amount'     => 38500,
            'status'           => Help::STATUS_TAKEN,
            'service_type'     => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category' => 'goods_document',
            'dispatch_mode'    => Help::DISPATCH_MODE_POOL,
        ]);

        $this->actingAs($this->customer);

        Livewire::test(CustomerHelpsIndex::class)
            ->set('statusFilter', 'diproses')
            ->assertSee('Budi Prakoso')
            ->assertSee('AB 1234 CD')
            ->assertSee('Honda Vario 160');
    }

    public function test_tracking_endpoint_returns_partner_plate_and_vehicle_info(): void
    {
        $pickupHelp = Help::create([
            'user_id'              => $this->customer->id,
            'mitra_id'             => $this->mitra->id,
            'city_id'              => $this->city->id,
            'title'                => 'Antar Dokumen Kantor',
            'description'          => 'Antar dokumen',
            'amount'               => 25000,
            'admin_fee'            => 2500,
            'total_amount'         => 27500,
            'status'               => Help::STATUS_PARTNER_ON_THE_WAY,
            'service_type'         => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category'     => 'goods_document',
            'dispatch_mode'        => Help::DISPATCH_MODE_POOL,
            'partner_current_lat'  => -7.7960,
            'partner_current_lng'  => 110.3700,
            'latitude'             => -7.7956,
            'longitude'            => 110.3695,
        ]);

        $this->actingAs($this->customer);

        $response = $this->getJson(route('customer.helps.tracking', $pickupHelp->id));

        $response->assertOk()
            ->assertJson([
                'partnerName'        => 'Budi Prakoso',
                'partnerPlateNumber' => 'AB 1234 CD',
                'partnerVehicle'     => 'Honda Vario 160 (Hitam)',
                'isPickup'           => true,
            ]);
    }
}
