<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.superadmin')]
class IdentitySettings extends Component
{
    use WithFileUploads;

    public $app_name;
    public $app_tagline;
    public $app_description;
    public $app_brand_font;
    public $app_brand_style;

    public $current_logo;
    public $logo;

    public $current_favicon;
    public $favicon;

    public function rules()
    {
        return [
            'app_name' => 'required|string|min:2|max:50',
            'app_tagline' => 'nullable|string|max:100',
            'app_description' => 'nullable|string|max:255',
            'app_brand_font' => 'nullable|string',
            'app_brand_style' => 'nullable|string',
            'logo' => 'nullable|image|max:2048|mimes:png,jpg,jpeg,svg,webp',
            'favicon' => 'nullable|image|max:1024|mimes:png,ico,svg,jpg',
        ];
    }

    public function messages()
    {
        return [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'app_name.min' => 'Nama aplikasi minimal 2 karakter.',
            'app_name.max' => 'Nama aplikasi maksimal 50 karakter.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'logo.mimes' => 'Format logo harus PNG, JPG, JPEG, SVG, atau WEBP.',
            'favicon.image' => 'File favicon harus berupa gambar.',
            'favicon.max' => 'Ukuran favicon maksimal 1MB.',
        ];
    }

    public function mount()
    {
        $this->app_name = AppSetting::get('app_name', 'SayaBantu');
        $this->app_tagline = AppSetting::get('app_tagline', 'Platform Layanan & Bantuan Serabutan');
        $this->app_description = AppSetting::get('app_description', 'Solusi bantuan cepat, aman, dan terpercaya.');
        $this->app_brand_font = AppSetting::get('app_brand_font', 'Plus Jakarta Sans');
        $this->app_brand_style = AppSetting::get('app_brand_style', 'two_tone');
        $this->current_logo = AppSetting::get('app_logo');
        $this->current_favicon = AppSetting::get('app_favicon');
    }

    public function save()
    {
        $this->validate();

        // Handle Logo upload
        if ($this->logo) {
            // Remove old logo if exists
            if ($this->current_logo && Storage::disk('public')->exists($this->current_logo)) {
                Storage::disk('public')->delete($this->current_logo);
            }

            $path = $this->logo->store('branding', 'public');
            AppSetting::set('app_logo', $path);
            $this->current_logo = $path;
            $this->logo = null;
        }

        // Handle Favicon upload
        if ($this->favicon) {
            // Remove old favicon if exists
            if ($this->current_favicon && Storage::disk('public')->exists($this->current_favicon)) {
                Storage::disk('public')->delete($this->current_favicon);
            }

            $path = $this->favicon->store('branding', 'public');
            AppSetting::set('app_favicon', $path);
            $this->current_favicon = $path;
            $this->favicon = null;
        }

        // Save text & typography settings
        AppSetting::set('app_name', trim($this->app_name));
        AppSetting::set('app_tagline', trim((string) $this->app_tagline));
        AppSetting::set('app_description', trim((string) $this->app_description));
        AppSetting::set('app_brand_font', $this->app_brand_font ?: 'Plus Jakarta Sans');
        AppSetting::set('app_brand_style', $this->app_brand_style ?: 'two_tone');

        session()->flash('message', 'Identitas dan tipografi aplikasi berhasil diperbarui!');
        $this->dispatch('identity-saved', ['message' => 'Identitas dan tipografi aplikasi berhasil diperbarui!']);
    }

    public function removeLogo()
    {
        if ($this->current_logo && Storage::disk('public')->exists($this->current_logo)) {
            Storage::disk('public')->delete($this->current_logo);
        }

        AppSetting::set('app_logo', '');
        $this->current_logo = null;
        $this->logo = null;

        session()->flash('message', 'Logo aplikasi berhasil dihapus. Sistem akan menggunakan emblem logo default.');
        $this->dispatch('identity-saved', ['message' => 'Logo aplikasi berhasil dihapus.']);
    }

    public function removeFavicon()
    {
        if ($this->current_favicon && Storage::disk('public')->exists($this->current_favicon)) {
            Storage::disk('public')->delete($this->current_favicon);
        }

        AppSetting::set('app_favicon', '');
        $this->current_favicon = null;
        $this->favicon = null;

        session()->flash('message', 'Favicon aplikasi berhasil dihapus.');
        $this->dispatch('identity-saved', ['message' => 'Favicon aplikasi berhasil dihapus.']);
    }

    public function render()
    {
        return view('livewire.superadmin.settings.identity-settings')
            ->layout('layouts.superadmin', [
                'title' => 'Pengaturan Identitas Aplikasi',
            ]);
    }
}
