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

    // temporary uploaded files (multiple)
    public $customerUploads = [];
    public $mitraUploads = [];

    protected function rules()
    {
        return [
            'customerUploads.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'mitraUploads.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    protected $messages = [
        'customerUploads.*.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
        'customerUploads.*.mimes' => 'Format file harus JPG, JPEG, atau PNG',
        'customerUploads.*.max' => 'Ukuran file banner maksimal 5MB',
        'mitraUploads.*.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
        'mitraUploads.*.mimes' => 'Format file harus JPG, JPEG, atau PNG',
        'mitraUploads.*.max' => 'Ukuran file banner maksimal 5MB',
    ];

    public function mount()
    {
        $this->customerBanners = json_decode((string) AppSetting::get('banner_customer', '[]'), true) ?: [];
        $this->mitraBanners = json_decode((string) AppSetting::get('banner_mitra', '[]'), true) ?: [];
    }

    public const MAX_BANNERS = 5;

    public function save()
    {
        $this->validate();
        $errors = [];
        $successCount = 0;

        // process customer uploads
        if (!empty($this->customerUploads)) {
            $customerRemaining = max(0, self::MAX_BANNERS - count($this->customerBanners));
            if ($customerRemaining <= 0) {
                $errors[] = 'Slot banner customer sudah penuh (maksimal ' . self::MAX_BANNERS . ' banner).';
                $this->customerUploads = [];
            } else {
                if (count($this->customerUploads) > $customerRemaining) {
                    $this->customerUploads = array_slice($this->customerUploads, 0, $customerRemaining);
                }
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

                // persist setting
                try {
                    AppSetting::set('banner_customer', json_encode(array_values($this->customerBanners)));
                    $successCount++;
                } catch (\Exception $e) {
                    \Log::error('Error setting banner_customer: ' . $e->getMessage());
                    $errors[] = 'Gagal menyimpan konfigurasi banner customer.';
                }
            }
        }

        // process mitra uploads
        if (!empty($this->mitraUploads)) {
            $mitraRemaining = max(0, self::MAX_BANNERS - count($this->mitraBanners));
            if ($mitraRemaining <= 0) {
                $errors[] = 'Slot banner mitra sudah penuh (maksimal ' . self::MAX_BANNERS . ' banner).';
                $this->mitraUploads = [];
            } else {
                if (count($this->mitraUploads) > $mitraRemaining) {
                    $this->mitraUploads = array_slice($this->mitraUploads, 0, $mitraRemaining);
                }
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
        }

        if ($successCount > 0) {
            session()->flash('message', 'Banner berhasil disimpan.');
            $this->dispatch('bannersSaved', [
                'customer' => array_values($this->customerBanners),
                'mitra' => array_values($this->mitraBanners),
            ]);
            if (!empty($errors)) {
                \Log::warning('Partial success saving banners: ' . implode(' | ', $errors));
                $errMsg = implode(' ', $errors);
                session()->flash('error', $errMsg);
                $this->dispatch('bannersError', ['message' => $errMsg]);
            }
        } else {
            if (!empty($errors)) {
                $errMsg = implode(' ', $errors);
                session()->flash('error', $errMsg);
                $this->dispatch('bannersError', ['message' => $errMsg]);
            } else {
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

    public function render()
    {
        return view('superadmin.banners');
    }
}
