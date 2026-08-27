<?php

namespace App\Livewire\Customer\Reports;

use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public $title = '';
    public $message = '';
    public $report_type = '';
    public $reported_user_id = null;
    public $reported_help_id = null;
    public $help_id = null; // For selecting help from dropdown
    public $evidence_photo = null;
    public $is_refund_request = false;

    // free-text fields
    public $reported_help_text = null;
    public $reported_user_text = null;
    public $selected_help_type = '';
    public $custom_help_type = '';

    public $helpTypes = [
        ''          => 'Pilih Jenis Bantuan (Opsional)',
        'pangan'    => 'Pangan / Bahan Pokok',
        'obat'      => 'Obat',
        'perbaikan' => 'Perbaikan Rumah',
        'uang'      => 'Bantuan Uang',
        'lainnya'   => 'Lainnya',
    ];

    public $reportTypes = [
        'klaim_refund_pekerjaan_fiktif' => '🛡️ Klaim Refund (Tugas Belum Dikerjakan / Fiktif - Garansi 1x24 Jam)',
        'mitra_tidak_selesai'           => '⚠️ Rekan Jasa Belum Menyelesaikan Tugas',
        'mitra_berperilaku_buruk'       => 'Mitra Berperilaku Buruk / Tidak Sopan',
        'bantuan_fiktif'                => 'Bantuan Fiktif',
        'penipuan'                      => 'Penipuan / Manipulasi',
        'pelanggaran_aturan'            => 'Pelanggaran Aturan & Standar Layanan',
        'pelayanan_tidak_sesuai'        => 'Pelayanan Tidak Sesuai Deskripsi',
        'konten_tidak_pantas'           => 'Konten / Foto Tidak Pantas',
        'lainnya'                       => 'Lainnya',
    ];

    protected function rules()
    {
        return [
            'title'              => 'required|string|max:255',
            'message'            => 'required|string|min:10|max:3000',
            'report_type'        => 'required|string',
            'reported_user_id'   => 'nullable|exists:users,id',
            'reported_help_id'   => 'nullable|exists:helps,id',
            'reported_help_text' => 'nullable|string|max:255',
            'reported_user_text' => 'nullable|string|max:255',
            'selected_help_type' => 'nullable|string',
            'custom_help_type'   => 'nullable|string|max:255',
            'evidence_photo'     => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    protected $messages = [
        'title.required'       => 'Judul laporan harus diisi.',
        'message.required'     => 'Detail penjelasan masalah wajib diisi.',
        'message.min'          => 'Detail pesan minimal 10 karakter.',
        'report_type.required' => 'Jenis laporan harus dipilih.',
        'evidence_photo.image' => 'Bukti foto harus berupa format gambar (JPG, JPEG, PNG).',
        'evidence_photo.max'   => 'Ukuran foto bukti maksimal 5MB.',
    ];

    public function mount($user_id = null, $help_id = null)
    {
        $targetUserId = request()->query('user_id', request()->route('user_id', $user_id));
        $targetHelpId = request()->query('help_id', request()->route('help_id', $help_id));
        $targetType   = request()->query('type', '');

        if ($targetUserId) {
            $this->reported_user_id = $targetUserId;
        }

        if ($targetHelpId) {
            $this->reported_help_id = $targetHelpId;
            $this->help_id          = $targetHelpId;
            $this->loadHelpDetails();
        }

        if ($targetType && array_key_exists($targetType, $this->reportTypes)) {
            $this->report_type = $targetType;
            if (in_array($targetType, ['klaim_refund_pekerjaan_fiktif', 'mitra_tidak_selesai'])) {
                $this->is_refund_request = true;
                $this->title = 'Klaim Pengembalian Dana (Refund) - Bantuan #' . ($this->help_id ?? '');
            }
        }
    }

    public function updatedHelpId($value)
    {
        $this->reported_help_id = $value;
        $this->loadHelpDetails();
    }

    public function updatedReportType($value)
    {
        if (in_array($value, ['klaim_refund_pekerjaan_fiktif', 'mitra_tidak_selesai'])) {
            $this->is_refund_request = true;
            if (empty($this->title) || str_starts_with($this->title, 'Klaim Pengembalian Dana')) {
                $this->title = 'Klaim Pengembalian Dana (Refund) - Bantuan #' . ($this->help_id ?? '');
            }
        }
    }

    private function loadHelpDetails(): void
    {
        if (!$this->help_id) return;

        $help = Help::with('mitra')->find($this->help_id);
        if ($help) {
            if ($help->mitra_id && !$this->reported_user_id) {
                $this->reported_user_id = $help->mitra_id;
            }
            if (!$this->reported_help_text) {
                $this->reported_help_text = $help->title;
            }
            if ($help->mitra && !$this->reported_user_text) {
                $this->reported_user_text = $help->mitra->name;
            }
        }
    }

    public function submit()
    {
        $this->validate();

        $help = null;
        if ($this->help_id) {
            $help = Help::find($this->help_id);
            if ($help) {
                $this->reported_help_id = $help->id;
                if ($help->mitra_id && !$this->reported_user_id) {
                    $this->reported_user_id = $help->mitra_id;
                }
                if (!$this->reported_help_text) {
                    $this->reported_help_text = $help->title;
                }
            }
        }

        // Simpan foto bukti jika ada
        $evidencePath = null;
        if ($this->evidence_photo) {
            $evidencePath = $this->evidence_photo->store('reports/evidence', 'public');
        }

        // Tentukan apakah laporan ini merupakan pengajuan refund
        $isRefund = $this->is_refund_request || in_array($this->report_type, ['klaim_refund_pekerjaan_fiktif', 'mitra_tidak_selesai']);
        $refundAmount = 0;
        if ($isRefund && $help) {
            $refundAmount = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);
        }

        $report = PartnerReport::create([
            'reporter_id'        => auth()->id(),
            'reported_user_id'   => $this->reported_user_id,
            'reported_help_id'   => $this->reported_help_id,
            'reported_help_text' => $this->reported_help_text,
            'reported_user_text' => $this->reported_user_text,
            'title'              => $this->title,
            'message'            => $this->message,
            'evidence_photo'     => $evidencePath,
            'report_type'        => $this->report_type,
            'category'           => 'dari_customer',
            'status'             => 'pending',
            'refund_status'      => $isRefund ? 'requested' : 'none',
            'refund_amount'      => $refundAmount,
        ]);

        session()->flash('message', 'Laporan aduan dan klaim Anda berhasil dikirim! Tim manajemen admin akan segera meninjau transaksi dan bukti laporan.');
        if ($this->help_id) {
            return redirect()->route('customer.helps.detail', ['id' => $this->help_id]);
        }
        return redirect()->route('customer.helps.history');
    }

    public function render()
    {
        $helps = auth()->user()->helps()
            ->whereIn('status', ['active', 'completed', 'selesai', 'in_progress', 'taken', 'sedang_diproses'])
            ->with('mitra')
            ->select('id', 'title', 'status', 'mitra_id', 'amount', 'total_amount', 'completed_at', 'created_at')
            ->latest()
            ->limit(50)
            ->get();

        $selectedHelp = $this->help_id ? Help::with('mitra')->find($this->help_id) : null;
        $mitras = User::where('role', 'mitra')->select('id', 'name', 'email')->limit(100)->get();

        return view('livewire.customer.reports.create', [
            'helps'        => $helps,
            'selectedHelp' => $selectedHelp,
            'mitras'       => $mitras,
        ]);
    }
}
