<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-indigo-500"></span>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <Squares2X2Icon class="w-7 h-7 text-indigo-600" />
                {{ $t("Inbound IVR Journey Studio & Visual Flow Builder") }}
              </h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Design customer journey pipelines, Customer 360 VIP recognition, multilingual voice prompts, smart callback offers, and export FreeSWITCH dialplan XML.") }}
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Flow Selector -->
            <select
              v-model="selectedFlowUuid"
              @change="switchFlow"
              class="rounded-lg border-gray-300 text-xs py-2 font-bold focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option v-for="f in flowsList" :key="f.ivr_flow_uuid" :value="f.ivr_flow_uuid">
                {{ f.name }} (Ext {{ f.extension }})
              </option>
            </select>

            <!-- Test Run Simulator -->
            <button
              @click="openSimulatorModal = true"
              class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition"
            >
              <PlayIcon class="w-4 h-4" />
              {{ $t("Test Run Simulator") }}
            </button>

            <!-- FreeSWITCH Dialplan XML -->
            <button
              @click="openXmlModal = true"
              class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition"
            >
              <CodeBracketIcon class="w-4 h-4 text-indigo-600" />
              {{ $t("FreeSWITCH XML") }}
            </button>

            <!-- Create Flow -->
            <button
              @click="openCreateFlowModal = true"
              class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("New Flow") }}
            </button>
          </div>
        </div>
      </div>

      <!-- Top Journey Analytics Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div class="text-xs text-gray-500 font-semibold mb-1">{{ $t("Total Calls Handled") }}</div>
          <div class="text-2xl font-black text-gray-900">{{ currentAnalytics.total_calls }}</div>
          <div class="text-[11px] text-gray-500 mt-1">Inbound IVR ingress</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("IVR Containment Rate") }}</span>
            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
              Target {{ currentFlow?.containment_rate_target || 35 }}%
            </span>
          </div>
          <div class="text-2xl font-black text-emerald-600">{{ currentAnalytics.containment_rate }}%</div>
          <div class="text-[11px] text-gray-500 mt-1">Resolved without agent bridge</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div class="text-xs text-gray-500 font-semibold mb-1">{{ $t("Queue Transfer Rate") }}</div>
          <div class="text-2xl font-black text-indigo-600">{{ currentAnalytics.transfer_rate }}%</div>
          <div class="text-[11px] text-gray-500 mt-1">Bridged to ACD skill queue</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("Drop-Off / Abandon") }}</span>
            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-blue-700">&lt;5% Good</span>
          </div>
          <div class="text-2xl font-black text-rose-600">{{ currentAnalytics.drop_off_rate }}%</div>
          <div class="text-[11px] text-gray-500 mt-1">Average Duration: {{ currentAnalytics.avg_duration_sec }}s</div>
        </div>
      </div>

      <!-- Visual Flow Canvas Pipeline -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div class="flex items-center justify-between border-b pb-4">
          <div>
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <SparklesIcon class="w-5 h-5 text-indigo-600" />
              {{ $t("Inbound Customer Journey Pipeline") }}: {{ currentFlow?.name }}
            </h2>
            <p class="text-xs text-gray-500">
              Extension: <span class="font-mono font-bold text-gray-800">{{ currentFlow?.extension }}</span> &bull; Status: <span class="text-emerald-600 font-semibold uppercase">{{ currentFlow?.status }}</span> &bull; Version {{ currentFlow?.version }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
              <span class="text-xs">👑</span> VIP Boost: Enabled (+100 ACD)
            </span>
          </div>
        </div>

        <!-- Node Pipeline Sequence -->
        <div class="space-y-6">
          <!-- Step 1: Ingress -->
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shrink-0">
              <PhoneIcon class="w-5 h-5" />
            </div>
            <div class="flex-1 bg-indigo-50/60 p-4 rounded-xl border border-indigo-100">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Step 1: Inbound Call Ingress</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white text-indigo-800 border border-indigo-200">
                  Ext {{ currentFlow?.extension }} / DID
                </span>
              </div>
              <p class="text-xs text-indigo-800 mt-1">
                FreeSWITCH answers call with 200 OK. Sets correlation ID and begins journey tracking.
              </p>
            </div>
          </div>

          <!-- Connector Arrow -->
          <div class="flex justify-center -my-3">
            <ArrowDownIcon class="w-5 h-5 text-gray-400" />
          </div>

          <!-- Step 2: Language Selector -->
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-md shrink-0">
              <LanguageIcon class="w-5 h-5" />
            </div>
            <div class="flex-1 bg-blue-50/60 p-4 rounded-xl border border-blue-100">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-blue-950 uppercase tracking-wider">Step 2: Multilingual Language Selector</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-blue-800 border border-blue-200">
                  Default: {{ currentFlow?.language_default }}
                </span>
              </div>
              <p class="text-xs text-blue-800 mt-1">
                Plays greeting prompt. Prompts caller to select English or Spanish voice audio prompts.
              </p>
            </div>
          </div>

          <!-- Connector Arrow -->
          <div class="flex justify-center -my-3">
            <ArrowDownIcon class="w-5 h-5 text-gray-400" />
          </div>

          <!-- Step 3: Customer 360 & VIP Recognition Split -->
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-bold shadow-md shrink-0">
              <SparklesIcon class="w-5 h-5" />
            </div>
            <div class="flex-1 bg-purple-50/60 p-4 rounded-xl border border-purple-200 space-y-3">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-purple-950 uppercase tracking-wider">Step 3: Customer 360 & VIP Recognition</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-600 text-white">
                  Automated Condition Split
                </span>
              </div>
              <p class="text-xs text-purple-900">
                Queries Customer 360 database by caller ID phone number in real time.
              </p>

              <!-- Two Branching Paths -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                <!-- Branch A: VIP Bypass -->
                <div class="bg-white p-3.5 rounded-lg border-2 border-purple-400 shadow-sm space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-xs text-purple-900 flex items-center gap-1">
                      👑 VIP Customer Match
                    </span>
                    <span class="text-[10px] font-bold bg-purple-100 text-purple-800 px-1.5 py-0.2 rounded">
                      +100 Boost
                    </span>
                  </div>
                  <p class="text-[11px] text-gray-600">
                    Bypasses main voice menu. Elevates call priority score directly to <strong>VIP Concierge Queue (Ext 8003)</strong>.
                  </p>
                </div>

                <!-- Branch B: Standard Caller -->
                <div class="bg-white p-3.5 rounded-lg border border-gray-200 shadow-sm space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-xs text-gray-900">
                      Standard / Unregistered Caller
                    </span>
                    <span class="text-[10px] font-bold bg-gray-100 text-gray-700 px-1.5 py-0.2 rounded">
                      Base Priority
                    </span>
                  </div>
                  <p class="text-[11px] text-gray-600">
                    Proceeds to Step 4 Interactive DTMF Voice Menu for skill-based queue distribution.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Connector Arrow -->
          <div class="flex justify-center -my-3">
            <ArrowDownIcon class="w-5 h-5 text-gray-400" />
          </div>

          <!-- Step 4: Interactive DTMF Menu Grid -->
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold shadow-md shrink-0">
              <Squares2X2Icon class="w-5 h-5" />
            </div>
            <div class="flex-1 bg-amber-50/50 p-4 rounded-xl border border-amber-200 space-y-3">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Step 4: Interactive DTMF Voice Menu</h3>
                <span class="text-[10px] text-amber-900 font-bold bg-amber-100 px-2 py-0.5 rounded">
                  mod_dptools / FreeSWITCH IVR
                </span>
              </div>

              <!-- Options Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 pt-1">
                <div class="bg-white p-3 rounded-lg border border-gray-200 space-y-1 shadow-xs">
                  <div class="font-black text-sm text-indigo-600">[ 1 ]</div>
                  <div class="font-bold text-xs text-gray-900">Technical Support</div>
                  <div class="text-[10px] text-gray-500">ACD Queue 8001</div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200 space-y-1 shadow-xs">
                  <div class="font-black text-sm text-indigo-600">[ 2 ]</div>
                  <div class="font-bold text-xs text-gray-900">Billing &amp; Payments</div>
                  <div class="text-[10px] text-gray-500">ACD Queue 8002</div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200 space-y-1 shadow-xs">
                  <div class="font-black text-sm text-indigo-600">[ 8 ]</div>
                  <div class="font-bold text-xs text-emerald-700">Smart Callback</div>
                  <div class="text-[10px] text-gray-500">Queue Bypass Offer</div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200 space-y-1 shadow-xs">
                  <div class="font-black text-sm text-indigo-600">[ 9 ]</div>
                  <div class="font-bold text-xs text-blue-700">Self-Service</div>
                  <div class="text-[10px] text-gray-500">Balance Inquiry (Contained)</div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200 space-y-1 shadow-xs">
                  <div class="font-black text-sm text-indigo-600">[ 0 ]</div>
                  <div class="font-bold text-xs text-gray-900">Operator Desk</div>
                  <div class="text-[10px] text-gray-500">Reception Ext 1000</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Caller Journey Traces -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
            {{ $t("Recent Caller Journey Path Traces") }}
          </h3>
          <span class="text-xs text-gray-500">{{ recentJourneysList.length }} recorded journeys</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase text-[10px]">
                <th class="p-3">{{ $t("Timestamp") }}</th>
                <th class="p-3">{{ $t("Caller ID & Name") }}</th>
                <th class="p-3">{{ $t("Path Taken") }}</th>
                <th class="p-3 text-center">{{ $t("Contained") }}</th>
                <th class="p-3 text-center">{{ $t("Duration") }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 font-medium text-gray-700">
              <tr v-for="j in recentJourneysList" :key="j.journey_uuid" class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-gray-900">{{ formatDate(j.created_at) }}</td>
                <td class="p-3">
                  <div class="font-bold text-gray-900">{{ j.caller_id_name || 'Anonymous' }}</div>
                  <div class="text-[11px] text-gray-500 font-mono">{{ j.caller_id_number }}</div>
                </td>
                <td class="p-3">
                  <div class="flex flex-wrap items-center gap-1">
                    <span
                      v-for="(node, idx) in j.path_taken"
                      :key="idx"
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-mono',
                        node.includes('vip') ? 'bg-purple-100 text-purple-800 font-bold' :
                        node.includes('queue') ? 'bg-indigo-100 text-indigo-800' :
                        node.includes('self_service') ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-gray-100 text-gray-700'
                      ]"
                    >
                      {{ node }}
                    </span>
                  </div>
                </td>
                <td class="p-3 text-center">
                  <span
                    v-if="j.contained"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"
                  >
                    Self-Service
                  </span>
                  <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                    Transferred
                  </span>
                </td>
                <td class="p-3 text-center font-mono font-semibold">{{ j.duration_seconds }}s</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Interactive Call Journey Simulator -->
    <div
      v-if="openSimulatorModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <PlayIcon class="w-5 h-5 text-emerald-600" />
            {{ $t("Interactive Call Journey Simulator") }}
          </h3>
          <button @click="openSimulatorModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Simulator Configuration -->
        <div class="space-y-3 text-xs bg-gray-50 p-4 rounded-xl border border-gray-200">
          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Caller Phone Number") }}</label>
            <div class="flex gap-2">
              <input
                v-model="simCallerNumber"
                type="text"
                class="flex-1 rounded-md border-gray-300 text-xs font-mono"
              />
              <button
                @click="simCallerNumber = '+1 (555) 234-5678'"
                type="button"
                class="px-2.5 py-1 bg-purple-100 text-purple-800 hover:bg-purple-200 rounded font-bold text-[11px]"
              >
                {{ $t("Load VIP Client") }}
              </button>
              <button
                @click="simCallerNumber = '+1 (555) 987-6543'"
                type="button"
                class="px-2.5 py-1 bg-gray-200 text-gray-800 hover:bg-gray-300 rounded font-medium text-[11px]"
              >
                {{ $t("Standard") }}
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Language") }}</label>
              <select v-model="simLanguage" class="w-full rounded-md border-gray-300 text-xs">
                <option value="en-US">English (en-US)</option>
                <option value="es-ES">Spanish (es-ES)</option>
              </select>
            </div>

            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("DTMF Key to Press") }}</label>
              <select v-model="simDtmfKey" class="w-full rounded-md border-gray-300 text-xs font-bold">
                <option value="1">Key 1: Technical Support (Ext 8001)</option>
                <option value="2">Key 2: Billing &amp; Payments (Ext 8002)</option>
                <option value="8">Key 8: Smart Callback Offer</option>
                <option value="9">Key 9: Self-Service Inquiry</option>
                <option value="0">Key 0: Operator Desk (Ext 1000)</option>
              </select>
            </div>
          </div>

          <button
            @click="runSimulation"
            :disabled="simRunning"
            class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm flex items-center justify-center gap-2 transition disabled:opacity-50"
          >
            <PlayIcon class="w-4 h-4" />
            {{ simRunning ? $t("Simulating Call Flow...") : $t("Run Dry-Run Journey Simulation") }}
          </button>
        </div>

        <!-- Simulation Output Trace -->
        <div v-if="simResult" class="space-y-3 pt-2">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-gray-900">{{ $t("Execution Trace Results") }}</h4>
            <span
              v-if="simResult.is_vip"
              class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800"
            >
              👑 VIP Bypass Triggered
            </span>
            <span
              v-else-if="simResult.contained"
              class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800"
            >
              Self-Service Contained
            </span>
          </div>

          <div class="space-y-2">
            <div
              v-for="step in simResult.trace"
              :key="step.step"
              :class="[
                'p-3 rounded-lg border text-xs space-y-1',
                step.status === 'vip_boost' ? 'bg-purple-50/70 border-purple-200' :
                step.status === 'contained' ? 'bg-emerald-50/70 border-emerald-200' :
                step.status === 'callback' ? 'bg-blue-50/70 border-blue-200' : 'bg-gray-50 border-gray-200'
              ]"
            >
              <div class="flex items-center justify-between font-bold text-gray-900">
                <span>{{ step.title }}</span>
                <span class="text-[10px] font-mono text-gray-400">Step {{ step.step }}</span>
              </div>
              <p class="text-[11px] text-gray-600">{{ step.detail }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: FreeSWITCH Dialplan XML Viewer -->
    <div
      v-if="openXmlModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-2xl w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <CodeBracketIcon class="w-5 h-5 text-indigo-600" />
            {{ $t("Generated FreeSWITCH Dialplan XML") }}
          </h3>
          <button @click="openXmlModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Destination Expression: <span class="font-mono text-gray-800 font-bold">^{{ currentFlow?.extension }}$</span></span>
            <button
              @click="copyXml"
              class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded font-semibold text-[11px]"
            >
              {{ copiedXml ? $t("Copied!") : $t("Copy XML") }}
            </button>
          </div>

          <pre class="bg-gray-900 text-emerald-400 p-4 rounded-xl text-xs font-mono overflow-x-auto max-h-96">{{ dialplanXmlText }}</pre>
        </div>

        <div class="flex justify-end pt-2 border-t">
          <button
            @click="openXmlModal = false"
            class="px-4 py-1.5 text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-bold text-xs"
          >
            {{ $t("Close") }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Create Flow -->
    <div
      v-if="openCreateFlowModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <PlusIcon class="w-5 h-5 text-indigo-600" />
            {{ $t("Create IVR Journey Flow") }}
          </h3>
          <button @click="openCreateFlowModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitCreateFlow" class="space-y-3 text-xs">
          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Flow Name") }} *</label>
            <input v-model="newFlowForm.name" required type="text" class="w-full rounded-md border-gray-300 text-xs" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Extension") }} *</label>
              <input v-model="newFlowForm.extension" required type="text" class="w-full rounded-md border-gray-300 text-xs font-mono" />
            </div>
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Default Language") }}</label>
              <select v-model="newFlowForm.language_default" class="w-full rounded-md border-gray-300 text-xs">
                <option value="en-US">English (en-US)</option>
                <option value="es-ES">Spanish (es-ES)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Description") }}</label>
            <textarea v-model="newFlowForm.description" rows="2" class="w-full rounded-md border-gray-300 text-xs"></textarea>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="newFlowForm.vip_boost_enabled" type="checkbox" id="vip_boost" class="rounded text-indigo-600" />
            <label for="vip_boost" class="text-gray-700 font-medium">
              {{ $t("Enable Customer 360 VIP Automatic Recognition") }}
            </label>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t">
            <button
              type="button"
              @click="openCreateFlowModal = false"
              class="px-3 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-xs"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="submit"
              class="px-4 py-1.5 text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-bold text-xs"
            >
              {{ $t("Create Flow") }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  Squares2X2Icon,
  PlayIcon,
  CodeBracketIcon,
  PlusIcon,
  PhoneIcon,
  LanguageIcon,
  SparklesIcon,
  ArrowDownIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  flows: {
    type: Array,
    default: () => [],
  },
  activeFlow: {
    type: Object,
    default: null,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const flowsList = ref([...props.flows]);
const currentFlow = ref(props.activeFlow?.flow || props.flows[0] || null);
const currentAnalytics = ref(props.activeFlow?.analytics || {
  total_calls: 124,
  containment_rate: 36.3,
  transfer_rate: 59.7,
  drop_off_rate: 4.0,
  avg_duration_sec: 28,
});
const recentJourneysList = ref(props.activeFlow?.recent_journeys || []);
const dialplanXmlText = ref(props.activeFlow?.dialplan_xml || "");

const selectedFlowUuid = ref(currentFlow.value?.ivr_flow_uuid || "");

const openSimulatorModal = ref(false);
const openXmlModal = ref(false);
const openCreateFlowModal = ref(false);
const copiedXml = ref(false);

const simCallerNumber = ref("+1 (555) 234-5678");
const simLanguage = ref("en-US");
const simDtmfKey = ref("1");
const simRunning = ref(false);
const simResult = ref(null);

const newFlowForm = ref({
  name: "",
  extension: "5100",
  description: "",
  language_default: "en-US",
  vip_boost_enabled: true,
});

function switchFlow() {
  router.get(
    "/contact-center/ivr-studio",
    { flow_uuid: selectedFlowUuid.value },
    { preserveState: true }
  );
}

async function runSimulation() {
  if (!currentFlow.value) return;
  simRunning.value = true;
  simResult.value = null;

  try {
    const res = await axios.post(`/api/contact-center/ivr-studio/${currentFlow.value.ivr_flow_uuid}/simulate`, {
      caller_number: simCallerNumber.value,
      language: simLanguage.value,
      dtmf_sequence: [simDtmfKey.value],
    });
    simResult.value = res.data;
    if (res.data.success) {
      currentAnalytics.value.total_calls++;
    }
  } catch (err) {
    alert("Simulation error.");
  } finally {
    simRunning.value = false;
  }
}

function copyXml() {
  navigator.clipboard.writeText(dialplanXmlText.value);
  copiedXml.value = true;
  setTimeout(() => (copiedXml.value = false), 2500);
}

async function submitCreateFlow() {
  try {
    const res = await axios.post("/api/contact-center/ivr-studio", newFlowForm.value);
    if (res.data?.success) {
      flowsList.value.unshift(res.data.flow);
      openCreateFlowModal.value = false;
      router.get(`/contact-center/ivr-studio?flow_uuid=${res.data.flow.ivr_flow_uuid}`);
    }
  } catch (err) {
    alert("Error creating flow.");
  }
}

function formatDate(isoStr) {
  if (!isoStr) return "-";
  return new Date(isoStr).toLocaleString();
}
</script>
