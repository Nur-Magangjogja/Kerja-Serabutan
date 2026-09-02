<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpPartnerExclusion extends Model
{
    use HasFactory;

    protected $table = 'help_partner_exclusions';

    protected $fillable = [
        'help_id',
        'mitra_id',
        'reason',
    ];

    public function help()
    {
        return $this->belongsTo(Help::class, 'help_id');
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }
}
