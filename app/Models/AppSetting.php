<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : (string) $value]);
    }

    /** Default list of banks & e-wallets with BI-FAST and platform account configuration */
    public static function getDefaultWithdrawBanks(): array
    {
        return [
            ['code' => 'BCA', 'name' => 'Bank Central Asia (BCA)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 0, 'is_platform_account' => true, 'is_active' => true],
            ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia (BRI)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'BNI', 'name' => 'Bank Negara Indonesia (BNI)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'MANDIRI', 'name' => 'Bank Mandiri', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 0, 'is_platform_account' => true, 'is_active' => true],
            ['code' => 'BSI', 'name' => 'Bank Syariah Indonesia (BSI)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'CIMB', 'name' => 'CIMB Niaga', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'PERMATA', 'name' => 'Bank Permata', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'DANAMON', 'name' => 'Bank Danamon', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'SEABANK', 'name' => 'SeaBank Indonesia', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'JAGO', 'name' => 'Bank Jago', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'DANA', 'name' => 'DANA', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 0, 'is_platform_account' => true, 'is_active' => true],
            ['code' => 'GOPAY', 'name' => 'GoPay', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'OVO', 'name' => 'OVO', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'LINKAJA', 'name' => 'LinkAja', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'OTHER', 'name' => 'Bank / E-Wallet Lainnya', 'category' => 'Lainnya', 'icon' => '💳', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
        ];
    }

    public static function getWithdrawBanks(): array
    {
        $raw = static::get('withdraw_banks_config');
        if ($raw) {
            $decoded = is_array($raw) ? $raw : json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return static::getDefaultWithdrawBanks();
    }

    public static function getWithdrawMinAmount(): int
    {
        return (int) static::get('withdraw_min_amount', 10000);
    }

    public static function getWithdrawDefaultFee(): int
    {
        return (int) static::get('withdraw_default_other_fee', 2500);
    }

    public static function getWithdrawFeeMode(): string
    {
        return (string) static::get('withdraw_fee_mode', 'deduct_from_received');
    }

    public static function calculateWithdrawFee(string $bankCode, int $amount = 0): array
    {
        $banks = static::getWithdrawBanks();
        $codeUpper = strtoupper(trim($bankCode));

        $matched = collect($banks)->first(function ($b) use ($codeUpper) {
            return strtoupper($b['code'] ?? '') === $codeUpper;
        });

        if ($matched) {
            $fee = (int) ($matched['fee'] ?? 0);
            $isPlatform = !empty($matched['is_platform_account']);
            $bankName = $matched['name'] ?? $bankCode;
            $bankIcon = $matched['icon'] ?? '🏦';
        } else {
            $fee = static::getWithdrawDefaultFee();
            $isPlatform = false;
            $bankName = $bankCode;
            $bankIcon = '💳';
        }

        $netAmount = max(0, $amount - $fee);

        return [
            'fee' => $fee,
            'is_platform_account' => $isPlatform,
            'bank_name' => $bankName,
            'bank_icon' => $bankIcon,
            'net_amount' => $netAmount,
            'fee_mode' => static::getWithdrawFeeMode(),
        ];
    }
}
