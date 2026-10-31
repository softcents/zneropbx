<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- WFM Header & Navigation Tabs -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <UserGroupIcon class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
              {{ $t("Workforce Management & Real-Time Adherence") }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ $t("Manage agent shift schedules, monitor real-time adherence drift, and forecast queue staffing using Erlang-C.") }}
            </p>
          </div>

          <!-- Tab Switcher -->
          <div class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-700 p-1 rounded-xl">
            <button
              type="button"
              @click="activeTab = 'adherence'"
              :class="activeTab === 'adherence' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'"
              class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition"
            >
              🕒 {{ $t("Real-Time Adherence") }}
            </button>
            <button
              type="button"
              @click="activeTab = 'forecast'"
              :class="activeTab === 'forecast' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'"
              class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition"
            >
              📊 {{ $t("Erlang-C Staffing") }}
            </button>
            <button
              type="button"
              @click="activeTab = 'roster'"
              :class="activeTab === 'roster' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'"
              class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition"
            >
              📅 {{ $t("Weekly Roster") }}
            </button>
          </div>
        </div>
      </div>

      <!-- TAB 1: Real-Time Adherence (RTA) -->
      <div v-if="activeTab === 'adherence'" class="space-y-6">
        <!-- Adherence KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Live Adherence Rate") }}</span>
            <div class="flex items-baseline space-x-2 mt-1">
              <span class="text-2xl font-black" :class="liveAdherence.adherence_rate >= 90 ? 'text-emerald-600' : 'text-amber-600'">
                {{ liveAdherence.adherence_rate }}%
              </span>
              <span class="text-xs text-gray-400">Target: 90%</span>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("In Adherence") }}</span>
            <span class="text-2xl font-black text-emerald-600 block mt-1">{{ liveAdherence.adherent_count }}</span>
          </div>
          <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Out of Adherence (Drift)") }}</span>
            <span class="text-2xl font-black text-rose-600 block mt-1">{{ liveAdherence.out_of_adherence_count }}</span>
          </div>
          <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Total Agents Checked") }}</span>
            <span class="text-2xl font-black text-gray-900 dark:text-white block mt-1">{{ liveAdherence.total_agents }}</span>
          </div>
        </div>

        <!-- Adherence Agent Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Agent Live Schedule vs Actual Activity") }}</h2>
            <button
              type="button"
              @click="fetchAdherence"
              class="text-xs text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 font-semibold"
            >
              {{ $t("Refresh Live") }}
            </button>
          </div>

          <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
              <thead>
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  <th class="py-3 px-4">{{ $t("Agent") }}</th>
                  <th class="py-3 px-4">{{ $t("Today's Shift") }}</th>
                  <th class="py-3 px-4">{{ $t("Scheduled Activity") }}</th>
                  <th class="py-3 px-4">{{ $t("Actual Live Status") }}</th>
                  <th class="py-3 px-4">{{ $t("Adherence Status") }}</th>
                  <th class="py-3 px-4">{{ $t("Drift Details") }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="ag in liveAdherence.agents" :key="ag.agent_uuid" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                  <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white">
                    {{ ag.agent_name }} <span class="text-xs font-normal text-gray-500">({{ ag.agent_id || 'Ext' }})</span>
                  </td>
                  <td class="py-3.5 px-4 text-xs font-mono text-gray-600 dark:text-gray-300">
                    {{ ag.shift_time }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                      {{ ag.expected_activity }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold" :class="ag.actual_status === 'Available' ? 'bg-emerald-100 text-emerald-800' : (ag.actual_status === 'On Break' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')">
                      {{ ag.actual_status }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                      :class="ag.is_adherent ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300'"
                    >
                      {{ ag.is_adherent ? 'IN ADHERENCE' : 'OUT OF ADHERENCE' }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-xs text-rose-600 dark:text-rose-400 font-medium">
                    {{ ag.drift_reason || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 2: Erlang-C Staffing Forecast Calculator -->
      <div v-else-if="activeTab === 'forecast'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Input Parameters (5 Cols) -->
          <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h2 class="text-base font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700">
              {{ $t("Queue Volume & SLA Assumptions") }}
            </h2>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {{ $t("Expected Calls Per Hour") }}
              </label>
              <input
                type="number"
                v-model.number="calcParams.calls_per_hour"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                min="1"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {{ $t("Average Handling Time (AHT in Seconds)") }}
              </label>
              <input
                type="number"
                v-model.number="calcParams.aht_seconds"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                min="10"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Target SLA %") }}
                </label>
                <input
                  type="number"
                  v-model.number="calcParams.target_sla_percent"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                  min="10"
                  max="100"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  {{ $t("Target Wait (Sec)") }}
                </label>
                <input
                  type="number"
                  v-model.number="calcParams.target_wait_seconds"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                  min="1"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {{ $t("Shrinkage Allowance % (Breaks, Training, Leave)") }}
              </label>
              <input
                type="number"
                v-model.number="calcParams.shrinkage_percent"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                min="0"
                max="80"
              />
            </div>

            <button
              type="button"
              @click="calculateStaffing"
              :disabled="calculating"
              class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ calculating ? $t("Calculating...") : $t("Calculate Required Staffing") }}
            </button>
          </div>

          <!-- Erlang-C Output Dashboard (7 Cols) -->
          <div class="lg:col-span-7 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-5">
            <h2 class="text-base font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700">
              {{ $t("Staffing Requirement & Performance Forecast") }}
            </h2>

            <!-- Big Highlight Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-xl p-6 text-white shadow-md">
              <span class="text-xs font-semibold uppercase tracking-wider text-indigo-100 block">
                {{ $t("Total Required Roster Staff (with Shrinkage)") }}
              </span>
              <div class="flex items-baseline space-x-3 mt-1">
                <span class="text-5xl font-black tracking-tight">{{ forecastResult.total_staff_with_shrinkage }}</span>
                <span class="text-sm text-indigo-100 font-medium">{{ $t("Agents Scheduled") }}</span>
              </div>
              <p class="text-xs text-indigo-200 mt-2">
                Requires <span class="font-bold text-white">{{ forecastResult.raw_agents_required }} active agents</span> on queue lines to satisfy {{ calcParams.target_sla_percent }}% within {{ calcParams.target_wait_seconds }}s.
              </p>
            </div>

            <!-- Detailed Telemetry Metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
              <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Traffic Intensity") }}</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ forecastResult.traffic_intensity_erlangs }} Erlangs</span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Achieved Service Level") }}</span>
                <span class="text-xl font-bold text-emerald-600">{{ forecastResult.achieved_service_level }}%</span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Wait Probability P(W)") }}</span>
                <span class="text-xl font-bold text-indigo-600">{{ forecastResult.probability_of_waiting }}%</span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Avg Speed of Answer (ASA)") }}</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ forecastResult.average_speed_of_answer_sec }}s</span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Agent Occupancy") }}</span>
                <span class="text-xl font-bold text-amber-600">{{ forecastResult.agent_occupancy_percent }}%</span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Shrinkage Overhead") }}</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">+{{ forecastResult.total_staff_with_shrinkage - forecastResult.raw_agents_required }} Agents</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: Weekly Shift Roster Matrix -->
      <div v-else-if="activeTab === 'roster'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-4">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
          <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Weekly Agent Schedule Matrix") }}</h2>
            <p class="text-xs text-gray-500">{{ $t("Click on any schedule box to assign or adjust agent shifts.") }}</p>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500">Available Shifts:</span>
            <span
              v-for="s in shifts"
              :key="s.shift_uuid"
              class="text-xs px-2.5 py-0.5 rounded-full font-semibold text-white"
              :style="{ backgroundColor: s.color }"
            >
              {{ s.name }} ({{ s.start_time.slice(0, 5) }} - {{ s.end_time.slice(0, 5) }})
            </span>
          </div>
        </div>

        <!-- Weekly Calendar Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
            <thead>
              <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <th class="py-3 px-4 w-48">{{ $t("Agent") }}</th>
                <th v-for="d in rosterData.dates" :key="d" class="py-3 px-3 text-center">
                  {{ formatDayHeader(d) }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="row in rosterData.roster" :key="row.agent_uuid">
                <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                  {{ row.agent_name }}
                  <span class="text-xs font-normal text-gray-500 block">{{ row.agent_id || 'Ext' }}</span>
                </td>
                <td
                  v-for="d in rosterData.dates"
                  :key="d"
                  @click="openShiftModal(row, d)"
                  class="py-2 px-2 text-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition"
                >
                  <div
                    v-if="row.days[d]"
                    class="p-2 rounded-lg text-xs font-medium text-white shadow-xs"
                    :style="{ backgroundColor: row.days[d].shift_color || '#4F46E5' }"
                  >
                    <span class="font-bold block">{{ row.days[d].shift_name }}</span>
                    <span class="text-[10px] opacity-90 block">{{ row.days[d].start_time.slice(0, 5) }} - {{ row.days[d].end_time.slice(0, 5) }}</span>
                  </div>
                  <div v-else class="p-2 rounded-lg border border-dashed border-gray-200 dark:border-gray-700 text-xs text-gray-400">
                    + Assign
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Shift Assignment Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 max-w-md w-full p-6 space-y-4">
          <h3 class="text-base font-bold text-gray-900 dark:text-white">
            {{ $t("Assign Shift") }} - {{ selectedModalAgent?.agent_name }}
          </h3>
          <p class="text-xs text-gray-500">Date: {{ selectedModalDate }}</p>

          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
              {{ $t("Select Shift Template") }}
            </label>
            <select
              v-model="modalSelectedShiftUuid"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            >
              <option value="">{{ $t("Day Off / Unscheduled") }}</option>
              <option v-for="s in shifts" :key="s.shift_uuid" :value="s.shift_uuid">
                {{ s.name }} ({{ s.start_time.slice(0, 5) }} - {{ s.end_time.slice(0, 5) }})
              </option>
            </select>
          </div>

          <div class="flex items-center justify-end space-x-2 pt-3">
            <button
              type="button"
              @click="showModal = false"
              class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="button"
              @click="saveModalShift"
              class="px-4 py-2 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white"
            >
              {{ $t("Save Shift") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import { UserGroupIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  shifts: {
    type: Array,
    default: () => [],
  },
  roster: {
    type: Object,
    default: () => ({ dates: [], roster: [] }),
  },
  adherence: {
    type: Object,
    default: () => ({
      adherence_rate: 100,
      adherent_count: 0,
      out_of_adherence_count: 0,
      total_agents: 0,
      agents: [],
    }),
  },
  currentWeekStart: {
    type: String,
    default: "",
  },
});

const activeTab = ref("adherence");
const liveAdherence = ref({ ...props.adherence });
const rosterData = ref({ ...props.roster });

// Erlang-C Parameters
const calcParams = ref({
  calls_per_hour: 120,
  aht_seconds: 180,
  target_sla_percent: 80,
  target_wait_seconds: 20,
  shrinkage_percent: 25,
});

const calculating = ref(false);
const forecastResult = ref({
  traffic_intensity_erlangs: 6.0,
  raw_agents_required: 8,
  total_staff_with_shrinkage: 11,
  achieved_service_level: 86.4,
  probability_of_waiting: 22.1,
  average_speed_of_answer_sec: 7.2,
  agent_occupancy_percent: 75.0,
});

// Modal state
const showModal = ref(false);
const selectedModalAgent = ref(null);
const selectedModalDate = ref("");
const modalSelectedShiftUuid = ref("");

function formatDayHeader(dateStr) {
  const d = new Date(dateStr + "T00:00:00");
  const dayName = d.toLocaleDateString([], { weekday: "short" });
  const dayNum = d.getDate();
  return `${dayName} ${dayNum}`;
}

async function fetchAdherence() {
  try {
    const res = await axios.get("/api/contact-center/wfm/live-adherence");
    liveAdherence.value = res.data;
  } catch (err) {
    console.error("Failed to fetch live adherence:", err);
  }
}

async function calculateStaffing() {
  calculating.value = true;
  try {
    const res = await axios.post("/api/contact-center/wfm/calculate-staffing", calcParams.value);
    forecastResult.value = res.data;
  } catch (err) {
    alert(err.response?.data?.message || "Failed to calculate staffing");
  } finally {
    calculating.value = false;
  }
}

function openShiftModal(agentRow, date) {
  selectedModalAgent.value = agentRow;
  selectedModalDate.value = date;
  modalSelectedShiftUuid.value = agentRow.days[date]?.shift_uuid || "";
  showModal.value = true;
}

async function saveModalShift() {
  if (!selectedModalAgent.value || !selectedModalDate.value) return;
  try {
    await axios.post("/api/contact-center/wfm/save-schedule", {
      agent_uuid: selectedModalAgent.value.agent_uuid,
      date: selectedModalDate.value,
      shift_uuid: modalSelectedShiftUuid.value || null,
    });

    // Refresh roster
    const res = await axios.get("/api/contact-center/wfm/roster");
    rosterData.value = res.data;
    showModal.value = false;
  } catch (err) {
    alert(err.response?.data?.message || "Failed to save schedule");
  }
}

let adherenceTimer = null;
onMounted(() => {
  calculateStaffing();
  adherenceTimer = setInterval(fetchAdherence, 6000);
});

onUnmounted(() => {
  if (adherenceTimer) clearInterval(adherenceTimer);
});
</script>
