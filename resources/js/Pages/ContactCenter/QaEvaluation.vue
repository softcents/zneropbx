<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- QA Dashboard Header & KPI Metrics -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <ClipboardDocumentCheckIcon class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
              {{ $t("Quality Assurance (QA) & Call Evaluation") }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ $t("Review recorded calls, evaluate agent customer interactions against quality rubrics, and provide coaching.") }}
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-semibold border border-indigo-200 dark:border-indigo-800">
              {{ scorecard.name }} (Target: {{ scorecard.passing_score }}%)
            </span>
          </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Total Evaluations") }}</span>
            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_evaluations }}</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Average Quality Score") }}</span>
            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ stats.avg_score }}%</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Pass Rate") }}</span>
            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.pass_rate }}%</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Coaching Flagged") }}</span>
            <span class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ stats.coaching_needed }}</span>
          </div>
        </div>
      </div>

      <!-- Main QA Workspace: Left (Recordings List & Audio Player) / Right (Evaluation Rubric) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Recordings List & Audio Player (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          <!-- Audio Player Card (Fixed when a call is selected) -->
          <div v-if="selectedCall" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-2 flex items-center justify-between">
              <span>{{ $t("Call Recording Player") }}</span>
              <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400 font-semibold">
                {{ selectedCall.duration }}s
              </span>
            </h2>
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
              Caller: <span class="font-medium text-gray-900 dark:text-white">{{ selectedCall.caller_id_number }}</span>
              &bull; Date: <span class="font-medium text-gray-900 dark:text-white">{{ selectedCall.start_date }}</span>
            </div>

            <!-- HTML5 Audio Component with Controls -->
            <audio
              ref="audioPlayer"
              controls
              class="w-full h-10 mt-2"
              :src="selectedCall.audio_url"
            ></audio>

            <div class="flex items-center justify-between mt-3 text-xs">
              <div class="flex items-center space-x-1">
                <span class="text-gray-500">{{ $t("Speed:") }}</span>
                <button
                  type="button"
                  v-for="rate in [0.75, 1.0, 1.25, 1.5]"
                  :key="rate"
                  @click="setPlaybackRate(rate)"
                  :class="playbackRate === rate ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                  class="px-2 py-0.5 rounded font-medium"
                >
                  {{ rate }}x
                </button>
              </div>
              <a
                :href="selectedCall.audio_url"
                download
                class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline flex items-center gap-1"
              >
                <ArrowDownTrayIcon class="w-3.5 h-3.5" />
                {{ $t("Download Audio") }}
              </a>
            </div>
          </div>

          <!-- Recorded Calls List -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
              <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $t("Recorded Calls for Review") }}</h2>
              <span class="text-xs text-gray-500">{{ calls.length }} {{ $t("calls") }}</span>
            </div>

            <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-700 max-h-[550px] overflow-y-auto">
              <div
                v-for="call in calls"
                :key="call.xml_cdr_uuid"
                @click="selectCall(call)"
                :class="selectedCall?.xml_cdr_uuid === call.xml_cdr_uuid ? 'bg-indigo-50/60 dark:bg-indigo-900/20 border-l-4 border-indigo-600' : 'hover:bg-gray-50 dark:hover:bg-gray-750'"
                class="p-3 cursor-pointer transition rounded-lg my-1"
              >
                <div class="flex items-center justify-between">
                  <span class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ call.caller_id_name || call.caller_id_number || 'Inbound Call' }}
                  </span>
                  <span
                    v-if="call.evaluation"
                    class="text-xs px-2 py-0.5 rounded-full font-bold"
                    :class="call.evaluation.result === 'PASS' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300'"
                  >
                    {{ call.evaluation.percentage }}% ({{ call.evaluation.result }})
                  </span>
                  <span
                    v-else
                    class="text-xs px-2 py-0.5 rounded-full font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                  >
                    Pending QA
                  </span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                  <span>{{ call.start_date }}</span>
                  <span class="font-mono">{{ call.billsec }}s talk</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- QA Rubric Evaluation Form (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
              <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t("Evaluation Scorecard Rubric") }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ scorecard.name }}</p>
              </div>
              <!-- Real-time Score Counter -->
              <div class="text-right">
                <span class="text-2xl font-black" :class="calculatedPercentage >= scorecard.passing_score ? 'text-emerald-600' : 'text-rose-600'">
                  {{ calculatedPercentage }}%
                </span>
                <span class="text-xs block font-semibold" :class="calculatedPercentage >= scorecard.passing_score ? 'text-emerald-600' : 'text-rose-600'">
                  {{ calculatedPercentage >= scorecard.passing_score ? 'PASSED TARGET' : 'BELOW TARGET' }}
                </span>
              </div>
            </div>

            <!-- Agent Assignment -->
            <div class="mt-4 pb-4 border-b border-gray-100 dark:border-gray-700">
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {{ $t("Evaluating Agent") }} *
              </label>
              <select
                v-model="selectedAgentUuid"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
              >
                <option value="" disabled>{{ $t("Select Agent") }}</option>
                <option v-for="ag in agents" :key="ag.call_center_agent_uuid" :value="ag.call_center_agent_uuid">
                  {{ ag.agent_name }} ({{ ag.agent_id || 'Ext' }})
                </option>
              </select>
            </div>

            <!-- Questions / Criteria Sections -->
            <form @submit.prevent="submitEvaluation" class="mt-5 space-y-6">
              <div
                v-for="sec in scorecard.sections"
                :key="sec.id"
                class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700"
              >
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ sec.title }}</h3>
                  <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Weight: {{ sec.weight }}%</span>
                </div>

                <div class="space-y-4">
                  <div
                    v-for="q in sec.questions"
                    :key="q.id"
                    class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700"
                  >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                      <p class="text-xs text-gray-800 dark:text-gray-200 font-medium max-w-md">
                        {{ q.text }}
                      </p>
                      <!-- Score Slider / Input -->
                      <div class="flex items-center space-x-2">
                        <input
                          type="range"
                          min="0"
                          :max="q.max_score"
                          step="1"
                          v-model.number="answers[q.id].score"
                          class="w-24 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-indigo-600"
                        />
                        <span class="text-xs font-bold font-mono w-10 text-right text-gray-900 dark:text-white">
                          {{ answers[q.id].score }}/{{ q.max_score }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- General Feedback & Coaching Notes -->
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    {{ $t("Supervisor Feedback & Strengths") }}
                  </label>
                  <textarea
                    v-model="feedbackText"
                    rows="3"
                    :placeholder="$t('Highlight what the agent did well during the call...')"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    {{ $t("Actionable Coaching Notes & Improvement Areas") }}
                  </label>
                  <textarea
                    v-model="coachingText"
                    rows="3"
                    :placeholder="$t('Provide clear steps for the agent to enhance customer interaction quality...')"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs"
                  ></textarea>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="submitting || !selectedCall || !selectedAgentUuid"
                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ submitting ? $t("Saving Evaluation...") : $t("Save QA Evaluation & Publish Coaching") }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, reactive } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  ClipboardDocumentCheckIcon,
  ArrowDownTrayIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  scorecard: {
    type: Object,
    required: true,
  },
  recordedCalls: {
    type: Array,
    default: () => [],
  },
  agents: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total_evaluations: 0,
      avg_score: 0,
      pass_rate: 100,
      coaching_needed: 0,
    }),
  },
});

const calls = ref([...props.recordedCalls]);
const selectedCall = ref(calls.value.length > 0 ? calls.value[0] : null);
const selectedAgentUuid = ref(props.agents.length > 0 ? props.agents[0].call_center_agent_uuid : "");
const playbackRate = ref(1.0);
const audioPlayer = ref(null);
const submitting = ref(false);

const feedbackText = ref("");
const coachingText = ref("");

// Initialize answers reactive map
const answers = reactive({});
props.scorecard.sections?.forEach((sec) => {
  sec.questions?.forEach((q) => {
    answers[q.id] = { score: q.max_score };
  });
});

const calculatedPercentage = computed(() => {
  let earned = 0;
  let max = 0;
  props.scorecard.sections?.forEach((sec) => {
    sec.questions?.forEach((q) => {
      max += q.max_score;
      if (answers[q.id]) {
        earned += Number(answers[q.id].score || 0);
      }
    });
  });
  return max > 0 ? Math.round((earned / max) * 100) : 0;
});

function selectCall(call) {
  selectedCall.value = call;
  if (call.evaluation?.answers) {
    // Populate previous evaluation
    Object.keys(call.evaluation.answers).forEach((k) => {
      if (answers[k]) {
        answers[k].score = call.evaluation.answers[k].score;
      }
    });
    coachingText.value = call.evaluation.coaching_notes || "";
  }
}

function setPlaybackRate(rate) {
  playbackRate.value = rate;
  if (audioPlayer.value) {
    audioPlayer.value.playbackRate = rate;
  }
}

async function submitEvaluation() {
  if (!selectedCall.value || !selectedAgentUuid.value) return;
  submitting.value = true;
  try {
    const res = await axios.post("/api/contact-center/qa/evaluate", {
      scorecard_uuid: props.scorecard.qa_scorecard_uuid,
      xml_cdr_uuid: selectedCall.value.xml_cdr_uuid,
      agent_uuid: selectedAgentUuid.value,
      answers: answers,
      feedback: feedbackText.value,
      coaching_notes: coachingText.value,
    });

    alert(res.data.message || "Evaluation saved successfully!");
    if (res.data.evaluation) {
      selectedCall.value.evaluation = res.data.evaluation;
    }
  } catch (err) {
    alert(err.response?.data?.message || err.response?.data?.error || "Failed to submit evaluation");
  } finally {
    submitting.value = false;
  }
}
</script>
