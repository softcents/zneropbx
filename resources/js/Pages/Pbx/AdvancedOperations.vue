<template>
  <MainLayout>
    <div class="min-h-screen bg-slate-950 text-slate-100 pb-16">
      <!-- Dark Luxury Spotlight Banner -->
      <div class="relative overflow-hidden border-b border-cyan-500/20 bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 px-6 py-8 shadow-2xl">
        <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative mx-auto max-w-7xl">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-400 ring-1 ring-inset ring-cyan-500/30">
                  <span class="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                  P4 — Fleet Governance & Operations
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/30">
                  Ray Baum's Act Ready
                </span>
              </div>
              <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                PBX Advanced Operations & Fleet Governance
              </h1>
              <p class="mt-1 text-sm text-slate-400">
                E911 dispatchable location mapping, caller-ID normalization, SIP/WebRTC device lifecycle, immutable recording legal holds, and Class of Service barring.
              </p>
            </div>

            <!-- Quick Navigation Back to CC Suite & Dashboard -->
            <div class="flex flex-wrap items-center gap-2">
              <a href="/dashboard" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800/80 px-3 py-2 text-xs font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition ring-1 ring-slate-700">
                ← Dashboard
              </a>
              <a href="/telecom/routing-intelligence" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600/20 px-3 py-2 text-xs font-medium text-teal-300 hover:bg-teal-600/30 transition ring-1 ring-teal-500/30">
                Carrier LCR
              </a>
              <a href="/billing/financial-studio" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600/20 px-3 py-2 text-xs font-medium text-emerald-300 hover:bg-emerald-600/30 transition ring-1 ring-emerald-500/30">
                Billing Studio
              </a>
            </div>
          </div>

          <!-- Real-Time Metrics Strip -->
          <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-cyan-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Total Endpoints</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-white">{{ metrics.total_devices }}</span>
                <span class="text-xs text-cyan-400 font-mono">{{ metrics.webrtc_count }} WebRTC</span>
              </div>
            </div>

            <div class="rounded-xl border border-emerald-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Registered Online</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-emerald-400">{{ metrics.online_devices }}</span>
                <span class="text-xs text-slate-400 font-mono">/ {{ metrics.total_devices }}</span>
              </div>
            </div>

            <div class="rounded-xl border border-blue-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Fleet Latency RTT</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-blue-400">{{ metrics.avg_rtt_ms }}</span>
                <span class="text-xs text-slate-400">ms avg</span>
              </div>
            </div>

            <div class="rounded-xl border border-indigo-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Kari's Law</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-indigo-400">100%</span>
                <span class="text-xs text-emerald-400">Direct 911</span>
              </div>
            </div>

            <div class="rounded-xl border border-amber-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Ray Baum's Act</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-amber-400">Active</span>
                <span class="text-xs text-slate-400">ELIN Ready</span>
              </div>
            </div>

            <div class="rounded-xl border border-rose-500/20 bg-slate-900/60 p-3.5 backdrop-blur-sm">
              <div class="text-xs font-medium text-slate-400">Active Legal Holds</div>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-rose-400">{{ metrics.active_legal_holds }}</span>
                <span class="text-xs text-rose-300 font-mono">Immutable</span>
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
                ? 'border-cyan-500 text-cyan-400 bg-cyan-500/10'
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

        <!-- TAB 1: EMERGENCY ROUTING & E911 -->
        <div v-if="activeTab === 'emergency'" class="mt-6 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>🚨</span> E911 Emergency Location Identification & Regulatory Compliance
              </h2>
              <p class="text-xs text-slate-400">
                Mandatory Kari's Law (direct 911 dialing without trunk access codes) & Ray Baum's Act (precise room/suite dispatchable location).
              </p>
            </div>
            <button
              @click="openE911Modal(emergencyLocations[0])"
              class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-lg hover:bg-rose-500 transition"
            >
              <span>⚡</span> Run Simulated 911 Dispatch
            </button>
          </div>

          <!-- Location Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              v-for="loc in emergencyLocations"
              :key="loc.uuid"
              class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg hover:border-slate-700 transition"
            >
              <div class="flex items-center justify-between">
                <span
                  :class="[
                    loc.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-amber-500/10 text-amber-400 ring-amber-500/30',
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1'
                  ]"
                >
                  {{ loc.status.toUpperCase() }}
                </span>
                <span class="text-xs text-slate-400 font-mono">ELIN: {{ loc.elin_number }}</span>
              </div>

              <h3 class="mt-3 text-sm font-bold text-white leading-snug">{{ loc.location_name }}</h3>

              <div class="mt-3 space-y-1.5 text-xs text-slate-300">
                <div class="flex items-start gap-1.5">
                  <span class="text-slate-500">📍</span>
                  <span>{{ loc.street_address }}<br><span class="text-cyan-400 font-semibold">{{ loc.unit_suite }}</span>, {{ loc.city }}, {{ loc.state_province }} {{ loc.postal_code }}</span>
                </div>
                <div class="flex items-center gap-1.5 pt-1 text-slate-400">
                  <span class="text-slate-500">🏢</span>
                  <span class="truncate">{{ loc.psap_name }}</span>
                </div>
              </div>

              <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-2">
                  <span :class="loc.karis_law_compliant ? 'text-emerald-400' : 'text-rose-400'">✓ Kari's Law</span>
                  <span :class="loc.ray_baums_compliant ? 'text-emerald-400' : 'text-rose-400'">✓ Ray Baum's</span>
                </div>
                <button
                  @click="openE911Modal(loc)"
                  class="text-xs font-semibold text-rose-400 hover:text-rose-300 underline"
                >
                  Test Route
                </button>
              </div>
            </div>
          </div>

          <!-- Regulatory Checklist Info -->
          <div class="rounded-xl border border-cyan-500/20 bg-cyan-950/20 p-4">
            <h4 class="text-xs font-bold text-cyan-300 uppercase tracking-wider">Federal Regulatory Invariants Enforced</h4>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-300">
              <div class="flex items-start gap-2">
                <span class="text-emerald-400">✔</span>
                <div>
                  <strong class="text-white">Kari's Law (47 U.S.C. § 623):</strong> Direct dial 911 enabled on all endpoints without dialing 9 or 8 prefix. Automated simultaneous notification sent to security desk upon trigger.
                </div>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-emerald-400">✔</span>
                <div>
                  <strong class="text-white">Ray Baum's Act Section 506:</strong> Dispatchable location automatically transmitted to PSAP, including validated floor, suite, and room coordinates alongside street address.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: CALLER-ID & NORMALIZATION -->
        <div v-if="activeTab === 'caller_id'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Rules Table -->
            <div class="lg:col-span-7 space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span>🆔</span> Outbound Presentation Rules & Regex Pipeline
                </h3>
                <span class="text-xs text-slate-400">{{ callerIdRules.length }} Active Rules</span>
              </div>

              <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 shadow">
                <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
                  <thead class="bg-slate-900/90 text-slate-400 font-medium">
                    <tr>
                      <th class="px-3.5 py-2.5">Rule / Scope</th>
                      <th class="px-3 py-2.5">Outbound ANI (Number)</th>
                      <th class="px-3 py-2.5">CNAM (Display Name)</th>
                      <th class="px-3 py-2.5">Format</th>
                      <th class="px-3 py-2.5 text-right">Priority</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    <tr v-for="rule in callerIdRules" :key="rule.uuid" class="hover:bg-slate-800/40">
                      <td class="px-3.5 py-3 font-semibold text-white">
                        <div>{{ rule.rule_name }}</div>
                        <span class="inline-block rounded bg-cyan-950 px-1.5 py-0.5 text-[10px] text-cyan-400 ring-1 ring-cyan-800">
                          {{ rule.scope_type }}: {{ rule.scope_value || 'ALL' }}
                        </span>
                      </td>
                      <td class="px-3 py-3 font-mono text-cyan-300">{{ rule.caller_id_number }}</td>
                      <td class="px-3 py-3">{{ rule.caller_id_name }}</td>
                      <td class="px-3 py-3">
                        <span class="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] font-mono text-slate-300">
                          {{ rule.normalization_format }}
                        </span>
                      </td>
                      <td class="px-3 py-3 text-right font-mono font-bold text-amber-400">{{ rule.priority }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Right: Interactive Normalization Sandbox -->
            <div class="lg:col-span-5 space-y-4">
              <div class="rounded-xl border border-cyan-500/30 bg-slate-900/80 p-5 shadow-xl">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span>🧪</span> Interactive Caller-ID & E.164 Sandbox
                </h3>
                <p class="mt-1 text-xs text-slate-400">
                  Simulate how an outbound call from an extension or department is normalized and presented.
                </p>

                <div class="mt-4 space-y-3 text-xs">
                  <div>
                    <label class="block font-medium text-slate-300">Calling Extension</label>
                    <select
                      v-model="simCid.extension"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-cyan-500 focus:outline-none"
                    >
                      <option value="">Global Default</option>
                      <option v-for="ext in extensions" :key="ext.extension" :value="ext.extension">
                        {{ ext.extension }} - {{ ext.description }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block font-medium text-slate-300">Department Scope</label>
                    <select
                      v-model="simCid.department"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-cyan-500 focus:outline-none"
                    >
                      <option value="">None</option>
                      <option value="Support">Support Desk</option>
                      <option value="Sales">Enterprise Sales</option>
                      <option value="Executive">Executive Office</option>
                    </select>
                  </div>

                  <div>
                    <label class="block font-medium text-slate-300">Dialed Number (Input)</label>
                    <input
                      v-model="simCid.dialed_number"
                      type="text"
                      placeholder="e.g. 5125551234 or 915125551234"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 font-mono text-white shadow-sm focus:border-cyan-500 focus:outline-none"
                    />
                  </div>

                  <button
                    @click="runCidSimulation"
                    :disabled="isSimulatingCid"
                    class="w-full mt-2 inline-flex justify-center items-center gap-1.5 rounded-lg bg-cyan-600 px-4 py-2 font-semibold text-white shadow hover:bg-cyan-500 transition disabled:opacity-50"
                  >
                    <span v-if="isSimulatingCid" class="animate-spin">⏳</span>
                    <span>{{ isSimulatingCid ? 'Normalizing...' : 'Evaluate Outbound Identity' }}</span>
                  </button>
                </div>

                <!-- Simulation Result Card -->
                <div v-if="cidResult" class="mt-4 rounded-lg border border-cyan-500/30 bg-cyan-950/30 p-3.5 space-y-2 text-xs">
                  <div class="flex items-center justify-between border-b border-cyan-500/20 pb-2">
                    <span class="font-bold text-cyan-300">Matched Policy</span>
                    <span class="rounded bg-cyan-900/60 px-2 py-0.5 font-semibold text-cyan-300">{{ cidResult.matched_rule }}</span>
                  </div>
                  <div class="grid grid-cols-2 gap-2 pt-1">
                    <div>
                      <div class="text-[10px] text-slate-400">Normalized Destination</div>
                      <div class="font-mono font-bold text-white">{{ cidResult.normalized_destination }}</div>
                    </div>
                    <div>
                      <div class="text-[10px] text-slate-400">Presentation ANI</div>
                      <div class="font-mono font-bold text-emerald-400">{{ cidResult.effective_caller_id_number }}</div>
                    </div>
                    <div>
                      <div class="text-[10px] text-slate-400">Display CNAM</div>
                      <div class="font-semibold text-slate-200">{{ cidResult.effective_caller_id_name }}</div>
                    </div>
                    <div>
                      <div class="text-[10px] text-slate-400">Emergency Caller-ID</div>
                      <div class="font-mono text-rose-400">{{ cidResult.emergency_caller_id_number }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: FLEET & WEBRTC INVENTORY -->
        <div v-if="activeTab === 'fleet'" class="mt-6 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>📱</span> SIP Hardware Fleet & WebRTC Softphone Lifecycle
              </h3>
              <p class="text-xs text-slate-400">
                Live registration telemetry, cipher profiles, remote reboot via SIP NOTIFY check-sync.
              </p>
            </div>
          </div>

          <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 shadow">
            <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
              <thead class="bg-slate-900/90 text-slate-400 font-medium">
                <tr>
                  <th class="px-3.5 py-2.5">Endpoint / MAC</th>
                  <th class="px-3 py-2.5">Vendor & Model</th>
                  <th class="px-3 py-2.5">Assigned Ext</th>
                  <th class="px-3 py-2.5">IP & Transport</th>
                  <th class="px-3 py-2.5">SIP Status</th>
                  <th class="px-3 py-2.5">Latency</th>
                  <th class="px-3 py-2.5">Firmware / Config</th>
                  <th class="px-3 py-2.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 text-slate-300">
                <tr v-for="dev in deviceFleet" :key="dev.uuid" class="hover:bg-slate-800/40">
                  <td class="px-3.5 py-3">
                    <div class="font-mono font-bold text-white">{{ dev.mac_address }}</div>
                  </td>
                  <td class="px-3 py-3">
                    <div class="font-semibold text-slate-200">{{ dev.vendor }}</div>
                    <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ dev.model }}</div>
                  </td>
                  <td class="px-3 py-3">
                    <span class="rounded bg-slate-800 px-2 py-0.5 font-mono text-cyan-300 font-semibold">
                      Ext {{ dev.assigned_extension || 'N/A' }}
                    </span>
                  </td>
                  <td class="px-3 py-3 font-mono text-slate-300">
                    <div>{{ dev.ip_address }}</div>
                    <span :class="[
                      dev.transport === 'TLS' || dev.transport === 'WSS' ? 'text-emerald-400' : 'text-amber-400',
                      'text-[10px] font-semibold'
                    ]">
                      🔒 {{ dev.transport }} (SRTP)
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      :class="[
                        dev.sip_status === 'registered' ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-rose-500/10 text-rose-400 ring-rose-500/30',
                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1'
                      ]"
                    >
                      <span :class="dev.sip_status === 'registered' ? 'bg-emerald-400' : 'bg-rose-400'" class="h-1.5 w-1.5 rounded-full"></span>
                      {{ dev.sip_status.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-3 py-3 font-mono">
                    <span v-if="dev.sip_status === 'registered'" class="text-blue-400 font-bold">{{ dev.rtt_ms }} ms</span>
                    <span v-else class="text-slate-500">—</span>
                  </td>
                  <td class="px-3 py-3">
                    <div class="font-mono text-[11px] text-slate-300">{{ dev.firmware_version }}</div>
                    <div class="text-[10px] text-slate-500 font-mono">{{ dev.config_version }}</div>
                  </td>
                  <td class="px-3 py-3 text-right">
                    <button
                      @click="triggerReboot(dev)"
                      :disabled="rebootingUuids.includes(dev.uuid)"
                      class="inline-flex items-center gap-1 rounded-md bg-slate-800 px-2.5 py-1 text-[11px] font-semibold text-cyan-400 hover:bg-slate-700 transition ring-1 ring-slate-700 disabled:opacity-50"
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
          <div v-if="rebootNotice" class="rounded-xl border border-cyan-500/30 bg-cyan-950/40 p-4 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-cyan-300">SIP NOTIFY Event Dispatched</span>
              <button @click="rebootNotice = null" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <pre class="mt-2 overflow-x-auto rounded bg-slate-950 p-2 font-mono text-[11px] text-slate-300">{{ rebootNotice }}</pre>
          </div>
        </div>

        <!-- TAB 4: RECORDING RETENTION & LEGAL HOLDS -->
        <div v-if="activeTab === 'retention'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Retention Rules -->
            <div class="lg:col-span-5 space-y-4">
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>⏳</span> Retention & Lifecycle Storage Rules
              </h3>

              <div class="space-y-3">
                <div
                  v-for="rule in retentionRules"
                  :key="rule.uuid"
                  class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"
                >
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-white text-xs">{{ rule.rule_name }}</span>
                    <span class="rounded bg-slate-800 px-2 py-0.5 text-[10px] font-mono text-cyan-300 font-semibold">
                      {{ rule.retention_days }} Days
                    </span>
                  </div>

                  <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] text-slate-400">
                    <div>
                      Storage Tier: <span class="font-mono text-slate-200">{{ rule.storage_tier }}</span>
                    </div>
                    <div>
                      Cipher: <span class="font-mono text-emerald-400">{{ rule.encryption_algorithm }}</span>
                    </div>
                  </div>

                  <div class="mt-2 text-[10px] text-slate-500 truncate font-mono">
                    KMS Key: {{ rule.kms_key_id }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Immutable Legal Holds Ledger -->
            <div class="lg:col-span-7 space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span>⚖️</span> Immutability Ledger: Active Legal Holds
                </h3>
                <span class="text-xs text-rose-400 font-semibold">{{ metrics.active_legal_holds }} Purge-Protected</span>
              </div>

              <div class="space-y-3">
                <div
                  v-for="hold in legalHolds"
                  :key="hold.uuid"
                  class="rounded-xl border border-slate-800 bg-slate-900/80 p-4 shadow-lg hover:border-slate-700 transition"
                >
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-white text-xs">{{ hold.case_number }}</span>
                        <span
                          :class="[
                            hold.status === 'active' ? 'bg-rose-500/10 text-rose-400 ring-rose-500/30' : 'bg-slate-700/50 text-slate-400 ring-slate-600',
                            'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1'
                          ]"
                        >
                          {{ hold.status.toUpperCase() }}
                        </span>
                      </div>
                      <div class="mt-1 text-xs font-semibold text-slate-200">{{ hold.case_title }}</div>
                    </div>

                    <button
                      @click="toggleHold(hold)"
                      class="rounded-md bg-slate-800 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:text-white hover:bg-slate-700 transition ring-1 ring-slate-700"
                    >
                      {{ hold.status === 'active' ? 'Release Hold' : 'Reinstate Hold' }}
                    </button>
                  </div>

                  <p class="mt-2 text-xs text-slate-400">{{ hold.reason_notes }}</p>

                  <div class="mt-3 pt-2.5 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[10px] text-slate-400">
                    <div>
                      Custodian: <span class="text-slate-300">{{ hold.custodian_name }}</span> | Counsel: <span class="text-slate-300">{{ hold.legal_officer }}</span>
                    </div>
                    <div class="font-mono text-cyan-400 truncate max-w-[240px]">
                      SHA256: {{ hold.tamper_checksum_sha256.substring(0, 16) }}...
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 5: CALL PERMISSIONS & CLASS OF SERVICE -->
        <div v-if="activeTab === 'permissions'" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Permission Profiles -->
            <div class="lg:col-span-7 space-y-4">
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>🛡️</span> Class of Service (CoS) Profiles & Outbound Barring
              </h3>

              <div class="space-y-3">
                <div
                  v-for="prof in permissionProfiles"
                  :key="prof.uuid"
                  class="rounded-xl border border-slate-800 bg-slate-900/80 p-4 shadow"
                >
                  <div class="flex items-center justify-between">
                    <h4 class="font-bold text-white text-xs">{{ prof.profile_name }}</h4>
                    <span class="rounded bg-slate-800 px-2 py-0.5 text-[10px] font-mono text-cyan-300 font-semibold">
                      Max {{ prof.max_concurrent_calls }} Calls
                    </span>
                  </div>

                  <p class="mt-1 text-xs text-slate-400">{{ prof.description }}</p>

                  <div class="mt-3 flex flex-wrap gap-2 text-[11px]">
                    <span :class="prof.allow_international ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-rose-500/10 text-rose-400 ring-rose-500/30'" class="rounded px-2 py-0.5 ring-1">
                      {{ prof.allow_international ? '✓ International' : '✕ Int\'l Barred' }}
                    </span>
                    <span :class="prof.allow_national ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-rose-500/10 text-rose-400 ring-rose-500/30'" class="rounded px-2 py-0.5 ring-1">
                      {{ prof.allow_national ? '✓ National' : '✕ National Barred' }}
                    </span>
                    <span :class="prof.allow_toll_free ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30' : 'bg-rose-500/10 text-rose-400 ring-rose-500/30'" class="rounded px-2 py-0.5 ring-1">
                      {{ prof.allow_toll_free ? '✓ Toll-Free' : '✕ Toll-Free Barred' }}
                    </span>
                    <span :class="prof.allow_premium_rate ? 'bg-amber-500/10 text-amber-400 ring-amber-500/30' : 'bg-rose-500/10 text-rose-400 ring-rose-500/30'" class="rounded px-2 py-0.5 ring-1">
                      {{ prof.allow_premium_rate ? '✓ 900/976 Toll' : '✕ 900 Barred' }}
                    </span>
                  </div>

                  <div class="mt-2.5 text-[10px] text-slate-400 font-mono">
                    Schedule: {{ prof.time_restriction_type }} ({{ prof.start_time || '00:00' }} - {{ prof.end_time || '23:59' }})
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Interactive Permission Evaluator -->
            <div class="lg:col-span-5 space-y-4">
              <div class="rounded-xl border border-indigo-500/30 bg-slate-900/80 p-5 shadow-xl">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span>🚦</span> Call Barring Gatekeeper Simulator
                </h3>
                <p class="mt-1 text-xs text-slate-400">
                  Verify whether an extension call to a destination is authorized by Class of Service policy.
                </p>

                <div class="mt-4 space-y-3 text-xs">
                  <div>
                    <label class="block font-medium text-slate-300">Calling Extension</label>
                    <select
                      v-model="simPerm.extension"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:outline-none"
                    >
                      <option v-for="ext in extensions" :key="ext.extension" :value="ext.extension">
                        {{ ext.extension }} - {{ ext.description }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block font-medium text-slate-300">Target Destination</label>
                    <input
                      v-model="simPerm.destination"
                      type="text"
                      placeholder="e.g. +442079460111, 9005551234, or 911"
                      class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 font-mono text-white shadow-sm focus:border-indigo-500 focus:outline-none"
                    />
                  </div>

                  <button
                    @click="runPermSimulation"
                    :disabled="isSimulatingPerm"
                    class="w-full mt-2 inline-flex justify-center items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white shadow hover:bg-indigo-500 transition disabled:opacity-50"
                  >
                    <span v-if="isSimulatingPerm" class="animate-spin">⏳</span>
                    <span>{{ isSimulatingPerm ? 'Evaluating Barring...' : 'Evaluate Call Authorization' }}</span>
                  </button>
                </div>

                <!-- Simulation Result Card -->
                <div v-if="permResult" class="mt-4 rounded-lg border p-3.5 space-y-2 text-xs" :class="permResult.allowed ? 'border-emerald-500/30 bg-emerald-950/30' : 'border-rose-500/30 bg-rose-950/30'">
                  <div class="flex items-center justify-between border-b border-slate-700/40 pb-2">
                    <span class="font-bold" :class="permResult.allowed ? 'text-emerald-400' : 'text-rose-400'">
                      {{ permResult.allowed ? 'CALL PERMITTED (AUTHORIZED)' : 'CALL BARRED (REJECTED)' }}
                    </span>
                    <span class="rounded bg-slate-800 px-2 py-0.5 font-mono text-[10px] text-slate-300">{{ permResult.destination_type.toUpperCase() }}</span>
                  </div>

                  <p class="text-xs text-slate-300">{{ permResult.reason }}</p>

                  <div class="pt-1 text-[10px] text-slate-400 flex items-center justify-between">
                    <span>Profile: <strong class="text-white">{{ permResult.matched_profile }}</strong></span>
                    <span>Status: <strong class="text-cyan-400 font-mono">{{ permResult.schedule_status }}</strong></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- E911 SIMULATION MODAL -->
      <div v-if="showE911Modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="w-full max-w-xl rounded-2xl border border-rose-500/40 bg-slate-900 p-6 shadow-2xl">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span>🚨</span> E911 Dispatch Live Simulation
            </h3>
            <button @click="showE911Modal = false" class="text-slate-400 hover:text-white">✕</button>
          </div>

          <p class="mt-1 text-xs text-slate-400">
            Testing emergency Kari's Law & Ray Baum's dispatch trajectory for <strong>{{ selectedLoc?.location_name }}</strong>.
          </p>

          <div class="mt-4 space-y-3 text-xs">
            <div>
              <label class="block font-medium text-slate-300">Test Caller Extension</label>
              <select
                v-model="e911Extension"
                class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white shadow-sm focus:border-rose-500 focus:outline-none"
              >
                <option v-for="ext in extensions" :key="ext.extension" :value="ext.extension">
                  {{ ext.extension }} - {{ ext.description }}
                </option>
              </select>
            </div>

            <button
              @click="executeE911Test"
              :disabled="isTestingE911"
              class="w-full inline-flex justify-center items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2.5 font-semibold text-white shadow-lg hover:bg-rose-500 transition disabled:opacity-50"
            >
              <span v-if="isTestingE911" class="animate-spin">⏳</span>
              <span>{{ isTestingE911 ? 'Executing Dispatch Test...' : 'Trigger 911 Test Call' }}</span>
            </button>
          </div>

          <!-- Dispatch Results Output -->
          <div v-if="e911Result" class="mt-4 rounded-xl border border-rose-500/30 bg-rose-950/30 p-4 space-y-3 text-xs">
            <div class="flex items-center justify-between border-b border-rose-500/20 pb-2">
              <span class="font-bold text-rose-300">ELIN Assigned: {{ e911Result.elin }}</span>
              <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">PASSED</span>
            </div>

            <div class="space-y-1.5">
              <div class="flex items-start gap-1.5 text-slate-300">
                <span class="text-emerald-400">✓</span>
                <span><strong>Kari's Law:</strong> {{ e911Result.karis_law.detail }}</span>
              </div>
              <div class="flex items-start gap-1.5 text-slate-300">
                <span class="text-emerald-400">✓</span>
                <span><strong>Ray Baum's Act:</strong> {{ e911Result.ray_baums.detail }}</span>
              </div>
            </div>

            <div class="pt-2 border-t border-rose-500/20">
              <div class="font-bold text-slate-300 text-[11px] mb-1">Stakeholder Alerts Dispatched:</div>
              <div class="space-y-1 max-h-32 overflow-y-auto">
                <div v-for="(notif, idx) in e911Result.notifications_dispatched" :key="idx" class="rounded bg-slate-950/70 p-2 text-[10px] font-mono text-slate-300">
                  <div class="flex justify-between text-cyan-400">
                    <span>[{{ notif.channel }}] → {{ notif.target }}</span>
                    <span>{{ notif.status }}</span>
                  </div>
                  <div class="mt-0.5 text-slate-400">{{ notif.payload }}</div>
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
