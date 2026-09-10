<?php

namespace App\Services;

use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Log;

class HelpEscrowService
{
    protected PartnerOnlineService $onlineService;

    public function __construct(PartnerOnlineService $onlineService)
    {
        $this->onlineService = $onlineService;
    }

    /**
     * MODEL V2: Lepaskan escrow dari Holding -> Split Payment: Earning (mitra) + Platform Fee (kas).
     *
     * Idempotency guard: jika earning sudah pernah dicatat, tidak akan diduplikasi.
     */
    public function releaseEscrowToMitra(Help $lockedHelp, string $triggeredBy = 'customer_confirm'): void
    {
        if ($lockedHelp->escrow_status === Help::ESCROW_STATUS_RELEASED) {
            Log::info("[HelpEscrowService] Escrow already released for help #{$lockedHelp->id}");
            return;
        }

        $netEarning  = (float) $lockedHelp->getNetEarning();
        $platformFee = (float) $lockedHelp->getPlatformFee();

        // 1. Catat Earning ke Saldo Mitra (Idempotent)
        $totalEarned = (float) BalanceTransaction::where('user_id', $lockedHelp->mitra_id)
            ->where('reference_id', $lockedHelp->id)
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->sum('amount');

        $totalClawedBack = (float) BalanceTransaction::where('user_id', $lockedHelp->mitra_id)
            ->where('reference_id', $lockedHelp->id)
            ->where('type', 'deduction')
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalEarned <= $totalClawedBack) {
            $mitraBalance = UserBalance::firstOrCreate(
                ['user_id' => $lockedHelp->mitra_id],
                ['balance' => 0]
            );

            $idSuffix = $totalClawedBack > 0 ? ":recredit_" . now()->timestamp : "";

            $mitraBalance->receiveEarning(
                $netEarning,
                $lockedHelp->id,
                "Pendapatan Bantuan '{$lockedHelp->title}'",
                $lockedHelp->order_id,
                "help:{$lockedHelp->id}:earning:{$lockedHelp->mitra_id}{$idSuffix}"
            );
        }

        // 2. Catat Platform Fee (Idempotent)
        if ($platformFee > 0) {
            $alreadyFee = BalanceTransaction::where('reference_id', $lockedHelp->id)
                ->where('type', 'platform_fee')
                ->exists();

            if (!$alreadyFee) {
                BalanceTransaction::create([
                    'idempotency_key' => "help:{$lockedHelp->id}:platform_fee",
                    'user_id'         => null,
                    'amount'          => $platformFee,
                    'direction'       => 'credit',
                    'type'            => 'platform_fee',
                    'description'     => "Biaya Layanan Platform {$lockedHelp->getCommissionRateLabel()} dari Bantuan '{$lockedHelp->title}'",
                    'reference_id'    => $lockedHelp->id,
                    'reference_type'  => 'help',
                    'order_id'        => $lockedHelp->order_id,
                    'status'          => 'completed',
                ]);
            }
        }

        // 3. Update Status Order
        $lockedHelp->update([
            'status'            => Help::STATUS_SELESAI,
            'escrow_status'     => Help::ESCROW_STATUS_RELEASED,
            'payment_status'    => Help::PAYMENT_STATUS_PAID,
            'rating_status'     => Help::RATING_STATUS_PENDING,
            'dispatch_mode'     => Help::DISPATCH_MODE_CLOSED,
            'completed_at'      => $lockedHelp->completed_at ?? now(),
            'auto_confirmed_at' => ($triggeredBy === 'auto_confirm') ? now() : null,
        ]);

        // Lepaskan status BUSY mitra
        if ($lockedHelp->mitra_id) {
            $this->onlineService->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
        }

        Log::info('[HelpEscrowService] releaseEscrowToMitra selesai', [
            'help_id'      => $lockedHelp->id,
            'mitra_id'     => $lockedHelp->mitra_id,
            'net_earning'  => $netEarning,
            'platform_fee' => $platformFee,
            'triggered_by' => $triggeredBy,
        ]);
    }

    /**
     * Kredit saldo mitra (wrapper).
     */
    public function creditMitra(Help $help): void
    {
        $this->releaseEscrowToMitra($help, 'manual_credit');
    }

    /**
     * Kembalikan escrow dari Holding ke saldo Customer secara langsung (Full / Parsial).
     */
    public function refundFromEscrowDirect(Help $help, User $customer, float $refundAmount, string $note = 'Pengembalian Dana'): void
    {
        if ($refundAmount <= 0) {
            return;
        }

        $customerBalance = UserBalance::firstOrCreate(
            ['user_id' => $customer->id],
            ['balance' => 0]
        );

        $customerBalance->refundToCustomer(
            $refundAmount,
            $help->id,
            $help->order_id,
            "{$note} (Bantuan #{$help->id} '{$help->title}')",
            "help:{$help->id}:refund_direct:" . uniqid()
        );

        Log::info('[HelpEscrowService] refundFromEscrowDirect executed', [
            'help_id'     => $help->id,
            'customer_id' => $customer->id,
            'amount'      => $refundAmount,
            'note'        => $note,
        ]);
    }

    /**
     * Pencairan kompensasi parsial dari Escrow ke Mitra.
     */
    public function payoutPartialFromEscrowDirect(Help $help, User $mitra, float $payoutAmount, string $note = 'Kompensasi'): void
    {
        if ($payoutAmount <= 0) {
            return;
        }

        $mitraBalance = UserBalance::firstOrCreate(
            ['user_id' => $mitra->id],
            ['balance' => 0]
        );

        $mitraBalance->credit(
            $payoutAmount,
            $help->id,
            $help->order_id,
            "{$note} (Bantuan #{$help->id} '{$help->title}')",
            "help:{$help->id}:payout_direct:" . uniqid()
        );

        Log::info('[HelpEscrowService] payoutPartialFromEscrowDirect executed', [
            'help_id'  => $help->id,
            'mitra_id' => $mitra->id,
            'amount'   => $payoutAmount,
            'note'     => $note,
        ]);
    }

    /**
     * MODEL V2: Kembalikan escrow dari Holding ke saldo Customer (Refund 100%).
     *
     * Dipanggil saat tugas dibatalkan. Platform TIDAK memotong komisi apapun.
     * Dana total dikembalikan utuh ke customer.
     */
    public function refundFromEscrow(Help $help, User $customer): void
    {
        // Idempotency: cek sudah pernah direfund
        $alreadyRefunded = BalanceTransaction::where('user_id', $customer->id)
            ->where('reference_id', $help->id)
            ->where('type', 'refund')
            ->exists();

        if ($alreadyRefunded) {
            Log::info('[HelpEscrowService] Refund sudah dilakukan untuk help ' . $help->id . ', skip.');
            return;
        }

        $customerBalance = UserBalance::firstOrCreate(
            ['user_id' => $customer->id],
            ['balance' => 0]
        );

        $refundAmount = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);

        $customerBalance->refundToCustomer(
            $refundAmount,
            $help->id,
            $help->order_id,
            "Pengembalian Dana 100% (Bantuan '{$help->title}' Dibatalkan)",
            "help:{$help->id}:refund:{$customer->id}"
        );

        Log::info('[HelpEscrowService] Refund escrow (v2) ke customer', [
            'help_id'     => $help->id,
            'customer_id' => $customer->id,
            'amount'      => $refundAmount,
        ]);

        if ($help->mitra_id) {
            $this->onlineService->releaseBusy($help->mitra_id, $help->id);
        }
    }
}
