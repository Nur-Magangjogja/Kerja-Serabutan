<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGreylistLog extends Model
{
    use HasFactory;

    protected $table = 'user_greylist_logs';

    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'warning_level',
        'reason',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'greylist_add'             => 'Masuk Daftar Abu-Abu',
            'greylist_remove'          => 'Dihapus dari Daftar Abu-Abu',
            'auto_greylist_low_rating' => 'Otomatis Masuk Daftar Abu-Abu (3x Bintang 1)',
            'warning_issued'           => 'Diberikan Surat Peringatan (SP ' . $this->warning_level . ')',
            'shadow_ban_enabled'       => 'Shadow Ban Diaktifkan',
            'shadow_ban_disabled'      => 'Shadow Ban Dinonaktifkan',
            default                    => ucfirst($this->action),
        };
    }
}
