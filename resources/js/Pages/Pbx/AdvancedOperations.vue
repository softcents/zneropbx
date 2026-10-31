<template>
  <MainLayout>
    <div class="m-3 space-y-6">
      <!-- Clean Native Software Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-md bg-cyan-50 px-2 py-0.5 text-xs font-semibold text-cyan-700 ring-1 ring-inset ring-cyan-700/10">
                <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                P4 — Fleet Governance &amp; Operations
              </span>
              <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-700/10">
                Ray Baum's Act Ready
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">
              PBX Advanced Operations &amp; Fleet Governance
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              E911 dispatchable location mapping, caller-ID normalization, SIP/WebRTC device lifecycle, immutable recording legal holds, and Class of Service barring.
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
            <div class="text-xs font-medium text-gray-500">Total Endpoints</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-gray-900">{{ metrics.total_devices }}</span>
              <span class="text-xs text-cyan-600 font-mono">{{ metrics.webrtc_count }} WebRTC</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Registered Online</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-emerald-600">{{ metrics.online_devices }}</span>
              <span class="text-xs text-gray-400 font-mono">/ {{ metrics.total_devices }}</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Fleet Latency RTT</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-indigo-600">{{ metrics.avg_rtt_ms }}</span>
              <span class="text-xs text-gray-400">ms avg</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Kari's Law</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-indigo-600">100%</span>
              <span class="text-xs text-emerald-600">Direct 911</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Ray Baum's Act</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-amber-600">Active</span>
              <span class="text-xs text-gray-400">ELIN Ready</span>
            </div>
          </div>

          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Active Legal Holds</div>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="text-xl font-bold text-rose-600">{{ metrics.active_legal_holds }}</span>
              <span class="text-xs text-rose-600 font-mono">Immutable</span>
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

      <!-- TAB 1: EMERGENCY ROUTING & E911 -->
      <div v-if="activeTab === 'emergency'" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <span>🚨</span> E911 Emergency Location Identification &amp; Regulatory Compliance
            </h2>
            <p class="text-xs text-gray-500">
              Mandatory Kari's Law (direct 911 dialing without trunk access codes) &amp; Ray Baum's Act (precise room/suite dispatchable location).
            </p>
          </div>
          <button
            @click="openE911Modal(emergencyLocations[0])"
            class="inline-flex items-center gap-1.5 rounded-md bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-500 transition"
          >
            <span>⚡</span> Run Simulated 911 Dispatch
          </button>
        </div>

        <!-- Location Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="loc in emergencyLocations"
            :key="loc.uuid"
            class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 hover:ring-gray-300 transition"
          >
            <div class="flex items-center justify-between">
              <span
                :class="[
                  loc.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20',
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1'
                ]"
              >
                {{ loc.status.toUpperCase() }}
              </span>
              <span class="text-xs text-gray-500 font-mono">ELIN: {{ loc.elin_number }}</span>
            </div>

            <h3 class="mt-3 text-sm font-bold text-gray-900 leading-snug">{{ loc.location_name }}</h3>

            <div class="mt-3 space-y-1.5 text-xs text-gray-600">
              <div class="flex items-start gap-1.5">
                <span class="text-gray-400">📍</span>
                <span>{{ loc.street_address }}<br><span class="text-indigo-600 font-semibold">{{ loc.unit_suite }}</span>, {{ loc.city }}, {{ loc.state_province }} {{ loc.postal_code }}</span>
              </div>
              <div class="flex items-center gap-1.5 pt-1 text-gray-500">
                <span class="text-gray-400">🏢</span>
                <span class="truncate">{{ loc.psap_name }}</span>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
              <div class="flex items-center gap-2">
                <span :class="loc.karis_law_compliant ? 'text-emerald-700' : 'text-rose-600'">✓ Kari's Law</span>
                <span :class="loc.ray_baums_compliant ? 'text-emerald-700' : 'text-rose-600'">✓ Ray Baum's</span>
              </div>
              <button
                @click="openE911Modal(loc)"
                class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline"
              >
                Test Route
              </button>
            </div>
          </div>
        </div>

        <!-- Regulatory Checklist Info -->
        <div class="rounded-lg bg-indigo-50/50 p-4 ring-1 ring-indigo-100">
          <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Federal Regulatory Invariants Enforced</h4>
          <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-700">
            <div class="flex items-start gap-2">
              <span class="text-emerald-600 font-bold">✔</span>
              <div>
                <strong class="text-gray-900">Kari's Law (47 U.S.C. § 623):</strong> Direct dial 911 enabled on all endpoints without dialing 9 or 8 prefix. Automated simultaneous notification sent to security desk upon trigger.
              </div>
            </div>
            <div class="flex items-start gap-2">
              <span class="text-emerald-600 font-bold">✔</span>
              <div>
                <strong class="text-gray-900">Ray Baum's Act Section 506:</strong> Dispatchable location automatically transmitted to PSAP, including validated floor, suite, and room coordinates alongside street address.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: CALLER-ID & NORMALIZATION -->
      <div v-if="activeTab === 'caller_id'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left: Rules Table -->
          <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>🆔</span> Outbound Presentation Rules &amp; Regex Pipeline
              </h3>
              <span class="text-xs text-gray-500">{{ callerIdRules.length }} Active Rules</span>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
              <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-gray-700 font-semibold">
                  <tr>
                    <th class="px-3.5 py-2.5">Rule / Scope</th>
                    <th class="px-3 py-2.5">Outbound ANI (Number)</th>
                    <th class="px-3 py-2.5">CNAM (Display Name)</th>
                    <th class="px-3 py-2.5">Format</th>
                    <th class="px-3 py-2.5 text-right">Priority</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-900">
                  <tr v-for="rule in callerIdRules" :key="rule.uuid" class="hover:bg-gray-50">
                    <td class="px-3.5 py-3 font-semibold text-gray-900">
                      <div>{{ rule.rule_name }}</div>
                      <span class="inline-block rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] text-indigo-700 ring-1 ring-indigo-700/10">
                        {{ rule.scope_type }}: {{ rule.scope_value || 'ALL' }}
                      </span>
                    </td>
                    <td class="px-3 py-3 font-mono text-indigo-600 font-semibold">{{ rule.caller_id_number }}</td>
                    <td class="px-3 py-3 text-gray-600">{{ rule.caller_id_name }}</td>
                    <td class="px-3 py-3">
                      <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-700 ring-1 ring-gray-200">
                        {{ rule.normalization_format }}
                      </span>
                    </td>
                    <td class="px-3 py-3 text-right font-mono font-bold text-amber-600">{{ rule.priority }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Right: Interactive Normalization Sandbox -->
          <div class="lg:col-span-5 space-y-4">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
              <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>🧪</span> Interactive Caller-ID &amp; E.164 Sandbox
              </h3>
              <p class="mt-1 text-xs text-gray-500">
                Simulate how an outbound call from an extension or department is normalized and presented.
              </p>

              <div class="mt-4 space-y-3 text-xs">
                <div>
                  <label class="block font-medium text-gray-700">Calling Extension</label>
                  <select
                    v-model="simCid.extension"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
                  >
                    <option value="">Global Default</option>
                    <option v-for="ext in extensions" :key="ext.extension" :value="ext.extension">
                      {{ ext.extension }} - {{ ext.description }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block font-medium text-gray-700">Department Scope</label>
                  <select
                    v-model="simCid.department"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
                  >
                    <option value="">None</option>
                    <option value="Support">Support Desk</option>
                    <option value="Sales">Enterprise Sales</option>
                    <option value="Executive">Executive Office</option>
                  </select>
                </div>

                <div>
                  <label class="block font-medium text-gray-700">Dialed Number (Input)</label>
                  <input
                    v-model="simCid.dialed_number"
                    type="text"
                    placeholder="e.g. 5125551234 or 915125551234"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 font-mono text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600"
                  />
                </div>

                <button
                  @click="runCidSimulation"
                  :disabled="isSimulatingCid"
                  class="w-full mt-2 inline-flex justify-center items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
                >
                  <span v-if="isSimulatingCid" class="animate-spin">⏳</span>
                  <span>{{ isSimulatingCid ? 'Normalizing...' : 'Evaluate Outbound Identity' }}</span>
                </button>
              </div>

              <!-- Simulation Result Card -->
              <div v-if="cidResult" class="mt-4 rounded-lg bg-gray-50 p-3.5 space-y-2 text-xs ring-1 ring-gray-200">
                <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                  <span class="font-bold text-gray-900">Matched Policy</span>
                  <span class="rounded bg-indigo-50 px-2 py-0.5 font-semibold text-indigo-700 ring-1 ring-indigo-700/10">{{ cidResult.matched_rule }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1">
                  <div>
                    <div class="text-[10px] text-gray-500">Normalized Destination</div>
                    <div class="font-mono font-bold text-gray-900">{{ cidResult.normalized_destination }}</div>
                  </div>
                  <div>
                    <div class="text-[10px] text-gray-500">Presentation ANI</div>
                    <div class="font-mono font-bold text-emerald-700">{{ cidResult.effective_caller_id_number }}</div>
                  </div>
                  <div>
                    <div class="text-[10px] text-gray-500">Display CNAM</div>
                    <div class="font-semibold text-gray-800">{{ cidResult.effective_caller_id_name }}</div>
                  </div>
                  <div>
                    <div class="text-[10px] text-gray-500">Emergency Caller-ID</div>
                    <div class="font-mono text-rose-600 font-bold">{{ cidResult.emergency_caller_id_number }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: FLEET & WEBRTC INVENTORY -->
      <div v-if="activeTab === 'fleet'" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>📱</span> SIP Hardware Fleet &amp; WebRTC Softphone Lifecycle
            </h3>
            <p class="text-xs text-gray-500">
              Live registration telemetry, cipher profiles, remote reboot via SIP NOTIFY check-sync.
            </p>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead class="bg-gray-50 text-gray-700 font-semibold">
              <tr>
                <th class="px-3.5 py-2.5">Endpoint / MAC</th>
                <th class="px-3 py-2.5">Vendor &amp; Model</th>
                <th class="px-3 py-2.5">Assigned Ext</th>
                <th class="px-3 py-2.5">IP &amp; Transport</th>
                <th class="px-3 py-2.5">SIP Status</th>
                <th class="px-3 py-2.5">Latency</th>
                <th class="px-3 py-2.5">Firmware / Config</th>
                <th class="px-3 py-2.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-900">
              <tr v-for="dev in deviceFleet" :key="dev.uuid" class="hover:bg-gray-50">
                <td class="px-3.5 py-3">
                  <div class="font-mono font-bold text-gray-900">{{ dev.mac_address }}</div>
                </td>
                <td class="px-3 py-3">
                  <div class="font-semibold text-gray-900">{{ dev.vendor }}</div>
                  <div class="text-[11px] text-gray-500 truncate max-w-[180px]">{{ dev.model }}</div>
                </td>
                <td class="px-3 py-3">
                  <span class="rounded bg-indigo-50 px-2 py-0.5 font-mono text-indigo-700 font-semibold ring-1 ring-indigo-700/10">
                    Ext {{ dev.assigned_extension || 'N/A' }}
                  </span>
                </td>
                <td class="px-3 py-3 font-mono text-gray-600">
                  <div>{{ dev.ip_address }}</div>
                  <span :class="[
                    dev.transport === 'TLS' || dev.transport === 'WSS' ? 'text-emerald-700' : 'text-amber-700',
                    'text-[10px] font-semibold'
                  ]">
                    🔒 {{ dev.transport }} (SRTP)
                  </span>
                </td>
                <td class="px-3 py-3">
                  <span
                    :class="[
                      dev.sip_status === 'registered' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20',
                      'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1'
                    ]"
                  >
                    <span :class="dev.sip_status === 'registered' ? 'bg-emerald-500' : 'bg-rose-500'" class="h-1.5 w-1.5 rounded-full"></span>
                    {{ dev.sip_status.toUpperCase() }}
                  </span>
                </td>
                <td class="px-3 py-3 font-mono">
                  <span v-if="dev.sip_status === 'registered'" class="text-indigo-600 font-bold">{{ dev.rtt_ms }} ms</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-3 py-3">
                  <div class="font-mono text-[11px] text-gray-700">{{ dev.firmware_version }}</div>
                  <div class="text-[10px] text-gray-500 font-mono">{{ dev.config_version }}</div>
                </td>
                <td class="px-3 py-3 text-right">
                  <button
                    @click="triggerReboot(dev)"
                    :disabled="rebootingUuids.includes(dev.uuid)"
                    class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition disabled:opacity-50"
                  >
                    <span v-if="rebootingUuids.includes(dev.uuid)" class="animate-spin">⏳</span>
                    <span>{{ rebootingUuids.includes(dev.uuid) ? 'Rebooting...' : 'Reboot / Resync' }}</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Reboot Log Toast Notification -->
        <div v-if="rebootNotice" class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-xs">
          <div class="flex items-center justify-between">
            <span class="font-bold text-gray-900">SIP NOTIFY Event Dispatched</span>
            <button @click="rebootNotice = null" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>
          <pre class="mt-2 overflow-x-auto rounded bg-gray-900 p-3 font-mono text-[11px] text-gray-100">{{ rebootNotice }}</pre>
        </div>
      </div>

      <!-- TAB 4: RECORDING RETENTION & LEGAL HOLDS -->
      <div v-if="activeTab === 'retention'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left: Retention Rules -->
          <div class="lg:col-span-5 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>⏳</span> Retention &amp; Lifecycle Storage Rules
            </h3>

            <div class="space-y-3">
              <div
                v-for="rule in retentionRules"
                :key="rule.uuid"
                class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-gray-900 text-xs">{{ rule.rule_name }}</span>
                  <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-mono text-indigo-700 font-semibold ring-1 ring-indigo-700/10">
                    {{ rule.retention_days }} Days
                  </span>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] text-gray-500">
                  <div>
                    Storage Tier: <span class="font-mono text-gray-800">{{ rule.storage_tier }}</span>
                  </div>
                  <div>
                    Cipher: <span class="font-mono text-emerald-700">{{ rule.encryption_algorithm }}</span>
                  </div>
                </div>

                <div class="mt-2 text-[10px] text-gray-400 truncate font-mono">
                  KMS Key: {{ rule.kms_key_id }}
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Immutable Legal Holds Ledger -->
          <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>⚖️</span> Immutability Ledger: Active Legal Holds
              </h3>
              <span class="text-xs text-rose-600 font-semibold">{{ metrics.active_legal_holds }} Purge-Protected</span>
            </div>

            <div class="space-y-3">
              <div
                v-for="hold in legalHolds"
                :key="hold.uuid"
                class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 hover:ring-gray-300 transition"
              >
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="font-mono font-bold text-gray-900 text-xs">{{ hold.case_number }}</span>
                      <span
                        :class="[
                          hold.status === 'active' ? 'bg-rose-50 text-rose-700 ring-rose-600/20' : 'bg-gray-100 text-gray-600 ring-gray-200',
                          'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1'
                        ]"
                      >
                        {{ hold.status.toUpperCase() }}
                      </span>
                    </div>
                    <div class="mt-1 text-xs font-semibold text-gray-800">{{ hold.case_title }}</div>
                  </div>

                  <button
                    @click="toggleHold(hold)"
                    class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition"
                  >
                    {{ hold.status === 'active' ? 'Release Hold' : 'Reinstate Hold' }}
                  </button>
                </div>

                <p class="mt-2 text-xs text-gray-500">{{ hold.reason_notes }}</p>

                <div class="mt-3 pt-2.5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[10px] text-gray-500">
                  <div>
                    Custodian: <span class="text-gray-700">{{ hold.custodian_name }}</span> | Counsel: <span class="text-gray-700">{{ hold.legal_officer }}</span>
                  </div>
                  <div class="font-mono text-indigo-600 truncate max-w-[240px]">
                    SHA256: {{ hold.tamper_checksum_sha256.substring(0, 16) }}...
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 5: CALL PERMISSIONS & CLASS OF SERVICE -->
      <div v-if="activeTab === 'permissions'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left: Permission Profiles -->
          <div class="lg:col-span-7 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <span>🛡️</span> Class of Service (CoS) Profiles &amp; Outbound Barring
            </h3>

            <div class="space-y-3">
              <div
                v-for="prof in permissionProfiles"
                :key="prof.uuid"
                class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200"
              >
                <div class="flex items-center justify-between">
                  <h4 class="font-bold text-gray-900 text-xs">{{ prof.profile_name }}</h4>
                  <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-mono text-indigo-700 font-semibold ring-1 ring-indigo-700/10">
                    Max {{ prof.max_concurrent_calls }} Calls
                  </span>
                </div>

                <p class="mt-1 text-xs text-gray-500">{{ prof.description }}</p>

                <div class="mt-3 flex flex-wrap gap-2 text-[11px]">
                  <span :class="prof.allow_international ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20'" class="rounded px-2 py-0.5 ring-1">
                    {{ prof.allow_international ? '✓ International' : '✕ Int\'l Barred' }}
                  </span>
                  <span :class="prof.allow_national ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20'" class="rounded px-2 py-0.5 ring-1">
                    {{ prof.allow_national ? '✓ National' : '✕ National Barred' }}
                  </span>
                  <span :class="prof.allow_toll_free ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20'" class="rounded px-2 py-0.5 ring-1">
                    {{ prof.allow_toll_free ? '✓ Toll-Free' : '✕ Toll-Free Barred' }}
                  </span>
                  <span :class="prof.allow_premium_rate ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20'" class="rounded px-2 py-0.5 ring-1">
                    {{ prof.allow_premium_rate ? '✓ 900/976 Toll' : '✕ 900 Barred' }}
                  </span>
                </div>

                <div class="mt-2.5 text-[10px] text-gray-500 font-mono">
                  Schedule: {{ prof.time_restriction_type }} ({{ prof.start_time || '00:00' }} - {{ prof.end_time || '23:59' }})
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Interactive Permission Evaluator -->
          <div class="lg:col-span-5 space-y-4">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
              <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>🚦</span> Call Barring Gatekeeper Simulator
              </h3>
              <p class="mt-1 text-xs text-gray-500">
                Verify whether an extension call to a destination is authorized by Class of Service policy.
              </p>

              <div class="mt-4 space-y-3 text-xs">
                <div>
                  <label class="block font-medium text-gray-700">Calling Extension</label>
                  <select
                    v-model="simPerm.extension"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600"
                  >
                    <option v-for="ext in extensions" :key="ext.extension" :value="ext.extension">
                      {{ ext.extension }} - {{ ext.description }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block font-medium text-gray-700">Target Destination</label>
                  <input
                    v-model="simPerm.destination"
                    type="text"
                    placeholder="e.g. +442079460111, 9005551234, or 911"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 font-mono text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600"
                  />
                </div>

                <button
                  @click="runPermSimulation"
                  :disabled="isSimulatingPerm"
                  class="w-full mt-2 inline-flex justify-center items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
                >
                  <span v-if="isSimulatingPerm" class="animate-spin">⏳</span>
                  <span>{{ isSimulatingPerm ? 'Evaluating Barring...' : 'Evaluate Call Authorization' }}</span>
                </button>
              </div>

              <!-- Simulation Result Card -->
              <div v-if="permResult" class="mt-4 rounded-lg border p-3.5 space-y-2 text-xs" :class="permResult.allowed ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50'">
                <div class="flex items-center justify-between border-b pb-2" :class="permResult.allowed ? 'border-emerald-100' : 'border-rose-100'">
                  <span class="font-bold" :class="permResult.allowed ? 'text-emerald-800' : 'text-rose-800'">
                    {{ permResult.allowed ? 'CALL PERMITTED (AUTHORIZED)' : 'CALL BARRED (REJECTED)' }}
                  </span>
                  <span class="rounded bg-white px-2 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-200">{{ permResult.destination_type.toUpperCase() }}</span>
                </div>

                <p class="text-xs" :class="permResult.allowed ? 'text-emerald-700' : 'text-rose-700'">{{ permResult.reason }}</p>

                <div class="pt-1 text-[10px] text-gray-600 flex items-center justify-between">
                  <span>Profile: <strong class="text-gray-900">{{ permResult.matched_profile }}</strong></span>
                  <span>Status: <strong class="text-indigo-600 font-mono">{{ permResult.schedule_status }}</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- E911 SIMULATION MODAL -->
      <div v-if="showE911Modal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-xl rounded-lg bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <span>🚨</span> E911 Dispatch Live Simulation
            </h3>
            <button @click="showE911Modal = false" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>

          <p class="mt-3 text-xs text-gray-500">
            Testing emergency Kari's Law &amp; Ray Baum's dispatch trajectory for <strong>{{ selectedLoc?.location_name }}</strong>.
          </p>

          <div class="mt-4 space-y-3 text-xs">
            <div>
              <label class="block font-medium text-gray-700">Test Caller Extension</label>
              <select
                v-model="e911Extension"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-rose-600"
              >
                <option v-for="ext in extensions" :key="ext.extension" :value="ext.extension">
                  {{ ext.extension }} - {{ ext.description }}
                </option>
              </select>
            </div>

            <button
              @click="executeE911Test"
              :disabled="isTestingE911"
              class="w-full inline-flex justify-center items-center gap-1.5 rounded-md bg-rose-600 px-4 py-2.5 font-semibold text-white shadow-sm hover:bg-rose-500 transition disabled:opacity-50"
            >
              <span v-if="isTestingE911" class="animate-spin">⏳</span>
              <span>{{ isTestingE911 ? 'Executing Dispatch Test...' : 'Trigger 911 Test Call' }}</span>
            </button>
          </div>

          <!-- Dispatch Results Output -->
          <div v-if="e911Result" class="mt-4 rounded-lg bg-rose-50 border border-rose-200 p-4 space-y-3 text-xs">
            <div class="flex items-center justify-between border-b border-rose-200 pb-2">
              <span class="font-bold text-rose-900">ELIN Assigned: {{ e911Result.elin }}</span>
              <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">PASSED</span>
            </div>

            <div class="space-y-1.5">
              <div class="flex items-start gap-1.5 text-rose-950">
                <span class="text-emerald-600">✓</span>
                <span><strong>Kari's Law:</strong> {{ e911Result.karis_law.detail }}</span>
              </div>
              <div class="flex items-start gap-1.5 text-rose-950">
                <span class="text-emerald-600">✓</span>
                <span><strong>Ray Baum's Act:</strong> {{ e911Result.ray_baums.detail }}</span>
              </div>
            </div>

            <div class="pt-2 border-t border-rose-200">
              <div class="font-bold text-rose-900 text-[11px] mb-1">Stakeholder Alerts Dispatched:</div>
              <div class="space-y-1 max-h-32 overflow-y-auto">
                <div v-for="(notif, idx) in e911Result.notifications_dispatched" :key="idx" class="rounded bg-white p-2 text-[10px] font-mono text-gray-800 ring-1 ring-gray-200">
                  <div class="flex justify-between text-indigo-700 font-semibold">
                    <span>[{{ notif.channel }}] → {{ notif.target }}</span>
                    <span>{{ notif.status }}</span>
                  </div>
                  <div class="mt-0.5 text-gray-500">{{ notif.payload }}</div>
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
  emergencyLocations: { type: Array, default: () => [] },
  callerIdRules: { type: Array, default: () => [] },
  permissionProfiles: { type: Array, default: () => [] },
  retentionRules: { type: Array, default: () => [] },
  legalHolds: { type: Array, default: () => [] },
  deviceFleet: { type: Array, default: () => [] },
  extensions: { type: Array, default: () => [] },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('emergency')

const tabs = [
  { id: 'emergency', name: 'Emergency Routing & E911', icon: '🚨', badge: 'Kari\'s Law' },
  { id: 'caller_id', name: 'Caller-ID & Normalization', icon: '🆔', badge: 'E.164' },
  { id: 'fleet', name: 'Fleet & WebRTC Inventory', icon: '📱', badge: `${props.metrics.total_devices || 0}` },
  { id: 'retention', name: 'Recording Retention & Holds', icon: '⏳', badge: `${props.metrics.active_legal_holds || 0}` },
  { id: 'permissions', name: 'Call Permissions & CoS', icon: '🛡️', badge: 'Barring' },
]

// E911 Modal State
const showE911Modal = ref(false)
const selectedLoc = ref(null)
const e911Extension = ref(props.extensions[0]?.extension || '1001')
const isTestingE911 = ref(false)
const e911Result = ref(null)

const openE911Modal = (loc) => {
  selectedLoc.value = loc || props.emergencyLocations[0]
  showE911Modal.value = true
  e911Result.value = null
}

const executeE911Test = async () => {
  if (!selectedLoc.value) return
  isTestingE911.value = true
  try {
    const res = await axios.post('/api/pbx/advanced-operations/emergency-dispatch', {
      location_uuid: selectedLoc.value.uuid,
      caller_extension: e911Extension.value,
    })
    e911Result.value = res.data
  } catch (err) {
    alert('E911 simulation error: ' + (err.response?.data?.message || err.message))
  } finally {
    isTestingE911.value = false
  }
}

// Caller-ID Simulation State
const simCid = ref({
  extension: '1001',
  department: '',
  dialed_number: '5125551234',
})
const isSimulatingCid = ref(false)
const cidResult = ref(null)

const runCidSimulation = async () => {
  isSimulatingCid.value = true
  try {
    const res = await axios.post('/api/pbx/advanced-operations/caller-id-simulate', simCid.value)
    cidResult.value = res.data
  } catch (err) {
    alert('Caller ID simulation error: ' + (err.response?.data?.message || err.message))
  } finally {
    isSimulatingCid.value = false
  }
}

// Call Barring / Permission State
const simPerm = ref({
  extension: props.extensions[0]?.extension || '1001',
  destination: '+442079460111',
})
const isSimulatingPerm = ref(false)
const permResult = ref(null)

const runPermSimulation = async () => {
  isSimulatingPerm.value = true
  try {
    const res = await axios.post('/api/pbx/advanced-operations/permission-evaluate', simPerm.value)
    permResult.value = res.data
  } catch (err) {
    alert('Permission evaluation error: ' + (err.response?.data?.message || err.message))
  } finally {
    isSimulatingPerm.value = false
  }
}

// Legal Hold Toggle
const toggleHold = async (hold) => {
  try {
    const res = await axios.post(`/api/pbx/advanced-operations/legal-holds/${hold.uuid}/toggle`)
    hold.status = res.data.status
    hold.tamper_checksum_sha256 = res.data.tamper_checksum_sha256
  } catch (err) {
    alert('Failed to toggle legal hold: ' + (err.response?.data?.message || err.message))
  }
}

// Device Remote Reboot
const rebootingUuids = ref([])
const rebootNotice = ref(null)

const triggerReboot = async (dev) => {
  rebootingUuids.value.push(dev.uuid)
  try {
    const res = await axios.post(`/api/pbx/advanced-operations/devices/${dev.uuid}/reboot`)
    rebootNotice.value = res.data.sip_event + '\n' + res.data.message
    dev.sip_status = 'registered'
    dev.rtt_ms = res.data.rtt_ms || dev.rtt_ms
  } catch (err) {
    alert('Failed to reboot device: ' + (err.response?.data?.message || err.message))
  } finally {
    rebootingUuids.value = rebootingUuids.value.filter(id => id !== dev.uuid)
  }
}
</script>
