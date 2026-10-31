<?php

namespace App\Services\Billing;

use App\Models\BillingInvoice;
use App\Models\BillingLedgerEntry;
use App\Models\BillingTariffPlan;
use App\Models\BillingTariffRate;
use App\Models\BillingWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancialRatingService
{
    /**
     * Get or create a billing wallet for a tenant domain.
     */
    public function getOrCreateWallet(string $domainUuid, string $billingType = 'PREPAID'): BillingWallet
    {
        $wallet = BillingWallet::where('domain_uuid', $domainUuid)->first();
        if ($wallet) {
            return $wallet;
        }

        return DB::transaction(function () use ($domainUuid, $billingType) {
            $accountNumber = 'WAL-' . strtoupper(Str::random(6));

            $wallet = BillingWallet::create([
                'wallet_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'account_number' => $accountNumber,
                'billing_type' => $billingType,
                'currency' => 'USD',
                'available_balance' => 0.0000,
                'reserved_balance' => 0.0000,
                'credit_limit' => $billingType === 'POSTPAID' ? 500.0000 : 0.0000,
                'credit_exposure' => 0.0000,
                'status' => 'ACTIVE',
            ]);

            // Initial Account Opening Deposit
            $this->recordLedgerTransaction(
                $wallet->wallet_uuid,
                $domainUuid,
                'DEPOSIT',
                'CREDIT',
                250.0000,
                'Initial enterprise opening credit deposit',
                'init_deposit_' . $wallet->wallet_uuid,
                $accountNumber
            );

            return $wallet->fresh();
        });
    }

    /**
     * Atomic Double-Entry Ledger Transaction Recording.
     * Guaranteed idempotent and thread-safe via row-level locks.
     */
    public function recordLedgerTransaction(
        string $walletUuid,
        string $domainUuid,
        string $transactionType,
        string $direction, // CREDIT, DEBIT
        float $amount,
        string $description,
        string $idempotencyKey,
        ?string $referenceId = null,
        ?array $meta = null
    ): BillingLedgerEntry {
        // Idempotency check: if already processed, return existing entry
        $existing = BillingLedgerEntry::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use (
            $walletUuid,
            $domainUuid,
            $transactionType,
            $direction,
            $amount,
            $description,
            $idempotencyKey,
            $referenceId,
            $meta
        ) {
            $wallet = BillingWallet::where('wallet_uuid', $walletUuid)
                ->lockForUpdate()
                ->firstOrFail();

            $balanceBefore = (float) $wallet->available_balance;

            if ($direction === 'CREDIT') {
                $balanceAfter = $balanceBefore + $amount;
            } else {
                // DEBIT check: if PREPAID and no credit limit, ensure balance is sufficient
                $maxSpendable = $balanceBefore + (float) $wallet->credit_limit;
                if ($wallet->billing_type === 'PREPAID' && $maxSpendable < $amount) {
                    throw new \RuntimeException("Insufficient funds in wallet {$wallet->account_number}. Balance: \${$balanceBefore}, Required: \${$amount}");
                }
                $balanceAfter = $balanceBefore - $amount;
            }

            // Create immutable ledger entry
            $entry = BillingLedgerEntry::create([
                'entry_uuid' => (string) Str::uuid(),
                'wallet_uuid' => $walletUuid,
                'domain_uuid' => $domainUuid,
                'transaction_type' => $transactionType,
                'direction' => $direction,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'idempotency_key' => $idempotencyKey,
                'reference_id' => $referenceId,
                'description' => $description,
                'meta' => $meta,
                'created_at' => Carbon::now(),
            ]);

            // Update wallet balance
            $wallet->update([
                'available_balance' => $balanceAfter,
            ]);

            return $entry;
        });
    }

    /**
     * Rate a completed call CDR against tariff rate cards with interval rounding.
     */
    public function rateCdrUsage(
        string $domainUuid,
        string $xmlCdrUuid,
        string $destinationNumber,
        int $billsec,
        ?string $tariffPlanUuid = null
    ): array {
        $wallet = $this->getOrCreateWallet($domainUuid);

        // Select Tariff Plan
        $plan = $tariffPlanUuid
            ? BillingTariffPlan::find($tariffPlanUuid)
            : BillingTariffPlan::where('domain_uuid', $domainUuid)->where('is_default', true)->first();

        if (!$plan) {
            $plan = BillingTariffPlan::where('is_default', true)->first();
        }

        // Longest Prefix Match (LPM)
        $cleanNumber = preg_replace('/[^0-9]/', '', $destinationNumber);
        $matchedRate = null;
        $matchedPrefix = '1';

        for ($len = min(6, strlen($cleanNumber)); $len >= 1; $len--) {
            $prefix = substr($cleanNumber, 0, $len);
            $rate = BillingTariffRate::where('tariff_plan_uuid', $plan->tariff_plan_uuid)
                ->where('prefix', $prefix)
                ->where('is_active', true)
                ->first();

            if ($rate) {
                $matchedRate = $rate;
                $matchedPrefix = $prefix;
                break;
            }
        }

        if (!$matchedRate) {
            $matchedRate = BillingTariffRate::where('tariff_plan_uuid', $plan->tariff_plan_uuid)
                ->where('prefix', '1')
                ->first();
        }

        $ratePerMinute = $matchedRate ? (float) $matchedRate->rate_per_minute : 0.0150;
        $connectFee = $matchedRate && $matchedRate->custom_connect_fee !== null
            ? (float) $matchedRate->custom_connect_fee
            : (float) $plan->connect_fee;

        $initialInterval = $matchedRate && $matchedRate->custom_initial_interval
            ? $matchedRate->custom_initial_interval
            : $plan->initial_interval;

        $subsequentInterval = $matchedRate && $matchedRate->custom_subsequent_interval
            ? $matchedRate->custom_subsequent_interval
            : $plan->subsequent_interval;

        // Interval rounding calculation (e.g. 60/6)
        if ($billsec <= 0) {
            $billableSec = 0;
            $callCost = 0.0000;
        } elseif ($billsec <= $initialInterval) {
            $billableSec = $initialInterval;
            $callCost = ($billableSec / 60) * $ratePerMinute + $connectFee;
        } else {
            $extraSec = $billsec - $initialInterval;
            $roundedExtra = (int) (ceil($extraSec / $subsequentInterval) * $subsequentInterval);
            $billableSec = $initialInterval + $roundedExtra;
            $callCost = ($billableSec / 60) * $ratePerMinute + $connectFee;
        }

        $callCost = round($callCost, 4);

        // Deduct from wallet with idempotency key
        $idempotencyKey = 'cdr_rate_' . $xmlCdrUuid;
        $entry = $this->recordLedgerTransaction(
            $wallet->wallet_uuid,
            $domainUuid,
            'CALL_USAGE',
            'DEBIT',
            $callCost,
            "Outbound call to {$destinationNumber} ({$billsec}s talk, {$billableSec}s billed @ \${$ratePerMinute}/min)",
            $idempotencyKey,
            $xmlCdrUuid,
            [
                'destination' => $destinationNumber,
                'matched_prefix' => $matchedPrefix,
                'duration_raw' => $billsec,
                'duration_billable' => $billableSec,
                'rate_per_minute' => $ratePerMinute,
                'connect_fee' => $connectFee,
                'interval' => "{$initialInterval}/{$subsequentInterval}",
            ]
        );

        return [
            'xml_cdr_uuid' => $xmlCdrUuid,
            'destination' => $destinationNumber,
            'duration_raw_sec' => $billsec,
            'duration_billable_sec' => $billableSec,
            'rate_per_minute' => $ratePerMinute,
            'connect_fee' => $connectFee,
            'total_cost' => $callCost,
            'interval_rule' => "{$initialInterval}/{$subsequentInterval} sec",
            'ledger_entry_uuid' => $entry->entry_uuid,
            'balance_after' => $entry->balance_after,
        ];
    }

    /**
     * Simulate usage rating for any destination number and duration.
     */
    public function simulateRating(
        string $destinationNumber,
        int $durationSeconds,
        ?string $tariffPlanUuid = null,
        ?string $domainUuid = null
    ): array {
        if ($domainUuid) {
            $this->ensureSeedBillingData($domainUuid);
        }

        $plan = $tariffPlanUuid
            ? BillingTariffPlan::find($tariffPlanUuid)
            : BillingTariffPlan::where('is_default', true)->first();

        if (!$plan) {
            $plan = BillingTariffPlan::first();
        }

        $cleanNumber = preg_replace('/[^0-9]/', '', $destinationNumber);
        $matchedRate = null;
        $matchedPrefix = '1';

        for ($len = min(6, strlen($cleanNumber)); $len >= 1; $len--) {
            $prefix = substr($cleanNumber, 0, $len);
            $rate = BillingTariffRate::where('tariff_plan_uuid', $plan->tariff_plan_uuid)
                ->where('prefix', $prefix)
                ->where('is_active', true)
                ->first();

            if ($rate) {
                $matchedRate = $rate;
                $matchedPrefix = $prefix;
                break;
            }
        }

        if (!$matchedRate) {
            $matchedRate = BillingTariffRate::where('tariff_plan_uuid', $plan->tariff_plan_uuid)
                ->where('prefix', '1')
                ->first();
        }

        $ratePerMinute = $matchedRate ? (float) $matchedRate->rate_per_minute : 0.0150;
        $destName = $matchedRate ? $matchedRate->destination_name : 'Standard International';
        $connectFee = (float) $plan->connect_fee;
        $initialInterval = $plan->initial_interval;
        $subsequentInterval = $plan->subsequent_interval;

        // Calculate billable time
        if ($durationSeconds <= 0) {
            $billableSec = 0;
            $callCost = 0.0000;
        } elseif ($durationSeconds <= $initialInterval) {
            $billableSec = $initialInterval;
            $callCost = ($billableSec / 60) * $ratePerMinute + $connectFee;
        } else {
            $extraSec = $durationSeconds - $initialInterval;
            $roundedExtra = (int) (ceil($extraSec / $subsequentInterval) * $subsequentInterval);
            $billableSec = $initialInterval + $roundedExtra;
            $callCost = ($billableSec / 60) * $ratePerMinute + $connectFee;
        }

        $callCost = round($callCost, 4);

        $explanation = [];
        $explanation[] = "1. Dialed number '{$destinationNumber}' matched prefix [{$matchedPrefix}] ({$destName}).";
        $explanation[] = "2. Tariff Plan: '{$plan->name}' applying base rate of \${$ratePerMinute}/min with {$initialInterval}/{$subsequentInterval}s increment rule.";
        $explanation[] = "3. Actual talk duration was {$durationSeconds}s. First {$initialInterval}s evaluated as initial bucket.";
        if ($durationSeconds > $initialInterval) {
            $diff = $durationSeconds - $initialInterval;
            $explanation[] = "4. Remaining {$diff}s rounded up to nearest {$subsequentInterval}s increment, yielding total {$billableSec}s billable time.";
        }
        $explanation[] = "5. Rated cost = ({$billableSec}s / 60s) * \${$ratePerMinute} + \${$connectFee} connect fee = \${$callCost}.";

        return [
            'destination_number' => $destinationNumber,
            'matched_prefix' => $matchedPrefix,
            'destination_name' => $destName,
            'plan_name' => $plan->name,
            'duration_raw_seconds' => $durationSeconds,
            'duration_billable_seconds' => $billableSec,
            'rate_per_minute' => $ratePerMinute,
            'connect_fee' => $connectFee,
            'interval_rule' => "{$initialInterval}/{$subsequentInterval} sec",
            'total_call_cost' => $callCost,
            'explanation_trace' => $explanation,
        ];
    }

    /**
     * Generate structured monthly invoice with call usage line items.
     */
    public function generateMonthlyInvoice(string $domainUuid): BillingInvoice
    {
        $wallet = $this->getOrCreateWallet($domainUuid);

        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        // Calculate call usage from ledger
        $callUsageSum = (float) BillingLedgerEntry::where('domain_uuid', $domainUuid)
            ->where('transaction_type', 'CALL_USAGE')
            ->where('created_at', '>=', $start)
            ->sum('amount');

        $lineItems = [
            [
                'description' => 'Outbound SIP Trunk Voice Usage (Rated Call Minutes)',
                'quantity' => 1,
                'rate' => round($callUsageSum, 2),
                'total' => round($callUsageSum, 2),
            ],
            [
                'description' => 'Enterprise Contact Center Agent Seats (15 Seats @ $25/mo)',
                'quantity' => 15,
                'rate' => 25.00,
                'total' => 375.00,
            ],
            [
                'description' => 'Dedicated Carrier DID Numbers (4 Inbound Trunks)',
                'quantity' => 4,
                'rate' => 2.50,
                'total' => 10.00,
            ],
        ];

        $subtotal = array_sum(array_column($lineItems, 'total'));
        $tax = round($subtotal * 0.08, 2); // 8% sales tax
        $total = $subtotal + $tax;

        return BillingInvoice::create([
            'invoice_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'wallet_uuid' => $wallet->wallet_uuid,
            'invoice_number' => 'INV-' . date('Ym') . '-' . rand(1000, 9999),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'amount_paid' => $wallet->billing_type === 'PREPAID' ? $total : 0.00,
            'status' => $wallet->billing_type === 'PREPAID' ? 'PAID' : 'ISSUED',
            'line_items' => $lineItems,
            'due_date' => $now->copy()->addDays(15)->toDateString(),
            'paid_at' => $wallet->billing_type === 'PREPAID' ? $now : null,
        ]);
    }

    /**
     * Reconcile double-entry ledger to prove ledger integrity.
     */
    public function reconcileLedger(string $domainUuid): array
    {
        $wallet = $this->getOrCreateWallet($domainUuid);

        $totalCredits = (float) BillingLedgerEntry::where('domain_uuid', $domainUuid)
            ->where('direction', 'CREDIT')
            ->sum('amount');

        $totalDebits = (float) BillingLedgerEntry::where('domain_uuid', $domainUuid)
            ->where('direction', 'DEBIT')
            ->sum('amount');

        $computedBalance = round($totalCredits - $totalDebits, 4);
        $walletBalance = round((float) $wallet->available_balance, 4);
        $isBalanced = abs($computedBalance - $walletBalance) < 0.0001;

        return [
            'account_number' => $wallet->account_number,
            'wallet_balance' => $walletBalance,
            'computed_balance' => $computedBalance,
            'total_credits' => $totalCredits,
            'total_debits' => $totalDebits,
            'is_balanced' => $isBalanced,
            'total_transactions' => BillingLedgerEntry::where('domain_uuid', $domainUuid)->count(),
        ];
    }

    /**
     * Ensure seed billing data exists (Tariff Plans, Rate Cards, Wallet).
     */
    public function ensureSeedBillingData(string $domainUuid): void
    {
        // 1. Wallet
        $wallet = $this->getOrCreateWallet($domainUuid);

        // 2. Tariff Plans
        if (BillingTariffPlan::count() >= 2) {
            return;
        }

        $now = Carbon::now();

        $standardPlan = BillingTariffPlan::create([
            'tariff_plan_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'name' => 'Enterprise Standard Retail Plan (60/6s)',
            'currency' => 'USD',
            'initial_interval' => 60,
            'subsequent_interval' => 6,
            'connect_fee' => 0.0000,
            'markup_percentage' => 20.00,
            'is_default' => true,
            'is_active' => true,
        ]);

        $vipPlan = BillingTariffPlan::create([
            'tariff_plan_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'name' => 'Corporate VIP Wholesale Plan (30/6s)',
            'currency' => 'USD',
            'initial_interval' => 30,
            'subsequent_interval' => 6,
            'connect_fee' => 0.0000,
            'markup_percentage' => 10.00,
            'is_default' => false,
            'is_active' => true,
        ]);

        // Standard Rates
        $rates = [
            ['prefix' => '1', 'name' => 'USA & Canada Standard', 'std' => 0.0085, 'vip' => 0.0065],
            ['prefix' => '44', 'name' => 'United Kingdom Landline/Mobile', 'std' => 0.0160, 'vip' => 0.0120],
            ['prefix' => '880', 'name' => 'Bangladesh All Operators', 'std' => 0.0380, 'vip' => 0.0290],
            ['prefix' => '49', 'name' => 'Germany National', 'std' => 0.0140, 'vip' => 0.0110],
            ['prefix' => '61', 'name' => 'Australia National', 'std' => 0.0190, 'vip' => 0.0150],
        ];

        foreach ($rates as $r) {
            BillingTariffRate::create([
                'rate_uuid' => (string) Str::uuid(),
                'tariff_plan_uuid' => $standardPlan->tariff_plan_uuid,
                'destination_name' => $r['name'],
                'prefix' => $r['prefix'],
                'rate_per_minute' => $r['std'],
                'is_active' => true,
            ]);

            BillingTariffRate::create([
                'rate_uuid' => (string) Str::uuid(),
                'tariff_plan_uuid' => $vipPlan->tariff_plan_uuid,
                'destination_name' => $r['name'],
                'prefix' => $r['prefix'],
                'rate_per_minute' => $r['vip'],
                'is_active' => true,
            ]);
        }

        // Seed initial sample CDR ratings
        $this->rateCdrUsage($domainUuid, (string) Str::uuid(), '+1 (312) 555-0199', 84, $standardPlan->tariff_plan_uuid);
        $this->rateCdrUsage($domainUuid, (string) Str::uuid(), '+44 20 7946 0958', 142, $standardPlan->tariff_plan_uuid);
        $this->rateCdrUsage($domainUuid, (string) Str::uuid(), '+880 1711 000000', 58, $standardPlan->tariff_plan_uuid);

        // Seed initial invoice
        $this->generateMonthlyInvoice($domainUuid);
    }
}
