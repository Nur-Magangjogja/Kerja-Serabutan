<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;

class UserBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Process users in chunks to avoid memory issues
        User::query()->chunkById(200, function ($users) {
            foreach ($users as $user) {
                // Calculate completed topups and deductions
                // Normalize status checks: trim and lowercase to tolerate variations
                $topups = BalanceTransaction::where('user_id', $user->id)
                    ->where('type', 'topup')
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $deductions = BalanceTransaction::where('user_id', $user->id)
                    ->where('type', 'deduction')
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $balance = $topups - $deductions;

                // Create or update user balance
                UserBalance::updateOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $balance]
                );
            }
        });
    }
}