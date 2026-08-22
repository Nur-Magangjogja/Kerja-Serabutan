<?php

namespace App\Livewire\SuperAdmin\Banners;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.superadmin')]
class Index extends Component
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
            'customerUploads.*' => 'image|mimes:png,jpg,jpeg|max:5120',
            'mitraUploads.*' => 'image|mimes:png,jpg,jpeg|max:5120',
        ];
    }

    protected $messages = [
        'customerUploads.*.image' => 'File yang diunggah harus berupa gambar (bukan PDF, dokumen, atau file lain).',
        'customerUploads.*.mimes' => 'Format file banner yang diizinkan hanya PNG, JPG, atau JPEG.',
        'customerUploads.*.max' => 'Ukuran file banner maksimal 5MB.',
        'mitraUploads.*.image' => 'File yang diunggah harus berupa gambar (bukan PDF, dokumen, atau file lain).',
        'mitraUploads.*.mimes' => 'Format file banner yang diizinkan hanya PNG, JPG, atau JPEG.',
        'mitraUploads.*.max' => 'Ukuran file banner maksimal 5MB.',
    ];

    public function updatedCustomerUploads()
    {
        $validFiles = [];
        $invalidFiles = [];
        $uploads = is_array($this->customerUploads) ? $this->customerUploads : ($this->customerUploads ? [$this->customerUploads] : []);

        foreach ($uploads as $file) {
            if (!$file) continue;
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();
            if (in_array($ext, ['png', 'jpg', 'jpeg']) && str_starts_with($mime, 'image/')) {
                $validFiles[] = $file;
            } else {
                $invalidFiles[] = $file->getClientOriginalName();
            }
        }

        if (!empty($invalidFiles)) {
            $this->customerUploads = $validFiles;
            $this->addError('customerUploads', 'Format file tidak diizinkan (' . implode(', ', $invalidFiles) . '). Hanya file gambar PNG, JPG, atau JPEG yang diperbolehkan (bukan PDF, DOC, atau Excel).');
        } else {
            $this->resetErrorBag('customerUploads');
            $this->validateOnly('customerUploads.*');
        }
    }

    public function updatedMitraUploads()
    {
        $validFiles = [];
        $invalidFiles = [];
        $uploads = is_array($this->mitraUploads) ? $this->mitraUploads : ($this->mitraUploads ? [$this->mitraUploads] : []);

        foreach ($uploads as $file) {
            if (!$file) continue;
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();
            if (in_array($ext, ['png', 'jpg', 'jpeg']) && str_starts_with($mime, 'image/')) {
                $validFiles[] = $file;
            } else {
                $invalidFiles[] = $file->getClientOriginalName();
            }
        }

        if (!empty($invalidFiles)) {
            $this->mitraUploads = $validFiles;
            $this->addError('mitraUploads', 'Format file tidak diizinkan (' . implode(', ', $invalidFiles) . '). Hanya file gambar PNG, JPG, atau JPEG yang diperbolehkan (bukan PDF, DOC, atau Excel).');
        } else {
            $this->resetErrorBag('mitraUploads');
            $this->validateOnly('mitraUploads.*');
        }
    }

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

        $custUploads = is_array($this->customerUploads) ? $this->customerUploads : ($this->customerUploads ? [$this->customerUploads] : []);
        $mitUploads = is_array($this->mitraUploads) ? $this->mitraUploads : ($this->mitraUploads ? [$this->mitraUploads] : []);

        // process customer uploads
        if (!empty($custUploads)) {
            $customerRemaining = max(0, self::MAX_BANNERS - count($this->customerBanners));
            if ($customerRemaining <= 0) {
                $errors[] = 'Slot banner customer sudah penuh (maksimal ' . self::MAX_BANNERS . ' banner).';
                $this->customerUploads = [];
            } else {
                if (count($custUploads) > $customerRemaining) {
                    $custUploads = array_slice($custUploads, 0, $customerRemaining);
                }
                try {
                    foreach ($custUploads as $f) {
                        if ($f) {
                            $path = $f->store('banners', 'public');
                            $this->customerBanners[] = $path;
                        }
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
        if (!empty($mitUploads)) {
            $mitraRemaining = max(0, self::MAX_BANNERS - count($this->mitraBanners));
            if ($mitraRemaining <= 0) {
                $errors[] = 'Slot banner mitra sudah penuh (maksimal ' . self::MAX_BANNERS . ' banner).';
                $this->mitraUploads = [];
            } else {
                if (count($mitUploads) > $mitraRemaining) {
                    $mitUploads = array_slice($mitUploads, 0, $mitraRemaining);
                }
                try {
                    foreach ($mitUploads as $f) {
                        if ($f) {
                            $path = $f->store('banners', 'public');
                            $this->mitraBanners[] = $path;
                        }
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
        return view('livewire.superadmin.banners.index');
    }
}

