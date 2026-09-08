<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function helps()
    {
        return $this->hasMany(Help::class, 'district_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'district_id');
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'admin_district', 'district_id', 'user_id')
                    ->withTimestamps();
    }

    public function cancelRequests()
    {
        return $this->hasMany(HelpCancelRequest::class, 'district_id');
    }

    /**
     * Dapatkan daftar kecamatan dalam kabupaten/kota yang sama (Adjacent Districts).
     */
    public function getAdjacentDistricts()
    {
        return self::where('city_id', $this->city_id)
            ->where('id', '!=', $this->id)
            ->where('is_active', true)
            ->get();
    }
}


