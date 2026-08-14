<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Help;
use App\Notifications\HelpTakenNotification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a customer user and a mitra
        $customer = User::where('role', 'kustomer')->first();
        $mitra = User::where('role', 'mitra')->first();

        if ($customer && $mitra) {
            // Get the customer's helps
            $helps = Help::where('user_id', $customer->id)
                ->where('status', 'approved')
                ->take(2)
                ->get();

            // Create notifications for each help
            foreach ($helps as $help) {
                $customer->notify(new HelpTakenNotification($help, $mitra));
            }

            $this->command->info('Sample notifications created successfully!');
        } else {
            $this->command->warn('No customer or mitra found. Run UserSeeder first.');
        }
    }
}
