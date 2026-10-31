<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingInvoice extends Model
{
    use HasFactory;

    protected $table = 'billing_invoices';
    protected $primaryKey = 'invoice_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'invoice_uuid',
        'domain_uuid',
        'wallet_uuid',
        'invoice_number',
        'period_start',
        'period_end',
        'subtotal',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'status',
        'line_items',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
        'amount_paid' => 'float',
        'line_items' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(BillingWallet::class, 'wallet_uuid', 'wallet_uuid');
    }
}
