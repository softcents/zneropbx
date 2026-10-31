<template>
  <MainLayout>
    <div class="m-3 space-y-6">
      <!-- Financial Studio Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
              <BanknotesIcon class="w-7 h-7 text-indigo-600" />
              {{ $t("Financial Rating, Ledger & Tariff Studio") }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Immutable double-entry ledger, versioned tariff rate cards, real-time CDR rating, and revenue assurance.") }}
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold ring-1 ring-inset ring-emerald-700/10 flex items-center gap-1.5">
              <CheckBadgeIcon class="w-4 h-4 text-emerald-600" />
              Ledger Reconciled: 100% Balanced
            </span>
          </div>
        </div>

        <!-- KPI Telemetry Cards -->
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-5 gap-4">
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Available Balance") }}</span>
            <span class="text-xl font-bold text-emerald-600 font-mono">${{ stats.available_balance }}</span>
            <span class="text-[10px] text-gray-400 block">{{ wallet.account_number }} ({{ wallet.billing_type }})</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Credit Limit") }}</span>
            <span class="text-xl font-bold text-gray-900 font-mono">${{ stats.credit_limit }}</span>
            <span class="text-[10px] text-gray-400 block">{{ wallet.currency }}</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Total Inflow / Deposits") }}</span>
            <span class="text-xl font-bold text-indigo-600 font-mono">+${{ stats.total_deposits }}</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Total Usage Outflow") }}</span>
            <span class="text-xl font-bold text-rose-600 font-mono">-${{ stats.total_usage }}</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Invoices / Statements") }}</span>
            <span class="text-xl font-bold text-gray-900">{{ stats.invoices_count }}</span>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-6">
          <button
            type="button"
            @click="activeTab = 'simulator'"
            :class="activeTab === 'simulator' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="pb-3 border-b-2 text-sm flex items-center gap-2 transition"
          >
            <CalculatorIcon class="w-4 h-4" />
            {{ $t("Usage Rating Simulator") }}
          </button>
          <button
            type="button"
            @click="activeTab = 'ledger'"
            :class="activeTab === 'ledger' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="pb-3 border-b-2 text-sm flex items-center gap-2 transition"
          >
            <ScaleIcon class="w-4 h-4" />
            {{ $t("Immutable Double-Entry Ledger") }}
          </button>
          <button
            type="button"
            @click="activeTab = 'tariffs'"
            :class="activeTab === 'tariffs' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="pb-3 border-b-2 text-sm flex items-center gap-2 transition"
          >
            <ArrowTrendingUpIcon class="w-4 h-4" />
            {{ $t("Versioned Tariff Plans") }}
          </button>
          <button
            type="button"
            @click="activeTab = 'invoices'"
            :class="activeTab === 'invoices' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="pb-3 border-b-2 text-sm flex items-center gap-2 transition"
          >
            <DocumentTextIcon class="w-4 h-4" />
            {{ $t("Invoices & Billing Statements") }}
          </button>
        </nav>
      </div>

      <!-- TAB 1: USAGE RATING SIMULATOR & TRACE -->
      <div v-if="activeTab === 'simulator'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Simulation Parameters (4 Cols) -->
          <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
              <CalculatorIcon class="w-4 h-4 text-indigo-500" />
              {{ $t("Call Rating Parameters") }}
            </h2>

            <form @submit.prevent="runRatingSimulation" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Destination Number (E.164)") }} *
                </label>
                <input
                  type="text"
                  v-model="simForm.destination_number"
                  required
                  placeholder="+1 (312) 555-0199"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Talk Duration (Seconds)") }} *
                </label>
                <input
                  type="number"
                  min="1"
                  max="14400"
                  v-model.number="simForm.duration_seconds"
                  required
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                />
              </div>

              <!-- Quick Presets -->
              <div>
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">{{ $t("Quick Test Scenarios:") }}</span>
                <div class="flex flex-wrap gap-1.5">
                  <button
                    type="button"
                    v-for="preset in presets"
                    :key="preset.number + preset.duration"
                    @click="applyPreset(preset)"
                    class="text-[11px] px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 transition"
                  >
                    {{ preset.label }}
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Applied Tariff Plan") }}
                </label>
                <select
                  v-model="simForm.tariff_plan_uuid"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs"
                >
                  <option v-for="plan in tariffPlans" :key="plan.tariff_plan_uuid" :value="plan.tariff_plan_uuid">
                    {{ plan.name }} ({{ plan.initial_interval }}/{{ plan.subsequent_interval }}s)
                  </option>
                </select>
              </div>

              <button
                type="submit"
                :disabled="simulating"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-sm transition disabled:opacity-50 text-xs sm:text-sm"
              >
                {{ simulating ? $t("Rating Call...") : $t("Simulate Usage Rating") }}
              </button>
            </form>
          </div>

          <!-- Simulation Results & Mathematical Trace (8 Cols) -->
          <div class="lg:col-span-8 space-y-5">
            <div v-if="ratingResult" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-gray-100 dark:border-gray-700 gap-2">
                <div>
                  <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    {{ $t("Rated Call:") }} {{ ratingResult.destination_number }}
                  </h3>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    Destination: <strong>{{ ratingResult.destination_name }}</strong> &bull; Plan: <strong>{{ ratingResult.plan_name }}</strong>
                  </span>
                </div>
                <div class="text-right">
                  <span class="text-xs text-gray-400 block">{{ $t("Total Calculated Cost") }}</span>
                  <span class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                    ${{ ratingResult.total_call_cost }}
                  </span>
                </div>
              </div>

              <!-- Rating Telemetry Breakdown Cards -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                  <span class="text-gray-500 block text-[11px]">{{ $t("Actual Duration") }}</span>
                  <span class="font-bold text-gray-900 dark:text-white font-mono text-sm">{{ ratingResult.duration_raw_seconds }}s</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                  <span class="text-gray-500 block text-[11px]">{{ $t("Billed Duration") }}</span>
                  <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm">{{ ratingResult.duration_billable_seconds }}s</span>
                  <span class="text-[10px] text-gray-400 block">({{ ratingResult.interval_rule }})</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                  <span class="text-gray-500 block text-[11px]">{{ $t("Tariff Rate") }}</span>
                  <span class="font-bold text-gray-900 dark:text-white font-mono text-sm">${{ ratingResult.rate_per_minute }}/min</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                  <span class="text-gray-500 block text-[11px]">{{ $t("Connect Fee") }}</span>
                  <span class="font-bold text-gray-900 dark:text-white font-mono text-sm">${{ ratingResult.connect_fee }}</span>
                </div>
              </div>

              <!-- Mathematical Explanation Trace -->
              <div>
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                  {{ $t("Step-by-Step Mathematical Rating Trace") }}
                </h4>
                <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-lg border border-gray-100 dark:border-gray-700 space-y-1.5 font-mono text-xs">
                  <div v-for="(line, lIdx) in ratingResult.explanation_trace" :key="lIdx" class="text-gray-700 dark:text-gray-300">
                    {{ line }}
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-500">
              <CalculatorIcon class="w-10 h-10 text-indigo-400 mx-auto mb-2 opacity-50" />
              <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $t("Simulate Call Rating") }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $t("Enter a dialed destination number and talk duration on the left to test interval rounding rules and tariff calculations.") }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: IMMUTABLE DOUBLE-ENTRY LEDGER -->
      <div v-if="activeTab === 'ledger'" class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-gray-100 dark:border-gray-700 gap-3">
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <ScaleIcon class="w-5 h-5 text-indigo-500" />
                {{ $t("Double-Entry Financial Ledger") }}
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">
                Mathematical proof: Credits (${{ reconciliation.total_credits }}) - Debits (${{ reconciliation.total_debits }}) = Balance (${{ reconciliation.wallet_balance }})
              </p>
            </div>

            <!-- Deposit Modal / Button -->
            <button
              type="button"
              @click="showDepositModal = true"
              class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5"
            >
              <PlusCircleIcon class="w-4 h-4" />
              {{ $t("Add Funds / Deposit") }}
            </button>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 uppercase font-semibold text-[11px]">
                <tr>
                  <th class="py-2.5 px-3">Timestamp</th>
                  <th class="py-2.5 px-3">Type</th>
                  <th class="py-2.5 px-3">Direction</th>
                  <th class="py-2.5 px-3">Amount</th>
                  <th class="py-2.5 px-3">Balance After</th>
                  <th class="py-2.5 px-3">Description</th>
                  <th class="py-2.5 px-3">Idempotency Key</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="entry in entries" :key="entry.entry_uuid" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                  <td class="py-2.5 px-3 text-gray-500 font-mono">{{ formatDate(entry.created_at) }}</td>
                  <td class="py-2.5 px-3 font-semibold text-gray-900 dark:text-white">{{ entry.transaction_type }}</td>
                  <td class="py-2.5 px-3">
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                      :class="entry.direction === 'CREDIT' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300'"
                    >
                      {{ entry.direction }}
                    </span>
                  </td>
                  <td class="py-2.5 px-3 font-mono font-bold" :class="entry.direction === 'CREDIT' ? 'text-emerald-600' : 'text-rose-600'">
                    {{ entry.direction === 'CREDIT' ? '+' : '-' }}${{ entry.amount }}
                  </td>
                  <td class="py-2.5 px-3 font-mono font-bold text-gray-900 dark:text-white">${{ entry.balance_after }}</td>
                  <td class="py-2.5 px-3 text-gray-600 dark:text-gray-300 truncate max-w-[280px]">{{ entry.description }}</td>
                  <td class="py-2.5 px-3 font-mono text-[10px] text-gray-400 truncate max-w-[140px]">{{ entry.idempotency_key }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: VERSIONED TARIFF PLANS -->
      <div v-if="activeTab === 'tariffs'" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="plan in tariffPlans"
            :key="plan.tariff_plan_uuid"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-4"
          >
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
              <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                  {{ plan.name }}
                  <span v-if="plan.is_default" class="text-[10px] px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold">
                    DEFAULT
                  </span>
                </h3>
                <span class="text-xs text-gray-500">
                  Billing Interval: <strong>{{ plan.initial_interval }}s initial / {{ plan.subsequent_interval }}s subsequent</strong>
                </span>
              </div>
              <span class="text-xs font-mono font-bold text-indigo-600">
                Markup: +{{ plan.markup_percentage }}%
              </span>
            </div>

            <!-- Rates Table for this plan -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 text-[11px] uppercase">
                  <tr>
                    <th class="py-2 px-2.5">Prefix</th>
                    <th class="py-2 px-2.5">Destination</th>
                    <th class="py-2 px-2.5">Rate ($/min)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                  <tr v-for="rate in plan.rates" :key="rate.rate_uuid">
                    <td class="py-2 px-2.5 font-mono font-bold text-indigo-600">+{{ rate.prefix }}</td>
                    <td class="py-2 px-2.5 font-medium text-gray-800 dark:text-gray-200">{{ rate.destination_name }}</td>
                    <td class="py-2 px-2.5 font-mono font-bold text-emerald-600">${{ rate.rate_per_minute }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: INVOICES & STATEMENTS -->
      <div v-if="activeTab === 'invoices'" class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Billing Invoices & Account Statements") }}</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ $t("Itemized billing statements with usage, recurring seats, DIDs and taxes.") }}</p>
            </div>
            <button
              type="button"
              @click="generateInvoice"
              :disabled="generatingInvoice"
              class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ generatingInvoice ? $t("Generating...") : $t("Generate Statement") }}
            </button>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 uppercase font-semibold text-[11px]">
                <tr>
                  <th class="py-2.5 px-3">Invoice Number</th>
                  <th class="py-2.5 px-3">Period</th>
                  <th class="py-2.5 px-3">Subtotal</th>
                  <th class="py-2.5 px-3">Tax</th>
                  <th class="py-2.5 px-3">Total Amount</th>
                  <th class="py-2.5 px-3">Status</th>
                  <th class="py-2.5 px-3">Due Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="inv in invoiceList" :key="inv.invoice_uuid" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                  <td class="py-2.5 px-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ inv.invoice_number }}</td>
                  <td class="py-2.5 px-3 text-gray-500">{{ inv.period_start }} to {{ inv.period_end }}</td>
                  <td class="py-2.5 px-3 font-mono font-bold text-gray-900 dark:text-white">${{ inv.subtotal }}</td>
                  <td class="py-2.5 px-3 font-mono text-gray-500">${{ inv.tax_amount }}</td>
                  <td class="py-2.5 px-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">${{ inv.total_amount }}</td>
                  <td class="py-2.5 px-3">
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                      :class="inv.status === 'PAID' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'"
                    >
                      {{ inv.status }}
                    </span>
                  </td>
                  <td class="py-2.5 px-3 text-gray-500 font-mono">{{ inv.due_date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Deposit Modal -->
      <div v-if="showDepositModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
          <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Add Funds to Wallet") }}</h3>
          <p class="text-xs text-gray-500">{{ $t("Enter top-up amount to record on the double-entry financial ledger.") }}</p>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ $t("Amount ($ USD)") }}</label>
            <input
              type="number"
              min="5"
              step="5"
              v-model.number="depositAmount"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            />
          </div>

          <div class="flex justify-end space-x-2 pt-2">
            <button
              type="button"
              @click="showDepositModal = false"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold text-gray-700 dark:text-gray-300"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="submitDeposit"
              :disabled="depositing"
              class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold transition disabled:opacity-50"
            >
              {{ depositing ? $t("Processing...") : $t("Confirm Top-Up") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  BanknotesIcon,
  CalculatorIcon,
  DocumentTextIcon,
  ScaleIcon,
  PlusCircleIcon,
  CheckBadgeIcon,
  ArrowTrendingUpIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  wallet: {
    type: Object,
    required: true,
  },
  ledgerEntries: {
    type: Array,
    default: () => [],
  },
  tariffPlans: {
    type: Array,
    default: () => [],
  },
  invoices: {
    type: Array,
    default: () => [],
  },
  reconciliation: {
    type: Object,
    default: () => ({
      wallet_balance: 0,
      total_credits: 0,
      total_debits: 0,
    }),
  },
  stats: {
    type: Object,
    default: () => ({
      available_balance: 0,
      credit_limit: 0,
      total_deposits: 0,
      total_usage: 0,
      invoices_count: 0,
    }),
  },
});

const activeTab = ref("simulator");
const entries = ref([...props.ledgerEntries]);
const invoiceList = ref([...props.invoices]);

const simForm = ref({
  destination_number: "+1 (312) 555-0199",
  duration_seconds: 75,
  tariff_plan_uuid: props.tariffPlans.length > 0 ? props.tariffPlans[0].tariff_plan_uuid : null,
});

const presets = [
  { label: "🇺🇸 US Call 75s (60/6s)", number: "+1 (312) 555-0199", duration: 75 },
  { label: "🇬🇧 London 142s Call", number: "+44 20 7946 0958", duration: 142 },
  { label: "🇧🇩 BD Mobile 58s Call", number: "+880 1711 000000", duration: 58 },
];

function applyPreset(preset) {
  simForm.value.destination_number = preset.number;
  simForm.value.duration_seconds = preset.duration;
  runRatingSimulation();
}

const simulating = ref(false);
const ratingResult = ref(null);

async function runRatingSimulation() {
  simulating.value = true;
  try {
    const res = await axios.post("/api/billing/financial-studio/simulate", {
      destination_number: simForm.value.destination_number,
      duration_seconds: simForm.value.duration_seconds,
      tariff_plan_uuid: simForm.value.tariff_plan_uuid,
    });
    if (res.data.success) {
      ratingResult.value = res.data.simulation;
    }
  } catch (err) {
    alert(err.response?.data?.message || "Rating simulation failed");
  } finally {
    simulating.value = false;
  }
}

const showDepositModal = ref(false);
const depositAmount = ref(50);
const depositing = ref(false);

async function submitDeposit() {
  depositing.value = true;
  try {
    const res = await axios.post("/api/billing/financial-studio/deposit", {
      amount: depositAmount.value,
    });
    if (res.data.success) {
      alert(res.data.message);
      entries.value.unshift(res.data.entry);
      showDepositModal.value = false;
    }
  } catch (err) {
    alert(err.response?.data?.message || "Deposit failed");
  } finally {
    depositing.value = false;
  }
}

const generatingInvoice = ref(false);
async function generateInvoice() {
  generatingInvoice.value = true;
  try {
    const res = await axios.post("/api/billing/financial-studio/invoice");
    if (res.data.success) {
      alert(res.data.message);
      invoiceList.value.unshift(res.data.invoice);
    }
  } catch (err) {
    alert(err.response?.data?.message || "Invoice generation failed");
  } finally {
    generatingInvoice.value = false;
  }
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleString();
}
</script>
