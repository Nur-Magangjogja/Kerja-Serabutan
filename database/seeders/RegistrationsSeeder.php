<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegistrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data pendaftaran & verifikasi KTP identitas pengguna (Approved, Pending Verification, & Rejected).
     */
    public function run(): void
    {
        $now = now();

        // 1. Registrasi Approved untuk User & Mitra yang sudah terdaftar
        $users = User::whereIn('role', ['customer', 'mitra'])->get();

        foreach ($users as $user) {
            Registration::updateOrCreate(
                ['email' => $user->email],
                [
                    'uuid'              => (string) Str::uuid(),
                    'nik'               => $user->nik,
                    'full_name'         => $user->name,
                    'phone'             => $user->phone,
                    'place_of_birth'    => $user->place_of_birth ?? 'Indonesia',
                    'date_of_birth'     => $user->date_of_birth ?? '1995-05-15',
                    'gender'            => $user->gender ?? 'Laki-laki',
                    'address'           => $user->address,
                    'rt'                => $user->rt ?? 1,
                    'rw'                => $user->rw ?? 1,
                    'kelurahan'         => $user->kelurahan ?? 'Kelurahan',
                    'kecamatan'         => $user->kecamatan ?? 'Kecamatan',
                    'district_id'       => $user->district_id,
                    'city'              => $user->city ?? 'Kota',
                    'city_id'           => $user->city_id,
                    'province'          => $user->province ?? 'Indonesia',
                    'ktp_photo_path'    => $user->ktp_path ?: 'ktp-photos/sample_ktp.jpg',
                    'selfie_photo_path' => $user->selfie_photo ?: 'selfie-photos/sample_selfie.png',
                    'role'              => $user->role,
                    'status'            => $user->verified ? 'approved' : 'pending_verification',
                    'created_at'        => $now->copy()->subDays(5),
                    'updated_at'        => $now->copy()->subDays(5),
                ]
            );
        }

        // 2. Sampel Pendaftaran Pending Verification (Customer & Mitra Baru)
        $slemanCity = City::where('code', '3404')->first() ?? City::first();
        $ngaglikDist = District::where('city_id', $slemanCity?->id)->first() ?? District::first();

        Registration::updateOrCreate(
            ['email' => 'calon.mitra@sayabantu.com'],
            [
                'uuid'              => (string) Str::uuid(),
                'nik'               => '3404123456780001',
                'full_name'         => 'Doni Kurniawan',
                'phone'             => '081299887711',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1992-08-10',
                'gender'            => 'Laki-laki',
                'address'           => 'Jl. Magelang KM 7.5, Mlati, Sleman',
                'district_id'       => $ngaglikDist?->id,
                'city_id'           => $slemanCity?->id,
                'city'              => $slemanCity?->name,
                'province'          => 'D.I. Yogyakarta',
                'ktp_photo_path'    => 'ktp-photos/sample_ktp.jpg',
                'selfie_photo_path' => 'selfie-photos/sample_selfie.png',
                'role'              => 'mitra',
                'status'            => 'pending_verification',
                'created_at'        => $now->copy()->subHours(5),
                'updated_at'        => $now->copy()->subHours(5),
            ]
        );

        // 3. Sampel Pendaftaran Ditolak (Rejected)
        Registration::updateOrCreate(
            ['email' => 'ditolak.ktp@sayabantu.com'],
            [
                'uuid'              => (string) Str::uuid(),
                'nik'               => '3404987654320002',
                'full_name'         => 'Rian Pratama',
                'phone'             => '081299887722',
                'place_of_birth'    => 'Yogyakarta',
                'date_of_birth'     => '1998-03-22',
                'gender'            => 'Laki-laki',
                'address'           => 'Jl. Gejayan No. 12, Sleman',
                'district_id'       => $ngaglikDist?->id,
                'city_id'           => $slemanCity?->id,
                'city'              => $slemanCity?->name,
                'province'          => 'D.I. Yogyakarta',
                'ktp_photo_path'    => 'ktp-photos/sample_ktp.jpg',
                'selfie_photo_path' => 'selfie-photos/sample_selfie.png',
                'role'              => 'mitra',
                'status'            => 'rejected',
                'rejection_reason'  => 'Foto KTP buram dan tidak terbaca jelas. Mohon upload ulang dengan pencahayaan terang.',
                'created_at'        => $now->copy()->subDays(2),
                'updated_at'        => $now->copy()->subDays(1),
            ]
        );

        $this->command->info('✓ RegistrationsSeeder berhasil mengisi sampel data verifikasi identitas.');
    }
}
