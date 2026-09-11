<?php

namespace App\Livewire\Customer\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class UpdatePhoto extends Component
{
    use WithFileUploads;

    public $photo = null;
    public $showModal = false;

    protected $rules = [
        'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048', // max 2MB
    ];

    protected $messages = [
        'photo.required' => 'Pilih foto terlebih dahulu',
        'photo.image'    => 'File harus berupa gambar (JPG, JPEG, PNG)',
        'photo.mimes'    => 'Format foto harus berupa PNG, JPG, atau JPEG',
        'photo.max'      => 'Ukuran foto maksimal 2MB',
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

            // Delete old profile photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Store new photo
            $path = $this->photo->store('profile-photos', 'public');

            // Update user profile photo
            $user->update([
                'profile_photo' => $path,
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

            // Delete profile photo file
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Update user profile photo
            $user->update([
                'profile_photo' => null,
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
        return view('livewire.customer.profile.update-photo');
    }
}
