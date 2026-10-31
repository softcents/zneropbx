<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- Studio Header & Actions -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-cyan-400"></span>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <RocketLaunchIcon class="w-7 h-7 text-cyan-600" />
                {{ $t("Outbound Campaign & Predictive Dialer Studio") }}
              </h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("High-velocity outbound campaigns with Predictive, Progressive, and Preview dialer modes, compliant pacing, and FreeSWITCH queue delivery.") }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              type="button"
              @click="openCreateModal = true"
              class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold rounded-lg shadow-sm transition"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("New Campaign") }}
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between text-gray-500 text-xs font-medium uppercase tracking-wider">
            <span>{{ $t("Active Campaigns") }}</span>
            <PlayCircleIcon class="w-5 h-5 text-emerald-500" />
          </div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ activeCampaignsCount }}</div>
          <p class="text-xs text-gray-400 mt-1">{{ totalCampaignsCount }} {{ $t("total configured") }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between text-gray-500 text-xs font-medium uppercase tracking-wider">
            <span>{{ $t("Leads Processed") }}</span>
            <UsersIcon class="w-5 h-5 text-cyan-500" />
          </div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ totalDialedLeads }}</div>
          <p class="text-xs text-gray-400 mt-1">{{ totalLeadsCount }} {{ $t("total in pipelines") }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between text-gray-500 text-xs font-medium uppercase tracking-wider">
            <span>{{ $t("Connected Leads") }}</span>
            <PhoneArrowUpRightIcon class="w-5 h-5 text-indigo-500" />
          </div>
          <div class="mt-2 text-2xl font-bold text-indigo-600">{{ totalConnectedLeads }}</div>
          <p class="text-xs text-gray-400 mt-1">{{ overallConnectRate }}% {{ $t("connect rate") }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between text-gray-500 text-xs font-medium uppercase tracking-wider">
            <span>{{ $t("Abandon Rate Compliance") }}</span>
            <ShieldCheckIcon class="w-5 h-5 text-amber-500" />
          </div>
          <div class="mt-2 text-2xl font-bold" :class="avgAbandonRate > 3 ? 'text-rose-600' : 'text-emerald-600'">
            {{ avgAbandonRate }}%
          </div>
          <p class="text-xs text-gray-400 mt-1">{{ $t("Target: < 3.0% (TCPA Safe)") }}</p>
        </div>
      </div>

      <!-- Campaign List -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-base font-bold text-gray-900">{{ $t("Campaign Operations & Real-Time Pacing") }}</h2>
          <span class="text-xs text-gray-400">{{ campaignsList.length }} {{ $t("campaigns") }}</span>
        </div>

        <div v-if="campaignsList.length === 0" class="p-12 text-center">
          <RocketLaunchIcon class="w-12 h-12 text-gray-300 mx-auto" />
          <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ $t("No Outbound Campaigns Yet") }}</h3>
          <p class="mt-1 text-xs text-gray-500">{{ $t("Get started by launching a Predictive, Progressive, or Preview dialer campaign.") }}</p>
          <div class="mt-4">
            <button
              type="button"
              @click="openCreateModal = true"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold rounded-md shadow-sm transition"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("Create First Campaign") }}
            </button>
          </div>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div
            v-for="camp in campaignsList"
            :key="camp.campaign_uuid"
            class="p-5 hover:bg-gray-50/70 transition"
          >
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
              <!-- Campaign Details & Mode -->
              <div class="space-y-1.5 max-w-md">
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-bold text-gray-900">{{ camp.name }}</h3>
                  
                  <!-- Status Badge -->
                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="{
                      'bg-emerald-100 text-emerald-800': camp.status === 'running',
                      'bg-amber-100 text-amber-800': camp.status === 'paused',
                      'bg-gray-100 text-gray-700': camp.status === 'draft',
                      'bg-blue-100 text-blue-800': camp.status === 'completed'
                    }"
                  >
                    {{ camp.status }}
                  </span>

                  <!-- Mode Badge -->
                  <span
                    class="px-2 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider"
                    :class="{
                      'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-700/20': camp.dialer_mode === 'predictive',
                      'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-700/20': camp.dialer_mode === 'progressive',
                      'bg-amber-50 text-amber-700 ring-1 ring-amber-700/20': camp.dialer_mode === 'preview'
                    }"
                  >
                    {{ camp.dialer_mode }}
                  </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-4 text-xs text-gray-500">
                  <span v-if="camp.queue_uuid">
                    <strong>{{ $t("Target Queue:") }}</strong> {{ camp.queue?.queue_name || camp.queue_uuid }}
                  </span>
                  <span>
                    <strong>{{ $t("Pacing Ratio:") }}</strong> {{ camp.pacing_ratio }}x
                  </span>
                  <span>
                    <strong>{{ $t("Max Abandon:") }}</strong> {{ camp.max_abandon_rate }}%
                  </span>
                </div>
              </div>

              <!-- Real-time Telemetry & Pacing Stats -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 rounded-lg p-3 text-center border border-gray-100 min-w-[320px]">
                <div>
                  <div class="text-xs text-gray-400 font-medium">{{ $t("Available Agents") }}</div>
                  <div class="text-sm font-bold text-emerald-600 mt-0.5">{{ camp.agents?.available || 0 }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 font-medium">{{ $t("Busy Agents") }}</div>
                  <div class="text-sm font-bold text-gray-700 mt-0.5">{{ camp.agents?.busy || 0 }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 font-medium">{{ $t("Abandon Rate") }}</div>
                  <div
                    class="text-sm font-bold mt-0.5"
                    :class="camp.current_abandon_rate > camp.max_abandon_rate ? 'text-rose-600' : 'text-gray-900'"
                  >
                    {{ camp.current_abandon_rate }}%
                  </div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 font-medium">{{ $t("Recommended Dials") }}</div>
                  <div class="text-sm font-bold text-cyan-600 mt-0.5">{{ camp.recommended_lines_to_dial || 0 }}</div>
                </div>
              </div>

              <!-- Controls -->
              <div class="flex items-center gap-2">
                <button
                  v-if="camp.status !== 'running'"
                  type="button"
                  @click="startCampaign(camp)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md shadow-sm transition"
                >
                  <PlayIcon class="w-4 h-4" />
                  {{ $t("Start") }}
                </button>

                <button
                  v-if="camp.status === 'running'"
                  type="button"
                  @click="pauseCampaign(camp)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-md shadow-sm transition"
                >
                  <PauseIcon class="w-4 h-4" />
                  {{ $t("Pause") }}
                </button>

                <button
                  v-if="camp.status === 'running' || camp.status === 'paused'"
                  type="button"
                  @click="stopCampaign(camp)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-md shadow-sm transition"
                >
                  <StopIcon class="w-4 h-4" />
                  {{ $t("Stop") }}
                </button>

                <button
                  type="button"
                  @click="openAddLeads(camp)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-md shadow-sm transition"
                >
                  <UserPlusIcon class="w-4 h-4 text-gray-500" />
                  {{ $t("Add Leads") }}
                </button>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-4 text-xs text-gray-500">
              <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                <div
                  class="bg-cyan-500 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${camp.progress_percentage || 0}%` }"
                ></div>
              </div>
              <div class="whitespace-nowrap font-medium text-gray-700">
                {{ camp.dialed_leads || 0 }} / {{ camp.total_leads || 0 }} {{ $t("Dialed") }} ({{ camp.progress_percentage || 0 }}%)
              </div>
              <div class="text-emerald-600 font-semibold">
                {{ camp.connected_leads || 0 }} {{ $t("Connected") }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Campaign Modal -->
      <div v-if="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">{{ $t("Launch New Outbound Campaign") }}</h3>
            <button @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>

          <form @submit.prevent="submitCreateCampaign" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">{{ $t("Campaign Name") }} *</label>
              <input
                v-model="newCampaign.name"
                required
                type="text"
                placeholder="e.g. Sales VIP Outbound Q4"
                class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Dialer Mode") }} *</label>
                <select
                  v-model="newCampaign.dialer_mode"
                  class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
                >
                  <option value="predictive">{{ $t("Predictive (High Throughput)") }}</option>
                  <option value="progressive">{{ $t("Progressive (1-to-1 Safe)") }}</option>
                  <option value="preview">{{ $t("Preview (Agent Verified)") }}</option>
                </select>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Target Queue") }}</label>
                <select
                  v-model="newCampaign.queue_uuid"
                  class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
                >
                  <option value="">{{ $t("-- None / Direct --") }}</option>
                  <option v-for="q in queues" :key="q.call_center_queue_uuid" :value="q.call_center_queue_uuid">
                    {{ q.queue_name }} (Ext: {{ q.queue_extension }})
                  </option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Caller ID Name") }}</label>
                <input
                  v-model="newCampaign.caller_id_name"
                  type="text"
                  placeholder="e.g. Support Team"
                  class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Caller ID Number") }}</label>
                <input
                  v-model="newCampaign.caller_id_number"
                  type="text"
                  placeholder="e.g. +18005550199"
                  class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Pacing Ratio (Multiplier)") }}</label>
                <input
                  v-model.number="newCampaign.pacing_ratio"
                  type="number"
                  step="0.1"
                  min="1.0"
                  max="4.0"
                  class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
                />
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $t("Lines dialed per available agent in Predictive mode") }}</p>
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Max Abandon Rate (%)") }}</label>
                <input
                  v-model.number="newCampaign.max_abandon_rate"
                  type="number"
                  step="0.1"
                  min="0.5"
                  max="10.0"
                  class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500"
                />
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $t("Auto throttles if abandon exceeds target") }}</p>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
              <button
                type="button"
                @click="openCreateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50"
              >
                {{ $t("Cancel") }}
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-4 py-2 bg-cyan-600 text-white rounded-lg font-semibold hover:bg-cyan-700 disabled:opacity-50"
              >
                {{ isSubmitting ? $t("Creating...") : $t("Save Campaign") }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Leads Modal -->
      <div v-if="openLeadsModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">{{ $t("Add Leads to Campaign") }}: {{ selectedCampaign?.name }}</h3>
            <button @click="openLeadsModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>

          <form @submit.prevent="submitAddLeads" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">
                {{ $t("Paste Phone Numbers (One per line)") }} *
              </label>
              <textarea
                v-model="rawNumbersText"
                rows="6"
                placeholder="+15551234567&#10;+15559876543&#10;+15552345678"
                class="w-full rounded-lg border-gray-300 text-xs focus:ring-cyan-500 focus:border-cyan-500 font-mono"
              ></textarea>
              <p class="text-[10px] text-gray-400 mt-1">
                {{ $t("Enter E.164 standard numbers or 10-digit telephone numbers.") }}
              </p>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
              <button
                type="button"
                @click="openLeadsModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50"
              >
                {{ $t("Cancel") }}
              </button>
              <button
                type="submit"
                :disabled="isSubmittingLeads || !rawNumbersText.trim()"
                class="px-4 py-2 bg-cyan-600 text-white rounded-lg font-semibold hover:bg-cyan-700 disabled:opacity-50"
              >
                {{ isSubmittingLeads ? $t("Adding...") : $t("Import Leads") }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  RocketLaunchIcon,
  PlayCircleIcon,
  UsersIcon,
  PhoneArrowUpRightIcon,
  ShieldCheckIcon,
  PlusIcon,
  PlayIcon,
  PauseIcon,
  StopIcon,
  UserPlusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  campaigns: {
    type: Array,
    default: () => [],
  },
  queues: {
    type: Array,
    default: () => [],
  },
  contactLists: {
    type: Array,
    default: () => [],
  },
});

const campaignsList = ref([...props.campaigns]);
const openCreateModal = ref(false);
const openLeadsModal = ref(false);
const selectedCampaign = ref(null);
const isSubmitting = ref(false);
const isSubmittingLeads = ref(false);
const rawNumbersText = ref("");

const newCampaign = ref({
  name: "",
  dialer_mode: "progressive",
  queue_uuid: "",
  caller_id_name: "",
  caller_id_number: "",
  pacing_ratio: 1.2,
  max_abandon_rate: 3.0,
});

// KPI Computeds
const totalCampaignsCount = computed(() => campaignsList.value.length);
const activeCampaignsCount = computed(
  () => campaignsList.value.filter((c) => c.status === "running").length
);
const totalLeadsCount = computed(() =>
  campaignsList.value.reduce((acc, c) => acc + (c.total_leads || 0), 0)
);
const totalDialedLeads = computed(() =>
  campaignsList.value.reduce((acc, c) => acc + (c.dialed_leads || 0), 0)
);
const totalConnectedLeads = computed(() =>
  campaignsList.value.reduce((acc, c) => acc + (c.connected_leads || 0), 0)
);

const overallConnectRate = computed(() => {
  if (totalDialedLeads.value === 0) return 0;
  return Math.round((totalConnectedLeads.value / totalDialedLeads.value) * 100);
});

const avgAbandonRate = computed(() => {
  if (campaignsList.value.length === 0) return 0;
  const sum = campaignsList.value.reduce(
    (acc, c) => acc + (c.current_abandon_rate || 0),
    0
  );
  return (sum / campaignsList.value.length).toFixed(1);
});

// Actions
const submitCreateCampaign = async () => {
  isSubmitting.value = true;
  try {
    const res = await axios.post("/api/contact-center/campaigns", newCampaign.value);
    if (res.data?.success) {
      campaignsList.value.unshift(res.data.campaign);
      openCreateModal.value = false;
      newCampaign.value = {
        name: "",
        dialer_mode: "progressive",
        queue_uuid: "",
        caller_id_name: "",
        caller_id_number: "",
        pacing_ratio: 1.2,
        max_abandon_rate: 3.0,
      };
    }
  } catch (err) {
    alert(err.response?.data?.message || "Failed to create campaign.");
  } finally {
    isSubmitting.value = false;
  }
};

const startCampaign = async (camp) => {
  try {
    const res = await axios.post(`/api/contact-center/campaigns/${camp.campaign_uuid}/start`);
    if (res.data?.success) {
      camp.status = "running";
      if (res.data.telemetry) {
        Object.assign(camp, res.data.telemetry);
      }
    }
  } catch (err) {
    alert("Could not start campaign: " + (err.response?.data?.message || err.message));
  }
};

const pauseCampaign = async (camp) => {
  try {
    const res = await axios.post(`/api/contact-center/campaigns/${camp.campaign_uuid}/pause`);
    if (res.data?.success) {
      camp.status = "paused";
    }
  } catch (err) {
    alert("Could not pause campaign.");
  }
};

const stopCampaign = async (camp) => {
  try {
    const res = await axios.post(`/api/contact-center/campaigns/${camp.campaign_uuid}/stop`);
    if (res.data?.success) {
      camp.status = "completed";
    }
  } catch (err) {
    alert("Could not stop campaign.");
  }
};

const openAddLeads = (camp) => {
  selectedCampaign.value = camp;
  rawNumbersText.value = "";
  openLeadsModal.value = true;
};

const submitAddLeads = async () => {
  if (!selectedCampaign.value || !rawNumbersText.value.trim()) return;

  const lines = rawNumbersText.value
    .split("\n")
    .map((l) => l.trim())
    .filter((l) => l.length > 0);

  const leads = lines.map((phone) => ({ phone_number: phone }));

  isSubmittingLeads.value = true;
  try {
    const res = await axios.post(
      `/api/contact-center/campaigns/${selectedCampaign.value.campaign_uuid}/leads`,
      { leads }
    );
    if (res.data?.success) {
      selectedCampaign.value.total_leads = res.data.total_leads;
      openLeadsModal.value = false;
      alert(res.data.message);
    }
  } catch (err) {
    alert("Failed to add leads.");
  } finally {
    isSubmittingLeads.value = false;
  }
};
</script>
