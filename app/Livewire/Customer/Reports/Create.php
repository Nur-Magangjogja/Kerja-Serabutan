<?php

namespace App\Livewire\Customer\Reports;

use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
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


    public $helpTypes = [
        '' => 'Pilih Jenis Bantuan (Opsional)',
        'pangan' => 'Pangan / Bahan Pokok',
        'obat' => 'Obat',
        'perbaikan' => 'Perbaikan Rumah',
        'uang' => 'Bantuan Uang',
        'lainnya' => 'Lainnya',
    ];

    public $reportTypes = [
        'mitra_berperilaku_buruk' => 'Mitra Berperilaku Buruk',
        'bantuan_fiktif' => 'Bantuan Fiktif',
        'penipuan' => 'Penipuan',
        'pelanggaran_aturan' => 'Pelanggaran Aturan',
        'konten_tidak_pantas' => 'Konten Tidak Pantas',
        'pelayanan_tidak_sesuai' => 'Pelayanan Tidak Sesuai',
    ];

    protected $rules = [
        'title' => 'required|string|max:255',
        'message' => 'required|string|min:10|max:2000',
        'report_type' => 'required|string',
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
                // If help has mitra, set reported_user_id
                if ($help->mitra_id && !$this->reported_user_id) {
                    $this->reported_user_id = $help->mitra_id;
                }
                if (!$this->reported_help_text) {
                    $this->reported_help_text = $help->title;
                }
            }
        }

        // If report_type == 'lainnya', accept custom_help_type as the help description
        if ($this->report_type === 'lainnya') {
            if (!empty(trim($this->custom_help_type))) {
                $this->reported_help_text = $this->custom_help_type;
            } elseif (empty(trim($this->reported_help_text))) {
                $this->addError('custom_help_type', 'Silakan isi jenis bantuan yang tidak tersedia pada pilihan.');
                return;
            }
        }

        PartnerReport::create([
            'user_id' => $this->reported_user_id, // legacy column (may be null now)
            'reporter_id' => auth()->id(),
            'reported_user_id' => $this->reported_user_id,
            'reported_help_id' => $this->reported_help_id,
            'reported_help_text' => $this->reported_help_text,
            'reported_user_text' => $this->reported_user_text,
            'title' => $this->title,
            'message' => $this->message,
            'report_type' => $this->report_type,
            'category' => 'dari_customer',
            'status' => 'pending',
        ]);

        session()->flash('message', 'Laporan aduan berhasil dikirim. Admin akan meninjau laporan Anda.');
        return redirect()->route('customer.dashboard');
    }

    public function render()
    {
        $helps = auth()->user()->helps()->whereIn('status', ['active', 'completed'])->get();
        $mitras = User::where('role', 'mitra')->get();

        return view('livewire.customer.reports.create', [
            'helps' => $helps,
            'mitras' => $mitras,
        ]);
    }
}
