<?php

namespace App\Livewire\Mitra\Withdraw;

use App\Models\WithdrawRequest;
use Livewire\Component;
use Livewire\WithPagination;

class WithdrawHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $withdraws = WithdrawRequest::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('livewire.mitra.withdraw.withdraw-history', [
            'withdraws' => $withdraws,
        ])->layout('layouts.app');
    }
}
