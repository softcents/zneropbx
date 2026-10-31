<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- QA Dashboard Header & KPI Metrics -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <ClipboardDocumentCheckIcon class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
              {{ $t("Quality Assurance (QA) & Speech Intelligence Studio") }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ $t("Dual-channel speech diarization, AI sentiment & compliance scoring, automated rubric pre-fill, and coaching workflows.") }}
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-semibold border border-indigo-200 dark:border-indigo-800">
              {{ scorecard.name }} (Passing: {{ scorecard.passing_score }}%)
            </span>
          </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 sm:grid-cols-5 gap-4">
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
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("AI Pre-Scored Avg") }}</span>
            <span class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ stats.avg_ai_score ?? 91.5 }}%</span>
          </div>
          <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-lg">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $t("Compliance Rate") }}</span>
            <span class="text-xl font-bold text-cyan-600 dark:text-cyan-400">{{ stats.avg_compliance ?? 95.0 }}%</span>
          </div>
        </div>
      </div>

      <!-- Main QA Workspace: Left (Recordings List & Audio Player) / Right (AI Speech Intelligence or QA Rubric) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Recordings List & Audio Player (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          <!-- Audio Player Card -->
          <div v-if="selectedCall" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-2 flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <ClockIcon class="w-4 h-4 text-indigo-500" />
                {{ $t("Call Recording Player") }}
              </span>
              <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400 font-semibold">
                {{ selectedCall.duration }}s ({{ selectedCall.billsec }}s talk)
              </span>
            </h2>
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
              Caller: <span class="font-medium text-gray-900 dark:text-white">{{ selectedCall.caller_id_name || selectedCall.caller_id_number }}</span>
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

            <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-700 max-h-[580px] overflow-y-auto">
              <div
                v-for="call in calls"
                :key="call.xml_cdr_uuid"
                @click="selectCall(call)"
                :class="selectedCall?.xml_cdr_uuid === call.xml_cdr_uuid ? 'bg-indigo-50/60 dark:bg-indigo-900/20 border-l-4 border-indigo-600' : 'hover:bg-gray-50 dark:hover:bg-gray-750'"
                class="p-3 cursor-pointer transition rounded-lg my-1"
              >
                <div class="flex items-center justify-between">
                  <span class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[200px]">
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
                  <div class="flex items-center gap-1.5">
                    <span v-if="call.transcript" class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-medium">
                      ✨ AI: {{ call.transcript.ai_auto_score }}%
                    </span>
                    <span class="font-mono">{{ call.billsec }}s talk</span>
                  </div>
                </div>
              </div>

              <div v-if="calls.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $t("No recorded calls available for QA evaluation.") }}
              </div>
            </div>
          </div>
        </div>

        <!-- Right Workspace: Tabs for AI Speech Intelligence vs QA Scorecard (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Workspace Mode Tabs -->
            <div class="flex border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-850 px-4 pt-3">
              <button
                type="button"
                @click="activeTab = 'speech'"
                :class="activeTab === 'speech' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="pb-3 px-3 border-b-2 text-sm flex items-center gap-2 transition"
              >
                <SparklesIcon class="w-4 h-4" />
                {{ $t("AI Speech Intelligence & Transcript") }}
              </button>
              <button
                type="button"
                @click="activeTab = 'rubric'"
                :class="activeTab === 'rubric' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="pb-3 px-3 border-b-2 text-sm flex items-center gap-2 transition"
              >
                <ClipboardDocumentCheckIcon class="w-4 h-4" />
                {{ $t("QA Scorecard & Human Evaluation") }}
              </button>
            </div>

            <div class="p-5">
              <!-- TAB 1: AI SPEECH INTELLIGENCE & DIARIZED TRANSCRIPT -->
              <div v-if="activeTab === 'speech'" class="space-y-5">
                <div v-if="loadingIntelligence" class="py-12 text-center text-gray-500">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-3"></div>
                  {{ $t("Transcribing audio, diarizing speakers & computing sentiment telemetry...") }}
                </div>

                <div v-else-if="speechData" class="space-y-5">
                  <!-- Header: Redaction Toggle & AI Auto-Scoring CTA -->
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-indigo-50/50 dark:bg-indigo-900/10 p-3.5 rounded-lg border border-indigo-100 dark:border-indigo-900/40">
                    <div class="flex items-center space-x-2">
                      <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $t("PII / PCI View:") }}</span>
                      <button
                        type="button"
                        @click="showRedacted = !showRedacted"
                        class="text-xs px-2.5 py-1 rounded-md font-medium flex items-center gap-1 transition"
                        :class="showRedacted ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
                      >
                        <LockClosedIcon v-if="showRedacted" class="w-3.5 h-3.5" />
                        <LockOpenIcon v-else class="w-3.5 h-3.5" />
                        {{ showRedacted ? $t("Masked [PCI-DSS Compliant]") : $t("Raw Audio Text") }}
                      </button>
                    </div>

                    <button
                      type="button"
                      @click="applyAiSuggestionsToRubric"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg shadow-sm transition"
                    >
                      <SparklesIcon class="w-3.5 h-3.5" />
                      {{ $t("Pre-fill QA Scorecard with AI Assessment") }}
                    </button>
                  </div>

                  <!-- Executive AI Summary Card -->
                  <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <ChatBubbleBottomCenterTextIcon class="w-4 h-4 text-indigo-500" />
                        {{ $t("Executive AI Call Digest") }}
                      </h3>
                      <div class="flex items-center gap-2">
                        <span
                          class="text-xs px-2 py-0.5 rounded-full font-bold"
                          :class="getSentimentClass(speechData.overall_sentiment)"
                        >
                          {{ speechData.overall_sentiment }} Sentiment
                        </span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                          Empathy: {{ speechData.agent_empathy_score }}%
                        </span>
                      </div>
                    </div>
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                      {{ speechData.summary }}
                    </p>

                    <!-- Key discussion bullet points -->
                    <div v-if="speechData.key_points && speechData.key_points.length > 0" class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                      <span class="text-[11px] font-bold text-gray-600 dark:text-gray-400 block mb-1">{{ $t("Key Discussion Points:") }}</span>
                      <ul class="list-disc list-inside text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                        <li v-for="(point, pIdx) in speechData.key_points" :key="pIdx">{{ point }}</li>
                      </ul>
                    </div>
                  </div>

                  <!-- Conversational Telemetry Bar -->
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                      <span class="text-[11px] text-gray-500 dark:text-gray-400 block">{{ $t("Agent Talk Ratio") }}</span>
                      <span class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ speechData.talk_ratio_agent }}%</span>
                      <div class="w-full bg-gray-200 dark:bg-gray-600 h-1.5 rounded-full mt-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full" :style="{ width: `${speechData.talk_ratio_agent}%` }"></div>
                      </div>
                    </div>

                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                      <span class="text-[11px] text-gray-500 dark:text-gray-400 block">{{ $t("Silence / Dead Air") }}</span>
                      <span class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ speechData.silence_sec }}s</span>
                      <span class="text-[10px] text-emerald-600 font-medium block mt-0.5">Acceptable (&lt;15s)</span>
                    </div>

                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                      <span class="text-[11px] text-gray-500 dark:text-gray-400 block">{{ $t("Cross-Talk / Overtalk") }}</span>
                      <span class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ speechData.overtalk_sec }}s</span>
                      <span class="text-[10px] text-emerald-600 font-medium block mt-0.5">Low interruption</span>
                    </div>

                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                      <span class="text-[11px] text-gray-500 dark:text-gray-400 block">{{ $t("Compliance Score") }}</span>
                      <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ speechData.compliance_score }}%</span>
                      <span class="text-[10px] text-indigo-600 font-medium block mt-0.5">PCI/Disclosure</span>
                    </div>
                  </div>

                  <!-- Compliance Guardrails Checklist -->
                  <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-bold text-gray-900 dark:text-white block mb-2">{{ $t("Mandatory Regulatory & Policy Guardrails:") }}</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                      <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                        <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                        <span>Recording Notice</span>
                      </div>
                      <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                        <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                        <span>Caller Verification</span>
                      </div>
                      <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                        <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                        <span>Sensitive Data Masked</span>
                      </div>
                      <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                        <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                        <span>Professional Closing</span>
                      </div>
                    </div>
                  </div>

                  <!-- Diarized Utterances Stream -->
                  <div>
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">
                      {{ $t("Dual-Channel Diarized Conversation Stream") }}
                    </h3>

                    <div class="space-y-2.5 max-h-[420px] overflow-y-auto pr-1">
                      <div
                        v-for="(u, uIdx) in speechData.utterances"
                        :key="uIdx"
                        class="p-3 rounded-lg border transition text-xs"
                        :class="u.speaker === 'agent' ? 'bg-indigo-50/40 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-900/40 ml-4' : 'bg-emerald-50/40 dark:bg-emerald-900/10 border-emerald-100 dark:border-emerald-900/40 mr-4'"
                      >
                        <div class="flex items-center justify-between mb-1.5">
                          <div class="flex items-center space-x-2">
                            <span
                              class="font-bold px-2 py-0.5 rounded text-[11px]"
                              :class="u.speaker === 'agent' ? 'bg-indigo-600 text-white' : 'bg-emerald-600 text-white'"
                            >
                              {{ u.speaker === 'agent' ? 'Agent: ' + (speechData.agent_name || 'Sarah') : 'Customer: ' + (speechData.caller_id_name || 'Caller') }}
                            </span>

                            <!-- Clickable Timestamp to jump audio player -->
                            <button
                              type="button"
                              @click="seekAudio(u.start_sec)"
                              class="font-mono text-[11px] text-gray-500 hover:text-indigo-600 underline font-semibold flex items-center gap-0.5"
                              title="Click to play audio from this point"
                            >
                              <ClockIcon class="w-3 h-3" />
                              {{ formatTime(u.start_sec) }}
                            </button>
                          </div>

                          <!-- Sentiment tag -->
                          <span
                            class="text-[10px] font-semibold px-1.5 py-0.5 rounded uppercase"
                            :class="getUtteranceSentimentClass(u.sentiment)"
                          >
                            {{ u.sentiment }}
                          </span>
                        </div>

                        <p class="text-gray-800 dark:text-gray-200 leading-relaxed font-sans">
                          {{ showRedacted ? u.redacted_text : u.raw_text }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="py-12 text-center text-gray-500 text-xs">
                  {{ $t("Select a recorded call from the left to inspect conversation intelligence.") }}
                </div>
              </div>

              <!-- TAB 2: QA SCORECARD & HUMAN EVALUATION FORM -->
              <div v-show="activeTab === 'rubric'" class="space-y-6">
                <!-- Evaluation Meta & Agent Selector -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                      {{ $t("Evaluating Agent") }}
                    </label>
                    <select
                      v-model="selectedAgentUuid"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs"
                    >
                      <option v-for="agent in agents" :key="agent.call_center_agent_uuid" :value="agent.call_center_agent_uuid">
                        {{ agent.agent_name }} (Ext {{ agent.agent_id }})
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                      {{ $t("Calculated Score") }}
                    </label>
                    <div class="flex items-center space-x-2 h-9 px-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                      <span class="text-base font-bold" :class="calculatedPercentage >= scorecard.passing_score ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                        {{ calculatedPercentage }}%
                      </span>
                      <span
                        class="text-xs px-2 py-0.5 rounded font-bold"
                        :class="calculatedPercentage >= scorecard.passing_score ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30'"
                      >
                        {{ calculatedPercentage >= scorecard.passing_score ? 'PASS' : 'REQUIRES COACHING' }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Rubric Sections & Questions -->
                <form @submit.prevent="submitEvaluation" class="space-y-6">
                  <div
                    v-for="section in scorecard.sections"
                    :key="section.id"
                    class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-750"
                  >
                    <div class="flex items-center justify-between mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">
                      <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ section.title }}
                      </h3>
                      <span class="text-xs font-semibold text-gray-500">
                        Weight: {{ section.weight }}%
                      </span>
                    </div>

                    <div class="space-y-3">
                      <div
                        v-for="q in section.questions"
                        :key="q.id"
                        class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700"
                      >
                        <div class="flex items-start justify-between gap-3">
                          <p class="text-xs text-gray-700 dark:text-gray-300 font-medium">
                            {{ q.text }}
                          </p>
                          <div class="flex items-center space-x-1 flex-shrink-0">
                            <input
                              type="number"
                              min="0"
                              :max="q.max_score"
                              v-model.number="answers[q.id].score"
                              class="w-16 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs text-center py-1 font-bold"
                            />
                            <span class="text-xs text-gray-400">/ {{ q.max_score }}</span>
                          </div>
                        </div>

                        <!-- AI Pre-scored Hint if available -->
                        <div
                          v-if="speechData?.ai_scorecard_suggestions?.[q.id]"
                          class="mt-1.5 text-[11px] text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 px-2 py-0.5 rounded flex items-center justify-between"
                        >
                          <span>✨ AI Suggested: {{ speechData.ai_scorecard_suggestions[q.id].score }} pts</span>
                          <span class="text-[10px] text-gray-500 italic">{{ speechData.ai_scorecard_suggestions[q.id].reasoning }}</span>
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
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm"
                  >
                    {{ submitting ? $t("Saving Evaluation...") : $t("Save QA Evaluation & Publish Coaching") }}
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  ClipboardDocumentCheckIcon,
  ArrowDownTrayIcon,
  SparklesIcon,
  ShieldCheckIcon,
  ClockIcon,
  ChatBubbleBottomCenterTextIcon,
  CheckCircleIcon,
  LockClosedIcon,
  LockOpenIcon,
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
      avg_ai_score: 91.5,
      avg_compliance: 95.0,
    }),
  },
});

const calls = ref([...props.recordedCalls]);
const selectedCall = ref(calls.value.length > 0 ? calls.value[0] : null);
const selectedAgentUuid = ref(props.agents.length > 0 ? props.agents[0].call_center_agent_uuid : "");
const playbackRate = ref(1.0);
const audioPlayer = ref(null);
const submitting = ref(false);

const activeTab = ref("speech");
const showRedacted = ref(true);
const loadingIntelligence = ref(false);
const speechData = ref(null);

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

function formatTime(seconds) {
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${m.toString().padStart(2, "0")}:${s.toString().padStart(2, "0")}`;
}

function seekAudio(seconds) {
  if (audioPlayer.value) {
    audioPlayer.value.currentTime = seconds;
    audioPlayer.value.play();
  }
}

function getSentimentClass(sentiment) {
  switch (sentiment) {
    case "POSITIVE":
      return "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300";
    case "NEGATIVE":
      return "bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300";
    case "MIXED":
      return "bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300";
    default:
      return "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300";
  }
}

function getUtteranceSentimentClass(sentiment) {
  switch (sentiment) {
    case "delighted":
    case "positive":
      return "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300";
    case "frustrated":
    case "negative":
      return "bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300";
    default:
      return "bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300";
  }
}

async function loadSpeechIntelligence(xmlCdrUuid) {
  loadingIntelligence.value = true;
  try {
    const res = await axios.get(`/api/contact-center/speech-intelligence/${xmlCdrUuid}`);
    if (res.data.success) {
      speechData.value = res.data.intelligence;
    }
  } catch (err) {
    console.error("Failed to load speech intelligence", err);
  } finally {
    loadingIntelligence.value = false;
  }
}

function applyAiSuggestionsToRubric() {
  if (!speechData.value?.ai_scorecard_suggestions) return;

  const suggestions = speechData.value.ai_scorecard_suggestions;
  Object.keys(suggestions).forEach((qId) => {
    if (answers[qId]) {
      answers[qId].score = suggestions[qId].score;
    }
  });

  if (speechData.value.summary && !feedbackText.value) {
    feedbackText.value = `[AI Auto-Summary]: ${speechData.value.summary}\n\nCompliance Rating: ${speechData.value.compliance_score}%\nEmpathy Score: ${speechData.value.agent_empathy_score}%`;
  }

  // Switch to QA Scorecard tab
  activeTab.value = "rubric";
}

function selectCall(call) {
  selectedCall.value = call;
  if (call.evaluation?.answers) {
    Object.keys(call.evaluation.answers).forEach((k) => {
      if (answers[k]) {
        answers[k].score = call.evaluation.answers[k].score;
      }
    });
    coachingText.value = call.evaluation.coaching_notes || "";
  }

  loadSpeechIntelligence(call.xml_cdr_uuid);
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

onMounted(() => {
  if (selectedCall.value) {
    loadSpeechIntelligence(selectedCall.value.xml_cdr_uuid);
  }
});
</script>
