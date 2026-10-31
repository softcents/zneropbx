<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingWallet extends Model
{
    use HasFactory;

    protected $table = 'billing_wallets';
    protected $primaryKey = 'wallet_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'wallet_uuid',
        'domain_uuid',
        'account_number',
        'billing_type',
        'currency',
        'available_balance',
        'reserved_balance',
        'credit_limit',
        'credit_exposure',
        'auto_recharge_enabled',
        'auto_recharge_threshold',
        'auto_recharge_amount',
        'status',
    ];

    protected $casts = [
        'available_balance' => 'float',
        'reserved_balance' => 'float',
        'credit_limit' => 'float',
        'credit_exposure' => 'float',
        'auto_recharge_enabled' => 'boolean',
        'auto_recharge_threshold' => 'float',
        'auto_recharge_amount' => 'float',
    ];

    public function ledgerEntries()
    {
        return $this->hasMany(BillingLedgerEntry::class, 'wallet_uuid', 'wallet_uuid')->orderBy('created_at', 'desc');
    }

    public function invoices()
    {
        return $this->hasMany(BillingInvoice::class, 'wallet_uuid', 'wallet_uuid')->orderBy('created_at', 'desc');
    }
}
