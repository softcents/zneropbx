<template>
  <MainLayout>
    <div class="m-3 space-y-6">
      <!-- Telecom Routing Studio Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
              <GlobeAltIcon class="w-7 h-7 text-indigo-600" />
              {{ $t("Telecom Routing Intelligence & Carrier LCR Studio") }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Least Cost Routing (LCR), trunk health telemetry, automated circuit breaker quarantine, and deterministic route simulation.") }}
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold ring-1 ring-inset ring-indigo-700/10">
              P2 Telecom Engine &bull; Active
            </span>
          </div>
        </div>

        <!-- KPI Telemetry Cards -->
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-5 gap-4">
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Carrier Trunks") }}</span>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span class="text-xl font-bold text-gray-900">{{ stats.total_carriers }}</span>
              <span class="text-xs font-semibold text-emerald-600">({{ stats.online_carriers }} Online)</span>
            </div>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Fleet Average ASR") }}</span>
            <span class="text-xl font-bold text-emerald-600">{{ stats.avg_asr }}%</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Average Post-Dial Delay") }}</span>
            <span class="text-xl font-bold text-indigo-600">{{ stats.avg_pdd }} ms</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Channel Utilization") }}</span>
            <span class="text-xl font-bold text-purple-600">{{ stats.active_channels }}/{{ stats.max_channels }}</span>
            <span class="text-[10px] text-gray-400 block">{{ stats.utilization_pct }}% load</span>
          </div>
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Quarantined Trunks") }}</span>
            <span class="text-xl font-bold" :class="stats.quarantined_carriers > 0 ? 'text-rose-600' : 'text-gray-400'">
              {{ stats.quarantined_carriers }}
            </span>
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
            <BoltIcon class="w-4 h-4" />
            {{ $t("Route Simulator & Dry-Run Engine") }}
          </button>
          <button
            type="button"
            @click="activeTab = 'carriers'"
            :class="activeTab === 'carriers' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="pb-3 border-b-2 text-sm flex items-center gap-2 transition"
          >
            <ServerStackIcon class="w-4 h-4" />
            {{ $t("Carrier Trunks & Circuit Breaker") }}
          </button>
          <button
            type="button"
            @click="activeTab = 'destinations'"
            :class="activeTab === 'destinations' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="pb-3 border-b-2 text-sm flex items-center gap-2 transition"
          >
            <ChartBarIcon class="w-4 h-4" />
            {{ $t("Prefix Rate Matrix (LCR)") }}
          </button>
        </nav>
      </div>

      <!-- TAB 1: INTERACTIVE ROUTE SIMULATOR & DRY-RUN -->
      <div v-if="activeTab === 'simulator'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Simulation Input Form (4 Cols) -->
          <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
              <BoltIcon class="w-4 h-4 text-indigo-500" />
              {{ $t("Dialed Call Parameters") }}
            </h2>

            <form @submit.prevent="runSimulation" class="space-y-4">
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

              <!-- Quick Presets -->
              <div>
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">{{ $t("Quick Presets:") }}</span>
                <div class="flex flex-wrap gap-1.5">
                  <button
                    type="button"
                    v-for="preset in presets"
                    :key="preset.number"
                    @click="applyPreset(preset)"
                    class="text-[11px] px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 transition"
                  >
                    {{ preset.label }}
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Routing Policy Strategy") }}
                </label>
                <select
                  v-model="simForm.strategy"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs"
                >
                  <option value="LCR">Least Cost Routing (Strict LCR)</option>
                  <option value="QUALITY">Quality First (Highest ASR & Lowest PDD)</option>
                  <option value="TIER_BASED">Customer Tier / VIP Routing</option>
                  <option value="WEIGHTED">Weighted Distribution</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Customer Account Tier") }}
                </label>
                <select
                  v-model="simForm.customer_tier"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs"
                >
                  <option value="Standard">Standard Account</option>
                  <option value="VIP">👑 VIP Enterprise (Zero Latency SLA)</option>
                  <option value="Wholesale">Wholesale Carrier</option>
                </select>
              </div>

              <button
                type="submit"
                :disabled="simulating"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-sm transition disabled:opacity-50 text-xs sm:text-sm flex items-center justify-center gap-2"
              >
                <PlayIcon class="w-4 h-4" />
                {{ simulating ? $t("Simulating Telecom Pipeline...") : $t("Run Dry-Run Route Simulation") }}
              </button>
            </form>
          </div>

          <!-- Simulation Results & Decision Trace (8 Cols) -->
          <div class="lg:col-span-8 space-y-5">
            <div v-if="simulationResult" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-gray-100 dark:border-gray-700 gap-2">
                <div>
                  <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <CheckCircleIcon class="w-5 h-5 text-emerald-500" />
                    {{ $t("Route Decision: Destination") }} {{ simulationResult.destination_number }}
                  </h3>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    Prefix: <strong class="text-gray-800 dark:text-gray-200">[{{ simulationResult.matched_prefix }}]</strong> &bull; Strategy: <strong>{{ simulationResult.strategy }}</strong> &bull; Tier: <strong>{{ simulationResult.customer_tier }}</strong>
                  </span>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                  Deterministic Route Verified
                </span>
              </div>

              <!-- Primary Route Chosen (Highlighted) -->
              <div v-if="simulationResult.primary_route" class="bg-indigo-50/60 dark:bg-indigo-900/15 border-2 border-indigo-500/40 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="bg-indigo-600 text-white text-[11px] font-bold px-2 py-0.5 rounded">
                      PRIMARY ROUTE (Rank #1)
                    </span>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                      {{ simulationResult.primary_route.carrier_name }}
                    </h4>
                  </div>
                  <span class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">
                    ${{ simulationResult.primary_route.cost_per_minute }}/min
                  </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mt-3 pt-3 border-t border-indigo-100 dark:border-indigo-900/30">
                  <div>
                    <span class="text-gray-500 block text-[11px]">{{ $t("SIP Gateway Proxy") }}</span>
                    <span class="font-mono font-medium text-gray-800 dark:text-gray-200">{{ simulationResult.primary_route.sip_proxy }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500 block text-[11px]">{{ $t("Historical ASR") }}</span>
                    <span class="font-bold text-emerald-600">{{ simulationResult.primary_route.asr_percentage }}%</span>
                  </div>
                  <div>
                    <span class="text-gray-500 block text-[11px]">{{ $t("Post-Dial Delay (PDD)") }}</span>
                    <span class="font-mono font-medium text-gray-800 dark:text-gray-200">{{ simulationResult.primary_route.pdd_ms }} ms</span>
                  </div>
                  <div>
                    <span class="text-gray-500 block text-[11px]">{{ $t("Average Duration (ACD)") }}</span>
                    <span class="font-mono font-medium text-gray-800 dark:text-gray-200">{{ simulationResult.primary_route.acd_seconds }}s</span>
                  </div>
                </div>
              </div>

              <!-- Failover Route Chain -->
              <div v-if="simulationResult.failover_routes && simulationResult.failover_routes.length > 0">
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                  {{ $t("Automatic Failover Hop Sequence") }} ({{ simulationResult.failover_routes.length }} {{ $t("Tolerant Routes") }})
                </h4>
                <div class="space-y-2">
                  <div
                    v-for="(fo, foIdx) in simulationResult.failover_routes"
                    :key="fo.carrier_uuid"
                    class="bg-gray-50 dark:bg-gray-750 p-3 rounded-lg border border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs"
                  >
                    <div class="flex items-center space-x-2">
                      <span class="font-bold px-1.5 py-0.5 rounded text-[10px] bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200">
                        Failover #{{ foIdx + 1 }}
                      </span>
                      <span class="font-bold text-gray-900 dark:text-white">{{ fo.carrier_name }}</span>
                      <span class="text-gray-500">({{ fo.sip_proxy }})</span>
                    </div>
                    <div class="flex items-center space-x-3 font-mono">
                      <span>ASR: <strong class="text-emerald-600">{{ fo.asr_percentage }}%</strong></span>
                      <span>PDD: {{ fo.pdd_ms }}ms</span>
                      <span class="font-bold text-indigo-600 dark:text-indigo-400">${{ fo.cost_per_minute }}/m</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Quarantined / Disqualified Trunks Section -->
              <div v-if="simulationResult.disqualified_carriers && simulationResult.disqualified_carriers.length > 0">
                <h4 class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                  <ShieldExclamationIcon class="w-4 h-4" />
                  {{ $t("Circuit Breaker Quarantined / Disqualified Candidates") }}
                </h4>
                <div class="space-y-1.5">
                  <div
                    v-for="(dq, dqIdx) in simulationResult.disqualified_carriers"
                    :key="dqIdx"
                    class="p-2.5 rounded bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 text-xs flex items-center justify-between"
                  >
                    <span class="font-semibold text-rose-800 dark:text-rose-300">{{ dq.carrier }}</span>
                    <span class="text-rose-600 dark:text-rose-400 text-[11px] italic">{{ dq.reason }}</span>
                  </div>
                </div>
              </div>

              <!-- FreeSWITCH Bridge String -->
              <div class="bg-gray-900 text-gray-100 p-3.5 rounded-lg font-mono text-xs overflow-x-auto border border-gray-700">
                <span class="text-gray-400 text-[10px] block mb-1 uppercase font-bold tracking-wider">
                  {{ $t("FreeSWITCH Native Bridge Dialstring (Failover Pipeline)") }}
                </span>
                <span class="text-emerald-400 select-all">{{ simulationResult.freeswitch_dialstring }}</span>
              </div>

              <!-- Decision Trace Timeline -->
              <div>
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                  {{ $t("Deterministic Decision Trace Log") }}
                </h4>
                <div class="space-y-2 border-l-2 border-indigo-200 dark:border-indigo-800 ml-2 pl-3">
                  <div v-for="step in simulationResult.decision_trace" :key="step.step" class="relative text-xs">
                    <span class="font-bold text-gray-900 dark:text-white">{{ step.phase }}:</span>
                    <span class="text-gray-600 dark:text-gray-300 ml-1">{{ step.detail }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-500">
              <BoltIcon class="w-10 h-10 text-indigo-400 mx-auto mb-2 opacity-50" />
              <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $t("Run a Route Simulation") }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $t("Enter a dialed destination number on the left and test carrier rankings, circuit breaker bypass, and FreeSWITCH bridge strings.") }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: CARRIER REGISTRY & CIRCUIT BREAKER HEALTH -->
      <div v-if="activeTab === 'carriers'" class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Carrier Trunk Registry & Circuit Breaker Controls") }}</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ $t("Real-time QoS metrics, capacity limits, and manual quarantine controls for NOC engineers.") }}</p>
            </div>
            <span class="text-xs font-medium text-gray-500">{{ carrierList.length }} {{ $t("registered carriers") }}</span>
          </div>

          <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
            <div
              v-for="carrier in carrierList"
              :key="carrier.carrier_uuid"
              class="py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
            >
              <div class="space-y-1">
                <div class="flex items-center space-x-2">
                  <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ carrier.name }}</h3>
                  <span
                    class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase"
                    :class="carrier.status === 'ONLINE' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300'"
                  >
                    {{ carrier.status }}
                  </span>
                  <span v-if="carrier.circuit_breaker_tripped" class="text-[10px] px-2 py-0.5 rounded bg-rose-600 text-white font-bold">
                    Circuit Breaker Tripped
                  </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                  SIP: {{ carrier.sip_proxy }} &bull; Code: [{{ carrier.carrier_code }}] &bull; Priority: {{ carrier.priority }} &bull; Weight: {{ carrier.weight }}
                </div>
              </div>

              <!-- Telemetry indicators -->
              <div class="grid grid-cols-3 gap-4 text-xs font-mono">
                <div>
                  <span class="text-[10px] text-gray-400 block font-sans">ASR</span>
                  <span class="font-bold" :class="carrier.asr_percentage >= 80 ? 'text-emerald-600' : 'text-rose-600'">
                    {{ carrier.asr_percentage }}%
                  </span>
                </div>
                <div>
                  <span class="text-[10px] text-gray-400 block font-sans">PDD</span>
                  <span class="font-bold text-gray-800 dark:text-gray-200">{{ carrier.pdd_ms }} ms</span>
                </div>
                <div>
                  <span class="text-[10px] text-gray-400 block font-sans">Channels</span>
                  <span class="font-bold text-purple-600">{{ carrier.current_channels }}/{{ carrier.max_channels }}</span>
                </div>
              </div>

              <!-- Action: Trip / Reset Circuit Breaker -->
              <div class="flex items-center space-x-2">
                <button
                  type="button"
                  @click="toggleCircuitBreaker(carrier)"
                  :class="carrier.circuit_breaker_tripped ? 'bg-emerald-600 hover:bg-emerald-500 text-white' : 'bg-rose-600 hover:bg-rose-500 text-white'"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm"
                >
                  {{ carrier.circuit_breaker_tripped ? $t("Reset & Restore Online") : $t("Trip Circuit Breaker") }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: PREFIX RATE MATRIX (LCR) -->
      <div v-if="activeTab === 'destinations'" class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Normalized Prefix Rate Cards (LCR Matrix)") }}</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ $t("Multi-carrier cost per minute comparison for destination prefixes.") }}</p>
            </div>
            <span class="text-xs text-gray-500">{{ destinations.length }} {{ $t("rates loaded") }}</span>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 uppercase font-semibold text-[11px]">
                <tr>
                  <th class="py-2.5 px-3">Prefix</th>
                  <th class="py-2.5 px-3">Destination Name</th>
                  <th class="py-2.5 px-3">Carrier Trunk</th>
                  <th class="py-2.5 px-3">Rate ($/min)</th>
                  <th class="py-2.5 px-3">Billing Increment</th>
                  <th class="py-2.5 px-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="dest in destinations" :key="dest.destination_uuid" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                  <td class="py-2.5 px-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">+{{ dest.prefix }}</td>
                  <td class="py-2.5 px-3 font-medium text-gray-900 dark:text-white">{{ dest.destination_name }}</td>
                  <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300">{{ dest.carrier?.name || 'Carrier' }}</td>
                  <td class="py-2.5 px-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">${{ dest.cost_per_minute }}</td>
                  <td class="py-2.5 px-3 font-mono text-gray-500">{{ dest.rate_increment }}/{{ dest.rate_increment }}s</td>
                  <td class="py-2.5 px-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                      Active
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
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
  GlobeAltIcon,
  BoltIcon,
  ServerStackIcon,
  ChartBarIcon,
  PlayIcon,
  CheckCircleIcon,
  ShieldExclamationIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  carriers: {
    type: Array,
    default: () => [],
  },
  destinations: {
    type: Array,
    default: () => [],
  },
  policies: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total_carriers: 0,
      online_carriers: 0,
      quarantined_carriers: 0,
      avg_asr: 0,
      avg_pdd: 0,
      active_channels: 0,
      max_channels: 0,
      utilization_pct: 0,
    }),
  },
});

const activeTab = ref("simulator");
const carrierList = ref([...props.carriers]);

const simForm = ref({
  destination_number: "+1 (312) 555-0199",
  strategy: "LCR",
  customer_tier: "Standard",
});

const presets = [
  { label: "🇺🇸 US Enterprise Call", number: "+1 (312) 555-0199", strategy: "LCR", tier: "Standard" },
  { label: "🇬🇧 UK London Call", number: "+44 20 7946 0958", strategy: "LCR", tier: "Standard" },
  { label: "🇧🇩 Bangladesh Mobile", number: "+880 1711 000000", strategy: "LCR", tier: "Standard" },
  { label: "👑 VIP Priority Call", number: "+1 (555) 234-5678", strategy: "TIER_BASED", tier: "VIP" },
];

function applyPreset(preset) {
  simForm.value.destination_number = preset.number;
  simForm.value.strategy = preset.strategy;
  simForm.value.customer_tier = preset.tier;
  runSimulation();
}

const simulating = ref(false);
const simulationResult = ref(null);

async function runSimulation() {
  simulating.value = true;
  try {
    const res = await axios.post("/api/telecom/routing-intelligence/simulate", {
      destination_number: simForm.value.destination_number,
      strategy: simForm.value.strategy,
      customer_tier: simForm.value.customer_tier,
    });
    if (res.data.success) {
      simulationResult.value = res.data.simulation;
    }
  } catch (err) {
    alert(err.response?.data?.message || "Route simulation failed");
  } finally {
    simulating.value = false;
  }
}

async function toggleCircuitBreaker(carrier) {
  const newTripState = !carrier.circuit_breaker_tripped;
  try {
    const res = await axios.post(`/api/telecom/routing-intelligence/carriers/${carrier.carrier_uuid}/circuit-breaker`, {
      trip: newTripState,
    });
    if (res.data.success) {
      carrier.circuit_breaker_tripped = res.data.carrier.circuit_breaker_tripped;
      carrier.status = res.data.carrier.status;
      alert(res.data.message);
    }
  } catch (err) {
    alert(err.response?.data?.message || "Failed to toggle circuit breaker");
  }
}
</script>
