<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingLedgerEntry extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'billing_ledger_entries';
    protected $primaryKey = 'entry_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'entry_uuid',
        'wallet_uuid',
        'domain_uuid',
        'transaction_type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'idempotency_key',
        'reference_id',
        'description',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(BillingWallet::class, 'wallet_uuid', 'wallet_uuid');
    }
}
