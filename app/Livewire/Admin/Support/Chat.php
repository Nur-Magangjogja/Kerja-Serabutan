<?php

namespace App\Livewire\Admin\Support;

use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public PartnerReport $report;
    public string $message = '';
    public $photo = null;

    protected function rules(): array
    {
        return [
            'message' => 'required_without:photo|nullable|string|max:2000',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:1536',
        ];
    }

    public function mount(PartnerReport $report)
    {
        // 1. Must be general support
        if ($report->report_type !== 'dukungan_umum') {
            abort(404, 'Percakapan bukan merupakan tiket dukungan umum.');
        }

        $admin = auth()->user();

        // 2. Strict Regional Authorization for Admin Wilayah (G8 Canonical Territory)
        $canonicalTerritory = $report->getCanonicalTerritory();
        $targetDistrictId = $canonicalTerritory['district_id'];
        $targetCityId = $canonicalTerritory['city_id'];

        $authService = app(\App\Services\Territory\AdminTerritoryAuthorizationService::class);
        if (!$authService->canAccessTerritory($admin, $targetDistrictId ? (int)$targetDistrictId : null, $targetCityId ? (int)$targetCityId : null)) {
            abort(403, 'Anda tidak memiliki wewenang untuk meninjau dukungan di luar wilayah Anda.');
        }

        $this->report = $report->load(['reporter.district', 'reporter.city', 'reportedHelp']);
        $this->markAsRead();

        // Saat admin membuka chat pertama kali, ubah status dari 'pending' (Menunggu Respon) menjadi 'in_progress' (Sedang Diproses)
        if ($this->report->status === 'pending') {
            $this->report->update(['status' => 'in_progress']);
        }
    }

    public function markAsRead(): void
    {
        PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function sendMessage(): void
    {
        $this->validate();

        $adminId = auth()->id();
        $photoName = $this->photo ? $this->photo->getClientOriginalName() : '';
        $lockKey = 'send_support_' . $adminId . '_rep_' . $this->report->id . '_' . md5(($this->message ?? '') . '_' . $photoName);

        if (!Cache::add($lockKey, true, 2)) {
            return;
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('reports/messages', 'public');
        }

        $msgText = trim($this->message) ?: ($photoPath ? '[Lampiran Foto]' : '');
        $recipientType = ($this->report->category === 'dari_customer') ? 'customer' : 'mitra';

        PartnerReportMessage::create([
            'partner_report_id' => $this->report->id,
            'sender_id'         => $adminId,
            'recipient_type'    => $recipientType,
            'message'           => $msgText,
            'photo'             => $photoPath,
            'is_read'           => false,
        ]);

        if ($this->report->status === 'pending') {
            $this->report->update(['status' => 'in_progress']);
        }

        $this->message = '';
        $this->photo   = null;

        $this->dispatch('message-sent');
        $this->dispatch('scroll-chat-bottom');
    }

    public function mute(string $duration): void
    {
        $target = match ($duration) {
            '1_hour'   => now()->addHour(),
            '8_hours'  => now()->addHours(8),
            '24_hours' => now()->addDay(),
            '3_days'   => now()->addDays(3),
            '7_days'   => now()->addDays(7),
            'forever'  => now()->addYears(50),
            default    => now()->addDay(),
        };

        $this->report->muteUntil($target);
        $this->report->refresh();

        session()->flash('success', 'Percakapan berhasil dibisukan.');
    }

    public function unmute(): void
    {
        $this->report->unmute();
        $this->report->refresh();

        session()->flash('success', 'Percakapan telah dibunyikan kembali.');
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);
        $routePrefix = $isSuperAdmin ? 'superadmin.' : 'admin.';
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        $messages = PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.admin.support.chat', [
            'messages'    => $messages,
            'routePrefix' => $routePrefix,
        ])->layout($layout);
    }
}
