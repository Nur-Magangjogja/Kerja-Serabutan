<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'city_id',
        'district_id',
        'ktp_path',
        'verified',
        'status',
        'phone',
        'address',
        'saved_landmarks',
        // KTP Fields
        'nik',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'city',
        'province',
        'religion',
        'marital_status',
        'occupation',
        'ktp_photo',
        'selfie_photo',
        'profile_photo',
        'notification_settings',
        // Greylist, Shadow Ban, and Warning Fields
        'is_greylisted',
        'greylisted_at',
        'greylist_reason',
        'is_shadow_banned',
        'shadow_banned_at',
        'warning_level',
        'latest_warning_message',
        'latest_warning_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified' => 'boolean',
            'date_of_birth' => 'date',
            'rt' => 'integer',
            'rw' => 'integer',
            'notification_settings' => 'array',
            'saved_landmarks' => 'array',
            'is_greylisted' => 'boolean',
            'greylisted_at' => 'datetime',
            'is_shadow_banned' => 'boolean',
            'shadow_banned_at' => 'datetime',
            'warning_level' => 'integer',
            'latest_warning_at' => 'datetime',
        ];
    }

    /**
     * Helper to normalize Indonesian phone number to '08...' format.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        // Ambil hanya digit angka
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if ($clean === '') {
            return '';
        }

        // Jika format diawali 620 (salah input +6208...)
        if (str_starts_with($clean, '620')) {
            $clean = '0' . substr($clean, 3);
        }
        // Jika diawali 62 (contoh: 62812... atau +62812...)
        elseif (str_starts_with($clean, '62')) {
            $clean = '0' . substr($clean, 2);
        }
        // Jika diawali 8 langsung tanpa 0 (contoh: 8123456789)
        elseif (str_starts_with($clean, '8')) {
            $clean = '0' . $clean;
        }

        return $clean;
    }

    /**
     * Mutator to ensure phone is always normalized when saved.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => self::normalizePhone($value),
        );
    }

    public function greylistLogs()
    {
        return $this->hasMany(UserGreylistLog::class, 'user_id')->latest();
    }

    public function getWarningLevelLabelAttribute(): string
    {
        return match ($this->warning_level) {
            1       => 'SP 1 (Peringatan Ringan)',
            2       => 'SP 2 (Peringatan Sedang)',
            3       => 'SP 3 (Peringatan Keras / Terakhir)',
            default => 'Normal',
        };
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if (!empty($this->kecamatan)) {
            $parts[] = 'Kec. ' . $this->kecamatan;
        } elseif ($this->district_id && $this->relationLoaded('district') && $this->district) {
            $parts[] = 'Kec. ' . $this->district->name;
        }
        if (!empty($this->city)) {
            $parts[] = $this->city;
        } elseif (!empty($this->city_name)) {
            $parts[] = $this->city_name;
        }
        if (!empty($this->province)) {
            $parts[] = $this->province;
        }

        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        return $this->address ?? '—';
    }

    public function getKtpUrlAttribute(): ?string
    {
        $path = $this->ktp_photo ?: $this->ktp_path;
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . $path);
        }
        return null;
    }

    public function getSelfieUrlAttribute(): ?string
    {
        if ($this->selfie_photo) {
            if (str_starts_with($this->selfie_photo, 'http://') || str_starts_with($this->selfie_photo, 'https://')) {
                return $this->selfie_photo;
            }
            return asset('storage/' . $this->selfie_photo);
        }
        return null;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->profile_photo ?: $this->photo;
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . $path);
        }
        return null;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->avatar_url;
    }

    public function getIsVerifiedAttribute(): bool
    {
        return (bool) ($this->verified ?? false);
    }

    public function isShadowBanned(): bool
    {
        return (bool) $this->is_shadow_banned;
    }

    public function isGreylisted(): bool
    {
        return (bool) $this->is_greylisted;
    }

    public function hasWarning(): bool
    {
        return $this->warning_level > 0;
    }

    /**
     * Hapus otomatis akun yang belum memverifikasi email setelah 10 menit.
     *
     * @param string|null $email
     * @return int Jumlah akun yang dihapus
     */
    public static function purgeExpiredUnverified(?string $email = null): int
    {
        $cutoff = now()->subMinutes(10);
        $query = static::whereNull('email_verified_at')
            ->where('verified', false)
            ->where('created_at', '<', $cutoff)
            ->whereNotIn('role', ['admin', 'super_admin']);

        if ($email) {
            $query->where('email', strtolower(trim($email)));
        }

        $users = $query->get();
        $count = 0;

        foreach ($users as $u) {
            try {
                \App\Models\Registration::where('email', $u->email)
                    ->where('status', '!=', 'approved')
                    ->delete();
                $u->delete();
                $count++;
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $count;
    }

    /**
     * Hapus otomatis akun yang statusnya masih inactive dan belum menyelesaikan pengisian form data diri / KTP setelah 1x24 jam.
     *
     * @param string|null $email
     * @return int Jumlah akun yang dihapus
     */
    public static function purgeExpiredInactive(?string $email = null): int
    {
        $cutoff = now()->subHours(24);
        $query = static::where('status', 'inactive')
            ->where('verified', false)
            ->where('created_at', '<', $cutoff)
            ->whereNotIn('role', ['admin', 'super_admin'])
            ->where(function ($q) {
                $q->whereNull('nik')
                  ->orWhere('nik', '')
                  ->orWhereNull('ktp_photo')
                  ->orWhere('ktp_photo', '');
            });

        if ($email) {
            $query->where('email', strtolower(trim($email)));
        }

        $users = $query->get();
        $count = 0;

        foreach ($users as $u) {
            try {
                $registrations = \App\Models\Registration::where('email', $u->email)
                    ->whereNotIn('status', ['approved', 'pending_verification'])
                    ->get();

                foreach ($registrations as $reg) {
                    if ($reg->ktp_photo_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($reg->ktp_photo_path);
                    }
                    if ($reg->selfie_photo_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($reg->selfie_photo_path);
                    }
                    $reg->delete();
                }

                $u->delete();
                $count++;
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $count;
    }

    // Relationships
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    // Districts managed by this admin (many-to-many)
    public function managedDistricts()
    {
        return $this->belongsToMany(District::class, 'admin_district', 'user_id', 'district_id')
                    ->withTimestamps();
    }

    public function districts()
    {
        return $this->managedDistricts();
    }

    /**
     * Get all unique district IDs that this admin is authorized to manage.
     * Merges the primary district_id with any districts in the admin_district pivot table.
     *
     * @return array<int>
     */
    public function getAdminDistrictIds(): array
    {
        if ($this->role !== 'admin') {
            return [];
        }

        if ($this->relationLoaded('managedDistricts')) {
            $managedIds = $this->managedDistricts->pluck('id')->all();
        } else {
            $managedIds = $this->managedDistricts()->allRelatedIds()->all();
        }

        if (!empty($managedIds)) {
            return array_values(array_unique(array_map('intval', $managedIds)));
        }

        if (!empty($this->district_id)) {
            return [(int) $this->district_id];
        }

        $cityIds = $this->getAdminCityIds();
        if (!empty($cityIds)) {
            $cityDistrictIds = District::whereIn('city_id', $cityIds)->pluck('id')->map('intval')->all();
            if (!empty($cityDistrictIds)) {
                return $cityDistrictIds;
            }
        }

        return [];
    }

    /**
     * Check if admin has authority over a given district.
     */
    public function hasAccessToDistrict($districtId): bool
    {
        if (in_array($this->role, ['superadmin', 'super_admin'])) {
            return true;
        }

        if ($this->role !== 'admin') {
            return false;
        }

        $allowedIds = $this->getAdminDistrictIds();
        return !empty($districtId) && in_array((int) $districtId, $allowedIds, true);
    }

    /**
     * Get Collection of District models managed by this admin.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAdminDistricts()
    {
        $districtIds = $this->getAdminDistrictIds();
        if (empty($districtIds)) {
            return collect();
        }

        return District::whereIn('id', $districtIds)->with('city')->orderBy('name')->get();
    }

    /**
     * Get comma-separated names of all districts managed by this admin.
     */
    public function getAdminDistrictNamesAttribute(): string
    {
        $districts = $this->getAdminDistricts();
        if ($districts->isEmpty()) {
            return $this->kecamatan ?: ($this->district?->name ?: 'Semua Wilayah');
        }

        return $districts->pluck('name')->map(fn($n) => 'Kec. ' . $n)->join(', ');
    }

    /**
     * Ambil filter wilayah kecamatan aktif admin dari sesi/cache.
     * Mengembalikan 'all' atau ID kecamatan (string angka).
     */
    public function getActiveAdminDistrictFilter(): string
    {
        $cachedDistrict = cache()->get("admin_active_district_{$this->id}");
        $sessionDistrict = session('admin_active_district_filter');

        $active = $sessionDistrict ?? $cachedDistrict ?? 'all';
        if ($active === 'all' || empty($active)) {
            return 'all';
        }

        $allowedIds = $this->getAdminDistrictIds();
        if (!in_array((int) $active, $allowedIds, true)) {
            return 'all';
        }

        return (string) $active;
    }

    /**
     * Set filter wilayah kecamatan aktif admin.
     */
    public function setActiveAdminDistrictFilter(?string $districtId): void
    {
        $val = ($districtId === null || $districtId === '' || $districtId === 'all') ? 'all' : (string) (int) $districtId;
        if ($val !== 'all') {
            $allowedIds = $this->getAdminDistrictIds();
            if (!in_array((int) $val, $allowedIds, true)) {
                $val = 'all';
            }
        }

        session(['admin_active_district_filter' => $val]);
        try {
            cache()->put("admin_active_district_{$this->id}", $val, now()->addDays(7));
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Ambil array ID kecamatan yang sedang aktif berlaku untuk query data.
     * Jika admin memfilter 1 kecamatan tertentu, kembalikan [district_id].
     * Jika 'all', kembalikan seluruh kecamatan wewenangnya.
     */
    public function getEffectiveAdminDistrictIds(): array
    {
        $active = $this->getActiveAdminDistrictFilter();
        if ($active !== 'all') {
            return [(int) $active];
        }

        return $this->getAdminDistrictIds();
    }

    /**
     * Label wilayah kecamatan aktif untuk header navbar admin.
     */
    public function getActiveAdminDistrictLabelAttribute(): string
    {
        $active = $this->getActiveAdminDistrictFilter();
        if ($active === 'all') {
            $districts = $this->getAdminDistricts();
            if ($districts->count() === 1) {
                return 'Kec. ' . $districts->first()->name;
            } elseif ($districts->count() > 1) {
                return "Semua Wilayah ({$districts->count()} Kecamatan)";
            }
            return 'Semua Wilayah Kecamatan';
        }

        $district = District::find((int) $active);
        return $district ? 'Kec. ' . $district->name : 'Semua Wilayah Kecamatan';
    }

    /**
     * Ambil konfigurasi wilayah aktif pantauan Super Admin.
     *
     * @return array{type: string, id: int|null, label: string}
     */
    public function getActiveSuperadminTerritory(): array
    {
        $sessionType = session('superadmin_active_territory_type');
        $sessionId   = session('superadmin_active_territory_id');

        $cachedType = cache()->get("superadmin_active_territory_type_{$this->id}");
        $cachedId   = cache()->get("superadmin_active_territory_id_{$this->id}");

        $type = $sessionType ?? $cachedType ?? 'all';
        $id   = $sessionId ?? $cachedId ?? null;

        if ($type === 'district' && $id) {
            $district = District::with('city')->find((int) $id);
            if ($district) {
                $cityLabel = $district->city ? " ({$district->city->name})" : '';
                return [
                    'type'  => 'district',
                    'id'    => (int) $id,
                    'label' => "Kec. {$district->name}{$cityLabel}",
                ];
            }
        } elseif ($type === 'city' && $id) {
            $city = City::find((int) $id);
            if ($city) {
                return [
                    'type'  => 'city',
                    'id'    => (int) $id,
                    'label' => "Kota {$city->name} (Semua Kec.)",
                ];
            }
        }

        return [
            'type'  => 'all',
            'id'    => null,
            'label' => 'Semua Wilayah (Nasional)',
        ];
    }

    /**
     * Set wilayah aktif pantauan Super Admin ke sesi dan cache.
     */
    public function setActiveSuperadminTerritory(string $type, $id = null): void
    {
        $type = in_array($type, ['city', 'district'], true) ? $type : 'all';
        $id   = ($type !== 'all' && $id) ? (int) $id : null;

        session([
            'superadmin_active_territory_type' => $type,
            'superadmin_active_territory_id'   => $id,
        ]);

        try {
            cache()->put("superadmin_active_territory_type_{$this->id}", $type, now()->addDays(7));
            cache()->put("superadmin_active_territory_id_{$this->id}", $id, now()->addDays(7));
        } catch (\Throwable $e) {
            // ignore
        }

        // Sync admin filter session for backwards compatibility
        if ($type === 'district' && $id) {
            session(['admin_active_district_filter' => (string) $id]);
            $cityId = District::where('id', $id)->value('city_id');
            session(['admin_active_city_filter' => $cityId ? (string) $cityId : 'all']);
        } elseif ($type === 'city' && $id) {
            session(['admin_active_district_filter' => 'all']);
            session(['admin_active_city_filter' => (string) $id]);
        } else {
            session(['admin_active_district_filter' => 'all']);
            session(['admin_active_city_filter' => 'all']);
        }
    }

    /**
     * Ambil array ID kecamatan yang sedang aktif berlaku untuk Super Admin.
     * Jika 'all', kembalikan [] (artinya seluruh wilayah tanpa batas).
     * Jika 'city', kembalikan seluruh ID kecamatan di kota tersebut.
     * Jika 'district', kembalikan [district_id].
     */
    public function getEffectiveSuperadminDistrictIds(): array
    {
        $territory = $this->getActiveSuperadminTerritory();
        if ($territory['type'] === 'district' && $territory['id']) {
            return [(int) $territory['id']];
        }

        if ($territory['type'] === 'city' && $territory['id']) {
            return District::where('city_id', (int) $territory['id'])->pluck('id')->map(fn($id) => (int)$id)->all();
        }

        return [];
    }

    // Cities managed by this admin (derived from managed districts)
    public function managedCities()
    {
        return $this->belongsToMany(City::class, 'admin_city', 'user_id', 'city_id')
                    ->withTimestamps();
    }

    public function getAdminCityIds(): array
    {
        if ($this->role !== 'admin') {
            return [];
        }

        $cityIds = [];

        // 1. Directly assigned managed cities (admin_city pivot)
        if ($this->relationLoaded('managedCities')) {
            $cityIds = array_merge($cityIds, $this->managedCities->pluck('id')->all());
        } else {
            $cityIds = array_merge($cityIds, $this->managedCities()->allRelatedIds()->all());
        }

        // 2. Cities derived from managed districts (admin_district pivot)
        $districtIds = [];
        if ($this->relationLoaded('managedDistricts')) {
            $districtIds = $this->managedDistricts->pluck('id')->all();
        } else {
            $districtIds = $this->managedDistricts()->allRelatedIds()->all();
        }
        if (!empty($districtIds)) {
            $cityIds = array_merge($cityIds, District::whereIn('id', $districtIds)->pluck('city_id')->all());
        }

        // 3. Primary district parent city
        if (!empty($this->district_id)) {
            $parentCityId = District::where('id', $this->district_id)->value('city_id');
            if ($parentCityId) {
                $cityIds[] = (int) $parentCityId;
            }
        }

        // 4. Primary city_id
        if (!empty($this->city_id)) {
            $cityIds[] = (int) $this->city_id;
        }

        return array_values(array_unique(array_filter(array_map('intval', $cityIds))));
    }

    /**
     * Get Collection of City models derived from districts managed by this admin.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAdminCities()
    {
        $cityIds = $this->getAdminCityIds();
        if (empty($cityIds)) {
            return collect();
        }

        return City::whereIn('id', $cityIds)->orderBy('name')->get();
    }

    /**
     * Get comma-separated names of all cities managed by this admin.
     */
    public function getAdminCityNamesAttribute(): string
    {
        $cities = $this->getAdminCities();
        if ($cities->isEmpty()) {
            return $this->city_name ?: 'Semua Wilayah';
        }

        return $cities->pluck('name')->join(', ');
    }

    /**
     * Ambil filter wilayah aktif admin dari sesi.
     * Mengembalikan 'all' atau ID kota (string angka).
     */
    public function getActiveAdminCityFilter(): string
    {
        $cachedCity = cache()->get("admin_active_city_{$this->id}");
        $sessionCity = session('admin_active_city_filter');

        $active = $sessionCity ?? $cachedCity ?? 'all';
        if ($active === 'all' || empty($active)) {
            return 'all';
        }

        $allowedIds = $this->getAdminCityIds();
        if (in_array((int) $active, $allowedIds, true)) {
            return (string) $active;
        }

        return 'all';
    }

    /**
     * Simpan filter wilayah aktif admin ke sesi dan cache.
     */
    public function setActiveAdminCityFilter(string $cityFilter): void
    {
        $allowedIds = $this->getAdminCityIds();

        if ($cityFilter === 'all' || empty($cityFilter)) {
            session(['admin_active_city_filter' => 'all']);
            cache()->put("admin_active_city_{$this->id}", 'all', now()->addDays(7));
        } elseif (in_array((int) $cityFilter, $allowedIds, true)) {
            session(['admin_active_city_filter' => (string) $cityFilter]);
            cache()->put("admin_active_city_{$this->id}", (string) $cityFilter, now()->addDays(7));
        } else {
            session(['admin_active_city_filter' => 'all']);
            cache()->put("admin_active_city_{$this->id}", 'all', now()->addDays(7));
        }

        try {
            session()->save();
        } catch (\Throwable $e) {
            // Silently ignore if session is not yet started in CLI
        }
    }

    /**
     * Ambil array ID kota yang sedang aktif berlaku untuk query data.
     * Jika admin memfilter 1 kota tertentu, kembalikan [city_id].
     * Jika 'all', kembalikan seluruh kota wewenangnya.
     */
    public function getEffectiveAdminCityIds(): array
    {
        $active = $this->getActiveAdminCityFilter();
        if ($active !== 'all') {
            return [(int) $active];
        }

        return $this->getAdminCityIds();
    }

    /**
     * Label teks wilayah yang sedang dipantau saat ini.
     */
    public function getActiveAdminCityLabelAttribute(): string
    {
        $active = $this->getActiveAdminCityFilter();
        if ($active === 'all') {
            $count = count($this->getAdminCityIds());
            return $count > 1 ? "Semua Wilayah ({$count} Kota)" : ($this->admin_city_names ?: 'Semua Wilayah');
        }

        $city = City::find((int) $active);
        return $city ? $city->name : 'Semua Wilayah';
    }

    public function helps()
    {
        return $this->hasMany(Help::class);
    }

    public function takenHelps()
    {
        return $this->hasMany(Help::class, 'mitra_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'mitra_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'ratee_id');
    }

    // Ratings received as customer (from mitra)
    public function customerRatings()
    {
        return $this->hasMany(Rating::class, 'ratee_id')->where('type', 'mitra_to_customer');
    }

    // Ratings received as mitra (from customer)  
    public function mitraRatings()
    {
        return $this->hasMany(Rating::class, 'ratee_id')
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            });
    }

    public function partnerReports()
    {
        return $this->hasMany(\App\Models\PartnerReport::class);
    }

    public function partnerActivities()
    {
        return $this->hasMany(\App\Models\PartnerActivity::class, 'user_id');
    }

    public function latestPartnerActivity()
    {
        return $this->hasOne(\App\Models\PartnerActivity::class, 'user_id')->latestOfMany();
    }




    public function balance()
    {
        return $this->hasOne(UserBalance::class);
    }

    public function userBalance()
    {
        return $this->hasOne(UserBalance::class);
    }

    public function withdrawRequests()
    {
        return $this->hasMany(\App\Models\WithdrawRequest::class);
    }

    public function hasPendingOrProcessingWithdraws(): bool
    {
        return $this->withdrawRequests()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }

    public function adjustBalance(int $amountDelta): void
    {
        // Use UserBalance helper methods to modify balance and record transactions.
        $userBalance = $this->userBalance()->first() ?? $this->balance()->first();
        if (!$userBalance) {
            $userBalance = $this->balance()->create(['balance' => 0]);
        }

        if ($amountDelta === 0)
            return;

        if ($amountDelta > 0) {
            // Refund / topup
            $userBalance->addBalance($amountDelta, 'refund');
        } else {
            // Deduction
            $userBalance->deductBalance(abs($amountDelta), 'withdraw_deduction');
        }
    }

    /**
     * Accessor to get numeric balance conveniently via $user->balance
     */
    public function getBalanceAttribute()
    {
        // Prefer UserBalance row
        $userBalance = $this->getRelationValue('userBalance')
            ?? $this->getRelationValue('balance')
            ?? $this->userBalance()->first()
            ?? $this->balance()->first();

        if ($userBalance && isset($userBalance->balance)) {
            return (float) $userBalance->balance;
        }

        return 0.0;
    }

    public function transactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    // Helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isMitra()
    {
        return $this->role === 'mitra';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isKustomer()
    {
        return $this->isCustomer();
    }

    public function isVerified()
    {
        return $this->verified;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Waktu aktivitas terakhir pengguna (berdasarkan permintaan/pekerjaan bantuan terbaru).
     */
    public function getLastActivityAtAttribute(): ?\Carbon\Carbon
    {
        $dt1 = $this->helps_max_updated_at ?? null;
        $dt2 = $this->taken_helps_max_updated_at ?? null;

        if ($dt1 || $dt2) {
            $c1 = $dt1 ? \Carbon\Carbon::parse($dt1) : null;
            $c2 = $dt2 ? \Carbon\Carbon::parse($dt2) : null;
            if ($c1 && $c2) return $c1->max($c2);
            return $c1 ?: $c2;
        }

        // Direct database lookup if withMax was not loaded
        $latestCustomerHelp = $this->helps()->latest('updated_at')->value('updated_at');
        $latestMitraHelp = $this->takenHelps()->latest('updated_at')->value('updated_at');

        $dates = collect([$latestCustomerHelp, $latestMitraHelp])
            ->filter()
            ->map(fn($d) => \Carbon\Carbon::parse($d));

        return $dates->max();
    }

    /**
     * Teks waktu relatif aktivitas terakhir (misal: "2 jam yang lalu").
     */
    public function getLastActivityForHumansAttribute(): string
    {
        $last = $this->last_activity_at;
        if (!$last) {
            return 'Belum ada aktivitas';
        }

        return $last->diffForHumans();
    }

    /**
     * Return the display name for the city.
     * Prefer the related City model (or city_id lookup), fallback to users.city attribute.
     */
    public function getCityNameAttribute(): ?string
    {
        $rel = $this->getRelationValue('city');
        if ($rel && isset($rel->name)) {
            return $rel->name;
        }

        if (!empty($this->city_id)) {
            $cityModel = City::find($this->city_id);
            if ($cityModel && isset($cityModel->name)) {
                return $cityModel->name;
            }
        }

        return isset($this->attributes['city']) && $this->attributes['city'] !== null
            ? (string) $this->attributes['city']
            : null;
    }

    // Notification Settings Accessor with robust defaults
    public function getNotificationSettingsAttribute($value)
    {
        $defaults = [
            'help_updates' => true,
            'chat_messages' => true,
            'transactions' => true,
            'sound_enabled' => true,
            'auto_mark_read' => false,
            'auto_cleanup_read' => false,
        ];

        if (empty($value)) {
            return $defaults;
        }

        $decoded = is_string($value) ? json_decode($value, true) : (array) $value;
        return array_merge($defaults, is_array($decoded) ? $decoded : []);
    }

    // Unified Rating Accessors
    public function getAverageRatingAttribute()
    {
        if ($this->isMitra()) {
            return $this->mitra_average_rating;
        } elseif ($this->isCustomer()) {
            return $this->customer_average_rating;
        }
        return $this->mitra_average_rating ?: $this->customer_average_rating;
    }

    public function getRatingCountAttribute()
    {
        if ($this->isMitra()) {
            return $this->mitra_rating_count;
        } elseif ($this->isCustomer()) {
            return $this->customer_rating_count;
        }
        return $this->mitra_rating_count ?: $this->customer_rating_count;
    }

    // Customer Rating Methods
    public function getCustomerAverageRatingAttribute()
    {
        $avg = Rating::where('ratee_id', $this->id)
            ->where('type', 'mitra_to_customer')
            ->avg('rating');

        return $avg ? round((float) $avg, 1) : 0;
    }

    public function getCustomerRatingCountAttribute()
    {
        return Rating::where('ratee_id', $this->id)
            ->where('type', 'mitra_to_customer')
            ->count();
    }

    public function getCustomerRatingBadgeAttribute()
    {
        $rating = $this->customer_average_rating;
        
        if ($rating >= 4.5) {
            return [
                'text' => 'Customer Terpercaya',
                'color' => 'green',
                'emoji' => '🌟'
            ];
        } elseif ($rating >= 4.0) {
            return [
                'text' => 'Customer Baik',
                'color' => 'blue',
                'emoji' => '⭐'
            ];
        } elseif ($rating >= 3.0) {
            return [
                'text' => 'Standar',
                'color' => 'yellow',
                'emoji' => '✓'
            ];
        } else {
            return [
                'text' => 'Perlu Perbaikan',
                'color' => 'red',
                'emoji' => '⚠️'
            ];
        }
    }

    // Mitra Rating Methods
    public function getMitraAverageRatingAttribute()
    {
        $avg = Rating::where('ratee_id', $this->id)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            })->avg('rating');

        return $avg ? round((float) $avg, 1) : 0;
    }

    public function getMitraRatingCountAttribute()
    {
        return Rating::where('ratee_id', $this->id)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            })->count();
    }

    public function onlineState()
    {
        return $this->hasOne(PartnerOnlineState::class, 'user_id');
    }

    /**
     * Customer Saved Landmarks / Patokan Presets
     */
    public function getSavedLandmarksList(): array
    {
        $landmarks = $this->saved_landmarks;
        if (is_string($landmarks)) {
            $landmarks = json_decode($landmarks, true);
        }
        return is_array($landmarks) ? array_values($landmarks) : [];
    }

    public function addSavedLandmark(string $label, string $patokan): array
    {
        $list = $this->getSavedLandmarksList();
        $item = [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'label' => trim($label),
            'patokan' => trim($patokan),
            'created_at' => now()->toIso8601String(),
        ];
        $list[] = $item;
        $this->update(['saved_landmarks' => $list]);
        return $item;
    }

    public function updateSavedLandmark(string $id, string $label, string $patokan): bool
    {
        $list = $this->getSavedLandmarksList();
        $found = false;
        foreach ($list as &$item) {
            if (($item['id'] ?? '') === $id) {
                $item['label'] = trim($label);
                $item['patokan'] = trim($patokan);
                $item['updated_at'] = now()->toIso8601String();
                $found = true;
                break;
            }
        }
        if ($found) {
            $this->update(['saved_landmarks' => $list]);
        }
        return $found;
    }

    public function deleteSavedLandmark(string $id): bool
    {
        $list = $this->getSavedLandmarksList();
        $filtered = array_values(array_filter($list, fn($item) => ($item['id'] ?? '') !== $id));
        $this->update(['saved_landmarks' => $filtered]);
        return count($filtered) < count($list);
    }
}
