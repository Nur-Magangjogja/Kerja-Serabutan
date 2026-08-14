<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PartnerReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // Backward compatibility
        'reporter_id',
        'reported_user_id',
        'reported_help_id',
        'reported_help_text',
        'reported_user_text',
        'title',
        'message',
        'status',
        'report_type',
        'category',
        'admin_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reportedHelp()
    {
        return $this->belongsTo(Help::class, 'reported_help_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeDismissed(Builder $query)
    {
        return $query->where('status', 'dismissed');
    }

    public function scopeFromCustomer(Builder $query)
    {
        return $query->where('category', 'dari_customer');
    }

    public function scopeFromMitra(Builder $query)
    {
        return $query->where('category', 'dari_mitra');
    }

    public function scopeByReportType(Builder $query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isDismissed()
    {
        return $this->status === 'dismissed';
    }

    public function isFromCustomer()
    {
        return $this->category === 'dari_customer';
    }

    public function isFromMitra()
    {
        return $this->category === 'dari_mitra';
    }

    // Get report type label
    public function getReportTypeLabelAttribute()
    {
        $types = [
            'mitra_berperilaku_buruk' => 'Mitra Berperilaku Buruk',
            'bantuan_fiktif' => 'Bantuan Fiktif',
            'penipuan' => 'Penipuan',
            'pelanggaran_aturan' => 'Pelanggaran Aturan',
            'konten_tidak_pantas' => 'Konten Tidak Pantas',
            'pelayanan_tidak_sesuai' => 'Pelayanan Tidak Sesuai',
            'pengguna_spam' => 'Pengguna Spam',
            'pengguna_kasar' => 'Pengguna Kasar',
            'data_tidak_valid' => 'Data Tidak Valid',
        ];

        return $types[$this->report_type] ?? ucfirst(str_replace('_', ' ', $this->report_type));
    }

    // Get category label
    public function getCategoryLabelAttribute()
    {
        return $this->category === 'dari_customer' ? 'Dari Customer' : 'Dari Mitra';
    }
}
