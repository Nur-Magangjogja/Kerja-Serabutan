<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\User;
use App\Notifications\HelpTakenNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        $mitra    = User::where('email', 'mitra@sayabantu.com')->first();

        if ($customer && $mitra) {
            $help = Help::where('user_id', $customer->id)
                ->whereIn('status', [Help::STATUS_TAKEN, Help::STATUS_SELESAI])
                ->first();

            if ($help) {
                try {
                    $customer->notify(new HelpTakenNotification($help, $mitra));
                } catch (\Throwable $e) {
                    // Ignore if mail driver / channel not configured
                }
            }

            $this->command->info('NotificationSeeder berhasil membuat notifikasi bantuan diambil.');
        }
    }
}
