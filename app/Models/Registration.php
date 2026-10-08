<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'registrations';

    protected $fillable = [
        'uuid',
        'role',
        'nik',
        'full_name',
        'phone',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'district_id',
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
        'rt' => 'integer',
        'rw' => 'integer',
        'city_id' => 'integer',
        'district_id' => 'integer',
    ];

    /**
     * Mutator to ensure phone is always normalized when saved.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => User::normalizePhone($value),
        );
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

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

    /**
     * Get the formatted full address from RT, RW, Kelurahan, Kecamatan, City, Province.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if (!empty($this->kecamatan)) {
            $parts[] = 'Kec. ' . $this->kecamatan;
        } elseif ($this->district) {
            $parts[] = 'Kec. ' . $this->district->name;
        }
        if (!empty($this->city)) {
            $parts[] = $this->city;
        }
        if (!empty($this->province)) {
            $parts[] = $this->province;
        }

        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        return $this->address ?? '—';
    }

    protected static array $memoizedPendingVerificationsCounts = [];

    /**
     * Menghitung total pendaftaran & verifikasi yang pending (KTP & Kendaraan Mitra)
     * dengan pembatasan wilayah wewenang untuk indikator badge sidebar Admin & Superadmin.
     */
    public static function getPendingVerificationsCountForUser(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'super_admin', 'superadmin'])) {
            return 0;
        }

        $isSuperAdmin = in_array($user->role, ['super_admin', 'superadmin']);
        $saTerritory = $isSuperAdmin ? $user->getActiveSuperadminTerritory() : null;
        $saKeyPart = $isSuperAdmin 
            ? ('sa_' . ($saTerritory['type'] ?? 'all') . '_' . ($saTerritory['id'] ?? 'all')) 
            : ('admin_' . $user->id . '_' . ($user->getActiveAdminDistrictFilter() ?? 'all'));

        $memoKey = $user->id . '_' . $saKeyPart;
        if (array_key_exists($memoKey, static::$memoizedPendingVerificationsCounts)) {
            return static::$memoizedPendingVerificationsCounts[$memoKey];
        }

        $version = Cache::get('pending_verifications_count_version', 1);
        $cacheKey = 'pending_verifications_count_v' . $version . '_' . $saKeyPart;

        return static::$memoizedPendingVerificationsCounts[$memoKey] = (int) Cache::remember($cacheKey, 180, function () use ($user, $isSuperAdmin, $saTerritory) {
            $ktpQuery = static::whereIn('status', ['pending', 'pending_verification']);
            $vehicleQuery = User::where('role', 'mitra')->where('vehicle_verification_status', 'pending');

            if (!$isSuperAdmin && $user->role === 'admin') {
                $effectiveDistrictIds = $user->getEffectiveAdminDistrictIds();
                if (!empty($effectiveDistrictIds)) {
                    $ktpQuery->whereIn('district_id', $effectiveDistrictIds);
                    $vehicleQuery->whereIn('district_id', $effectiveDistrictIds);
                } else {
                    return 0;
                }
            } elseif ($isSuperAdmin) {
                if ($saTerritory && $saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                    $dId = (int) $saTerritory['id'];
                    $ktpQuery->where('district_id', $dId);
                    $vehicleQuery->where('district_id', $dId);
                } elseif ($saTerritory && $saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                    $cityId = (int) $saTerritory['id'];
                    $saDistrictIds = $user->getEffectiveSuperadminDistrictIds();

                    $ktpQuery->where(function ($sub) use ($cityId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) {
                            $sub->whereIn('district_id', $saDistrictIds);
                        }
                        if ($cityId) {
                            $sub->orWhere('city_id', $cityId);
                        }
                    });

                    $vehicleQuery->where(function ($sub) use ($cityId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) {
                            $sub->whereIn('district_id', $saDistrictIds);
                        }
                        if ($cityId) {
                            $sub->orWhere('city_id', $cityId);
                        }
                    });
                }
            }

            return (int) ($ktpQuery->count() + $vehicleQuery->count());
        });
    }

    /**
     * Bersihkan cache counter verifikasi saat ada perubahan status pendaftaran/kendaraan.
     */
    public static function clearPendingVerificationsCountCache(): void
    {
        static::$memoizedPendingVerificationsCounts = [];
        Cache::increment('pending_verifications_count_version');
    }
}

