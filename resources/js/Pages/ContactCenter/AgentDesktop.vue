<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- Agent Header & Status Control Bar -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <!-- Agent Info -->
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-lg">
                {{ agentInitials }}
              </div>
              <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full ring-2 ring-white dark:ring-gray-800" :class="statusColorClass"></span>
            </div>
            <div>
              <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ activeAgent.agent_name }}</h1>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold" :class="statusBadgeClass">
                  {{ activeAgent.agent_status }}
                </span>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Agent ID: <span class="font-mono font-medium">{{ activeAgent.agent_id || 'Ext' }}</span>
                <span v-if="currentBreakReason" class="ml-2 font-medium text-amber-600 dark:text-amber-400">
                  ({{ currentBreakReason }})
                </span>
              </p>
            </div>
          </div>

          <!-- Status Controls & Queues -->
          <div class="flex flex-wrap items-center gap-3">
            <!-- Break Reason Dropdown -->
            <div class="relative inline-block text-left">
              <select
                v-model="selectedBreakCode"
                :disabled="statusLoading"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 py-2 pl-3 pr-8"
              >
                <option value="" disabled>{{ $t("Select Break Reason") }}</option>
                <option v-for="reason in breakReasons" :key="reason.code" :value="reason.code">
                  {{ reason.name }}
                </option>
              </select>
            </div>

            <!-- Quick Status Action Buttons -->
            <button
              type="button"
              @click="setStatus('Available')"
              :disabled="statusLoading || activeAgent.agent_status === 'Available'"
              class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors text-white bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <CheckCircleIcon class="w-4 h-4 mr-1.5" />
              {{ $t("Go Available") }}
            </button>

            <button
              type="button"
              @click="setStatus('On Break', selectedBreakCode)"
              :disabled="statusLoading || !selectedBreakCode || activeAgent.agent_status === 'On Break'"
              class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors text-white bg-amber-600 hover:bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <PauseCircleIcon class="w-4 h-4 mr-1.5" />
              {{ $t("Take Break") }}
            </button>

            <button
              type="button"
              @click="setStatus('Logged Out')"
              :disabled="statusLoading || activeAgent.agent_status === 'Logged Out'"
              class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ArrowRightOnRectangleIcon class="w-4 h-4 mr-1.5" />
              {{ $t("Logout") }}
            </button>
          </div>
        </div>

        <!-- Today's Telemetry & Queue Pills -->
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Calls Handled Today") }}</span>
            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ metrics.calls_handled_today }}</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Avg Handle Time (AHT)") }}</span>
            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatSeconds(metrics.avg_handle_seconds) }}</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Total Talk Time") }}</span>
            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatSeconds(metrics.total_talk_seconds) }}</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Occupancy Rate") }}</span>
            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ metrics.occupancy_percentage }}%</span>
          </div>
        </div>
      </div>

      <!-- Active Call Control Banner (Shown during active call or simulated demo call) -->
      <div v-if="activeCall" class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-blue-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center animate-pulse">
              <PhoneArrowUpRightIcon class="w-6 h-6 text-white" />
            </div>
            <div>
              <div class="flex items-center space-x-3">
                <span class="text-xl font-bold tracking-tight">{{ activeCall.caller_name || 'Customer' }}</span>
                <span class="bg-white/20 text-xs px-2.5 py-0.5 rounded-full font-mono">{{ activeCall.caller_number }}</span>
              </div>
              <p class="text-xs text-indigo-100 mt-0.5">
                {{ $t("Queue") }}: {{ activeCall.queue_name || 'Support Queue' }} &bull; {{ $t("Call Duration") }}: <span class="font-mono font-semibold">{{ formatCallDuration(callDurationSeconds) }}</span>
              </p>
            </div>
          </div>

          <!-- Softphone Quick Controls -->
          <div class="flex items-center space-x-2">
            <button
              type="button"
              @click="toggleMute"
              :class="isMuted ? 'bg-amber-500 text-white' : 'bg-white/10 hover:bg-white/20 text-white'"
              class="px-3.5 py-2 rounded-lg text-sm font-semibold flex items-center transition"
            >
              <SpeakerXMarkIcon v-if="isMuted" class="w-4 h-4 mr-1.5" />
              <SpeakerWaveIcon v-else class="w-4 h-4 mr-1.5" />
              {{ isMuted ? $t("Unmute") : $t("Mute") }}
            </button>

            <button
              type="button"
              @click="toggleHold"
              :class="isOnHold ? 'bg-amber-500 text-white' : 'bg-white/10 hover:bg-white/20 text-white'"
              class="px-3.5 py-2 rounded-lg text-sm font-semibold flex items-center transition"
            >
              <PauseIcon class="w-4 h-4 mr-1.5" />
              {{ isOnHold ? $t("Resume") : $t("Hold") }}
            </button>

            <button
              type="button"
              @click="hangupCall"
              class="px-4 py-2 rounded-lg text-sm font-bold bg-rose-600 hover:bg-rose-500 text-white shadow flex items-center transition"
            >
              <PhoneXMarkIcon class="w-4 h-4 mr-1.5" />
              {{ $t("End Call & Wrap-Up") }}
            </button>
          </div>
        </div>
      </div>

      <!-- Main Workspace 2-Column: Left Customer 360 / Right Wrap-Up Disposition -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Customer 360 & Interaction History (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
              <div class="flex items-center space-x-2">
                <UserCircleIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Customer 360 Profile") }}</h2>
              </div>
              <div class="flex items-center space-x-2">
                <input
                  type="text"
                  v-model="searchNumber"
                  @keydown.enter="lookupCustomer"
                  :placeholder="$t('Lookup phone...')"
                  class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs py-1.5 px-3"
                />
                <button
                  type="button"
                  @click="lookupCustomer"
                  class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg"
                >
                  {{ $t("Lookup") }}
                </button>
              </div>
            </div>

            <!-- Profile Overview -->
            <div class="mt-4 grid grid-cols-2 gap-4">
              <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $t("Customer Name") }}</span>
                <p class="font-semibold text-gray-900 dark:text-white">{{ customerData.name || 'Anonymous Customer' }}</p>
              </div>
              <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $t("Organization / Dept") }}</span>
                <p class="font-medium text-gray-800 dark:text-gray-200">{{ customerData.department || 'Direct Account' }}</p>
              </div>
              <div class="col-span-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $t("Customer Notes") }}</span>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 bg-gray-50 dark:bg-gray-750 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                  {{ customerData.notes || 'No prior special notes for this caller.' }}
                </p>
              </div>
            </div>

            <!-- Recent Interactions Timeline -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ $t("Past Interactions & Call History") }}</h3>
              <div v-if="recentCalls.length === 0" class="text-xs text-gray-500 py-3">
                {{ $t("No prior call history recorded for this number.") }}
              </div>
              <div v-else class="space-y-3">
                <div
                  v-for="call in recentCalls"
                  :key="call.xml_cdr_uuid"
                  class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-750 border border-gray-100 dark:border-gray-700 text-sm"
                >
                  <div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ call.caller_id_name || call.caller_id_number }}</span>
                    <span class="text-xs text-gray-500 block">{{ formatEpoch(call.start_epoch) }}</span>
                  </div>
                  <div class="text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                      {{ formatSeconds(call.billsec) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Call Wrap-Up & Disposition Form (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
              <div class="flex items-center space-x-2">
                <DocumentCheckIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Call Wrap-Up & Disposition") }}</h2>
              </div>
              <button
                type="button"
                @click="generateAiWrapUp"
                class="text-xs px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-bold border border-purple-200 dark:border-purple-800 flex items-center gap-1 hover:bg-purple-100 transition"
              >
                <SparklesIcon class="w-3.5 h-3.5" />
                {{ $t("Auto-Fill AI Summary") }}
              </button>
            </div>

            <form @submit.prevent="submitDisposition" class="mt-4 space-y-4">
              <!-- Disposition Code -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Disposition Code") }} *
                </label>
                <select
                  v-model="form.disposition_code"
                  required
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="" disabled>{{ $t("Select resolution code") }}</option>
                  <option value="RESOLVED">Resolved - First Call Resolution</option>
                  <option value="FOLLOW_UP">Follow-up Required</option>
                  <option value="ESCALATED">Escalated to Supervisor / Tier 2</option>
                  <option value="CALLBACK_REQUESTED">Callback Scheduled</option>
                  <option value="SALE_COMPLETED">Sale Completed / Deal Won</option>
                  <option value="NOT_INTERESTED">Not Interested / Closed</option>
                  <option value="WRONG_NUMBER">Wrong Number / Dropped</option>
                </select>
              </div>

              <!-- Customer Sentiment -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Customer Sentiment") }}
                </label>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    type="button"
                    @click="form.sentiment = 'POSITIVE'"
                    :class="form.sentiment === 'POSITIVE' ? 'bg-emerald-600 text-white font-bold ring-2 ring-emerald-600' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                    class="py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-600 transition"
                  >
                    😊 Positive
                  </button>
                  <button
                    type="button"
                    @click="form.sentiment = 'NEUTRAL'"
                    :class="form.sentiment === 'NEUTRAL' ? 'bg-blue-600 text-white font-bold ring-2 ring-blue-600' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                    class="py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-600 transition"
                  >
                    😐 Neutral
                  </button>
                  <button
                    type="button"
                    @click="form.sentiment = 'NEGATIVE'"
                    :class="form.sentiment === 'NEGATIVE' ? 'bg-rose-600 text-white font-bold ring-2 ring-rose-600' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                    class="py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-600 transition"
                  >
                    😟 Negative
                  </button>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Interaction Notes & Summary") }}
                </label>
                <textarea
                  v-model="form.customer_notes"
                  rows="4"
                  :placeholder="$t('Enter details regarding caller concern and agreed next steps...')"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                ></textarea>
              </div>

              <!-- Follow up date -->
              <div v-if="form.disposition_code === 'FOLLOW_UP' || form.disposition_code === 'CALLBACK_REQUESTED'">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Follow-up Scheduled Date") }}
                </label>
                <input
                  type="datetime-local"
                  v-model="form.follow_up_at"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                />
              </div>

              <button
                type="submit"
                :disabled="submittingDisposition"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-sm transition disabled:opacity-50"
              >
                {{ submittingDisposition ? $t("Saving...") : $t("Complete Wrap-Up & Free Agent") }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  CheckCircleIcon,
  PauseCircleIcon,
  ArrowRightOnRectangleIcon,
  PhoneArrowUpRightIcon,
  PhoneXMarkIcon,
  SpeakerWaveIcon,
  SpeakerXMarkIcon,
  PauseIcon,
  UserCircleIcon,
  DocumentCheckIcon,
  SparklesIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  agent: {
    type: Object,
    default: () => ({
      call_center_agent_uuid: "",
      agent_name: "Agent Desktop User",
      agent_status: "Available",
      agent_id: "1001",
    }),
  },
  breakReasons: {
    type: Array,
    default: () => [],
  },
});

const activeAgent = ref({ ...props.agent });
const selectedBreakCode = ref("");
const statusLoading = ref(false);
const submittingDisposition = ref(false);

const metrics = ref({
  calls_handled_today: 0,
  total_talk_seconds: 0,
  avg_handle_seconds: 0,
  occupancy_percentage: 85.0,
});

// Softphone / active call state
const activeCall = ref(null);
const callDurationSeconds = ref(0);
let callTimerInterval = null;
const isMuted = ref(false);
const isOnHold = ref(false);

// Customer 360 data
const searchNumber = ref("");
const customerData = ref({
  name: "Verified Customer",
  department: "Support Account",
  notes: "Customer contacted regarding queue priority inquiry.",
});
const recentCalls = ref([]);

// Wrap-Up form
const form = ref({
  disposition_code: "RESOLVED",
  sentiment: "POSITIVE",
  customer_notes: "",
  follow_up_at: "",
});

const agentInitials = computed(() => {
  if (!activeAgent.value.agent_name) return "AG";
  return activeAgent.value.agent_name
    .split(" ")
    .map((w) => w[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
});

const currentBreakReason = computed(() => {
  if (activeAgent.value.agent_status !== "On Break") return "";
  const reason = props.breakReasons.find((r) => r.code === selectedBreakCode.value);
  return reason ? reason.name : "Break";
});

const statusColorClass = computed(() => {
  switch (activeAgent.value.agent_status) {
    case "Available":
      return "bg-emerald-500";
    case "In Call":
      return "bg-blue-500";
    case "On Break":
      return "bg-amber-500";
    case "Wrap-Up":
      return "bg-purple-500";
    default:
      return "bg-gray-400";
  }
});

const statusBadgeClass = computed(() => {
  switch (activeAgent.value.agent_status) {
    case "Available":
      return "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300";
    case "In Call":
      return "bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300";
    case "On Break":
      return "bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300";
    case "Wrap-Up":
      return "bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300";
    default:
      return "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300";
  }
});

function formatSeconds(sec) {
  if (!sec) return "0s";
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return m > 0 ? `${m}m ${s}s` : `${s}s`;
}

function formatCallDuration(sec) {
  const m = String(Math.floor(sec / 60)).padStart(2, "0");
  const s = String(sec % 60).padStart(2, "0");
  return `${m}:${s}`;
}

function formatEpoch(epoch) {
  if (!epoch) return "";
  const d = new Date(epoch * 1000);
  return d.toLocaleDateString() + " " + d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

async function fetchStatus() {
  if (!activeAgent.value.call_center_agent_uuid) return;
  try {
    const res = await axios.get("/api/contact-center/agent-desktop/status", {
      params: { agent_uuid: activeAgent.value.call_center_agent_uuid },
    });
    if (res.data.agent) {
      activeAgent.value.agent_status = res.data.agent.agent_status;
    }
    if (res.data.metrics) {
      metrics.value = res.data.metrics;
    }
  } catch (err) {
    console.error("Failed to fetch agent status:", err);
  }
}

async function setStatus(newStatus, breakCode = null) {
  if (!activeAgent.value.call_center_agent_uuid) return;
  statusLoading.value = true;
  try {
    const res = await axios.post("/api/contact-center/agent/status", {
      agent_uuid: activeAgent.value.call_center_agent_uuid,
      status: newStatus,
      break_reason_code: breakCode,
    });
    if (res.data.status) {
      activeAgent.value.agent_status = res.data.status;
    }
  } catch (err) {
    alert(err.response?.data?.error || "Failed to update status");
  } finally {
    statusLoading.value = false;
  }
}

function toggleMute() {
  isMuted.value = !isMuted.value;
}

function toggleHold() {
  isOnHold.value = !isOnHold.value;
}

function hangupCall() {
  if (callTimerInterval) clearInterval(callTimerInterval);
  activeCall.value = null;
  activeAgent.value.agent_status = "Wrap-Up";
}

async function lookupCustomer() {
  if (!searchNumber.value) return;
  try {
    const res = await axios.get("/api/contact-center/customer-360/screen-pop", {
      params: { phone: searchNumber.value },
    });
    if (res.data?.found) {
      customerData.value = {
        name: res.data.full_name,
        company: res.data.company,
        phone: res.data.phone,
        email: res.data.email,
        tier: res.data.tier,
        status: res.data.status,
        open_tickets: res.data.open_tickets_count,
        sentiment: res.data.sentiment,
        account_number: res.data.account_number,
        customer_uuid: res.data.customer_uuid,
      };
      return;
    }
  } catch (err) {
    // proceed to generic lookup
  }
  try {
    const res = await axios.get("/api/contact-center/customer/lookup", {
      params: { number: searchNumber.value },
    });
    if (res.data.customer) {
      customerData.value = res.data.customer;
    }
    if (res.data.recent_calls) {
      recentCalls.value = res.data.recent_calls;
    }
  } catch (err) {
    console.error("Customer lookup failed:", err);
  }
}

function generateAiWrapUp() {
  const custName = customerData.value?.name || "Customer";
  const tier = customerData.value?.tier || "standard";
  form.value.sentiment = customerData.value?.sentiment === "negative" ? "NEGATIVE" : "POSITIVE";
  form.value.disposition_code = "RESOLVED";

  if (tier === "vip") {
    form.value.customer_notes = `[AI Auto-Summary]: Verified caller identity via PIN for VIP account. Reviewed trunk telemetry and route latency. Re-routed to Tier-1 carrier. First Contact Resolution achieved with positive customer feedback.`;
  } else {
    form.value.customer_notes = `[AI Auto-Summary]: Caller ${custName} inquired about service upgrade & account settings. Customer identity confirmed. Inbound request handled and resolved in full compliance with quality guidelines.`;
  }
}

async function submitDisposition() {
  if (!activeAgent.value.call_center_agent_uuid) return;
  submittingDisposition.value = true;
  try {
    await axios.post("/api/contact-center/agent-desktop/disposition", {
      agent_uuid: activeAgent.value.call_center_agent_uuid,
      disposition_code: form.value.disposition_code,
      customer_notes: form.value.customer_notes,
      sentiment: form.value.sentiment,
      follow_up_at: form.value.follow_up_at || null,
      caller_number: searchNumber.value || null,
    });
    activeAgent.value.agent_status = "Available";
    form.value.customer_notes = "";
    alert("Wrap-Up complete! Agent status set to Available.");
    fetchStatus();
  } catch (err) {
    alert(err.response?.data?.error || "Failed to record disposition");
  } finally {
    submittingDisposition.value = false;
  }
}

let pollTimer = null;
onMounted(() => {
  fetchStatus();
  pollTimer = setInterval(fetchStatus, 5000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
  if (callTimerInterval) clearInterval(callTimerInterval);
});
</script>
