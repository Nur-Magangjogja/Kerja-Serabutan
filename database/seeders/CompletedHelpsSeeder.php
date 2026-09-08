<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Help;
use App\Models\User;

class CompletedHelpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get customer user
        $customer = User::where('email', 'customer@sayabantu.com')->first() 
            ?? User::where('role', 'customer')->first();
        
        if (!$customer) {
            $this->command->error('No customer user found. Run UserSeeder first.');
            return;
        }

        // Get a mitra user
        $mitra = User::where('email', 'mitra@sayabantu.com')->first()
            ?? User::where('role', 'mitra')->first();
        
        if (!$mitra) {
            $this->command->error('No mitra user found. Run UserSeeder first.');
            return;
        }

        // Update some existing helps to completed status
        $helps = Help::where('user_id', $customer->id)
            ->where('status', '!=', 'selesai')
            ->take(3)
            ->get();

        if ($helps->count() === 0) {
            $this->command->warn('No helps found for customer. Run HelpsSeeder first.');
            return;
        }

        foreach ($helps as $help) {
            $completedDate = now()->subDays(rand(1, 7));
            $help->update([
                'status'         => Help::STATUS_SELESAI,
                'escrow_status'  => Help::ESCROW_STATUS_RELEASED,
                'payment_status' => Help::PAYMENT_STATUS_PAID,
                'rating_status'  => Help::RATING_STATUS_RATED,
                'mitra_id'       => $mitra->id,
                'completed_at'   => $completedDate,
                'updated_at'     => $completedDate,
            ]);
        }

        $this->command->info("Successfully marked {$helps->count()} helps as completed for customer {$customer->name}");
    }
}
