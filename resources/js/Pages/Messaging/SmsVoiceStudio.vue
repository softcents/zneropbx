<template>
  <MainLayout>
    <div class="m-3 space-y-6">
      <!-- Clean Native Software Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-md bg-pink-50 px-2 py-0.5 text-xs font-semibold text-pink-700 ring-1 ring-inset ring-pink-700/10">
                <span class="h-1.5 w-1.5 rounded-full bg-pink-500"></span>
                P7 — Messaging Fabric &amp; Voice Broadcast
              </span>
              <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-700/10">
                TCPA &amp; 10DLC Compliant
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">
              SMS &amp; Voice Broadcast Studio
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Multi-carrier SMPP &amp; REST interconnects, dual-tier priority queue (OTP vs Bulk), live GSM-7/UCS-2 segment calculation, and automated DND suppression.
            </p>
          </div>

          <!-- Quick Navigation -->
          <div class="flex flex-wrap items-center gap-2">
            <a href="/dashboard" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
              ← Dashboard
            </a>
            <a href="/telecom/routing-intelligence" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
              Carrier LCR
            </a>
            <a href="/billing/financial-studio" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
              Billing Studio
            </a>
          </div>
        </div>

        <!-- Real-Time Metrics Strip -->
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 border-t border-gray-100 pt-4">
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Total Dispatched</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-gray-900">{{ metrics.total_messages_dispatched.toLocaleString() }}</span>
              <span class="text-xs text-pink-600 font-mono">Msgs</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Delivery Rate</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-emerald-600">{{ metrics.delivery_success_rate_pct }}%</span>
              <span class="text-xs text-emerald-600 font-mono">DLR OK</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">OTP Latency</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-indigo-600">{{ metrics.avg_otp_delivery_latency_sec }}s</span>
              <span class="text-xs text-gray-400">Tier 10</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Active SMPP Binds</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-teal-600">{{ metrics.active_smpp_binds }}</span>
              <span class="text-xs text-emerald-600">Connected</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">DND Suppressions</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-rose-600">{{ metrics.dnd_suppression_count }}</span>
              <span class="text-xs text-rose-600">Opt-Out</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Net Margin</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-emerald-600">+{{ metrics.profit_margin_pct }}%</span>
              <span class="text-xs text-gray-400">Markup</span>
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

      <!-- TAB 1: INTERACTIVE DISPATCH & SEGMENT SIMULATOR -->
      <div v-if="activeTab === 'simulator'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left: Dispatch Form -->
          <div class="lg:col-span-7 space-y-4">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-4">
              <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <span>✉️</span> Interactive Message Dispatch &amp; Pacing Engine
                </h3>
                <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-mono text-indigo-700 ring-1 ring-indigo-700/10 font-semibold">
                  Dual-Tier Priority Queue
                </span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                  <label class="block font-medium text-gray-700">Recipient Phone (E.164)</label>
                  <input
                    v-model="msgForm.recipient_phone"
                    type="text"
                    placeholder="+15125550101"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 font-mono text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600"
                  />
                </div>

                <div>
                  <label class="block font-medium text-gray-700">Sender ID (Alphanumeric or Short Code)</label>
                  <input
                    v-model="msgForm.sender_id"
                    type="text"
                    placeholder="ZNERO_AUTH"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 font-mono text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600"
                  />
                </div>

                <div>
                  <label class="block font-medium text-gray-700">Traffic Type &amp; Tier</label>
                  <select
                    v-model="msgForm.traffic_type"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
                  >
                    <option value="transactional_otp">Transactional OTP / 2FA (Priority Tier 10 - Instant)</option>
                    <option value="marketing_bulk">Marketing Bulk Campaign (Priority Tier 2 - Fair Paced)</option>
                    <option value="voice_sms">Voice SMS Audio Dial (Priority Tier 9 - TTS Phone Call)</option>
                    <option value="whatsapp">WhatsApp Business Interactive (Priority Tier 7)</option>
                  </select>
                </div>

                <div class="flex items-center pt-5">
                  <label class="relative flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="msgForm.voice_tts_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-gray-700 font-medium">Enable Voice SMS Audio Dial (TTS)</span>
                  </label>
                </div>
              </div>

              <!-- Message Body with Live Segment Calculator -->
              <div class="space-y-1.5 text-xs">
                <div class="flex justify-between">
                  <label class="font-medium text-gray-700">Message Body</label>
                  <div class="text-[11px] font-mono text-gray-500">
                    Encoding: <span class="text-indigo-600 font-bold">{{ liveSegment.encoding }}</span> |
                    Chars: <span class="text-gray-900 font-bold">{{ liveSegment.character_count }}</span> |
                    Segments: <span class="text-pink-600 font-bold">{{ liveSegment.segments }}</span>
                  </div>
                </div>
                <textarea
                  v-model="msgForm.message_body"
                  @input="onTextChange"
                  rows="3"
                  placeholder="Enter message content..."
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 font-sans"
                ></textarea>
              </div>

              <!-- Quick Presets -->
              <div class="flex flex-wrap gap-2 pt-1 text-[11px]">
                <span class="text-gray-500">Quick Test Templates:</span>
                <button
                  @click="setTemplate('Your Znero PBX one-time passcode is 738194. Valid for 5 minutes.')"
                  class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition"
                >
                  2FA Passcode
                </button>
                <button
                  @click="setTemplate('Critical alert: SIP trunk Telnyx-East experienced high packet loss. Failover engaged.')"
                  class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition"
                >
                  NOC Alert
                </button>
                <button
                  @click="setTemplate('Save 25% on enterprise PBX lines this month! Reply STOP to unsubscribe.')"
                  class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition"
                >
                  Marketing Promo
                </button>
              </div>

              <button
                @click="dispatchMessage"
                :disabled="isSending"
                class="w-full inline-flex justify-center items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2.5 font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50 text-sm"
              >
                <span v-if="isSending" class="animate-spin">⏳</span>
                <span>{{ isSending ? 'Routing &amp; Delivering...' : 'Dispatch Message (Simulate SMPP / REST)' }}</span>
              </button>
            </div>
          </div>

          <!-- Right: Result & DLR Output -->
          <div class="lg:col-span-5 space-y-4">
            <div v-if="dispatchResult" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
              <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                <span class="font-bold text-sm" :class="dispatchResult.success ? 'text-emerald-700' : 'text-rose-700'">
                  {{ dispatchResult.success ? '✓ MESSAGE DELIVERED (DLR: DELIVRD)' : '✕ SUBMISSION DROPPED' }}
                </span>
                <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-mono font-bold text-indigo-700 ring-1 ring-indigo-700/10">
                  {{ dispatchResult.protocol || 'COMPLIANCE_GATE' }}
                </span>
              </div>

              <p v-if="!dispatchResult.success" class="text-xs text-rose-700">
                {{ dispatchResult.reason }}
              </p>

              <div v-else class="space-y-2 text-xs text-gray-600">
                <div class="flex justify-between border-b border-gray-100 pb-1">
                  <span class="text-gray-400">Carrier Gateway:</span>
                  <span class="font-semibold text-gray-900">{{ dispatchResult.routed_via }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-1">
                  <span class="text-gray-400">Queue Priority:</span>
                  <span class="font-bold text-indigo-600">Tier {{ dispatchResult.priority_tier }} (Protected)</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-1">
                  <span class="text-gray-400">Segments Billed:</span>
                  <span class="font-mono text-indigo-600 font-semibold">{{ dispatchResult.segment_info?.segments }} Segment(s) ({{ dispatchResult.segment_info?.encoding }})</span>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-1 text-center font-mono">
                  <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                    <div class="text-[10px] text-gray-500">Cost</div>
                    <div class="text-rose-600 font-bold">${{ Number(dispatchResult.cost_incurred).toFixed(5) }}</div>
                  </div>
                  <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                    <div class="text-[10px] text-gray-500">Billed</div>
                    <div class="text-emerald-700 font-bold">${{ Number(dispatchResult.retail_billed).toFixed(5) }}</div>
                  </div>
                  <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                    <div class="text-[10px] text-gray-500">Margin</div>
                    <div class="text-teal-700 font-bold">+${{ Number(dispatchResult.profit_margin).toFixed(5) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Regulatory Compliance Info Card -->
            <div class="rounded-lg bg-indigo-50/50 p-4 space-y-2 text-xs text-gray-700 ring-1 ring-indigo-100">
              <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                <span>⚖️</span> TCPA &amp; Regulatory Compliance Guarantees
              </div>
              <ul class="space-y-1 text-[11px] text-gray-600 list-disc list-inside">
                <li><strong>TCPA DND Filtering:</strong> Numbers in suppression list blocked at gateway boundary before reaching carrier interconnect.</li>
                <li><strong>Dual-Tier Queuing:</strong> Transactional OTP messages process in dedicated high-priority queue; never delayed by bulk promo blasts.</li>
                <li><strong>GSM-7 / UCS-2 Auto-Detection:</strong> Dynamic encoding prevents accidental message bloating and billing surprises.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: CONNECTORS & SMPP BINDS -->
      <div v-if="activeTab === 'connectors'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="conn in connectors"
            :key="conn.uuid"
            class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 hover:ring-gray-300 transition space-y-3"
          >
            <div class="flex items-center justify-between">
              <div>
                <span
                  :class="[
                    conn.bind_status === 'bound' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20',
                    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-semibold ring-1'
                  ]"
                >
                  <span :class="conn.bind_status === 'bound' ? 'bg-emerald-500' : 'bg-rose-500'" class="h-1.5 w-1.5 rounded-full"></span>
                  {{ conn.bind_status.toUpperCase() }}
                </span>
                <h4 class="mt-2 text-sm font-bold text-gray-900">{{ conn.provider_name }}</h4>
              </div>

              <span class="rounded bg-indigo-50 px-2 py-0.5 font-mono text-xs text-indigo-700 font-semibold ring-1 ring-indigo-700/10">
                {{ conn.protocol }}
              </span>
            </div>

            <div class="space-y-1.5 text-xs text-gray-600 font-mono">
              <div class="flex justify-between">
                <span class="text-gray-400">Host / Port:</span>
                <span class="text-gray-900 font-semibold">{{ conn.host }}:{{ conn.port }}</span>
              </div>
              <div v-if="conn.smpp_system_id" class="flex justify-between">
                <span class="text-gray-400">System ID / Mode:</span>
                <span class="text-indigo-600">{{ conn.smpp_system_id }} ({{ conn.smpp_bind_mode }})</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Throughput Limit:</span>
                <span class="text-indigo-600 font-bold">{{ conn.tps_limit }} TPS (Pacing Ceiling)</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Success Rate:</span>
                <span class="text-emerald-700 font-bold">{{ conn.success_rate_pct }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Cost Per Segment:</span>
                <span class="text-gray-900 font-semibold">${{ Number(conn.cost_per_segment).toFixed(5) }}</span>
              </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end">
              <button
                @click="toggleBind(conn)"
                class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition"
                :class="conn.bind_status === 'bound' ? 'text-amber-700' : 'text-emerald-700'"
              >
                {{ conn.bind_status === 'bound' ? 'Unbind / Quarantine' : 'Bind / Reconnect' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: ROUTING POLICIES -->
      <div v-if="activeTab === 'routing'" class="space-y-6">
        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead class="bg-gray-50 text-gray-700 font-semibold">
              <tr>
                <th class="px-3.5 py-2.5">Policy / Traffic Type</th>
                <th class="px-3 py-2.5">Priority Tier</th>
                <th class="px-3 py-2.5">Primary Connector</th>
                <th class="px-3 py-2.5">Failover Connector</th>
                <th class="px-3 py-2.5">Sender ID Type</th>
                <th class="px-3 py-2.5">Prefix Match</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-900">
              <tr v-for="pol in policies" :key="pol.uuid" class="hover:bg-gray-50">
                <td class="px-3.5 py-3">
                  <div class="font-bold text-gray-900">{{ pol.policy_name }}</div>
                  <span class="rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] text-indigo-700 font-mono ring-1 ring-indigo-700/10">{{ pol.traffic_type }}</span>
                </td>
                <td class="px-3 py-3">
                  <span
                    :class="[
                      pol.queue_priority >= 8 ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-blue-50 text-blue-700 ring-blue-600/20',
                      'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1'
                    ]"
                  >
                    Tier {{ pol.queue_priority }}
                  </span>
                </td>
                <td class="px-3 py-3 font-semibold text-emerald-700">
                  {{ pol.primary_connector?.provider_name || 'Telnyx Direct' }}
                </td>
                <td class="px-3 py-3 text-gray-500">
                  {{ pol.failover_connector?.provider_name || 'Twilio Failover' }}
                </td>
                <td class="px-3 py-3 font-mono text-[11px] text-gray-700">
                  {{ pol.sender_id_type }}
                </td>
                <td class="px-3 py-3 font-mono text-indigo-600 font-bold">
                  {{ pol.prefix_match }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 4: TCPA & DND SUPPRESSION -->
      <div v-if="activeTab === 'dnd'" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>🚫</span> TCPA Opt-Out &amp; DND Suppression List
            </h3>
            <p class="text-xs text-gray-500">
              Numbers in this list are guaranteed to be dropped before dispatching to carrier routes.
            </p>
          </div>

          <!-- Add DND Form -->
          <div class="flex items-center gap-2">
            <input
              v-model="newDndPhone"
              type="text"
              placeholder="+15125550999"
              class="rounded-md border-0 py-1.5 px-3 text-xs text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 font-mono"
            />
            <button
              @click="addDndNumber"
              class="rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition shadow-sm"
            >
              + Add Opt-Out
            </button>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead class="bg-gray-50 text-gray-700 font-semibold">
              <tr>
                <th class="px-3.5 py-2.5">Phone Number</th>
                <th class="px-3 py-2.5">Suppression Reason</th>
                <th class="px-3 py-2.5">Source Channel</th>
                <th class="px-3 py-2.5">Opt-Out Date</th>
                <th class="px-3 py-2.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-900">
              <tr v-for="d in suppressions" :key="d.uuid" class="hover:bg-gray-50">
                <td class="px-3.5 py-3 font-mono font-bold text-gray-900">{{ d.phone_number }}</td>
                <td class="px-3 py-3">
                  <span class="rounded bg-rose-50 px-2 py-0.5 text-[10px] text-rose-700 font-mono ring-1 ring-rose-600/20">
                    {{ d.reason }}
                  </span>
                </td>
                <td class="px-3 py-3 text-gray-500">{{ d.source_channel }}</td>
                <td class="px-3 py-3 text-gray-500 font-mono">{{ d.created_at }}</td>
                <td class="px-3 py-3 text-right">
                  <button
                    @click="removeDnd(d)"
                    class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-rose-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition"
                  >
                    Remove
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 5: DISPATCH QUEUE & DLR LOG -->
      <div v-if="activeTab === 'queue'" class="space-y-6">
        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
          <span>📋</span> Real-Time Delivery Receipts (DLR) Log
        </h3>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead class="bg-gray-50 text-gray-700 font-semibold">
              <tr>
                <th class="px-3.5 py-2.5">Time / Recipient</th>
                <th class="px-3 py-2.5">Sender ID</th>
                <th class="px-3 py-2.5">Traffic Type</th>
                <th class="px-3 py-2.5">Message Excerpt</th>
                <th class="px-3 py-2.5">DLR Status</th>
                <th class="px-3 py-2.5 text-right">Cost / Margin</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-900">
              <tr v-for="q in queueItems" :key="q.uuid" class="hover:bg-gray-50">
                <td class="px-3.5 py-3">
                  <div class="font-mono font-bold text-gray-900">{{ q.recipient_phone }}</div>
                  <div class="text-[10px] text-gray-400 font-mono">{{ q.created_at }}</div>
                </td>
                <td class="px-3 py-3 font-mono text-indigo-600 font-semibold">{{ q.sender_id }}</td>
                <td class="px-3 py-3">
                  <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-700 ring-1 ring-gray-200">
                    {{ q.traffic_type }}
                  </span>
                </td>
                <td class="px-3 py-3 max-w-[260px] truncate text-gray-600">
                  {{ q.message_body }}
                </td>
                <td class="px-3 py-3">
                  <span
                    :class="[
                      q.dlr_status === 'DELIVRD' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' :
                      q.dlr_status === 'PENDING' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' :
                      'bg-rose-50 text-rose-700 ring-rose-600/20',
                      'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1'
                    ]"
                  >
                    {{ q.dlr_status }}
                  </span>
                </td>
                <td class="px-3 py-3 text-right font-mono">
                  <div class="text-emerald-700 font-bold">${{ Number(q.retail_billed_amount).toFixed(4) }}</div>
                  <div class="text-[10px] text-gray-400">Cost: ${{ Number(q.cost_amount).toFixed(4) }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 6: VOICE SMS CAMPAIGNS -->
      <div v-if="activeTab === 'voice_campaigns'" class="space-y-6">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            <span>🎙️</span> Voice SMS Audio Broadcast Campaigns (TTS &amp; DTMF)
          </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="camp in voiceCampaigns"
            :key="camp.uuid"
            class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 hover:ring-gray-300 transition space-y-3"
          >
            <div class="flex items-center justify-between">
              <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                {{ camp.status.toUpperCase() }}
              </span>
              <span class="text-xs font-mono text-indigo-600 font-semibold">{{ camp.tts_voice }}</span>
            </div>

            <h4 class="text-sm font-bold text-gray-900">{{ camp.campaign_title }}</h4>
            <p class="text-xs text-gray-700 bg-gray-50 p-3 rounded font-mono ring-1 ring-gray-200">
              "{{ camp.tts_script }}"
            </p>

            <div class="grid grid-cols-3 gap-2 pt-1 text-center font-mono text-xs">
              <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                <div class="text-[10px] text-gray-500">Recipients</div>
                <div class="text-gray-900 font-bold">{{ camp.recipients_count }}</div>
              </div>
              <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                <div class="text-[10px] text-gray-500">Delivered</div>
                <div class="text-indigo-600 font-bold">{{ camp.completed_count }}</div>
              </div>
              <div class="rounded bg-gray-50 p-2 ring-1 ring-gray-100">
                <div class="text-[10px] text-gray-500">DTMF Ack</div>
                <div class="text-emerald-700 font-bold">{{ camp.acknowledged_count }}</div>
              </div>
            </div>
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
  connectors: { type: Array, default: () => [] },
  policies: { type: Array, default: () => [] },
  suppressions: { type: Array, default: () => [] },
  queueItems: { type: Array, default: () => [] },
  voiceCampaigns: { type: Array, default: () => [] },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('simulator')

const tabs = [
  { id: 'simulator', name: 'Message Dispatch Simulator', icon: '✉️', badge: 'Dual-Tier' },
  { id: 'connectors', name: 'SMPP & REST Connectors', icon: '🔌', badge: `${props.connectors.length || 0}` },
  { id: 'routing', name: 'Routing & Pacing Policies', icon: '🚦', badge: `${props.policies.length || 0}` },
  { id: 'dnd', name: 'TCPA DND Suppression', icon: '🚫', badge: `${props.suppressions.length || 0}` },
  { id: 'queue', name: 'Delivery Receipts (DLR)', icon: '📋', badge: `${props.queueItems.length || 0}` },
  { id: 'voice_campaigns', name: 'Voice SMS Campaigns', icon: '🎙️', badge: `${props.voiceCampaigns.length || 0}` },
]

// Dispatch Form State
const msgForm = ref({
  recipient_phone: '+15125550101',
  sender_id: 'ZNERO_AUTH',
  traffic_type: 'transactional_otp',
  voice_tts_enabled: false,
  message_body: 'Your Znero PBX one-time passcode is 738194. Valid for 5 minutes. Do not share.',
})

const liveSegment = ref({
  character_count: 67,
  encoding: 'GSM-7 (7-bit Default)',
  segments: 1,
})

const onTextChange = async () => {
  try {
    const res = await axios.post('/api/messaging/sms-voice-studio/calculate-segments', {
      message_body: msgForm.value.message_body,
    })
    liveSegment.value = res.data
  } catch (err) {
    // fallback local calculation
    liveSegment.value.character_count = msgForm.value.message_body.length
  }
}

const setTemplate = (text) => {
  msgForm.value.message_body = text
  onTextChange()
}

const isSending = ref(false)
const dispatchResult = ref(null)

const dispatchMessage = async () => {
  isSending.value = true
  try {
    const res = await axios.post('/api/messaging/sms-voice-studio/send', msgForm.value)
    dispatchResult.value = res.data
    if (res.data.queue_item) {
      props.queueItems.unshift(res.data.queue_item)
    }
  } catch (err) {
    alert('Submission error: ' + (err.response?.data?.message || err.message))
  } finally {
    isSending.value = false
  }
}

// Connector Bind Toggle
const toggleBind = async (conn) => {
  try {
    const res = await axios.post(`/api/messaging/sms-voice-studio/connectors/${conn.uuid}/toggle-bind`)
    conn.bind_status = res.data.bind_status
  } catch (err) {
    alert('Failed to toggle bind: ' + (err.response?.data?.message || err.message))
  }
}

// DND Operations
const newDndPhone = ref('')
const addDndNumber = async () => {
  if (!newDndPhone.value) return
  try {
    const res = await axios.post('/api/messaging/sms-voice-studio/dnd', {
      phone_number: newDndPhone.value,
      reason: 'manual_dnd',
    })
    props.suppressions.unshift(res.data)
    newDndPhone.value = ''
  } catch (err) {
    alert('Failed to add DND number: ' + (err.response?.data?.message || err.message))
  }
}

const removeDndNumber = async (d) => {
  try {
    await axios.delete(`/api/messaging/sms-voice-studio/dnd/${d.uuid}`)
    const idx = props.suppressions.findIndex(item => item.uuid === d.uuid)
    if (idx !== -1) props.suppressions.splice(idx, 1)
  } catch (err) {
    alert('Failed to remove DND: ' + (err.response?.data?.message || err.message))
  }
}
</script>
