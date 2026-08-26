<?php

namespace App\Livewire\Admin\Partners\Reports;

use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public PartnerReport $report;
    public $activeTab = 'customer'; // customer or mitra
    public $message = '';
    public $photo;

    public function mount(PartnerReport $report)
    {
        $this->report = $report->load(['reporter', 'reportedUser', 'reportedHelp']);
        $this->markAsRead();
    }

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
        $this->markAsRead();
    }

    public function markAsRead()
    {
        PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->where('recipient_type', $this->activeTab)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required_without:photo|nullable|string|max:2000',
            'photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('reports/messages', 'public');
        }

        PartnerReportMessage::create([
            'partner_report_id' => $this->report->id,
            'sender_id' => auth()->id(),
            'recipient_type' => $this->activeTab,
            'message' => $this->message,
            'photo' => $photoPath,
        ]);

        $this->reset(['message', 'photo']);
        $this->dispatch('chat-scrolled');
    }

    public function render()
    {
        $messages = PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->where(function ($q) {
                $q->where('recipient_type', $this->activeTab)
                  ->orWhere('recipient_type', 'all');
            })
            ->with('sender')
            ->oldest()
            ->get();

        $unreadCustomer = PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->where('recipient_type', 'customer')
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->count();

        $unreadMitra = PartnerReportMessage::where('partner_report_id', $this->report->id)
            ->where('recipient_type', 'mitra')
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->count();

        $isSuperAdmin = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.reports.chat', [
            'messages' => $messages,
            'unreadCustomer' => $unreadCustomer,
            'unreadMitra' => $unreadMitra,
            'isSuperAdmin' => $isSuperAdmin,
        ])->layout($layout);
    }
}
