<template>
  <MainLayout>
    <div class="min-h-screen bg-slate-950 text-slate-100 pb-16">
      <!-- Dark Luxury Spotlight Banner -->
      <div class="relative overflow-hidden border-b border-pink-500/20 bg-gradient-to-r from-slate-900 via-pink-950 to-slate-900 px-6 py-8 shadow-2xl">
        <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-pink-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-rose-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative mx-auto max-w-7xl">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-pink-500/10 px-3 py-1 text-xs font-semibold text-pink-400 ring-1 ring-inset ring-pink-500/30">
                  <span class="h-1.5 w-1.5 rounded-full bg-pink-400 animate-pulse"></span>
                  P7 — SMS, Voice SMS & SMPP Gateway
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/30">
                  TCPA & Dual-Tier Ready
                </span>
              </div>
              <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                SMS, Voice SMS & SMPP Gateway Routing Studio
              </h1>
              <p class="mt-1 text-sm text-slate-400">
                SMPP v3.4 transceiver binds, GSM-7/UCS-2 segmentation, dual-tier priority queue (protecting OTPs from bulk starvation), and DND compliance suppression.
              </p>
            </div>

            <!-- Quick Navigation Back to CC Suite & Operations -->
            <div class="flex flex-wrap items-center gap-2">
              <a href="/dashboard" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800/80 px-3 py-2 text-xs font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition ring-1 ring-slate-700">
                ← Dashboard
              </a>
              <a href="/telecom/routing-intelligence" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600/20 px-3 py-2 text-xs font-medium text-teal-300 hover:bg-teal-600/30 transition ring-1 ring-teal-500/30">
                Carrier LCR
              </a>
              <a href="/carrier/infrastructure" class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600/20 px-3 py-2 text-xs font-medium text-sky-300 hover:bg-sky-600/30 transition ring-1 ring-sky-500/30">
                Carrier Infra
              </a>
            </div>
          </div>

          <!-- Real-Time Metrics Strip -->
          <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-pink-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Total Dispatched</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-white">{{ metrics.total_sent }}</span>
                <span class="text-xs text-pink-400 font-mono">msgs</span>
              </div>
            </div>

            <div class="rounded-xl border border-emerald-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Delivery Rate (DLR)</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-emerald-400">{{ metrics.delivery_rate_pct }}%</span>
                <span class="text-xs text-slate-400">DELIVRD</span>
              </div>
            </div>

            <div class="rounded-xl border border-blue-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Aggregate TPS</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-blue-400">{{ metrics.aggregate_tps_capacity }}</span>
                <span class="text-xs text-slate-400">msgs/sec</span>
              </div>
            </div>

            <div class="rounded-xl border border-rose-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">TCPA Suppression</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-rose-400">{{ metrics.dnd_suppressed_count }}</span>
                <span class="text-xs text-rose-300 font-mono">DND Active</span>
              </div>
            </div>

            <div class="rounded-xl border border-amber-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Voice Campaigns</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-amber-400">{{ metrics.active_voice_campaigns }}</span>
                <span class="text-xs text-slate-400">TTS Audio</span>
              </div>
            </div>

            <div class="rounded-xl border border-teal-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Net Routing Margin</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-teal-400">${{ Number(metrics.total_margin_usd).toFixed(4) }}</span>
                <span class="text-xs text-emerald-400">Profit</span>
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
                ? 'border-pink-500 text-pink-400 bg-pink-500/10'
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

        <!-- TAB 1: INTERACTIVE DISPATCH & SEGMENT SIMULATOR -->
        <div v-if="activeTab === 'simulator'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Dispatch Form -->
            <div class="lg:col-span-7 space-y-4">
              <div class="rounded-xl border border-pink-500/30 bg-slate-900/80 p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>✉️</span> Interactive Message Dispatch & Pacing Engine
                  </h3>
                  <span class="rounded bg-pink-950 px-2 py-0.5 text-[10px] font-mono text-pink-400">
                    Dual-Tier Priority Queue
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                  <div>
                    <label class="block font-medium text-slate-300">Recipient Phone (E.164)</label>
                    <input
                      v-model="msgForm.recipient_phone"
                      type="text"
                      placeholder="+15125550101"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 font-mono text-white shadow-sm focus:border-pink-500 focus:outline-none"
                    />
                  </div>

                  <div>
                    <label class="block font-medium text-slate-300">Sender ID (Alphanumeric or Short Code)</label>
                    <input
                      v-model="msgForm.sender_id"
                      type="text"
                      placeholder="ZNERO_AUTH"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 font-mono text-white shadow-sm focus:border-pink-500 focus:outline-none"
                    />
                  </div>

                  <div>
                    <label class="block font-medium text-slate-300">Traffic Type & Tier</label>
                    <select
                      v-model="msgForm.traffic_type"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-pink-500 focus:outline-none"
                    >
                      <option value="transactional_otp">Transactional OTP / 2FA (Priority Tier 10 - Instant)</option>
                      <option value="marketing_bulk">Marketing Bulk Campaign (Priority Tier 2 - Fair Paced)</option>
                      <option value="voice_sms">Voice SMS Audio Dial (Priority Tier 9 - TTS Phone Call)</option>
                      <option value="whatsapp">WhatsApp Business Interactive (Priority Tier 7)</option>
                    </select>
                  </div>

                  <div class="flex items-center pt-5">
                    <label class="relative flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" v-model="msgForm.voice_tts_enabled" class="rounded border-slate-700 text-pink-600 focus:ring-pink-500">
                      <span class="text-xs text-slate-300 font-medium">Enable Voice SMS Audio Dial (TTS)</span>
                    </label>
                  </div>
                </div>

                <!-- Message Body with Live Segment Calculator -->
                <div class="space-y-1.5 text-xs">
                  <div class="flex justify-between">
                    <label class="font-medium text-slate-300">Message Body</label>
                    <div class="text-[11px] font-mono text-slate-400">
                      Encoding: <span class="text-cyan-400 font-bold">{{ liveSegment.encoding }}</span> |
                      Chars: <span class="text-white font-bold">{{ liveSegment.character_count }}</span> |
                      Segments: <span class="text-pink-400 font-bold">{{ liveSegment.segments }}</span>
                    </div>
                  </div>
                  <textarea
                    v-model="msgForm.message_body"
                    @input="onTextChange"
                    rows="3"
                    placeholder="Enter message content..."
                    class="block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-pink-500 focus:outline-none font-sans"
                  ></textarea>
                </div>

                <!-- Quick Presets -->
                <div class="flex flex-wrap gap-2 pt-1 text-[11px]">
                  <span class="text-slate-400">Quick Test Templates:</span>
                  <button
                    @click="setTemplate('Your Znero PBX one-time passcode is 738194. Valid for 5 minutes.')"
                    class="rounded bg-slate-800 px-2 py-0.5 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                  >
                    2FA Passcode
                  </button>
                  <button
                    @click="setTemplate('Critical alert: SIP trunk Telnyx-East experienced high packet loss. Failover engaged.')"
                    class="rounded bg-slate-800 px-2 py-0.5 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                  >
                    NOC Alert
                  </button>
                  <button
                    @click="setTemplate('Save 25% on enterprise PBX lines this month! Reply STOP to unsubscribe.')"
                    class="rounded bg-slate-800 px-2 py-0.5 text-slate-300 hover:text-white hover:bg-slate-700 transition"
                  >
                    Marketing Promo
                  </button>
                </div>

                <button
                  @click="dispatchMessage"
                  :disabled="isSending"
                  class="w-full inline-flex justify-center items-center gap-1.5 rounded-lg bg-pink-600 px-4 py-2.5 font-semibold text-white shadow-lg hover:bg-pink-500 transition disabled:opacity-50"
                >
                  <span v-if="isSending" class="animate-spin">⏳</span>
                  <span>{{ isSending ? 'Routing & Delivering...' : 'Dispatch Message (Simulate SMPP / REST)' }}</span>
                </button>
              </div>
            </div>

            <!-- Right: Result & DLR Output -->
            <div class="lg:col-span-5 space-y-4">
              <div v-if="dispatchResult" class="rounded-xl border p-5 shadow-xl space-y-3" :class="dispatchResult.success ? 'border-emerald-500/30 bg-slate-900/90' : 'border-rose-500/30 bg-rose-950/40'">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                  <span class="font-bold text-sm" :class="dispatchResult.success ? 'text-emerald-400' : 'text-rose-400'">
                    {{ dispatchResult.success ? '✓ MESSAGE DELIVERED (DLR: DELIVRD)' : '✕ SUBMISSION DROPPED' }}
                  </span>
                  <span class="rounded bg-slate-800 px-2 py-0.5 text-[10px] font-mono font-bold text-cyan-300">
                    {{ dispatchResult.protocol || 'COMPLIANCE_GATE' }}
                  </span>
                </div>

                <p v-if="!dispatchResult.success" class="text-xs text-rose-300">
                  {{ dispatchResult.reason }}
                </p>

                <div v-else class="space-y-2 text-xs text-slate-300">
                  <div class="flex justify-between border-b border-slate-800 pb-1">
                    <span class="text-slate-500">Carrier Gateway:</span>
                    <span class="font-semibold text-white">{{ dispatchResult.routed_via }}</span>
                  </div>
                  <div class="flex justify-between border-b border-slate-800 pb-1">
                    <span class="text-slate-500">Queue Priority:</span>
                    <span class="font-bold text-pink-400">Tier {{ dispatchResult.priority_tier }} (Protected)</span>
                  </div>
                  <div class="flex justify-between border-b border-slate-800 pb-1">
                    <span class="text-slate-500">Segments Billed:</span>
                    <span class="font-mono text-cyan-300">{{ dispatchResult.segment_info?.segments }} Segment(s) ({{ dispatchResult.segment_info?.encoding }})</span>
                  </div>
                  <div class="grid grid-cols-3 gap-2 pt-1 text-center font-mono">
                    <div class="rounded bg-slate-800/80 p-2">
                      <div class="text-[10px] text-slate-400">Cost</div>
                      <div class="text-rose-400 font-bold">${{ Number(dispatchResult.cost_incurred).toFixed(5) }}</div>
                    </div>
                    <div class="rounded bg-slate-800/80 p-2">
                      <div class="text-[10px] text-slate-400">Billed</div>
                      <div class="text-emerald-400 font-bold">${{ Number(dispatchResult.retail_billed).toFixed(5) }}</div>
                    </div>
                    <div class="rounded bg-slate-800/80 p-2">
                      <div class="text-[10px] text-slate-400">Margin</div>
                      <div class="text-teal-400 font-bold">+${{ Number(dispatchResult.profit_margin).toFixed(5) }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Regulatory Compliance Info Card -->
              <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-4 space-y-2 text-xs text-slate-300">
                <div class="font-bold text-white flex items-center gap-1.5">
                  <span>⚖️</span> TCPA & Regulatory Compliance Guarantees
                </div>
                <ul class="space-y-1 text-[11px] text-slate-400 list-disc list-inside">
                  <li><strong>TCPA DND Filtering:</strong> Numbers in suppression list blocked at gateway boundary before reaching carrier interconnect.</li>
                  <li><strong>Dual-Tier Queuing:</strong> Transactional OTP messages process in dedicated high-priority queue; never delayed by bulk promo blasts.</li>
                  <li><strong>GSM-7 / UCS-2 Auto-Detection:</strong> Dynamic encoding prevents accidental message bloating and billing surprises.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: CONNECTORS & SMPP BINDS -->
        <div v-if="activeTab === 'connectors'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="conn in connectors"
              :key="conn.uuid"
              class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg space-y-3"
            >
              <div class="flex items-center justify-between">
                <div>
                  <span
                    :class="[
                      conn.bind_status === 'bound' ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-rose-500/10 text-rose-400 ring-rose-500/30',
                      'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-semibold ring-1'
                    ]"
                  >
                    <span :class="conn.bind_status === 'bound' ? 'bg-emerald-400' : 'bg-rose-400'" class="h-1.5 w-1.5 rounded-full"></span>
                    {{ conn.bind_status.toUpperCase() }}
                  </span>
                  <h4 class="mt-2 text-sm font-bold text-white">{{ conn.provider_name }}</h4>
                </div>

                <span class="rounded bg-slate-800 px-2 py-0.5 font-mono text-xs text-pink-400 font-semibold">
                  {{ conn.protocol }}
                </span>
              </div>

              <div class="space-y-1.5 text-xs text-slate-300 font-mono">
                <div class="flex justify-between">
                  <span class="text-slate-500">Host / Port:</span>
                  <span class="text-white">{{ conn.host }}:{{ conn.port }}</span>
                </div>
                <div v-if="conn.smpp_system_id" class="flex justify-between">
                  <span class="text-slate-500">System ID / Mode:</span>
                  <span class="text-cyan-300">{{ conn.smpp_system_id }} ({{ conn.smpp_bind_mode }})</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Throughput Limit:</span>
                  <span class="text-blue-400 font-bold">{{ conn.tps_limit }} TPS (Pacing Ceiling)</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Success Rate:</span>
                  <span class="text-emerald-400 font-bold">{{ conn.success_rate_pct }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Cost Per Segment:</span>
                  <span class="text-slate-200">${{ Number(conn.cost_per_segment).toFixed(5) }}</span>
                </div>
              </div>

              <div class="pt-3 border-t border-slate-800 flex justify-end">
                <button
                  @click="toggleBind(conn)"
                  class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold hover:bg-slate-700 transition ring-1 ring-slate-700"
                  :class="conn.bind_status === 'bound' ? 'text-amber-400' : 'text-emerald-400'"
                >
                  {{ conn.bind_status === 'bound' ? 'Unbind / Quarantine' : 'Bind / Reconnect' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: ROUTING POLICIES -->
        <div v-if="activeTab === 'routing'" class="mt-6 space-y-6">
          <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 shadow">
            <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
              <thead class="bg-slate-900/90 text-slate-400 font-medium">
                <tr>
                  <th class="px-3.5 py-2.5">Policy / Traffic Type</th>
                  <th class="px-3 py-2.5">Priority Tier</th>
                  <th class="px-3 py-2.5">Primary Connector</th>
                  <th class="px-3 py-2.5">Failover Connector</th>
                  <th class="px-3 py-2.5">Sender ID Type</th>
                  <th class="px-3 py-2.5">Prefix Match</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 text-slate-300">
                <tr v-for="pol in policies" :key="pol.uuid" class="hover:bg-slate-800/40">
                  <td class="px-3.5 py-3">
                    <div class="font-bold text-white">{{ pol.policy_name }}</div>
                    <span class="rounded bg-pink-950 px-1.5 py-0.5 text-[10px] text-pink-400 font-mono">{{ pol.traffic_type }}</span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      :class="[
                        pol.queue_priority >= 8 ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-blue-500/10 text-blue-400 ring-blue-500/30',
                        'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1'
                      ]"
                    >
                      Tier {{ pol.queue_priority }}
                    </span>
                  </td>
                  <td class="px-3 py-3 font-semibold text-emerald-400">
                    {{ pol.primary_connector?.provider_name || 'Telnyx Direct' }}
                  </td>
                  <td class="px-3 py-3 text-slate-400">
                    {{ pol.failover_connector?.provider_name || 'Twilio Failover' }}
                  </td>
                  <td class="px-3 py-3 font-mono text-[11px] text-slate-300">
                    {{ pol.sender_id_type }}
                  </td>
                  <td class="px-3 py-3 font-mono text-cyan-300 font-bold">
                    {{ pol.prefix_match }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 4: TCPA & DND SUPPRESSION -->
        <div v-if="activeTab === 'dnd'" class="mt-6 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>🚫</span> TCPA Opt-Out & DND Suppression List
              </h3>
              <p class="text-xs text-slate-400">
                Numbers in this list are guaranteed to be dropped before dispatching to carrier routes.
              </p>
            </div>

            <!-- Add DND Form -->
            <div class="flex items-center gap-2">
              <input
                v-model="newDndPhone"
                type="text"
                placeholder="+15125550999"
                class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white shadow-sm focus:border-pink-500 focus:outline-none font-mono"
              />
              <button
                @click="addDndNumber"
                class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition shadow"
              >
                + Add Opt-Out
              </button>
            </div>
          </div>

          <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 shadow">
            <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
              <thead class="bg-slate-900/90 text-slate-400 font-medium">
                <tr>
                  <th class="px-3.5 py-2.5">Phone Number</th>
                  <th class="px-3 py-2.5">Suppression Reason</th>
                  <th class="px-3 py-2.5">Source Channel</th>
                  <th class="px-3 py-2.5">Opt-Out Date</th>
                  <th class="px-3 py-2.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 text-slate-300">
                <tr v-for="d in suppressions" :key="d.uuid" class="hover:bg-slate-800/40">
                  <td class="px-3.5 py-3 font-mono font-bold text-white">{{ d.phone_number }}</td>
                  <td class="px-3 py-3">
                    <span class="rounded bg-rose-950 px-2 py-0.5 text-[10px] text-rose-400 font-mono">
                      {{ d.reason }}
                    </span>
                  </td>
                  <td class="px-3 py-3 text-slate-400">{{ d.source_channel }}</td>
                  <td class="px-3 py-3 text-slate-400 font-mono">{{ d.opt_out_timestamp }}</td>
                  <td class="px-3 py-3 text-right">
                    <button
                      @click="removeDndNumber(d)"
                      class="text-xs text-rose-400 hover:text-rose-300 underline font-semibold"
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
        <div v-if="activeTab === 'queue'" class="mt-6 space-y-6">
          <h3 class="text-sm font-bold text-white flex items-center gap-2">
            <span>📋</span> Real-Time Delivery Receipts (DLR) Log
          </h3>

          <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 shadow">
            <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
              <thead class="bg-slate-900/90 text-slate-400 font-medium">
                <tr>
                  <th class="px-3.5 py-2.5">Time / Recipient</th>
                  <th class="px-3 py-2.5">Sender ID</th>
                  <th class="px-3 py-2.5">Traffic Type</th>
                  <th class="px-3 py-2.5">Message Excerpt</th>
                  <th class="px-3 py-2.5">DLR Status</th>
                  <th class="px-3 py-2.5 text-right">Cost / Margin</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 text-slate-300">
                <tr v-for="q in queueItems" :key="q.uuid" class="hover:bg-slate-800/40">
                  <td class="px-3.5 py-3">
                    <div class="font-mono font-bold text-white">{{ q.recipient_phone }}</div>
                    <div class="text-[10px] text-slate-500 font-mono">{{ q.created_at }}</div>
                  </td>
                  <td class="px-3 py-3 font-mono text-cyan-300 font-semibold">{{ q.sender_id }}</td>
                  <td class="px-3 py-3">
                    <span class="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] font-mono text-slate-300">
                      {{ q.traffic_type }}
                    </span>
                  </td>
                  <td class="px-3 py-3 max-w-[260px] truncate text-slate-400">
                    {{ q.message_body }}
                  </td>
                  <td class="px-3 py-3">
                    <span
                      :class="[
                        q.dlr_status === 'DELIVRD' ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' :
                        q.dlr_status === 'PENDING' ? 'bg-amber-500/10 text-amber-400 ring-amber-500/30' :
                        'bg-rose-500/10 text-rose-400 ring-rose-500/30',
                        'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1'
                      ]"
                    >
                      {{ q.dlr_status }}
                    </span>
                  </td>
                  <td class="px-3 py-3 text-right font-mono">
                    <div class="text-emerald-400 font-bold">${{ Number(q.retail_billed_amount).toFixed(4) }}</div>
                    <div class="text-[10px] text-slate-500">Cost: ${{ Number(q.cost_amount).toFixed(4) }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 6: VOICE SMS CAMPAIGNS -->
        <div v-if="activeTab === 'voice_campaigns'" class="mt-6 space-y-6">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>🎙️</span> Voice SMS Audio Broadcast Campaigns (TTS & DTMF)
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="camp in voiceCampaigns"
              :key="camp.uuid"
              class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 shadow space-y-3"
            >
              <div class="flex items-center justify-between">
                <span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-400 ring-1 ring-emerald-500/30">
                  {{ camp.status.toUpperCase() }}
                </span>
                <span class="text-xs font-mono text-cyan-300">{{ camp.tts_voice }}</span>
              </div>

              <h4 class="text-sm font-bold text-white">{{ camp.campaign_title }}</h4>
              <p class="text-xs text-slate-400 bg-slate-950 p-3 rounded-lg font-mono">
                "{{ camp.tts_script }}"
              </p>

              <div class="grid grid-cols-3 gap-2 pt-1 text-center font-mono text-xs">
                <div class="rounded bg-slate-800 p-2">
                  <div class="text-[10px] text-slate-400">Recipients</div>
                  <div class="text-white font-bold">{{ camp.recipients_count }}</div>
                </div>
                <div class="rounded bg-slate-800 p-2">
                  <div class="text-[10px] text-slate-400">Delivered</div>
                  <div class="text-blue-400 font-bold">{{ camp.completed_count }}</div>
                </div>
                <div class="rounded bg-slate-800 p-2">
                  <div class="text-[10px] text-slate-400">DTMF Ack</div>
                  <div class="text-emerald-400 font-bold">{{ camp.acknowledged_count }}</div>
                </div>
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
