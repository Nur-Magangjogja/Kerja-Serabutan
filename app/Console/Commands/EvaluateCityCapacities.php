<?php

namespace App\Console\Commands;

use App\Services\SupplyDemandService;
use Illuminate\Console\Command;

class EvaluateCityCapacities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city:evaluate-capacities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluasi metrik supply-demand dan status kapasitas pendaftaran seluruh kota aktif';

    /**
     * Execute the console command.
     */
    public function handle(SupplyDemandService $service): int
    {
        $this->info('Memulai evaluasi kapasitas dan supply-demand seluruh kota...');

        $evaluated = $service->evaluateAllCities();

        $this->info("Berhasil mengevaluasi kapasitas {$evaluated} kota.");
        return Command::SUCCESS;
    }
}
