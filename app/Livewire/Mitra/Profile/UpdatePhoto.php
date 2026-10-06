<?php

namespace App\Livewire\Mitra\Profile;

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
        'photo' => 'required|image|mimes:jpg,jpeg,png|max:1536', // max 1.5MB
    ];

    protected $messages = [
        'photo.required' => 'Pilih foto terlebih dahulu',
        'photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
        'photo.mimes' => 'Format foto harus berupa PNG, JPG, atau JPEG',
        'photo.max' => 'Ukuran foto maksimal 1.5MB',
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

            // Reload mitra profile page
            return redirect()->route('mitra.profile');

        } catch (\Exception $e) {
            \Log::error('Error updating mitra profile photo: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengupload foto.');
        }
    }

    public function saveCroppedPhoto(string $dataUrl)
    {
        if (empty($dataUrl) || !preg_match('/^data:image\/(jpeg|jpg|png);base64,([A-Za-z0-9+\/=\r\n]+)$/', $dataUrl, $matches)) {
            $this->addError('photo', 'Format gambar tidak valid.');
            return;
        }

        $base64Data = $matches[2];
        $imageData = base64_decode($base64Data, true);

        if ($imageData === false || empty($imageData)) {
            $this->addError('photo', 'Gagal memproses data gambar.');
            return;
        }

        // Max 1536KB (1.5MB) of decoded binary data
        if (strlen($imageData) > 1536 * 1024) {
            $this->addError('photo', 'Ukuran foto maksimal 1.5MB.');
            return;
        }

        // Verify genuine image binary and safe raster MIME
        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false || !isset($imageInfo['mime']) || !in_array($imageInfo['mime'], ['image/jpeg', 'image/png'])) {
            $this->addError('photo', 'File harus berupa gambar valid (JPG, JPEG, PNG).');
            return;
        }

        try {
            $user = auth()->user();

            // Delete old profile photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $extension = ($imageInfo['mime'] === 'image/png') ? 'png' : 'jpg';
            $filename = 'profile-photos/' . uniqid('avatar_') . '.' . $extension;
            Storage::disk('public')->put($filename, $imageData);

            $user->update([
                'profile_photo' => $filename,
            ]);

            session()->flash('status', 'Foto profil berhasil diperbarui!');
            $this->closeModal();
            $this->dispatch('profile-photo-updated');

            return redirect()->route('mitra.profile');
        } catch (\Exception $e) {
            \Log::error('Error saving cropped mitra profile photo: ' . $e->getMessage());
            $this->addError('photo', 'Terjadi kesalahan saat menyimpan foto.');
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
            return redirect()->route('mitra.profile');

        } catch (\Exception $e) {
            \Log::error('Error removing mitra profile photo: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menghapus foto.');
        }
    }

    public function render()
    {
        return view('livewire.mitra.profile.update-photo');
    }
}

