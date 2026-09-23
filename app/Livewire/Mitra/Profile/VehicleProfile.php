<?php

namespace App\Livewire\Mitra\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class VehicleProfile extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    // Form fields
    public ?string $vehicle_plate_number = null;
    public ?string $vehicle_sim_number = null;
    public $new_sim_photo = null;
    public ?string $current_sim_photo = null;

    public ?string $vehicle_stnk_number = null;
    public $new_stnk_photo = null;
    public ?string $current_stnk_photo = null;

    public ?string $vehicle_brand = null;
    public ?string $vehicle_model = null;
    public ?string $vehicle_color = null;

    // Status tracking
    public string $verification_status = 'unsubmitted';
    public bool $is_verified = false;
    public ?string $verified_at = null;
    public ?string $rejection_reason = null;

    #[On('openVehicleProfileModal')]
    public function openModal(): void
    {
        $this->loadUserData();
        $this->resetValidation();
        $this->new_sim_photo = null;
        $this->new_stnk_photo = null;
        $this->showModal = true;
        $this->dispatch('vehicle-modal-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->new_sim_photo = null;
        $this->new_stnk_photo = null;
        $this->dispatch('vehicle-modal-closed');
    }

    public function mount(): void
    {
        $this->loadUserData();
    }

    public function loadUserData(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->vehicle_plate_number = $user->vehicle_plate_number;
        $this->vehicle_sim_number   = $user->vehicle_sim_number;
        $this->current_sim_photo    = $user->vehicle_sim_photo;
        $this->vehicle_stnk_number  = $user->vehicle_stnk_number;
        $this->current_stnk_photo   = $user->vehicle_stnk_photo;
        $this->vehicle_brand        = $user->vehicle_brand;
        $this->vehicle_model        = $user->vehicle_model;
        $this->vehicle_color        = $user->vehicle_color;

        $this->verification_status  = $user->vehicle_verification_status ?? 'unsubmitted';
        $this->is_verified          = (bool) $user->vehicle_verified;
        $this->verified_at          = $user->vehicle_verified_at?->translatedFormat('d F Y H:i');
        $this->rejection_reason     = $user->vehicle_rejection_reason;
    }

    public function save(): void
    {
        $user = auth()->user();
        if (!$user) return;

        // Normalisasi format plat nomor (hilangkan spasi ganda, jadikan uppercase)
        if ($this->vehicle_plate_number) {
            $this->vehicle_plate_number = strtoupper(trim(preg_replace('/\s+/', ' ', $this->vehicle_plate_number)));
        }
        if ($this->vehicle_sim_number) {
            $this->vehicle_sim_number = trim(preg_replace('/[^0-9]/', '', $this->vehicle_sim_number));
        }
        if ($this->vehicle_stnk_number) {
            $this->vehicle_stnk_number = strtoupper(trim(preg_replace('/\s+/', '', $this->vehicle_stnk_number)));
        }

        // Rules: Wajib Plat Nomor + Wajib SIM Motor + Wajib STNK
        $rules = [
            'vehicle_plate_number' => [
                'required',
                'string',
                'min:3',
                'max:15',
                'regex:/^[A-Z]{1,2}\s?[0-9]{1,4}\s?[A-Z]{1,3}$/i',
            ],
            'vehicle_sim_number'   => 'required|string|min:8|max:14',
            'vehicle_stnk_number'  => 'required|string|min:5|max:8',
            'vehicle_brand'        => 'nullable|string|max:50',
            'vehicle_model'        => 'nullable|string|max:50',
            'vehicle_color'        => 'nullable|string|max:30',
        ];

        // Validasi foto SIM & STNK
        if (empty($this->current_sim_photo)) {
            $rules['new_sim_photo'] = 'required|image|max:4096';
        } else {
            $rules['new_sim_photo'] = 'nullable|image|max:4096';
        }

        if (empty($this->current_stnk_photo)) {
            $rules['new_stnk_photo'] = 'required|image|max:4096';
        } else {
            $rules['new_stnk_photo'] = 'nullable|image|max:4096';
        }

        // Custom error messages
        $messages = [
            'vehicle_plate_number.required' => 'Plat nomor kendaraan wajib diisi.',
            'vehicle_plate_number.regex'    => 'Format plat nomor tidak valid (contoh: AB 1234 CD atau B 1234 ABC).',
            'vehicle_sim_number.required'   => 'Nomor SIM Motor (SIM C) wajib diisi.',
            'vehicle_sim_number.min'        => 'Nomor SIM Motor minimal 8 digit.',
            'vehicle_sim_number.max'        => 'Nomor SIM Motor maksimal 14 karakter.',
            'new_sim_photo.required'        => 'Foto fisik SIM Motor (SIM C) wajib diunggah.',
            'new_sim_photo.image'           => 'File foto SIM harus berupa gambar (JPG, PNG, WEBP).',
            'new_sim_photo.max'             => 'Ukuran foto SIM maksimal 4MB.',
            'vehicle_stnk_number.required'  => 'Nomor STNK kendaraan wajib diisi.',
            'vehicle_stnk_number.min'       => 'Nomor STNK kendaraan minimal 5 karakter.',
            'vehicle_stnk_number.max'      => 'Nomor STNK kendaraan maksimal 8 karakter.',
            'new_stnk_photo.required'       => 'Foto fisik STNK kendaraan wajib diunggah.',
            'new_stnk_photo.image'          => 'File foto STNK harus berupa gambar (JPG, PNG, WEBP).',
            'new_stnk_photo.max'            => 'Ukuran foto STNK maksimal 4MB.',
        ];

        $this->validate($rules, $messages);

        // Upload foto baru jika ada
        $simPhotoPath = $this->current_sim_photo;
        if ($this->new_sim_photo) {
            if ($this->current_sim_photo && Storage::disk('public')->exists($this->current_sim_photo)) {
                Storage::disk('public')->delete($this->current_sim_photo);
            }
            $simPhotoPath = $this->new_sim_photo->store('vehicles/sim', 'public');
        }

        $stnkPhotoPath = $this->current_stnk_photo;
        if ($this->new_stnk_photo) {
            if ($this->current_stnk_photo && Storage::disk('public')->exists($this->current_stnk_photo)) {
                Storage::disk('public')->delete($this->current_stnk_photo);
            }
            $stnkPhotoPath = $this->new_stnk_photo->store('vehicles/stnk', 'public');
        }

        // Simpan data ke user
        $user->update([
            'vehicle_plate_number'        => $this->vehicle_plate_number,
            'vehicle_sim_number'          => $this->vehicle_sim_number,
            'vehicle_sim_photo'           => $simPhotoPath,
            'vehicle_stnk_number'         => $this->vehicle_stnk_number,
            'vehicle_stnk_photo'          => $stnkPhotoPath,
            'vehicle_brand'               => $this->vehicle_brand,
            'vehicle_model'               => $this->vehicle_model,
            'vehicle_color'               => $this->vehicle_color,
            // Setiap perubahan data kendaraan me-reset status verifikasi ke pending
            'vehicle_verification_status' => 'pending',
            'vehicle_verified'            => false,
            'vehicle_verified_at'         => null,
            'vehicle_verified_by'         => null,
            'vehicle_rejection_reason'    => null,
        ]);

        $this->loadUserData();
        $this->new_sim_photo = null;
        $this->new_stnk_photo = null;

        session()->flash('success_vehicle', 'Data kendaraan berhasil dikirim! Status saat ini Menunggu Verifikasi Admin. Layanan Antar & Jemput akan terbuka setelah disetujui.');
        $this->dispatch('vehicle-profile-updated');
    }

    public function render()
    {
        return view('livewire.mitra.profile.vehicle-profile');
    }
}
