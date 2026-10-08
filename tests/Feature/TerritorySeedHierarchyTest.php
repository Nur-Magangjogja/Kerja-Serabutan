<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Services\RegionService;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritorySeedHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_province_seeder_populates_38_provinces_idempotently(): void
    {
        // 1. Eksekusi pertama
        $this->seed(ProvinceSeeder::class);

        $this->assertEquals(38, Province::count(), 'Harus terdapat tepat 38 provinsi di Indonesia.');
        $this->assertDatabaseHas('provinces', ['code' => '34', 'name' => 'DI Yogyakarta']);
        $this->assertDatabaseHas('provinces', ['code' => '31', 'name' => 'DKI Jakarta']);
        $this->assertDatabaseHas('provinces', ['code' => '33', 'name' => 'Jawa Tengah']);

        // Verifikasi tidak ada duplikasi kode
        $duplicateCodes = Province::select('code')
            ->groupBy('code')
            ->havingRaw('count(*) > 1')
            ->pluck('code');
        $this->assertEmpty($duplicateCodes, 'Dilarang ada kode provinsi yang duplikat.');

        // 2. Eksekusi kedua (idempotensi)
        $this->seed(ProvinceSeeder::class);

        $this->assertEquals(38, Province::count(), 'Seeder kedua tidak boleh menduplikasi jumlah provinsi.');
    }

    public function test_city_seeder_links_cities_to_actual_database_province_id(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        $totalCities = City::count();
        $this->assertGreaterThan(500, $totalCities, 'Jumlah kota/kabupaten harus terisi lengkap (> 500).');

        // Orphan Check 1: Pastikan tidak ada kota dengan province_id bernilai null
        $nullProvinceCities = City::whereNull('province_id')->count();
        $this->assertEquals(0, $nullProvinceCities, 'Jumlah kota dengan province_id null harus 0.');

        // Orphan Check 2: Pastikan seluruh province_id pada cities valid merujuk ke id tabel provinces
        $provinceIds = Province::pluck('id')->toArray();
        $invalidProvinceCities = City::whereNotIn('province_id', $provinceIds)->count();
        $this->assertEquals(0, $invalidProvinceCities, 'Jumlah kota dengan province_id tidak valid harus 0.');

        // Verifikasi Relasi Eloquent City -> belongsTo(Province)
        $cityJogja = City::where('code', '3471')->first();
        $cityProv = $cityJogja->province()->first();
        $this->assertNotNull($cityProv);
        $this->assertEquals('DI Yogyakarta', $cityProv->name);
        $this->assertEquals((int) $cityJogja->province_id, (int) $cityProv->id);

        // Verifikasi Relasi Eloquent Province -> hasMany(City)
        $provDiy = Province::where('code', '34')->first();
        $this->assertNotNull($provDiy);
        $diyCityNames = $provDiy->cities->pluck('name')->toArray();
        $this->assertContains('Kota Yogyakarta', $diyCityNames);
        $this->assertContains('Kabupaten Sleman', $diyCityNames);
        $this->assertContains('Kabupaten Bantul', $diyCityNames);
    }

    public function test_city_seeder_links_districts_to_actual_database_city_id(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        $totalDistricts = District::count();
        $this->assertGreaterThan(7000, $totalDistricts, 'Jumlah kecamatan harus terisi lengkap (> 7000).');

        // Orphan Check 1: District dengan city_id null
        $nullCityDistricts = District::whereNull('city_id')->count();
        $this->assertEquals(0, $nullCityDistricts, 'Jumlah kecamatan dengan city_id null harus 0.');

        // Orphan Check 2: District dengan city_id yang tidak ada di tabel cities
        $cityIds = City::pluck('id')->toArray();
        $invalidCityDistricts = District::whereNotIn('city_id', $cityIds)->count();
        $this->assertEquals(0, $invalidCityDistricts, 'Jumlah kecamatan dengan city_id tidak valid harus 0.');

        // Verifikasi Relasi Eloquent District -> belongsTo(City)
        $districtDanurejan = District::where('code', '347104')->first();
        $this->assertNotNull($districtDanurejan, 'Kecamatan Danurejan (347104) harus ada.');
        $this->assertNotNull($districtDanurejan->city);
        $this->assertEquals('Kota Yogyakarta', $districtDanurejan->city->name);
    }

    public function test_representative_hierarchy_relations(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        // Contoh 1: DI Yogyakarta (34) -> Kota Yogyakarta (3471) -> Danurejan (347104)
        $provDiy = Province::where('code', '34')->firstOrFail();
        $cityJogja = $provDiy->cities()->where('code', '3471')->firstOrFail();
        $distDanurejan = $cityJogja->districts()->where('code', '347104')->firstOrFail();

        $this->assertEquals('Danurejan', $distDanurejan->name);
        $this->assertEquals($cityJogja->id, $distDanurejan->city_id);
        $this->assertEquals($provDiy->id, $cityJogja->province_id);

        // Contoh 2: Jawa Tengah (33) -> Kota Surakarta (3372)
        $provJateng = Province::where('code', '33')->firstOrFail();
        $citySolo = $provJateng->cities()->where('code', '3372')->firstOrFail();
        $this->assertEquals('Kota Surakarta', $citySolo->name);
        $this->assertGreaterThan(0, $citySolo->districts()->count());

        // Contoh 3: DKI Jakarta (31) -> Kota Jakarta Selatan (3174)
        $provDki = Province::where('code', '31')->firstOrFail();
        $cityJaksel = $provDki->cities()->where('code', '3174')->firstOrFail();
        $this->assertEquals('Kota Jakarta Selatan', $cityJaksel->name);
        $this->assertGreaterThan(0, $cityJaksel->districts()->count());
    }

    public function test_superadmin_wilayah_queries_provinces_and_child_cities(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        $provinces = Province::orderBy('name')->get();
        $this->assertCount(38, $provinces);

        // Simulasi pemilihan provinsi pada SuperAdmin Wilayah
        $selectedProvince = Province::where('code', '34')->firstOrFail();
        $citiesUnderProvince = City::where('province_id', $selectedProvince->id)->get();

        $this->assertCount(5, $citiesUnderProvince, 'DI Yogyakarta harus memiliki 5 kota/kabupaten.');
        $cityNames = $citiesUnderProvince->pluck('name')->toArray();
        $this->assertContains('Kota Yogyakarta', $cityNames);
        $this->assertContains('Kabupaten Sleman', $cityNames);
        $this->assertContains('Kabupaten Bantul', $cityNames);
        $this->assertContains('Kabupaten Kulon Progo', $cityNames);
        $this->assertContains('Kabupaten Gunung Kidul', $cityNames);
    }

    public function test_region_service_cascade_active_state_with_database_province(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        $regionService = app(RegionService::class);

        $cityJogja = City::where('code', '3471')->firstOrFail();
        $districtDanurejan = District::where('code', '347104')->firstOrFail();
        $provDiy = Province::where('code', '34')->firstOrFail();

        // Kondisi Awal: Seluruh hierarchy aktif
        $this->assertTrue($provDiy->is_active);
        $this->assertTrue($cityJogja->is_active);
        $this->assertTrue($districtDanurejan->is_active);
        $this->assertTrue($regionService->isRegionActive($districtDanurejan->id, $cityJogja->id));

        // Cascade 1: Nonaktifkan Provinsi -> City & District menjadi effectively inactive
        $provDiy->update(['is_active' => false]);
        $this->assertFalse($regionService->isRegionActive($districtDanurejan->id, $cityJogja->id));

        // Restore Provinsi, Nonaktifkan Kota -> District effectively inactive
        $provDiy->update(['is_active' => true]);
        $cityJogja->update(['is_active' => false]);
        $this->assertFalse($regionService->isRegionActive($districtDanurejan->id, $cityJogja->id));

        // Restore Kota, Nonaktifkan Kecamatan -> District inactive
        $cityJogja->update(['is_active' => true]);
        $districtDanurejan->update(['is_active' => false]);
        $this->assertFalse($regionService->isRegionActive($districtDanurejan->id, $cityJogja->id));
    }

    public function test_seeder_is_fully_idempotent_without_count_drift(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        $pCount1 = Province::count();
        $cCount1 = City::count();
        $dCount1 = District::count();

        // Jalankan kembali seeder yang sama
        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);

        $this->assertEquals($pCount1, Province::count(), 'Province count tidak boleh berubah pada seeder berulang.');
        $this->assertEquals($cCount1, City::count(), 'City count tidak boleh berubah pada seeder berulang.');
        $this->assertEquals($dCount1, District::count(), 'District count tidak boleh berubah pada seeder berulang.');
    }
}
