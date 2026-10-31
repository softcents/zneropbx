<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Dark Spotlight Banner Header -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 via-indigo-950 to-slate-900 p-6 text-white shadow-xl ring-1 ring-white/10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-md bg-indigo-500/20 px-2 py-0.5 text-xs font-medium text-indigo-300 ring-1 ring-indigo-500/30">
                Phase 9 • Developer Ecosystem
              </span>
              <span class="inline-flex items-center rounded-md bg-emerald-500/20 px-2 py-0.5 text-xs font-medium text-emerald-300 ring-1 ring-emerald-500/30">
                OpenAPI 3.1 & REST v2
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
              API, Integration Ecosystem & Developer Portal
            </h1>
            <p class="mt-1 text-sm text-gray-300">
              Enterprise REST v2 gateway, scoped API keys, cryptographic HMAC-SHA256 webhook signatures, idempotency locks, and zero-replay defense.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="openNewKeyModal"
              class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
            >
              <KeyIcon class="h-4 w-4" />
              Generate API Key
            </button>
            <button
              @click="openNewWebhookModal"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition"
            >
              <SignalIcon class="h-4 w-4" />
              New Webhook
            </button>
          </div>
        </div>

        <!-- Telemetry KPI Summary Cards -->
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 border-t border-white/10 pt-4">
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Active API Keys</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-white">{{ metrics.active_credentials }}</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Live Webhooks</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-indigo-400">{{ metrics.active_webhooks }}</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Events Delivered</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-emerald-400">{{ Number(metrics.total_events_delivered).toLocaleString() }}</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Delivery Success</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-cyan-400">{{ metrics.webhook_delivery_success_rate }}%</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Avg Dispatch Latency</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-amber-400">{{ metrics.avg_webhook_dispatch_latency_ms }} ms</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Replay Quarantines</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-rose-400">{{ metrics.replay_attacks_quarantined }}</div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-indigo-600 text-indigo-600 font-semibold'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-3 px-1 text-sm flex items-center gap-2 transition'
            ]"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.name }}
            <span
              v-if="tab.count !== undefined"
              :class="[
                activeTab === tab.id ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600',
                'rounded-full px-2 py-0.5 text-xs font-semibold'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Alert for newly created key secret (One-time view) -->
      <div v-if="newlyCreatedSecret" class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-sm">
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-3">
            <ShieldExclamationIcon class="h-6 w-6 text-amber-600 flex-none" />
            <div>
              <h3 class="text-sm font-bold text-amber-900">Make sure to copy your API Secret Key now</h3>
              <p class="mt-1 text-xs text-amber-800">
                For security reasons, this secret is hashed and will <strong>never be shown again</strong>. Store it in your secure secrets vault.
              </p>
              <div class="mt-3 flex items-center gap-2">
                <code class="rounded bg-white px-3 py-1.5 font-mono text-sm font-bold text-gray-950 border border-amber-200 select-all">
                  {{ newlyCreatedSecret }}
                </code>
                <button
                  @click="copyToClipboard(newlyCreatedSecret)"
                  class="rounded bg-amber-200 px-2.5 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-300 transition"
                >
                  {{ copied ? 'Copied!' : 'Copy Secret' }}
                </button>
              </div>
            </div>
          </div>
          <button @click="newlyCreatedSecret = null" class="text-amber-500 hover:text-amber-700 text-sm font-semibold">
            Dismiss
          </button>
        </div>
      </div>

      <!-- TAB 1: API Keys & Security Scopes -->
      <div v-if="activeTab === 'keys'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Active API Keys & Tokens</h2>
              <p class="text-xs text-gray-500">Fine-grained token access with automatic rate limiting and scope enforcement.</p>
            </div>
            <span class="text-xs text-gray-500">Standard Bearer Token authentication via <code class="font-mono bg-gray-100 px-1 py-0.5 rounded">Authorization: Bearer zn_live_...</code></span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Credential Name</th>
                  <th scope="col" class="px-6 py-3">Key ID</th>
                  <th scope="col" class="px-6 py-3">Secret Preview</th>
                  <th scope="col" class="px-6 py-3">Access Scopes</th>
                  <th scope="col" class="px-6 py-3">Rate Limit</th>
                  <th scope="col" class="px-6 py-3">Status</th>
                  <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="cred in credentials" :key="cred.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-semibold text-gray-900">{{ cred.name }}</div>
                    <div class="text-xs text-gray-500">Expires: {{ formatDate(cred.expires_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <code class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-indigo-700 font-semibold">{{ cred.key_id }}</code>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                    {{ cred.secret_preview }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1 max-w-xs">
                      <span
                        v-for="sc in cred.scopes"
                        :key="sc"
                        class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-200"
                      >
                        {{ sc }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold tabular-nums text-gray-900">{{ cred.rate_limit_per_minute }}</span>
                    <span class="text-xs text-gray-500"> req/min</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        cred.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200',
                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 capitalize'
                      ]"
                    >
                      {{ cred.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-xs space-x-2">
                    <button
                      v-if="cred.status === 'active'"
                      @click="rotateKeySecret(cred)"
                      class="font-semibold text-indigo-600 hover:text-indigo-800"
                    >
                      Rotate Secret
                    </button>
                    <button
                      v-if="cred.status === 'active'"
                      @click="revokeKey(cred)"
                      class="font-semibold text-rose-600 hover:text-rose-800"
                    >
                      Revoke
                    </button>
                    <span v-else class="text-gray-400 italic">Revoked</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 2: Webhooks & Live Dispatch Simulator -->
      <div v-if="activeTab === 'webhooks'" class="space-y-6">
        <!-- Live Dispatch Simulator Widget -->
        <div class="rounded-xl bg-slate-900 p-6 text-white shadow-md ring-1 ring-white/10">
          <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
              <h3 class="text-base font-semibold text-white">Live Webhook Dispatch & HMAC Signature Simulator</h3>
              <p class="text-xs text-gray-400">Trigger test telemetry events with calculated <code class="text-emerald-400">X-Znero-Signature</code> headers and measure ingestion turnaround latency.</p>
            </div>
            <span class="rounded bg-indigo-500/20 px-2.5 py-1 text-xs font-semibold text-indigo-300 ring-1 ring-indigo-500/30">
              SHA-256 Digest
            </span>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
              <label class="block text-xs font-medium text-gray-300">Target Webhook Subscription</label>
              <select
                v-model="simWebhook.webhook_uuid"
                class="mt-1 block w-full rounded-lg border-0 bg-slate-800 py-2 pl-3 pr-8 text-sm text-white shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-indigo-500"
              >
                <option v-for="wh in webhooks" :key="wh.uuid" :value="wh.uuid">
                  {{ wh.name }} ({{ wh.target_url.slice(0, 35) }}...)
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-300">Event Type</label>
              <select
                v-model="simWebhook.event_type"
                class="mt-1 block w-full rounded-lg border-0 bg-slate-800 py-2 pl-3 pr-8 text-sm text-white shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-indigo-500"
              >
                <option value="call.completed">call.completed (Voice Leg Teardown)</option>
                <option value="sms.delivered">sms.delivered (Carrier DLR Handshake)</option>
                <option value="ai.sentiment_escalated">ai.sentiment_escalated (Agent Warm Handoff)</option>
                <option value="invoice.rated">invoice.rated (Realtime CDR Invoiced)</option>
              </select>
            </div>
            <div>
              <button
                @click="dispatchSimulatedWebhook"
                :disabled="simulating"
                class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition disabled:opacity-50"
              >
                <PaperAirplaneIcon class="h-4 w-4" />
                {{ simulating ? 'Dispatching...' : 'Dispatch Live Event' }}
              </button>
            </div>
          </div>

          <!-- Simulation Result Box -->
          <div v-if="simResult" class="mt-5 rounded-lg bg-slate-950 p-4 ring-1 ring-white/10 text-xs font-mono space-y-2">
            <div class="flex items-center justify-between text-emerald-400 font-bold border-b border-white/10 pb-2">
              <span class="flex items-center gap-1.5">
                <CheckCircleIcon class="h-4 w-4 text-emerald-400" />
                Dispatch Status: HTTP {{ simResult.http_status }} OK ({{ simResult.latency_ms }}ms turnaround)
              </span>
              <span class="text-gray-400">Idempotency Key: {{ simResult.idempotency_key }}</span>
            </div>
            <div class="text-indigo-300">
              <span class="text-gray-500">X-Znero-Signature:</span> {{ simResult.signature_header }}
            </div>
            <div class="text-gray-300 max-h-40 overflow-y-auto bg-slate-900 p-2.5 rounded border border-white/10">
              <pre>{{ JSON.stringify(simResult.payload, null, 2) }}</pre>
            </div>
          </div>
        </div>

        <!-- Webhook Subscriptions Table -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Webhook Subscriptions</h2>
            <span class="text-xs text-gray-500">Automatic exponential backoff with dead-letter queue isolation</span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Endpoint Name</th>
                  <th scope="col" class="px-6 py-3">Target URL</th>
                  <th scope="col" class="px-6 py-3">Signing Secret</th>
                  <th scope="col" class="px-6 py-3">Subscribed Events</th>
                  <th scope="col" class="px-6 py-3">Delivery Rate</th>
                  <th scope="col" class="px-6 py-3">Health Status</th>
                  <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="wh in webhooks" :key="wh.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">
                    {{ wh.name }}
                  </td>
                  <td class="px-6 py-4 font-mono text-xs text-gray-600 max-w-xs truncate" :title="wh.target_url">
                    {{ wh.target_url }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-indigo-700">
                    <span v-if="revealedSecrets[wh.uuid]">{{ wh.signing_secret }}</span>
                    <span v-else>whsec_••••••••••••••••</span>
                    <button
                      @click="toggleRevealSecret(wh.uuid)"
                      class="ml-2 text-gray-400 hover:text-gray-600 text-xs font-sans"
                    >
                      {{ revealedSecrets[wh.uuid] ? 'Hide' : 'Reveal' }}
                    </button>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1 max-w-xs">
                      <span
                        v-for="ev in wh.subscribed_events"
                        :key="ev"
                        class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200"
                      >
                        {{ ev }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold tabular-nums text-gray-900">{{ Number(wh.total_delivered).toLocaleString() }}</span>
                    <span v-if="wh.total_failed > 0" class="text-xs text-rose-500 font-semibold"> ({{ wh.total_failed }} failed)</span>
                    <span v-else class="text-xs text-emerald-600 font-semibold"> (100% OK)</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                      {{ wh.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-xs space-x-2">
                    <button
                      @click="triggerWebhookTest(wh)"
                      class="font-semibold text-emerald-600 hover:text-emerald-800"
                    >
                      Test
                    </button>
                    <button
                      @click="deleteWebhook(wh)"
                      class="font-semibold text-rose-600 hover:text-rose-800"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: Live Delivery Logs -->
      <div v-if="activeTab === 'logs'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Webhook Delivery Audit Logs</h2>
              <p class="text-xs text-gray-500">Immutable trace with idempotency keys, HMAC signatures, and measured millisecond response times.</p>
            </div>
            <span class="text-xs text-gray-500">Showing last 25 delivery dispatches</span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Timestamp</th>
                  <th scope="col" class="px-6 py-3">Event Type</th>
                  <th scope="col" class="px-6 py-3">Idempotency Key</th>
                  <th scope="col" class="px-6 py-3">HTTP Status</th>
                  <th scope="col" class="px-6 py-3">Latency</th>
                  <th scope="col" class="px-6 py-3">Signature (HMAC-SHA256)</th>
                  <th scope="col" class="px-6 py-3 text-right">Payload</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="log in webhookLogs" :key="log.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                    {{ formatDate(log.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-mono font-semibold text-indigo-700 ring-1 ring-indigo-200">
                      {{ log.event_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-700">
                    {{ log.idempotency_key }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        log.http_status >= 200 && log.http_status < 300 ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200',
                        'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold ring-1'
                      ]"
                    >
                      {{ log.http_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs tabular-nums text-gray-700">
                    {{ log.latency_ms }} ms
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-500 max-w-xs truncate" :title="log.request_signature">
                    {{ log.request_signature }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <button
                      @click="viewPayload(log)"
                      class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                    >
                      View JSON
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 4: Interactive API Explorer & Docs -->
      <div v-if="activeTab === 'explorer'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Endpoints Sidebar -->
          <div class="lg:col-span-1 space-y-2">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">REST v2 Endpoints</div>
            <button
              v-for="(ep, idx) in catalog"
              :key="idx"
              @click="selectedEndpoint = ep"
              :class="[
                selectedEndpoint?.endpoint === ep.endpoint
                  ? 'bg-indigo-50 border-indigo-500 text-indigo-900 shadow-sm'
                  : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50',
                'w-full text-left p-3.5 rounded-xl border text-sm transition flex flex-col gap-1'
              ]"
            >
              <div class="flex items-center justify-between">
                <span
                  :class="[
                    ep.method === 'GET' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800',
                    'font-mono font-bold text-xs px-2 py-0.5 rounded'
                  ]"
                >
                  {{ ep.method }}
                </span>
                <span class="text-xs font-mono text-gray-500">{{ ep.required_scope }}</span>
              </div>
              <div class="font-mono text-xs font-bold text-gray-900 truncate mt-1">{{ ep.endpoint }}</div>
              <div class="text-xs text-gray-500 truncate">{{ ep.summary }}</div>
            </button>
          </div>

          <!-- Endpoint Details & Interactive Snippets -->
          <div class="lg:col-span-2 space-y-6">
            <div v-if="selectedEndpoint" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 space-y-5">
              <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        selectedEndpoint.method === 'GET' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800',
                        'font-mono font-bold text-xs px-2.5 py-1 rounded'
                      ]"
                    >
                      {{ selectedEndpoint.method }}
                    </span>
                    <h2 class="font-mono text-base font-bold text-gray-900">{{ selectedEndpoint.endpoint }}</h2>
                  </div>
                  <p class="mt-1 text-sm text-gray-600">{{ selectedEndpoint.description }}</p>
                </div>
                <div class="text-right">
                  <div class="text-xs text-gray-500">Required OAuth Scope</div>
                  <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                    {{ selectedEndpoint.required_scope }}
                  </span>
                </div>
              </div>

              <!-- Parameters Table -->
              <div v-if="selectedEndpoint.parameters && selectedEndpoint.parameters.length > 0">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Request Parameters</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                  <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 font-medium text-gray-500 uppercase">
                      <tr>
                        <th class="px-4 py-2">Parameter</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Required</th>
                        <th class="px-4 py-2">Example</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                      <tr v-for="param in selectedEndpoint.parameters" :key="param.name">
                        <td class="px-4 py-2 font-mono font-bold text-indigo-700">{{ param.name }}</td>
                        <td class="px-4 py-2 font-mono text-gray-600">{{ param.type }}</td>
                        <td class="px-4 py-2">
                          <span v-if="param.required" class="text-rose-600 font-bold">Required</span>
                          <span v-else class="text-gray-400">Optional</span>
                        </td>
                        <td class="px-4 py-2 font-mono text-gray-500">{{ param.example }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- cURL Generator -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Production cURL Command</h3>
                  <button
                    @click="copyToClipboard(selectedEndpoint.curl_sample)"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                  >
                    Copy cURL
                  </button>
                </div>
                <div class="rounded-lg bg-slate-950 p-4 font-mono text-xs text-indigo-300 overflow-x-auto ring-1 ring-white/10">
                  <pre>{{ selectedEndpoint.curl_sample }}</pre>
                </div>
              </div>

              <!-- Sample JSON Response -->
              <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Sample JSON Response (HTTP 200 OK)</h3>
                <div class="rounded-lg bg-slate-900 p-4 font-mono text-xs text-emerald-400 overflow-x-auto ring-1 ring-white/10">
                  <pre>{{ JSON.stringify(selectedEndpoint.response_sample, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL: Generate API Key -->
      <div v-if="showNewKeyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Issue New Enterprise API Key</h3>
            <button @click="showNewKeyModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Credential Name / Service Purpose</label>
              <input
                v-model="newKeyForm.name"
                type="text"
                placeholder="e.g. Data Warehouse ETL Pipeline"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Assigned OAuth Scopes</label>
              <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg bg-gray-50">
                <label v-for="scope in metrics.available_scopes" :key="scope" class="flex items-center gap-2 text-xs text-gray-800">
                  <input
                    type="checkbox"
                    :value="scope"
                    v-model="newKeyForm.scopes"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <code>{{ scope }}</code>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700">Rate Limit (req / min)</label>
                <input
                  v-model.number="newKeyForm.rate_limit_per_minute"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700">Validity (Months)</label>
                <input
                  v-model.number="newKeyForm.expires_in_months"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="showNewKeyModal = false"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitNewKey"
              :disabled="savingKey || !newKeyForm.name || newKeyForm.scopes.length === 0"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
            >
              {{ savingKey ? 'Generating...' : 'Issue API Key' }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: New Webhook -->
      <div v-if="showNewWebhookModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Create Webhook Subscription</h3>
            <button @click="showNewWebhookModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Webhook Name</label>
              <input
                v-model="newWebhookForm.name"
                type="text"
                placeholder="e.g. Slack Incident Alert Channel"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">HTTPS Target URL</label>
              <input
                v-model="newWebhookForm.target_url"
                type="url"
                placeholder="https://your-domain.com/webhooks/znero"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Subscribed Events</label>
              <div class="grid grid-cols-2 gap-2 p-2 border rounded-lg bg-gray-50">
                <label v-for="ev in ['call.completed', 'call.recorded', 'sms.delivered', 'ai.sentiment_escalated', 'invoice.rated']" :key="ev" class="flex items-center gap-2 text-xs text-gray-800">
                  <input
                    type="checkbox"
                    :value="ev"
                    v-model="newWebhookForm.subscribed_events"
                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                  />
                  <code>{{ ev }}</code>
                </label>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="showNewWebhookModal = false"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitNewWebhook"
              :disabled="savingWebhook || !newWebhookForm.name || !newWebhookForm.target_url"
              class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition disabled:opacity-50"
            >
              {{ savingWebhook ? 'Saving...' : 'Register Subscription' }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: View Log JSON Payload -->
      <div v-if="activeLogPayload" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
              <h3 class="text-base font-bold text-gray-900">Delivery Event Payload JSON</h3>
              <p class="text-xs text-gray-500 font-mono">Event: {{ activeLogPayload.event_type }} | Idempotency: {{ activeLogPayload.idempotency_key }}</p>
            </div>
            <button @click="activeLogPayload = null" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 rounded-lg bg-slate-950 p-4 font-mono text-xs text-emerald-400 max-h-96 overflow-y-auto">
            <pre>{{ JSON.stringify(activeLogPayload.payload_json, null, 2) }}</pre>
          </div>

          <div class="mt-4 flex justify-end">
            <button
              @click="activeLogPayload = null"
              class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-800 transition"
            >
              Close
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
import MainLayout from '@/Layouts/MainLayout.vue'
import {
  KeyIcon,
  SignalIcon,
  ShieldExclamationIcon,
  CommandLineIcon,
  DocumentTextIcon,
  CheckCircleIcon,
  PaperAirplaneIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  credentials: { type: Array, default: () => [] },
  webhooks: { type: Array, default: () => [] },
  webhookLogs: { type: Array, default: () => [] },
  catalog: { type: Array, default: () => [] },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('keys')
const selectedEndpoint = ref(props.catalog[0] || null)
const revealedSecrets = ref({})
const newlyCreatedSecret = ref(null)
const copied = ref(false)

const tabs = [
  { id: 'keys', name: 'API Keys & Scopes', icon: KeyIcon, count: props.credentials.length },
  { id: 'webhooks', name: 'Webhooks & Signing', icon: SignalIcon, count: props.webhooks.length },
  { id: 'logs', name: 'Delivery Audit Logs', icon: DocumentTextIcon, count: props.webhookLogs.length },
  { id: 'explorer', name: 'OpenAPI Explorer & Docs', icon: CommandLineIcon, count: props.catalog.length },
]

// Modal states
const showNewKeyModal = ref(false)
const savingKey = ref(false)
const newKeyForm = ref({
  name: '',
  scopes: ['calls:read', 'sms:send'],
  rate_limit_per_minute: 600,
  expires_in_months: 12,
})

const showNewWebhookModal = ref(false)
const savingWebhook = ref(false)
const newWebhookForm = ref({
  name: '',
  target_url: '',
  subscribed_events: ['call.completed'],
})

// Simulation states
const simWebhook = ref({
  webhook_uuid: props.webhooks[0]?.uuid || '',
  event_type: 'call.completed',
})
const simulating = ref(false)
const simResult = ref(null)

const activeLogPayload = ref(null)

const formatDate = (dateStr) => {
  if (!dateStr) return 'Never'
  const d = new Date(dateStr)
  return d.toLocaleString()
}

const toggleRevealSecret = (uuid) => {
  revealedSecrets.value[uuid] = !revealedSecrets.value[uuid]
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2500)
  } catch (err) {
    console.error('Failed to copy', err)
  }
}

const openNewKeyModal = () => {
  newKeyForm.value = {
    name: '',
    scopes: ['calls:read', 'sms:send'],
    rate_limit_per_minute: 600,
    expires_in_months: 12,
  }
  showNewKeyModal.value = true
}

const submitNewKey = async () => {
  savingKey.value = true
  try {
    const res = await axios.post('/api/ecosystem/credentials', newKeyForm.value)
    props.credentials.unshift(res.data.credential)
    newlyCreatedSecret.value = res.data.raw_secret
    showNewKeyModal.value = false
  } catch (err) {
    alert('Failed to issue API Key: ' + (err.response?.data?.message || err.message))
  } finally {
    savingKey.value = false
  }
}

const rotateKeySecret = async (cred) => {
  if (!confirm(`Are you sure you want to rotate the secret for "${cred.name}"? The old secret will stop working immediately.`)) return
  try {
    const res = await axios.post(`/api/ecosystem/credentials/${cred.uuid}/rotate`)
    cred.secret_preview = res.data.credential.secret_preview
    newlyCreatedSecret.value = res.data.raw_secret
  } catch (err) {
    alert('Failed to rotate secret: ' + (err.response?.data?.message || err.message))
  }
}

const revokeKey = async (cred) => {
  if (!confirm(`Are you sure you want to permanently revoke API Key "${cred.name}"?`)) return
  try {
    await axios.post(`/api/ecosystem/credentials/${cred.uuid}/revoke`)
    cred.status = 'revoked'
  } catch (err) {
    alert('Failed to revoke API key: ' + (err.response?.data?.message || err.message))
  }
}

const openNewWebhookModal = () => {
  newWebhookForm.value = {
    name: '',
    target_url: '',
    subscribed_events: ['call.completed'],
  }
  showNewWebhookModal.value = true
}

const submitNewWebhook = async () => {
  savingWebhook.value = true
  try {
    const res = await axios.post('/api/ecosystem/webhooks', newWebhookForm.value)
    props.webhooks.unshift(res.data)
    showNewWebhookModal.value = false
  } catch (err) {
    alert('Failed to register webhook: ' + (err.response?.data?.message || err.message))
  } finally {
    savingWebhook.value = false
  }
}

const deleteWebhook = async (wh) => {
  if (!confirm(`Delete webhook subscription "${wh.name}"?`)) return
  try {
    await axios.delete(`/api/ecosystem/webhooks/${wh.uuid}`)
    const idx = props.webhooks.findIndex(w => w.uuid === wh.uuid)
    if (idx !== -1) props.webhooks.splice(idx, 1)
  } catch (err) {
    alert('Failed to delete webhook: ' + (err.response?.data?.message || err.message))
  }
}

const triggerWebhookTest = (wh) => {
  simWebhook.value.webhook_uuid = wh.uuid
  activeTab.value = 'webhooks'
  dispatchSimulatedWebhook()
}

const dispatchSimulatedWebhook = async () => {
  if (!simWebhook.value.webhook_uuid) {
    alert('Please select a webhook subscription first')
    return
  }
  simulating.value = true
  try {
    const res = await axios.post(`/api/ecosystem/webhooks/${simWebhook.value.webhook_uuid}/test`, {
      event_type: simWebhook.value.event_type,
    })
    simResult.value = res.data
    if (res.data.log) {
      props.webhookLogs.unshift(res.data.log)
    }
  } catch (err) {
    alert('Simulation error: ' + (err.response?.data?.message || err.message))
  } finally {
    simulating.value = false
  }
}

const viewPayload = (log) => {
  activeLogPayload.value = log
}
</script>
