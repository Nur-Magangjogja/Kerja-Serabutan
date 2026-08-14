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
        'pengguna_spam' => 'Pengguna Spam',
        'pengguna_kasar' => 'Pengguna Kasar',
        'data_tidak_valid' => 'Data Tidak Valid',
        'penipuan' => 'Penipuan',
        'pelanggaran_aturan' => 'Pelanggaran Aturan',
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
        // no separate custom report type; custom help type handled separately
    ];

    protected $messages = [
        'title.required' => 'Judul laporan harus diisi',
        'message.required' => 'Pesan laporan harus diisi',
        'message.min' => 'Pesan minimal 10 karakter',
        'report_type.required' => 'Jenis laporan harus dipilih',
    ];

    public function mount($user_id = null, $help_id = null)
    {
        // If the current user already has a recent report that's not closed/resolved,
        // redirect them to its status page instead of showing the create form.
        // Allow forcing the create form with ?new=1 in the URL.
        if (auth()->check() && request()->query('new') !== '1') {
            $latest = PartnerReport::where('reporter_id', auth()->id())->latest()->first();
            if ($latest && !in_array($latest->status, ['resolved', 'closed', 'rejected'])) {
                return redirect()->route('mitra.reports.show', ['report' => $latest->id]);
            }
        }
        // Handle route parameters
        if (request()->route('user_id')) {
            $this->reported_user_id = request()->route('user_id');
        } elseif ($user_id) {
            $this->reported_user_id = $user_id;
        }

        if (request()->route('help_id')) {
            $this->reported_help_id = request()->route('help_id');
            $this->help_id = request()->route('help_id');
        } elseif ($help_id) {
            $this->reported_help_id = $help_id;
            $this->help_id = $help_id;
        }
    }

    public function submit()
    {
        $this->validate();

        // If help_id is selected, set reported_help_id
        if ($this->help_id) {
            $help = Help::find($this->help_id);
            if ($help) {
                $this->reported_help_id = $help->id;
                // If help has customer, set reported_user_id
                if ($help->user_id && !$this->reported_user_id) {
                    $this->reported_user_id = $help->user_id;
                }
                // populate reported_help_text as fallback
                if (!$this->reported_help_text) {
                    $this->reported_help_text = $help->title;
                }
            }
        }

        // If user provided a custom help type when report_type == 'lainnya', use it
        if ($this->report_type === 'lainnya') {
            // First handle custom report type (user-entered label for the report)
            if (!empty(trim($this->custom_report_type))) {
                $this->report_type = trim($this->custom_report_type);
            }

            // Then handle custom help type (if the report is about a help type)
            if (!empty(trim($this->custom_help_type))) {
                $this->reported_help_text = $this->custom_help_type;
            } elseif (empty(trim($this->reported_help_text))) {
                // If neither a help selection nor custom help type provided, require at least one
                // Note: if the report is purely about a user and not a help, reported_help_text may stay empty
                // so we only add error when appropriate (keep existing behavior)
                $this->addError('custom_help_type', 'Silakan isi jenis bantuan yang tidak tersedia pada pilihan.');
                return;
            }
        }

        $report = PartnerReport::create([
            'user_id' => $this->reported_user_id, // legacy column (may be null now)
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

        session()->flash('message', 'Laporan aduan berhasil dikirim. Admin akan meninjau laporan Anda.');
        return redirect()->route('mitra.reports.show', ['report' => $report->id]);
    }

    public function render()
    {
        $helps = auth()->user()->takenHelps()->whereIn('status', ['active', 'completed'])->get();
        $customers = User::where('role', 'customer')->orWhere('role', 'kustomer')->get();

        return view('livewire.mitra.reports.create', [
            'helps' => $helps,
            'customers' => $customers,
        ]);
    }
}
