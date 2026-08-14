<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class UpdateProfilePhoto extends Component
{
    use WithFileUploads;

    public $photo = null;
    public $showModal = false;

    protected $rules = [
        'photo' => 'required|image|max:2048', // max 2MB
    ];

    protected $messages = [
        'photo.required' => 'Pilih foto terlebih dahulu',
        'photo.image' => 'File harus berupa gambar',
        'photo.max' => 'Ukuran foto maksimal 2MB',
    ];

    #[On('openModal')]
    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->photo = null;
        $this->resetErrorBag();
    }

    public function updatePhoto()
    {
        $this->validate();

        try {
            $user = auth()->user();

            // Delete old photo if exists
            if ($user->selfie_photo && Storage::disk('public')->exists($user->selfie_photo)) {
                Storage::disk('public')->delete($user->selfie_photo);
            }

            // Store new photo
            $path = $this->photo->store('profile-photos', 'public');

            // Update user
            $user->update([
                'selfie_photo' => $path,
            ]);

            session()->flash('status', 'Foto profil berhasil diperbarui!');

            $this->closeModal();
            $this->dispatch('profile-photo-updated');

            // Reload page to show new photo
            return redirect()->route('profile');

        } catch (\Exception $e) {
            \Log::error('Error updating profile photo: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengupload foto.');
        }
    }

    #[On('removePhoto')]
    public function removePhoto()
    {
        try {
            $user = auth()->user();

            // Delete photo file
            if ($user->selfie_photo && Storage::disk('public')->exists($user->selfie_photo)) {
                Storage::disk('public')->delete($user->selfie_photo);
            }

            // Update user
            $user->update([
                'selfie_photo' => null,
            ]);

            session()->flash('status', 'Foto profil berhasil dihapus!');

            // Reload page
            return redirect()->route('profile');

        } catch (\Exception $e) {
            \Log::error('Error removing profile photo: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menghapus foto.');
        }
    }

    public function render()
    {
        return view('livewire.customer.update-profile-photo');
    }
}