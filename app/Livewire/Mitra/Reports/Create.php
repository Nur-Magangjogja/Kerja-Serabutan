<?php

namespace App\Livewire\Mitra\Reports;

use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class Create extends Component
{
    public $title = '';
    public $message = '';
    public $report_type = '';
    public $reported_user_id = null;
    public $reported_help_id = null;
    public $help_id = null; // For selecting help from dropdown
    
    // free-text fields
    public $reported_help_text = null;
    public $reported_user_text = null;
    public $selected_help_type = '';
    public $custom_help_type = '';
    public $custom_report_type = '';

    // available help types (can be adjusted)
    public $helpTypes = [
        '' => 'Pilih Jenis Bantuan (Opsional)',
        'pangan' => 'Pangan / Bahan Pokok',
        'obat' => 'Obat',
        'perbaikan' => 'Perbaikan Rumah',
        'uang' => 'Bantuan Uang',
        'lainnya' => 'Lainnya',
    ];

    public $reportTypes = [
        'customer_tidak_merespon' => 'Customer Tidak Merespon / Sulit Dihubungi',
        'pengguna_kasar' => 'Pengguna Kasar / Tidak Sopan',
        'data_tidak_valid' => 'Data / Lokasi Tugas Tidak Sesuai',
        'penipuan' => 'Penipuan / Manipulasi',
        'pelanggaran_aturan' => 'Pelanggaran Aturan Komunitas',
        'pengguna_spam' => 'Pengguna Spam / Pesanan Palsu',
        'pembatalan_sepihak' => 'Permintaan Pembatalan Sepihak',
        'lainnya' => 'Lainnya',
    ];

    protected $rules = [
        'title' => 'required|string|max:255',
        'message' => 'required|string|min:10|max:2000',
        'report_type' => 'required|string',
        'custom_report_type' => 'nullable|string|max:255',
        'reported_user_id' => 'nullable|exists:users,id',
        'reported_help_id' => 'nullable|exists:helps,id',
        'reported_help_text' => 'nullable|string|max:255',
        'reported_user_text' => 'nullable|string|max:255',
        'selected_help_type' => 'nullable|string',
        'custom_help_type' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'title.required' => 'Judul laporan harus diisi',
        'message.required' => 'Detail penjelasan laporan harus diisi',
        'message.min' => 'Detail laporan minimal 10 karakter',
        'report_type.required' => 'Jenis laporan harus dipilih',
    ];

    public function mount($user_id = null, $help_id = null)
    {
        $this->reported_user_id = request()->route('user_id') ?? request()->query('user_id') ?? $user_id;
        $this->reported_help_id = request()->route('help_id') ?? request()->query('help_id') ?? $help_id;
        $this->help_id = $this->reported_help_id;

        $this->loadDetails();
    }

    public function updatedHelpId($value)
    {
        $this->reported_help_id = $value ?: null;
        $this->loadDetails();
    }

    public function loadDetails(): void
    {
        if ($this->reported_help_id) {
            $help = Help::with('user')->find($this->reported_help_id);
            if ($help) {
                $this->reported_help_text = $help->title;
                $this->selected_help_type = $help->category ?? ($help->service_type ?? '');
                if (!$this->reported_user_id && $help->user_id) {
                    $this->reported_user_id = $help->user_id;
                }
            }
        }

        if ($this->reported_user_id) {
            $user = User::find($this->reported_user_id);
            if ($user) {
                $this->reported_user_text = $user->name;
                
                // If help_id is not yet set, try finding active/recent help between this mitra and customer
                if (!$this->reported_help_id) {
                    $activeHelp = Help::where('mitra_id', auth()->id())
                        ->where('user_id', $this->reported_user_id)
                        ->latest()
                        ->first();
                    if ($activeHelp) {
                        $this->reported_help_id = $activeHelp->id;
                        $this->help_id = $activeHelp->id;
                        $this->reported_help_text = $activeHelp->title;
                        $this->selected_help_type = $activeHelp->category ?? ($activeHelp->service_type ?? '');
                    }
                }
            }
        }
    }

    public function submit()
    {
        $this->validate();

        // Cegah laporan diri sendiri
        if ($this->reported_user_id && (int) $this->reported_user_id === auth()->id()) {
            $this->addError('reported_user_id', 'Anda tidak dapat melaporkan akun Anda sendiri.');
            return;
        }

        // If help_id is selected, ensure reported_help_id is set
        if ($this->help_id) {
            $help = Help::find($this->help_id);
            if ($help) {
                $this->reported_help_id = $help->id;
                if ($help->user_id && !$this->reported_user_id) {
                    $this->reported_user_id = $help->user_id;
                }
                if (!$this->reported_help_text) {
                    $this->reported_help_text = $help->title;
                }
            }
        }

        if ($this->reported_user_id && !$this->reported_user_text) {
            $u = User::find($this->reported_user_id);
            if ($u) {
                $this->reported_user_text = $u->name;
            }
        }

        // If user provided a custom report type when report_type == 'lainnya', use it
        if ($this->report_type === 'lainnya') {
            if (!empty(trim($this->custom_report_type))) {
                $this->report_type = trim($this->custom_report_type);
            }

            if (!empty(trim($this->custom_help_type))) {
                $this->reported_help_text = $this->custom_help_type;
            }
        }

        // Hidden anti-spam: Jika mitra sudah memiliki laporan aduan yang masih pending/diperiksa admin untuk bantuan ini
        // atau baru saja mengirim aduan dalam 5 menit terakhir, serap pengiriman secara senyap tanpa membuat duplikat di database
        $existingPendingReport = PartnerReport::where('reporter_id', auth()->id())
            ->whereIn('status', ['pending', 'investigating', 'under_review', 'proses'])
            ->where(function ($q) {
                if ($this->reported_help_id) {
                    $q->where('reported_help_id', $this->reported_help_id);
                } else {
                    $q->where('created_at', '>=', now()->subMinutes(5));
                }
            })
            ->first();

        if ($existingPendingReport) {
            session()->flash('message', 'Laporan aduan berhasil dikirim. Admin akan meninjau laporan Anda.');
            if ($this->reported_help_id) {
                return redirect()->route('mitra.helps.detail', ['id' => $this->reported_help_id]);
            }
            return redirect()->route('mitra.dashboard');
        }

        $report = PartnerReport::create([
            'reporter_id' => auth()->id(),
            'reported_user_id' => $this->reported_user_id,
            'reported_help_id' => $this->reported_help_id,
            'reported_help_text' => $this->reported_help_text,
            'reported_user_text' => $this->reported_user_text,
            'title' => $this->title,
            'message' => $this->message,
            'report_type' => $this->report_type,
            'category' => 'dari_mitra',
            'status' => 'pending',
        ]);

        \App\Models\ActivityLog::record(
            auth()->user(),
            'report_created',
            "Mitra " . auth()->user()->name . " mengajukan laporan aduan: '{$this->title}'",
            [
                'report_id'        => $report->id,
                'target_user_id'   => $this->reported_user_id,
                'help_id'          => $this->reported_help_id,
                'report_type'      => $this->report_type,
                'reason'           => $this->message,
            ]
        );

        // Kirim notifikasi ke Admin regional terkait
        try {
            $cityId = auth()->user()->city_id;
            $admins = \App\Models\User::where('role', 'admin')
                ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                ->where('status', 'active')
                ->get();
            if ($admins->isEmpty()) {
                $admins = \App\Models\User::where('role', 'admin')->where('status', 'active')->get();
            }
            foreach ($admins as $adm) {
                $adm->notify(new \App\Notifications\NewReportNotification($report));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[MitraReport] Gagal mengirim notifikasi ke admin: ' . $e->getMessage());
        }

        session()->flash('message', 'Laporan aduan berhasil dikirim. Admin akan meninjau laporan Anda.');
        if ($this->reported_help_id) {
            return redirect()->route('mitra.helps.detail', ['id' => $this->reported_help_id]);
        }
        return redirect()->route('mitra.dashboard');
    }

    public function render()
    {
        $allowedStatuses = array_merge(Help::activeStatuses(), [
            Help::STATUS_WAITING_CONFIRMATION,
            Help::STATUS_SELESAI,
            Help::STATUS_DIBATALKAN,
        ]);

        $helps = auth()->user()->takenHelps()
            ->with('user')
            ->whereIn('status', $allowedStatuses)
            ->select('id', 'title', 'status', 'user_id', 'created_at')
            ->latest()
            ->limit(50)
            ->get();

        $selectedHelp = $this->reported_help_id ? Help::with('user')->find($this->reported_help_id) : null;
        $selectedUser = $this->reported_user_id ? User::find($this->reported_user_id) : ($selectedHelp?->user);

        return view('livewire.mitra.reports.create', [
            'helps' => $helps,
            'selectedHelp' => $selectedHelp,
            'selectedUser' => $selectedUser,
        ]);
    }
}
