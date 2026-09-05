<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegistrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi riwayat registrasi dan verifikasi data identitas (KTP) pengguna yang telah disetujui untuk seluruh Customer & Mitra.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['customer', 'mitra'])->get();

        foreach ($users as $index => $user) {
            Registration::updateOrCreate(
                ['email' => $user->email],
                [
                    'uuid'              => (string) Str::uuid(),
                    'nik'               => $user->nik,
                    'full_name'         => $user->name,
                    'phone'             => $user->phone,
                    'place_of_birth'    => $user->place_of_birth ?? 'Sleman',
                    'date_of_birth'     => $user->date_of_birth ?? '1995-05-15',
                    'gender'            => $user->gender ?? 'Laki-laki',
                    'address'           => $user->address,
                    'rt'                => $user->rt ?? 1,
                    'rw'                => $user->rw ?? 1,
                    'kelurahan'         => $user->kelurahan ?? 'Tridadi',
                    'kecamatan'         => $user->kecamatan ?? 'Sleman',
                    'city'              => $user->city ?? 'Kabupaten Sleman',
                    'city_id'           => $user->city_id ?? 1,
                    'province'          => $user->province ?? 'D.I. Yogyakarta',
                    'ktp_photo_path'    => $user->ktp_path ?: 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                    'selfie_photo_path' => $user->selfie_photo ?: 'selfie-photos/9lp933vpWL9YN6JQ8ISEbocs2qLwvr78DklO0dEt.png',
                    'role'              => $user->role,
                    'status'            => 'approved',
                    'created_at'        => Carbon::parse('2026-08-04 08:00:00')->addMinutes($index * 15),
                    'updated_at'        => Carbon::parse('2026-08-04 09:30:00')->addMinutes($index * 15),
                ]
            );
        }

        $this->command->info('RegistrationsSeeder berhasil membuat riwayat registrasi terverifikasi untuk seluruh customer & mitra di 4 wilayah.');
    }
}
