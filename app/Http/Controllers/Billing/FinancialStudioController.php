<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingInvoice;
use App\Models\BillingLedgerEntry;
use App\Models\BillingTariffPlan;
use App\Services\Billing\FinancialRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FinancialStudioController extends Controller
{
    protected FinancialRatingService $ratingService;

    public function __construct(FinancialRatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Render the Financial Rating & Ledger Studio.
     */
    public function index(Request $request): Response
    {
        $domainUuid = session('domain_uuid');
        $this->ratingService->ensureSeedBillingData($domainUuid);

        $wallet = $this->ratingService->getOrCreateWallet($domainUuid);
        $ledgerEntries = BillingLedgerEntry::where('domain_uuid', $domainUuid)
            ->orderBy('created_at', 'desc')
            ->limit(40)
            ->get();

        $tariffPlans = BillingTariffPlan::where(function ($q) use ($domainUuid) {
                $q->where('domain_uuid', $domainUuid)->orWhereNull('domain_uuid');
            })
            ->with(['rates' => function ($q) {
                $q->orderBy('prefix', 'asc');
            }])
            ->where('is_active', true)
            ->get();

        $invoices = BillingInvoice::where('domain_uuid', $domainUuid)
            ->orderBy('created_at', 'desc')
            ->get();

        $reconciliation = $this->ratingService->reconcileLedger($domainUuid);

        $totalDeposits = (float) BillingLedgerEntry::where('domain_uuid', $domainUuid)
            ->where('transaction_type', 'DEPOSIT')
            ->sum('amount');

        $totalUsage = (float) BillingLedgerEntry::where('domain_uuid', $domainUuid)
            ->where('transaction_type', 'CALL_USAGE')
            ->sum('amount');

        return Inertia::render('Billing/FinancialStudio', [
            'wallet' => $wallet,
            'ledgerEntries' => $ledgerEntries,
            'tariffPlans' => $tariffPlans,
            'invoices' => $invoices,
            'reconciliation' => $reconciliation,
            'stats' => [
                'available_balance' => round($wallet->available_balance, 2),
                'credit_limit' => round($wallet->credit_limit, 2),
                'total_deposits' => round($totalDeposits, 2),
                'total_usage' => round($totalUsage, 2),
                'invoices_count' => $invoices->count(),
            ],
        ]);
    }

    /**
     * Simulate usage rating for a destination and duration.
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destination_number' => 'required|string',
            'duration_seconds' => 'required|integer|min:1',
            'tariff_plan_uuid' => 'nullable|uuid',
        ]);

        $domainUuid = session('domain_uuid');
        $result = $this->ratingService->simulateRating(
            $validated['destination_number'],
            $validated['duration_seconds'],
            $validated['tariff_plan_uuid'] ?? null,
            $domainUuid
        );

        return response()->json([
            'success' => true,
            'simulation' => $result,
        ]);
    }

    /**
     * Add funds / Deposit to wallet via double-entry transaction.
     */
    public function deposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:5|max:10000',
            'payment_reference' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid');
        $wallet = $this->ratingService->getOrCreateWallet($domainUuid);

        $idempotencyKey = 'manual_deposit_' . (string) Str::uuid();

        $entry = $this->ratingService->recordLedgerTransaction(
            $wallet->wallet_uuid,
            $domainUuid,
            'DEPOSIT',
            'CREDIT',
            (float) $validated['amount'],
            "Manual credit top-up via portal (" . ($validated['payment_reference'] ?? 'Online Payment') . ")",
            $idempotencyKey,
            $validated['payment_reference'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully deposited \${$validated['amount']} to wallet {$wallet->account_number}.",
            'balance' => $entry->balance_after,
            'entry' => $entry,
        ]);
    }

    /**
     * Generate an ad-hoc or monthly invoice.
     */
    public function generateInvoice(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $invoice = $this->ratingService->generateMonthlyInvoice($domainUuid);

        return response()->json([
            'success' => true,
            'message' => "Invoice {$invoice->invoice_number} generated successfully for total \${$invoice->total_amount}.",
            'invoice' => $invoice,
        ]);
    }
}
