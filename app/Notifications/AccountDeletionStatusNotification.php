<?php

namespace App\Notifications;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountDeletionStatusNotification extends Notification
{
    use Queueable;

    public AccountDeletionRequest $deletionRequest;

    public function __construct(AccountDeletionRequest $deletionRequest)
    {
        $this->deletionRequest = $deletionRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->deletionRequest->status;
        $isMitra = $notifiable->role === 'mitra';
        $url = $isMitra ? route('mitra.settings') : route('profile.settings');

        if ($status === 'rejected') {
            $title = 'Permintaan Hapus Akun Ditolak';
            $message = 'Permintaan penghapusan akun Anda ditolak oleh Superadmin.' .
                ($this->deletionRequest->admin_notes ? ' Catatan: ' . $this->deletionRequest->admin_notes : '');
        } else {
            $title = 'Status Permintaan Hapus Akun Diperbarui';
            $message = 'Status permohonan hapus akun Anda: ' . $this->deletionRequest->status_label;
        }

        return [
            'type'                => 'account_deletion_' . $status,
            'title'               => $title,
            'message'             => $message,
            'admin_notes'         => $this->deletionRequest->admin_notes,
            'deletion_request_id' => $this->deletionRequest->id,
            'url'                 => $url,
            'status'              => $status,
        ];
    }
}
