<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'registrations';

    protected $fillable = [
        'uuid',
        'role',
        'nik',
        'full_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'city',
        'city_id',
        'province',
        'religion',
        'marital_status',
        'occupation',
        'ktp_photo_path',
        'selfie_photo_path',
        'email',
        'password',
        'rejection_reason',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the URL for the selfie photo.
     */
    public function getSelfieUrlAttribute()
    {
        if ($this->selfie_photo_path) {
            return asset('storage/' . $this->selfie_photo_path);
        }
        return null;
    }

    /**
     * Get the URL for the KTP photo.
     */
    public function getKtpUrlAttribute()
    {
        if ($this->ktp_photo_path) {
            return asset('storage/' . $this->ktp_photo_path);
        }
        return null;
    }
}
