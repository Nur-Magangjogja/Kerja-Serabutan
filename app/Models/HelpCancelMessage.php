<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class HelpCancelMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'help_cancel_request_id',
        'sender_id',
        'recipient_type',
        'message',
        'photo',
        'is_read',
        'read_at',
        'customer_read_at',
        'mitra_read_at',
    ];

    protected $casts = [
        'is_read'          => 'boolean',
        'read_at'          => 'datetime',
        'customer_read_at' => 'datetime',
        'mitra_read_at'    => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    // Relationships
    public function cancelRequest(): BelongsTo
    {
        return $this->belongsTo(HelpCancelRequest::class, 'help_cancel_request_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromAdmin(): bool
    {
        return in_array($this->sender?->role ?? '', ['admin', 'super_admin', 'superadmin']);
    }

    public function isFromCustomer(): bool
    {
        return $this->sender?->role === 'customer';
    }

    public function isFromMitra(): bool
    {
        return $this->sender?->role === 'mitra';
    }

    protected static function booted()
    {
        static::created(function (HelpCancelMessage $message) {
            try {
                // Notifikasi ke target saat admin mengirim pesan
                if ($message->isFromAdmin()) {
                    $req = $message->cancelRequest;
                    if (!$req) return;

                    $help = $req->help;
                    $customer = $help?->user ?? $req->customer;
                    $partner = $help?->mitra ?? $req->partner;

                    $shortMsg = \Illuminate\Support\Str::limit($message->message, 80);

                    if ($message->recipient_type === 'customer' || $message->recipient_type === 'all') {
                        $customer?->notify(new \App\Notifications\HelpStatusNotification(
                            $help,
                            $help?->status ?? 'in_progress',
                            'admin_clarification',
                            $partner,
                            "Pesan dari Tim Admin: \"{$shortMsg}\"",
                            "Pesan Baru dari Tim Admin SayaBantu"
                        ));
                    }

                    if ($message->recipient_type === 'mitra' || $message->recipient_type === 'all') {
                        $partner?->notify(new \App\Notifications\HelpStatusNotification(
                            $help,
                            $help?->status ?? 'in_progress',
                            'admin_clarification',
                            $partner,
                            "Pesan dari Tim Admin: \"{$shortMsg}\"",
                            "Pesan Baru dari Tim Admin SayaBantu"
                        ));
                    }
                } else {
                    // Jika dikirim oleh Customer / Mitra ke Admin, kirim notifikasi ke Admin
                    $req = $message->cancelRequest;
                    if (!$req) return;

                    $districtId = $req->district_id ?? $req->help?->district_id;
                    $admins = User::where('role', 'admin')
                        ->when($districtId, fn($q) => $q->where('district_id', $districtId))
                        ->where('status', 'active')
                        ->get();

                    if ($admins->isEmpty()) {
                        $admins = User::where('role', 'admin')->where('status', 'active')->get();
                    }

                    $superAdmins = User::whereIn('role', ['super_admin', 'superadmin'])
                        ->where('status', 'active')
                        ->get();

                    $recipients = $admins->merge($superAdmins)->unique('id');
                    $senderName = $message->sender?->name ?? 'Pengguna';
                    $shortMsg = \Illuminate\Support\Str::limit($message->message, 80);

                    foreach ($recipients as $recipient) {
                        $recipient->notify(new \App\Notifications\HelpStatusNotification(
                            $req->help,
                            $req->help?->status ?? 'in_progress',
                            'cancellation_response',
                            null,
                            "Balasan klarifikasi pembatalan dari {$senderName}: \"{$shortMsg}\"",
                            "Tanggapan Klarifikasi Pembatalan"
                        ));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[HelpCancelMessage] Gagal mengirim notifikasi: ' . $e->getMessage());
            }
        });
    }
}
