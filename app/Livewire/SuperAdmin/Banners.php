<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.superadmin')]
class Banners extends Component
{
    use WithFileUploads;

    // store arrays of stored paths
    public $customerBanners = [];
    public $mitraBanners = [];
    public $homeBanners = [];

    // temporary uploaded files (multiple)
    public $customerUploads = [];
    public $mitraUploads = [];
    public $homeUploads = [];

    protected function rules()
    {
        return [
            'customerUploads.*' => 'image|max:5120',
            'mitraUploads.*' => 'image|max:5120',
            'homeUploads.*' => 'image|max:5120',
        ];
    }

    public function mount()
    {
        $this->customerBanners = json_decode((string) AppSetting::get('banner_customer', '[]'), true) ?: [];
        $this->mitraBanners = json_decode((string) AppSetting::get('banner_mitra', '[]'), true) ?: [];
        $this->homeBanners = json_decode((string) AppSetting::get('banner_home', '[]'), true) ?: [];
    }

    public function save()
    {
        $this->validate();
        $errors = [];
        $successCount = 0;

        // process customer uploads
        if (!empty($this->customerUploads)) {
            try {
                foreach ($this->customerUploads as $f) {
                    $path = $f->store('banners', 'public');
                    $this->customerBanners[] = $path;
                }
                $this->customerUploads = [];
            } catch (\Exception $e) {
                \Log::error('Error storing customer upload files: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan file banner customer.';
            }

            // try to persist setting even if file store succeeded
            try {
                AppSetting::set('banner_customer', json_encode(array_values($this->customerBanners)));
                $successCount++;
            } catch (\Exception $e) {
                \Log::error('Error setting banner_customer: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan konfigurasi banner customer.';
            }
        }

        // process mitra uploads
        if (!empty($this->mitraUploads)) {
            try {
                foreach ($this->mitraUploads as $f) {
                    $path = $f->store('banners', 'public');
                    $this->mitraBanners[] = $path;
                }
                $this->mitraUploads = [];
            } catch (\Exception $e) {
                \Log::error('Error storing mitra upload files: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan file banner mitra.';
            }

            try {
                AppSetting::set('banner_mitra', json_encode(array_values($this->mitraBanners)));
                $successCount++;
            } catch (\Exception $e) {
                \Log::error('Error setting banner_mitra: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan konfigurasi banner mitra.';
            }
        }

        // process home uploads
        if (!empty($this->homeUploads)) {
            try {
                foreach ($this->homeUploads as $f) {
                    $path = $f->store('banners', 'public');
                    $this->homeBanners[] = $path;
                }
                $this->homeUploads = [];
            } catch (\Exception $e) {
                \Log::error('Error storing home upload files: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan file banner home.';
            }

            try {
                AppSetting::set('banner_home', json_encode(array_values($this->homeBanners)));
                $successCount++;
            } catch (\Exception $e) {
                \Log::error('Error setting banner_home: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan konfigurasi banner home.';
            }
        }

        if ($successCount > 0) {
            session()->flash('message', 'Banner berhasil disimpan.');
            // Kirim payload berisi path banner yang tersimpan supaya frontend
            // bisa membangun ulang preview setelah simpan.
            $this->dispatch('bannersSaved', [
                'customer' => array_values($this->customerBanners),
                'mitra' => array_values($this->mitraBanners),
                'home' => array_values($this->homeBanners),
            ]);
            if (!empty($errors)) {
                // partial success — log and show non-blocking warning
                \Log::warning('Partial success saving banners: ' . implode(' | ', $errors));
                $errMsg = implode(' ', $errors);
                session()->flash('error', $errMsg);
                // dispatch an error event so frontend can show a modal
                $this->dispatch('bannersError', ['message' => $errMsg]);
            }
        } else {
            if (!empty($errors)) {
                $errMsg = implode(' ', $errors);
                session()->flash('error', $errMsg);
                // dispatch error event so frontend can show a modal
                $this->dispatch('bannersError', ['message' => $errMsg]);
            } else {
                // nothing to do
                // use an "info" flash so frontend shows an informational modal
                // instead of a success modal when there was no upload
                session()->flash('info', 'Tidak ada file yang diunggah.');
            }
        }
    }

    public function removeCustomer($index)
    {
        if (!isset($this->customerBanners[$index]))
            return;
        $path = $this->customerBanners[$index];
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        array_splice($this->customerBanners, $index, 1);
        AppSetting::set('banner_customer', json_encode(array_values($this->customerBanners)));
        session()->flash('message', 'Banner customer dihapus.');
    }

    public function removeMitra($index)
    {
        if (!isset($this->mitraBanners[$index]))
            return;
        $path = $this->mitraBanners[$index];
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        array_splice($this->mitraBanners, $index, 1);
        AppSetting::set('banner_mitra', json_encode(array_values($this->mitraBanners)));
        session()->flash('message', 'Banner mitra dihapus.');
    }

    public function removeHome($index)
    {
        if (!isset($this->homeBanners[$index]))
            return;
        $path = $this->homeBanners[$index];
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        array_splice($this->homeBanners, $index, 1);
        AppSetting::set('banner_home', json_encode(array_values($this->homeBanners)));
        session()->flash('message', 'Banner home dihapus.');
    }

    public function render()
    {
        return view('superadmin.banners');
    }
}
