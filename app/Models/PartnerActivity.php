<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PartnerActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a partner activity record.
     *
     * @param mixed $user User model or user id
     * @param string $activityType
     * @param string|null $description
     * @param string|null $ip
     * @param string|null $userAgent
     * @return static
     */
    public static function record($user, string $activityType, ?string $description = null, ?string $ip = null, ?string $userAgent = null)
    {
        $userId = null;
        if (is_object($user) && property_exists($user, 'id')) {
            $userId = $user->id;
        } elseif (is_int($user) || ctype_digit((string) $user)) {
            $userId = (int) $user;
        }

        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => $ip ?? (function_exists('request') ? request()?->ip() : null),
            'user_agent' => $userAgent ?? (function_exists('request') ? request()?->header('User-Agent') : null),
        ]);
    }
}
