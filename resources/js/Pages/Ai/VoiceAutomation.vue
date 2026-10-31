<template>
  <MainLayout>
    <div class="m-3 space-y-6">
      <!-- Clean Native Software Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-md bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700 ring-1 ring-inset ring-purple-700/10">
                <span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>
                P3 — AI Telephony &amp; Autonomous Agents
              </span>
              <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-700/10">
                Turnaround &lt; 280ms
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">
              AI Voice Automation &amp; Autonomous Agent Studio
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Ultra-low latency streaming voice bots (STT + LLM + TTS), RAG enterprise knowledge retrieval, warm agent transfer, and BYOK token quotas.
            </p>
          </div>

          <!-- Quick Navigation -->
          <div class="flex flex-wrap items-center gap-2">
            <a href="/dashboard" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
              ← Dashboard
            </a>
            <a href="/contact-center/supervisor-command-center" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
              Live Ops
            </a>
            <a href="/telecom/routing-intelligence" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
              Carrier LCR
            </a>
          </div>
        </div>

        <!-- Real-Time Metrics Strip -->
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 border-t border-gray-100 pt-4">
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Active Personas</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-gray-900">{{ metrics.active_agents }}</span>
              <span class="text-xs text-purple-600 font-mono">Agents</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Turnaround Latency</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-emerald-600">{{ metrics.avg_turnaround_latency_ms }}</span>
              <span class="text-xs text-emerald-600">ms</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Resolution Rate</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-indigo-600">{{ metrics.resolution_rate_pct }}%</span>
              <span class="text-xs text-gray-400">Autonomous</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Warm Handoffs</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-amber-600">{{ metrics.handoff_rate_pct }}%</span>
              <span class="text-xs text-gray-400">Live Desk</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">RAG Knowledge Docs</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-teal-600">{{ metrics.rag_indexed_documents }}</span>
              <span class="text-xs text-teal-600">Vectors</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Avg Customer CSAT</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-amber-600">★ {{ metrics.csat_average }}</span>
              <span class="text-xs text-gray-400">/ 5.0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-4 overflow-x-auto" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-indigo-600 text-indigo-600 font-bold'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium',
              'whitespace-nowrap border-b-2 py-3 px-3 text-sm transition flex items-center gap-2'
            ]"
          >
            <span>{{ tab.icon }}</span>
            <span>{{ tab.name }}</span>
            <span v-if="tab.badge" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 ring-1 ring-inset ring-gray-200">
              {{ tab.badge }}
            </span>
          </button>
        </nav>
      </div>

      <!-- TAB 1: INTERACTIVE VOICE SIMULATOR -->
      <div v-if="activeTab === 'simulator'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left: Simulation Playground Input -->
          <div class="lg:col-span-7 space-y-4">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-4">
              <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <span>🎙️</span> AI Voice Agent Interactive Audio Playground
                </h3>
                <span class="rounded bg-purple-50 px-2 py-0.5 text-[10px] font-mono text-purple-700 ring-1 ring-purple-700/10 font-semibold">
                  Realtime Streaming
                </span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                  <label class="block font-medium text-gray-700">Selected Voice Agent Persona</label>
                  <select
                    v-model="simTurn.agent_uuid"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
                  >
                    <option v-for="agent in agents" :key="agent.uuid" :value="agent.uuid">
                      {{ agent.agent_name }} (Ext {{ agent.assigned_extension }})
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block font-medium text-gray-700">Active LLM &amp; Audio Stack</label>
                  <div class="mt-1 p-2 rounded-md bg-gray-50 border border-gray-200 text-gray-700 font-mono text-[11px] truncate">
                    GPT-4o Realtime + ElevenLabs Neural
                  </div>
                </div>
              </div>

              <!-- Caller Voice Utterance Input -->
              <div class="space-y-1.5 text-xs">
                <label class="font-medium text-gray-700">Caller Voice Input (Simulated Audio Stream)</label>
                <textarea
                  v-model="simTurn.caller_utterance"
                  rows="3"
                  placeholder="Speak or type caller query..."
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600"
                ></textarea>
              </div>

              <!-- Quick Presets -->
              <div class="flex flex-wrap gap-2 text-[11px]">
                <span class="text-gray-500">Quick Test Prompts:</span>
                <button
                  @click="setUtterance('What is our outbound call termination rate for calling London?')"
                  class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition"
                >
                  Rates Inquiry (RAG)
                </button>
                <button
                  @click="setUtterance('How do we configure our Yealink desk phones via auto provisioning?')"
                  class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition"
                >
                  Yealink Setup (Tech)
                </button>
                <button
                  @click="setUtterance('I have an unauthorized charge on my invoice! Transfer me to a person right now!')"
                  class="rounded bg-rose-50 px-2 py-0.5 text-rose-700 hover:bg-rose-100 transition"
                >
                  Billing Escalation (Handoff)
                </button>
              </div>

              <div class="pt-2 flex justify-end">
                <button
                  @click="runVoiceSimulation"
                  :disabled="isSimulatingTurn"
                  class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50 text-xs"
                >
                  <span v-if="isSimulatingTurn" class="animate-spin">⏳</span>
                  <span>{{ isSimulatingTurn ? 'Streaming Turnaround...' : 'Send Audio Stream &amp; Synthesize' }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Right: Real-Time Audio Response & Telemetry Card -->
          <div class="lg:col-span-5 space-y-4">
            <div v-if="turnResult" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
              <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                <span class="font-bold text-sm text-gray-900">{{ turnResult.agent_name }}</span>
                <span
                  :class="[
                    turnResult.hand_off_triggered ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                    'rounded-full px-2 py-0.5 text-[10px] font-bold ring-1'
                  ]"
                >
                  {{ turnResult.hand_off_triggered ? 'WARM HANDOFF ENGAGED' : 'AI RESOLVED' }}
                </span>
              </div>

              <!-- Spoken AI Audio Message -->
              <div class="rounded-lg bg-indigo-50/60 p-3.5 border border-indigo-100 space-y-1">
                <div class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1">
                  <span>🔊</span> Spoken Audio Output
                </div>
                <p class="text-xs text-gray-900 leading-relaxed font-medium">
                  "{{ turnResult.spoken_response }}"
                </p>
              </div>

              <!-- Handoff Notification if Triggered -->
              <div v-if="turnResult.hand_off_triggered" class="rounded-lg bg-amber-50 p-3 border border-amber-200 text-xs text-amber-900 space-y-1">
                <div class="font-bold flex items-center gap-1">
                  <span>📞</span> Warm Transfer Executed → {{ turnResult.handoff_destination }}
                </div>
                <div class="text-[11px] text-amber-700">{{ turnResult.handoff_reason }}</div>
              </div>

              <!-- Latency Telemetry Breakdown -->
              <div class="pt-1">
                <div class="text-[11px] font-semibold text-gray-500 mb-1.5 flex justify-between">
                  <span>Streaming Latency Telemetry</span>
                  <span class="text-emerald-700 font-mono font-bold">{{ turnResult.latency_breakdown?.total_latency_ms }} ms Total</span>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center font-mono text-[11px]">
                  <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                    <div class="text-[10px] text-gray-500">STT Stream</div>
                    <div class="text-indigo-600 font-bold">{{ turnResult.latency_breakdown?.stt_ms }} ms</div>
                  </div>
                  <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                    <div class="text-[10px] text-gray-500">LLM Inference</div>
                    <div class="text-purple-600 font-bold">{{ turnResult.latency_breakdown?.llm_inference_ms }} ms</div>
                  </div>
                  <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                    <div class="text-[10px] text-gray-500">TTS Audio</div>
                    <div class="text-emerald-600 font-bold">{{ turnResult.latency_breakdown?.tts_synthesis_ms }} ms</div>
                  </div>
                </div>
              </div>

              <!-- RAG Source Attribution -->
              <div v-if="turnResult.rag_source_document" class="pt-2 border-t border-gray-100 text-xs">
                <span class="text-gray-500">RAG Vector Attribution:</span>
                <div class="mt-1 flex items-center justify-between rounded bg-gray-50 p-2 text-[11px] ring-1 ring-gray-200">
                  <span class="font-semibold text-gray-800 truncate max-w-[220px]">{{ turnResult.rag_source_document.title }}</span>
                  <span class="font-mono text-emerald-700 font-bold">{{ (turnResult.rag_source_document.similarity_score * 100).toFixed(0) }}% Match</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: AGENT PERSONAS & PROMPT STUDIO -->
      <div v-if="activeTab === 'agents'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="ag in agents"
            :key="ag.uuid"
            class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 hover:ring-gray-300 transition space-y-3"
          >
            <div class="flex items-center justify-between">
              <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                {{ ag.status.toUpperCase() }}
              </span>
              <span class="text-xs font-mono font-bold text-indigo-600">Ext {{ ag.assigned_extension }}</span>
            </div>

            <h4 class="text-sm font-bold text-gray-900">{{ ag.agent_name }}</h4>
            <p class="text-xs text-gray-500">{{ ag.persona_role }}</p>

            <div class="rounded bg-gray-50 p-2.5 text-[11px] font-mono text-gray-700 max-h-24 overflow-y-auto ring-1 ring-gray-200">
              "{{ ag.system_prompt }}"
            </div>

            <div class="space-y-1 text-xs text-gray-600 font-mono pt-1">
              <div class="flex justify-between">
                <span class="text-gray-400">Model:</span>
                <span class="text-gray-900 font-semibold">{{ ag.primary_model }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Voice Synthesis:</span>
                <span class="text-gray-800">{{ ag.tts_voice }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">CSAT Score:</span>
                <span class="text-amber-600 font-bold">★ {{ ag.csat_score }} / 5.0</span>
              </div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex justify-between items-center text-[11px]">
              <span class="text-gray-400 font-mono">Prompt: {{ ag.prompt_version }}</span>
              <button
                @click="openPromptModal(ag)"
                class="rounded-md bg-white px-2.5 py-1 text-indigo-600 font-semibold shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition"
              >
                Edit System Prompt
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: RAG KNOWLEDGE BASE DOCUMENTS -->
      <div v-if="activeTab === 'rag_docs'" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>📚</span> RAG Vector Knowledge Base &amp; Document Index
            </h3>
            <p class="text-xs text-gray-500">
              Documents dynamically retrieved and cited by AI voice agents during customer dialogue turns.
            </p>
          </div>

          <!-- Search Query Test -->
          <div class="flex items-center gap-2">
            <input
              v-model="ragSearchQuery"
              type="text"
              placeholder="Search knowledge base..."
              class="rounded-md border-0 py-1.5 px-3 text-xs text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600"
            />
            <button
              @click="searchKnowledge"
              class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 transition shadow-sm"
            >
              Vector Query
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="doc in knowledgeDocs"
            :key="doc.uuid"
            class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 hover:ring-gray-300 transition space-y-3"
          >
            <div class="flex items-center justify-between">
              <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-mono text-indigo-700 uppercase font-bold ring-1 ring-indigo-700/10">
                {{ doc.category }}
              </span>
              <span class="text-emerald-700 text-xs font-bold">✓ {{ doc.vector_index_status.toUpperCase() }}</span>
            </div>

            <h4 class="text-sm font-bold text-gray-900">{{ doc.document_title }}</h4>
            <p class="text-xs text-gray-600 leading-relaxed">
              {{ doc.document_content }}
            </p>

            <div class="pt-2 border-t border-gray-100 flex justify-between items-center text-[10px] text-gray-400 font-mono">
              <span>{{ doc.chunk_count }} Vector Chunks</span>
              <span>Freshness: {{ doc.freshness_date }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: SESSIONS & AUDIT LOGS -->
      <div v-if="activeTab === 'sessions'" class="space-y-6">
        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
          <span>📜</span> Recent Autonomous AI Voice Dialogue Sessions
        </h3>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead class="bg-gray-50 text-gray-700 font-semibold">
              <tr>
                <th class="px-3.5 py-2.5">Caller / Time</th>
                <th class="px-3 py-2.5">Agent</th>
                <th class="px-3 py-2.5">Summary</th>
                <th class="px-3 py-2.5">Latency</th>
                <th class="px-3 py-2.5">Sentiment</th>
                <th class="px-3 py-2.5 text-right">Cost</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-900">
              <tr v-for="s in sessions" :key="s.uuid" class="hover:bg-gray-50">
                <td class="px-3.5 py-3">
                  <div class="font-mono font-bold text-gray-900">{{ s.caller_phone }}</div>
                  <div class="text-[10px] text-gray-500">{{ s.caller_name || 'Anonymous' }}</div>
                </td>
                <td class="px-3 py-3 font-semibold text-indigo-600">
                  {{ s.agent?.agent_name || 'Maya' }}
                </td>
                <td class="px-3 py-3 max-w-[280px] text-gray-600">
                  <div class="truncate">{{ s.call_summary }}</div>
                  <span v-if="s.hand_off_triggered" class="inline-block rounded bg-amber-50 px-1.5 py-0.5 text-[10px] text-amber-700 font-bold ring-1 ring-amber-700/20">
                    Handoff: {{ s.handoff_reason }}
                  </span>
                </td>
                <td class="px-3 py-3 font-mono">
                  <span class="text-emerald-700 font-bold">{{ s.total_latency_ms }} ms</span>
                </td>
                <td class="px-3 py-3">
                  <span
                    :class="[
                      s.sentiment_score >= 0.5 ? 'text-emerald-700' :
                      s.sentiment_score >= 0 ? 'text-indigo-600' : 'text-rose-600',
                      'font-bold font-mono'
                    ]"
                  >
                    {{ s.sentiment_score >= 0 ? '+' : '' }}{{ s.sentiment_score }}
                  </span>
                </td>
                <td class="px-3 py-3 text-right font-mono font-bold text-gray-800">
                  ${{ Number(s.cost_usd).toFixed(4) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 5: QUOTAS & BYOK PROVIDERS -->
      <div v-if="activeTab === 'quotas'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Left: Providers Vault -->
          <div class="space-y-4">
            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>🔑</span> BYOK Provider Connectors &amp; Models
            </h4>

            <div class="space-y-3">
              <div
                v-for="pr in providers"
                :key="pr.uuid"
                class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 space-y-2"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-gray-900 text-xs">{{ pr.provider_name }}</span>
                  <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-600/20">ACTIVE</span>
                </div>
                <div class="text-xs text-gray-500 font-mono">Key: {{ pr.api_key_masked }}</div>
                <div class="flex justify-between text-xs text-gray-600 font-mono">
                  <span>Rate: ${{ Number(pr.cost_per_minute_usd).toFixed(4) }}/min</span>
                  <span>Priority: #{{ pr.priority_order }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Quota Metering -->
          <div class="space-y-4">
            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>📊</span> Monthly Token &amp; Spend Quota
            </h4>

            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-4">
              <div class="space-y-1.5">
                <div class="flex justify-between text-xs">
                  <span class="text-gray-500">Monthly Tokens Consumed</span>
                  <span class="font-mono font-bold text-gray-900">{{ (quota?.tokens_consumed_month / 1000000).toFixed(2) }}M / {{ (quota?.monthly_token_quota / 1000000).toFixed(0) }}M</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                  <div
                    class="h-full bg-indigo-600 rounded-full"
                    :style="{ width: Math.min(100, (quota?.tokens_consumed_month / quota?.monthly_token_quota) * 100) + '%' }"
                  ></div>
                </div>
              </div>

              <div class="space-y-1.5">
                <div class="flex justify-between text-xs">
                  <span class="text-gray-500">Spend vs Monthly Ceiling</span>
                  <span class="font-mono font-bold text-amber-600">${{ Number(quota?.monthly_spend_usd).toFixed(2) }} / ${{ Number(quota?.spend_budget_ceiling_usd).toFixed(0) }}</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                  <div
                    class="h-full bg-amber-500 rounded-full"
                    :style="{ width: Math.min(100, (quota?.monthly_spend_usd / quota?.spend_budget_ceiling_usd) * 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- EDIT PROMPT MODAL -->
      <div v-if="showPromptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-xl rounded-lg bg-white p-6 shadow-2xl ring-1 ring-gray-200 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <span>✏️</span> Edit Voice Agent System Prompt
            </h3>
            <button @click="showPromptModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-medium text-gray-700">Prompt Version</label>
              <input
                v-model="editForm.version"
                type="text"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 font-mono text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
              />
            </div>
            <div>
              <label class="block font-medium text-gray-700">System Prompt</label>
              <textarea
                v-model="editForm.prompt"
                rows="5"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 font-mono text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
              ></textarea>
            </div>

            <button
              @click="savePrompt"
              class="w-full rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
            >
              Save &amp; Roll Out Prompt Version
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
