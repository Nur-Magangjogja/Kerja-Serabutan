<?php

namespace App\Livewire\Mitra\GPS;

use App\Models\Help;
use App\Services\LocationTrackingService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Tracker extends Component
{
    public $helpId;
    public $isTracking = false;
    public $currentStatus;
    public $distanceInfo;
    public $lastUpdate;

    protected $locationService;

    public function boot(LocationTrackingService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function mount($helpId)
    {
        $this->helpId = $helpId;
        $this->checkTrackingStatus();
    }

    public function checkTrackingStatus()
    {
        $help = Help::find($this->helpId);
        
        if (!$help) {
            $this->isTracking = false;
            return;
        }

        // Tracking aktif jika status masih dalam proses
        $this->isTracking = in_array($help->status, ['memperoleh_mitra', 'taken', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'sedang_diproses']);
        $this->currentStatus = $help->status;
        
        Log::info('GPS Tracker Status Check', [
            'help_id' => $this->helpId,
            'status' => $help->status,
            'is_tracking' => $this->isTracking
        ]);
    }

    public function updateLocation($latitude, $longitude)
    {
        try {
            $help = Help::find($this->helpId);
            
            if (!$help) {
                Log::error("Help not found for GPS update", ['help_id' => $this->helpId]);
                return [
                    'success' => false,
                    'message' => 'Bantuan tidak ditemukan'
                ];
            }

            // Update lokasi menggunakan service
            $result = $this->locationService->updatePartnerLocation($help, $latitude, $longitude);

            // Update UI info
            $this->currentStatus = $result['status'];
            $this->distanceInfo = [
                'from_initial' => $result['distance_from_initial'],
                'to_customer' => $result['distance_to_customer']
            ];
            $this->lastUpdate = now()->format('H:i:s');

            // Jika status berubah, dispatch event
            if ($result['status_changed']) {
                $this->dispatch('status-changed', [
                    'helpId' => $this->helpId,
                    'oldStatus' => $result['old_status'],
                    'newStatus' => $result['status']
                ]);
            }

            // Refresh tracking status
            $this->checkTrackingStatus();

            return $result;

        } catch (\Exception $e) {
            Log::error("GPS tracking error", [
                'help_id' => $this->helpId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat update lokasi'
            ];
        }
    }

    public function stopTracking()
    {
        $this->isTracking = false;
        $this->dispatch('tracking-stopped');
    }

    public function render()
    {
        return view('livewire.mitra.gps.tracker');
    }
}

