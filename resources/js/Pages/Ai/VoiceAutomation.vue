<template>
  <MainLayout>
    <div class="min-h-screen bg-slate-950 text-slate-100 pb-16">
      <!-- Dark Luxury Spotlight Banner -->
      <div class="relative overflow-hidden border-b border-purple-500/20 bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 px-6 py-8 shadow-2xl">
        <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-purple-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative mx-auto max-w-7xl">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-400 ring-1 ring-inset ring-purple-500/30">
                  <span class="h-1.5 w-1.5 rounded-full bg-purple-400 animate-pulse"></span>
                  P8 — AI Voice & Autonomous Automation
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/30">
                  Sub-300ms Turnaround
                </span>
              </div>
              <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                AI Voice Agent & Autonomous Automation Platform
              </h1>
              <p class="mt-1 text-sm text-slate-400">
                Real-time streaming speech recognition & neural synthesis, RAG knowledge ingestion, sentiment-triggered human handoff, and BYOK quota metering.
              </p>
            </div>

            <!-- Quick Navigation Back to CC Suite & Dashboard -->
            <div class="flex flex-wrap items-center gap-2">
              <a href="/dashboard" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800/80 px-3 py-2 text-xs font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition ring-1 ring-slate-700">
                ← Dashboard
              </a>
              <a href="/contact-center/supervisor-command-center" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600/20 px-3 py-2 text-xs font-medium text-indigo-300 hover:bg-indigo-600/30 transition ring-1 ring-indigo-500/30">
                Supervisor CC
              </a>
              <a href="/messaging/sms-voice-studio" class="inline-flex items-center gap-1.5 rounded-lg bg-pink-600/20 px-3 py-2 text-xs font-medium text-pink-300 hover:bg-pink-600/30 transition ring-1 ring-pink-500/30">
                SMS Studio
              </a>
            </div>
          </div>

          <!-- Real-Time Metrics Strip -->
          <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-purple-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Active AI Agents</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-white">{{ metrics.active_agents }}</span>
                <span class="text-xs text-purple-400 font-mono">/ {{ metrics.total_agents }} configured</span>
              </div>
            </div>

            <div class="rounded-xl border border-emerald-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Calls Handled</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-emerald-400">{{ metrics.total_calls_handled.toLocaleString() }}</span>
                <span class="text-xs text-slate-400">sessions</span>
              </div>
            </div>

            <div class="rounded-xl border border-blue-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Turnaround Latency</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-blue-400">{{ metrics.avg_turnaround_latency_ms }}</span>
                <span class="text-xs text-emerald-400 font-mono">ms (&lt; 300ms SLA)</span>
              </div>
            </div>

            <div class="rounded-xl border border-indigo-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">RAG Documents</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-indigo-400">{{ metrics.rag_documents_indexed }}</span>
                <span class="text-xs text-slate-400">Vector Indexed</span>
              </div>
            </div>

            <div class="rounded-xl border border-amber-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Monthly AI Spend</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-amber-400">${{ Number(metrics.monthly_spend_usd).toFixed(2) }}</span>
                <span class="text-xs text-slate-400">/ ${{ Number(metrics.budget_ceiling_usd).toFixed(0) }}</span>
              </div>
            </div>

            <div class="rounded-xl border border-pink-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Monthly Tokens</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-pink-400">{{ (metrics.monthly_token_consumption / 1000000).toFixed(1) }}M</span>
                <span class="text-xs text-slate-400 font-mono">/ 50M quota</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Operational Body -->
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
        <!-- Tab Navigation -->
        <div class="flex overflow-x-auto border-b border-slate-800 pb-px space-x-2">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-purple-500 text-purple-400 bg-purple-500/10'
                : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700',
              'whitespace-nowrap rounded-t-lg border-b-2 px-4 py-2.5 text-xs font-semibold transition flex items-center gap-2'
            ]"
          >
            <span>{{ tab.icon }}</span>
            <span>{{ tab.name }}</span>
            <span v-if="tab.badge" class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] text-slate-300 ring-1 ring-slate-700">
              {{ tab.badge }}
            </span>
          </button>
        </div>

        <!-- TAB 1: INTERACTIVE VOICE SIMULATOR -->
        <div v-if="activeTab === 'simulator'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Simulation Playground Input -->
            <div class="lg:col-span-7 space-y-4">
              <div class="rounded-xl border border-purple-500/30 bg-slate-900/80 p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>🎙️</span> AI Voice Agent Interactive Audio Playground
                  </h3>
                  <span class="rounded bg-purple-950 px-2 py-0.5 text-[10px] font-mono text-purple-400 font-semibold">
                    Realtime Streaming
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                  <div>
                    <label class="block font-medium text-slate-300">Selected Voice Agent Persona</label>
                    <select
                      v-model="simTurn.agent_uuid"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-purple-500 focus:outline-none"
                    >
                      <option v-for="agent in agents" :key="agent.uuid" :value="agent.uuid">
                        {{ agent.agent_name }} (Ext {{ agent.assigned_extension }})
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block font-medium text-slate-300">Active LLM & Audio Stack</label>
                    <div class="mt-1 p-2 rounded-lg bg-slate-800/80 border border-slate-700 text-slate-300 font-mono text-[11px] truncate">
                      GPT-4o Realtime + ElevenLabs Neural
                    </div>
                  </div>
                </div>

                <!-- Caller Voice Utterance Input -->
                <div class="space-y-1.5 text-xs">
                  <label class="font-medium text-slate-300">Caller Voice Input (Simulated Audio Stream)</label>
                  <textarea
                    v-model="simTurn.caller_utterance"
                    rows="3"
                    placeholder="Speak or type caller query..."
                    class="block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-purple-500 focus:outline-none"
                  ></textarea>
                </div>

                <!-- Quick Presets -->
                <div class="flex flex-wrap gap-2 text-[11px]">
                  <span class="text-slate-400">Quick Test Prompts:</span>
                  <button
                    @click="setUtterance('What is our outbound call termination rate for calling London?')"
                    class="rounded bg-slate-800 px-2 py-0.5 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                  >
                    Rates Inquiry (RAG)
                  </button>
                  <button
                    @click="setUtterance('How do we configure our Yealink desk phones via auto provisioning?')"
                    class="rounded bg-slate-800 px-2 py-0.5 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                  >
                    Yealink Setup (Tech)
                  </button>
                  <button
                    @click="setUtterance('I have an unauthorized charge on my invoice! Transfer me to a person right now!')"
                    class="rounded bg-slate-800 px-2 py-0.5 text-rose-300 hover:text-white hover:bg-rose-950 transition ring-1 ring-rose-800"
                  >
                    Human Handoff Trigger
                  </button>
                </div>

                <button
                  @click="runVoiceSimulation"
                  :disabled="isSimulating"
                  class="w-full inline-flex justify-center items-center gap-1.5 rounded-lg bg-purple-600 px-4 py-2.5 font-semibold text-white shadow-lg hover:bg-purple-500 transition disabled:opacity-50"
                >
                  <span v-if="isSimulating" class="animate-spin">⏳</span>
                  <span>{{ isSimulating ? 'Processing Speech & RAG...' : 'Simulate Voice Dialogue Turn' }}</span>
                </button>
              </div>
            </div>

            <!-- Right: Turnaround & Audio Playback Output -->
            <div class="lg:col-span-5 space-y-4">
              <div v-if="turnResult" class="rounded-xl border border-purple-500/30 bg-slate-900/90 p-5 shadow-xl space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                  <span class="font-bold text-sm text-purple-300">{{ turnResult.agent_name }}</span>
                  <span
                    :class="[
                      turnResult.hand_off_triggered ? 'bg-amber-500/10 text-amber-400 ring-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30',
                      'rounded-full px-2 py-0.5 text-[10px] font-bold ring-1'
                    ]"
                  >
                    {{ turnResult.hand_off_triggered ? 'WARM HANDOFF ENGAGED' : 'AI RESOLVED' }}
                  </span>
                </div>

                <!-- Spoken AI Audio Message -->
                <div class="rounded-lg bg-purple-950/40 p-3.5 border border-purple-500/20 space-y-1">
                  <div class="text-[10px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1">
                    <span>🔊</span> Spoken Audio Output
                  </div>
                  <p class="text-xs text-white leading-relaxed">
                    "{{ turnResult.spoken_response }}"
                  </p>
                </div>

                <!-- Handoff Notification if Triggered -->
                <div v-if="turnResult.hand_off_triggered" class="rounded-lg bg-amber-950/40 p-3 border border-amber-500/30 text-xs text-amber-300 space-y-1">
                  <div class="font-bold flex items-center gap-1">
                    <span>📞</span> Warm Transfer Executed → {{ turnResult.handoff_destination }}
                  </div>
                  <div class="text-[11px] text-amber-200/80">{{ turnResult.handoff_reason }}</div>
                </div>

                <!-- Latency Telemetry Breakdown -->
                <div class="pt-1">
                  <div class="text-[11px] font-semibold text-slate-400 mb-1.5 flex justify-between">
                    <span>Streaming Latency Telemetry</span>
                    <span class="text-emerald-400 font-mono font-bold">{{ turnResult.latency_breakdown?.total_latency_ms }} ms Total</span>
                  </div>

                  <div class="grid grid-cols-3 gap-2 text-center font-mono text-[11px]">
                    <div class="rounded bg-slate-800 p-2">
                      <div class="text-[10px] text-slate-500">STT Stream</div>
                      <div class="text-blue-400 font-bold">{{ turnResult.latency_breakdown?.stt_ms }} ms</div>
                    </div>
                    <div class="rounded bg-slate-800 p-2">
                      <div class="text-[10px] text-slate-500">LLM Inference</div>
                      <div class="text-purple-400 font-bold">{{ turnResult.latency_breakdown?.llm_inference_ms }} ms</div>
                    </div>
                    <div class="rounded bg-slate-800 p-2">
                      <div class="text-[10px] text-slate-500">TTS Audio</div>
                      <div class="text-emerald-400 font-bold">{{ turnResult.latency_breakdown?.tts_synthesis_ms }} ms</div>
                    </div>
                  </div>
                </div>

                <!-- RAG Source Attribution -->
                <div v-if="turnResult.rag_source_document" class="pt-2 border-t border-slate-800 text-xs">
                  <span class="text-slate-400">RAG Vector Attribution:</span>
                  <div class="mt-1 flex items-center justify-between rounded bg-slate-800/80 p-2 text-[11px]">
                    <span class="font-semibold text-cyan-300 truncate max-w-[220px]">{{ turnResult.rag_source_document.title }}</span>
                    <span class="font-mono text-emerald-400 font-bold">{{ (turnResult.rag_source_document.similarity_score * 100).toFixed(0) }}% Match</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: AGENT PERSONAS & PROMPT STUDIO -->
        <div v-if="activeTab === 'agents'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              v-for="ag in agents"
              :key="ag.uuid"
              class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg space-y-3"
            >
              <div class="flex items-center justify-between">
                <span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-400 ring-1 ring-emerald-500/30">
                  {{ ag.status.toUpperCase() }}
                </span>
                <span class="text-xs font-mono font-bold text-purple-400">Ext {{ ag.assigned_extension }}</span>
              </div>

              <h4 class="text-sm font-bold text-white">{{ ag.agent_name }}</h4>
              <p class="text-xs text-slate-400">{{ ag.persona_role }}</p>

              <div class="rounded bg-slate-950 p-2.5 text-[11px] font-mono text-slate-300 max-h-24 overflow-y-auto">
                "{{ ag.system_prompt }}"
              </div>

              <div class="space-y-1 text-xs text-slate-300 font-mono pt-1">
                <div class="flex justify-between">
                  <span class="text-slate-500">Model:</span>
                  <span class="text-cyan-300">{{ ag.primary_model }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Voice Synthesis:</span>
                  <span class="text-slate-200">{{ ag.tts_voice }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">CSAT Score:</span>
                  <span class="text-amber-400 font-bold">★ {{ ag.csat_score }} / 5.0</span>
                </div>
              </div>

              <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-[11px]">
                <span class="text-slate-400 font-mono">Prompt: {{ ag.prompt_version }}</span>
                <button
                  @click="openPromptModal(ag)"
                  class="rounded bg-slate-800 px-2.5 py-1 text-purple-400 font-semibold hover:bg-slate-700 transition"
                >
                  Edit System Prompt
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: RAG KNOWLEDGE BASE DOCUMENTS -->
        <div v-if="activeTab === 'rag_docs'" class="mt-6 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>📚</span> RAG Vector Knowledge Base & Document Index
              </h3>
              <p class="text-xs text-slate-400">
                Documents dynamically retrieved and cited by AI voice agents during customer dialogue turns.
              </p>
            </div>

            <!-- Search Query Test -->
            <div class="flex items-center gap-2">
              <input
                v-model="ragSearchQuery"
                type="text"
                placeholder="Search knowledge base..."
                class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white shadow-sm focus:border-purple-500 focus:outline-none"
              />
              <button
                @click="searchKnowledge"
                class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-purple-500 transition shadow"
              >
                Vector Query
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="doc in knowledgeDocs"
              :key="doc.uuid"
              class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 shadow space-y-3"
            >
              <div class="flex items-center justify-between">
                <span class="rounded bg-purple-950 px-2 py-0.5 text-[10px] font-mono text-purple-400 uppercase font-bold">
                  {{ doc.category }}
                </span>
                <span class="text-emerald-400 text-xs font-bold">✓ {{ doc.vector_index_status.toUpperCase() }}</span>
              </div>

              <h4 class="text-sm font-bold text-white">{{ doc.document_title }}</h4>
              <p class="text-xs text-slate-400 leading-relaxed">
                {{ doc.document_content }}
              </p>

              <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-500 font-mono">
                <span>{{ doc.chunk_count }} Vector Chunks</span>
                <span>Freshness: {{ doc.freshness_date }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: SESSIONS & AUDIT LOGS -->
        <div v-if="activeTab === 'sessions'" class="mt-6 space-y-6">
          <h3 class="text-sm font-bold text-white flex items-center gap-2">
            <span>📜</span> Recent Autonomous AI Voice Dialogue Sessions
          </h3>

          <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 shadow">
            <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
              <thead class="bg-slate-900/90 text-slate-400 font-medium">
                <tr>
                  <th class="px-3.5 py-2.5">Caller / Time</th>
                  <th class="px-3 py-2.5">Agent</th>
                  <th class="px-3 py-2.5">Summary</th>
                  <th class="px-3 py-2.5">Latency</th>
                  <th class="px-3 py-2.5">Sentiment</th>
                  <th class="px-3 py-2.5 text-right">Cost</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 text-slate-300">
                <tr v-for="s in sessions" :key="s.uuid" class="hover:bg-slate-800/40">
                  <td class="px-3.5 py-3">
                    <div class="font-mono font-bold text-white">{{ s.caller_phone }}</div>
                    <div class="text-[10px] text-slate-500">{{ s.caller_name || 'Anonymous' }}</div>
                  </td>
                  <td class="px-3 py-3 font-semibold text-purple-300">
                    {{ s.agent?.agent_name || 'Maya' }}
                  </td>
                  <td class="px-3 py-3 max-w-[280px] text-slate-400">
                    <div class="truncate">{{ s.call_summary }}</div>
                    <span v-if="s.hand_off_triggered" class="inline-block rounded bg-amber-950 px-1.5 py-0.2 text-[10px] text-amber-400 font-bold">
                      Handoff: {{ s.handoff_reason }}
                    </span>
                  </td>
                  <td class="px-3 py-3 font-mono">
                    <span class="text-emerald-400 font-bold">{{ s.total_latency_ms }} ms</span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      :class="[
                        s.sentiment_score >= 0.5 ? 'text-emerald-400' :
                        s.sentiment_score >= 0 ? 'text-blue-400' : 'text-rose-400',
                        'font-bold font-mono'
                      ]"
                    >
                      {{ s.sentiment_score >= 0 ? '+' : '' }}{{ s.sentiment_score }}
                    </span>
                  </td>
                  <td class="px-3 py-3 text-right font-mono font-bold text-slate-200">
                    ${{ Number(s.cost_usd).toFixed(4) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 5: QUOTAS & BYOK PROVIDERS -->
        <div v-if="activeTab === 'quotas'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left: Providers Vault -->
            <div class="space-y-4">
              <h4 class="text-sm font-bold text-white flex items-center gap-2">
                <span>🔑</span> BYOK Provider Connectors & Models
              </h4>

              <div class="space-y-3">
                <div
                  v-for="pr in providers"
                  :key="pr.uuid"
                  class="rounded-xl border border-slate-800 bg-slate-900/80 p-4 space-y-2"
                >
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-white text-xs">{{ pr.provider_name }}</span>
                    <span class="rounded bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-400">ACTIVE</span>
                  </div>
                  <div class="text-xs text-slate-400 font-mono">Key: {{ pr.api_key_masked }}</div>
                  <div class="flex justify-between text-xs text-slate-300 font-mono">
                    <span>Rate: ${{ Number(pr.cost_per_minute_usd).toFixed(4) }}/min</span>
                    <span>Priority: #{{ pr.priority_order }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Quota Metering -->
            <div class="space-y-4">
              <h4 class="text-sm font-bold text-white flex items-center gap-2">
                <span>📊</span> Monthly Token & Spend Quota
              </h4>

              <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 space-y-4">
                <div class="space-y-1.5">
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-400">Monthly Tokens Consumed</span>
                    <span class="font-mono font-bold text-white">{{ (quota?.tokens_consumed_month / 1000000).toFixed(2) }}M / {{ (quota?.monthly_token_quota / 1000000).toFixed(0) }}M</span>
                  </div>
                  <div class="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
                    <div
                      class="h-full bg-purple-500 rounded-full"
                      :style="{ width: `${Math.min(100, (quota?.tokens_consumed_month / quota?.monthly_token_quota) * 100)}%` }"
                    ></div>
                  </div>
                </div>

                <div class="space-y-1.5">
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-400">Spend vs Monthly Ceiling</span>
                    <span class="font-mono font-bold text-amber-400">${{ Number(quota?.monthly_spend_usd).toFixed(2) }} / ${{ Number(quota?.spend_budget_ceiling_usd).toFixed(0) }}</span>
                  </div>
                  <div class="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
                    <div
                      class="h-full bg-amber-500 rounded-full"
                      :style="{ width: `${Math.min(100, (quota?.monthly_spend_usd / quota?.spend_budget_ceiling_usd) * 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- EDIT PROMPT MODAL -->
      <div v-if="showPromptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="w-full max-w-xl rounded-2xl border border-purple-500/40 bg-slate-900 p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span>✏️</span> Edit Voice Agent System Prompt
            </h3>
            <button @click="showPromptModal = false" class="text-slate-400 hover:text-white">✕</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-medium text-slate-300">Prompt Version</label>
              <input
                v-model="editForm.version"
                type="text"
                class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 font-mono text-white"
              />
            </div>
            <div>
              <label class="block font-medium text-slate-300">System Prompt</label>
              <textarea
                v-model="editForm.prompt"
                rows="5"
                class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white font-mono"
              ></textarea>
            </div>

            <button
              @click="savePrompt"
              class="w-full rounded-lg bg-purple-600 px-4 py-2 text-xs font-semibold text-white hover:bg-purple-500 transition shadow"
            >
              Save & Roll Out Prompt Version
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import MainLayout from '../../Layouts/MainLayout.vue'

const props = defineProps({
  agents: { type: Array, default: () => [] },
  knowledgeDocs: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
  sessions: { type: Array, default: () => [] },
  quota: { type: Object, default: () => ({}) },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('simulator')

const tabs = [
  { id: 'simulator', name: 'Voice Turnaround Simulator', icon: '🎙️', badge: 'Sub-300ms' },
  { id: 'agents', name: 'Agent Personas & Prompts', icon: '🤖', badge: `${props.agents.length || 0}` },
  { id: 'rag_docs', name: 'RAG Knowledge Documents', icon: '📚', badge: `${props.knowledgeDocs.length || 0}` },
  { id: 'sessions', name: 'Dialogue Sessions & Logs', icon: '📜', badge: `${props.sessions.length || 0}` },
  { id: 'quotas', name: 'Quotas & BYOK Models', icon: '🔑', badge: 'Active' },
]

// Voice Turn Simulation
const simTurn = ref({
  agent_uuid: props.agents[0]?.uuid || '',
  caller_utterance: 'What is our outbound call termination rate for calling London?',
})

const isSimulating = ref(false)
const turnResult = ref(null)

const setUtterance = (text) => {
  simTurn.value.caller_utterance = text
}

const runVoiceSimulation = async () => {
  isSimulating.value = true
  try {
    const res = await axios.post('/api/ai/voice-automation/simulate-turn', simTurn.value)
    turnResult.value = res.data
  } catch (err) {
    alert('Voice simulation error: ' + (err.response?.data?.message || err.message))
  } finally {
    isSimulating.value = false
  }
}

// RAG Search
const ragSearchQuery = ref('')
const searchKnowledge = async () => {
  if (!ragSearchQuery.value) return
  try {
    const res = await axios.post('/api/ai/voice-automation/search-knowledge', {
      query: ragSearchQuery.value,
    })
    alert('Top RAG Match: ' + res.data[0]?.title + ' (' + (res.data[0]?.similarity_score * 100).toFixed(0) + '% confidence)')
  } catch (err) {
    alert('Search error: ' + (err.response?.data?.message || err.message))
  }
}

// Edit Prompt Modal
const showPromptModal = ref(false)
const editForm = ref({
  agent_uuid: '',
  version: '',
  prompt: '',
})

const openPromptModal = (ag) => {
  editForm.value.agent_uuid = ag.uuid
  editForm.value.version = ag.prompt_version
  editForm.value.prompt = ag.system_prompt
  showPromptModal.value = true
}

const savePrompt = async () => {
  try {
    const res = await axios.post(`/api/ai/voice-automation/agents/${editForm.value.agent_uuid}/prompt`, {
      system_prompt: editForm.value.prompt,
      prompt_version: editForm.value.version,
    })
    const agent = props.agents.find(a => a.uuid === editForm.value.agent_uuid)
    if (agent) {
      agent.system_prompt = res.data.system_prompt
      agent.prompt_version = res.data.prompt_version
    }
    showPromptModal.value = false
  } catch (err) {
    alert('Failed to save prompt: ' + (err.response?.data?.message || err.message))
  }
}
</script>
