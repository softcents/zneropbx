<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Clean Native Software Header -->
      <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-700/10">
                Phase 10 • Threat Intelligence
              </span>
              <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-700/10">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                {{ metrics.threat_posture }}
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">
              Security, Fraud Detection & Compliance Studio
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Carrier-grade toll fraud prevention, SIP brute-force quarantine, nftables dynamic border firewalling, and immutable regulatory data governance.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="openQuarantineModal"
              class="inline-flex items-center gap-1.5 rounded-md bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 transition"
            >
              <NoSymbolIcon class="h-4 w-4" />
              Quarantine IP
            </button>
            <button
              @click="openNewRuleModal"
              class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
            >
              <ShieldCheckIcon class="h-4 w-4" />
              Add Fraud Rule
            </button>
          </div>
        </div>

        <!-- Telemetry KPI Summary Cards -->
        <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 border-t border-gray-100 pt-4">
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Threat Posture</div>
            <div class="mt-1 text-lg font-bold text-amber-700 truncate">{{ metrics.threat_posture.split('•')[1] || 'ELEVATED' }}</div>
          </div>
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Quarantined IPs</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-rose-600">{{ metrics.active_quarantines }}</div>
          </div>
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Open Incidents</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-amber-600">{{ metrics.open_incidents }}</div>
          </div>
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Attacks Mitigated</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-emerald-600">{{ metrics.total_mitigated_attacks }}</div>
          </div>
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Fraud Loss Prevented</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-indigo-600">${{ Number(metrics.fraud_capital_saved_usd).toLocaleString() }}</div>
          </div>
          <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="text-xs font-medium text-gray-500">Mitigation Latency</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-emerald-600">&lt; {{ metrics.avg_mitigation_latency_ms }} ms</div>
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
                ? 'border-rose-600 text-rose-600 font-semibold'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-3 px-1 text-sm flex items-center gap-2 transition'
            ]"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.name }}
            <span
              v-if="tab.count !== undefined"
              :class="[
                activeTab === tab.id ? 'bg-rose-100 text-rose-600' : 'bg-gray-100 text-gray-600',
                'rounded-full px-2 py-0.5 text-xs font-semibold'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- TAB 1: Active Incidents -->
      <div v-if="activeTab === 'incidents'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Security Incidents & Anomaly Radar</h2>
              <p class="text-xs text-gray-500">Live operational telemetry capturing toll fraud velocity, brute-force scans, and carrier route deviations.</p>
            </div>
            <span class="text-xs text-gray-500">Auto-correlated with SIP call legs and CDR usage</span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Incident Code</th>
                  <th scope="col" class="px-6 py-3">Vector & Details</th>
                  <th scope="col" class="px-6 py-3">Source IP</th>
                  <th scope="col" class="px-6 py-3">Severity</th>
                  <th scope="col" class="px-6 py-3">Risk Score</th>
                  <th scope="col" class="px-6 py-3">Status</th>
                  <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="inc in incidents" :key="inc.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-900">
                    {{ inc.incident_code }}
                    <div class="text-[11px] text-gray-400 font-sans font-normal">{{ formatDate(inc.detected_at) }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="font-semibold text-gray-900 text-sm">{{ inc.title }}</div>
                    <div class="text-xs text-rose-600 font-mono">{{ inc.detected_vector }}</div>
                    <div v-if="inc.audit_notes" class="mt-1 text-xs text-gray-500 italic max-w-md">{{ inc.audit_notes }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-700">
                    {{ inc.source_ip || 'Internal / Trunk' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        inc.severity === 'critical' ? 'bg-rose-50 text-rose-700 ring-rose-200' :
                        inc.severity === 'high' ? 'bg-amber-50 text-amber-700 ring-amber-200' :
                        'bg-sky-50 text-sky-700 ring-sky-200',
                        'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold ring-1 uppercase'
                      ]"
                    >
                      {{ inc.severity }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold">
                    <span :class="inc.risk_score >= 80 ? 'text-rose-600' : 'text-amber-600'">
                      {{ inc.risk_score }}%
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        inc.status === 'resolved' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' :
                        inc.status === 'mitigated' ? 'bg-cyan-50 text-cyan-700 ring-cyan-200' :
                        'bg-amber-50 text-amber-700 ring-amber-200',
                        'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium ring-1 capitalize'
                      ]"
                    >
                      {{ inc.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                    <button
                      v-if="inc.status !== 'resolved'"
                      @click="openResolveModal(inc)"
                      class="rounded bg-indigo-50 px-2.5 py-1 font-semibold text-indigo-600 hover:bg-indigo-100 transition"
                    >
                      Resolve
                    </button>
                    <span v-else class="text-emerald-600 font-semibold">Resolved</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 2: Fraud Rules -->
      <div v-if="activeTab === 'rules'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Configured Fraud & Velocity Rules</h2>
              <p class="text-xs text-gray-500">Real-time heuristics inspecting call destination velocity, spend ceilings, and SIP auth storms.</p>
            </div>
            <button
              @click="openNewRuleModal"
              class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition"
            >
              + Create Rule
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Rule Name</th>
                  <th scope="col" class="px-6 py-3">Detection Type</th>
                  <th scope="col" class="px-6 py-3">Threshold Limit</th>
                  <th scope="col" class="px-6 py-3">Mitigation Action</th>
                  <th scope="col" class="px-6 py-3">Severity</th>
                  <th scope="col" class="px-6 py-3">Auto-Mitigate</th>
                  <th scope="col" class="px-6 py-3 text-right">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="rule in rules" :key="rule.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">
                    {{ rule.rule_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono font-medium text-gray-700">
                      {{ rule.rule_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs">
                    <span class="font-bold text-gray-900">{{ rule.threshold_value }}</span> in {{ rule.time_window_sec }}s
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-mono font-bold text-rose-700 ring-1 ring-rose-200">
                      {{ rule.action }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        rule.severity === 'critical' ? 'text-rose-600' :
                        rule.severity === 'high' ? 'text-amber-600' : 'text-sky-600',
                        'font-bold text-xs uppercase'
                      ]"
                    >
                      {{ rule.severity }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                    <span v-if="rule.auto_mitigate" class="text-emerald-600 font-semibold flex items-center gap-1">
                      <CheckCircleIcon class="h-4 w-4" /> Enabled
                    </span>
                    <span v-else class="text-gray-400">Manual Review</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <button
                      @click="toggleRule(rule)"
                      :class="[
                        rule.is_enabled ? 'bg-emerald-600' : 'bg-gray-300',
                        'relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none'
                      ]"
                    >
                      <span
                        :class="[
                          rule.is_enabled ? 'translate-x-4' : 'translate-x-0',
                          'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                        ]"
                      />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: IP Quarantine -->
      <div v-if="activeTab === 'quarantine'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Quarantined IP Addresses & Dynamic Border ACLs</h2>
              <p class="text-xs text-gray-500">Automated synchronization with Debian nftables kernel tables and FreeSWITCH SIP challenges.</p>
            </div>
            <button
              @click="openQuarantineModal"
              class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition"
            >
              + Add IP Quarantine
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">IP Address</th>
                  <th scope="col" class="px-6 py-3">Threat Reason</th>
                  <th scope="col" class="px-6 py-3">Risk Score</th>
                  <th scope="col" class="px-6 py-3">Mitigation Filter</th>
                  <th scope="col" class="px-6 py-3">Quarantined Date</th>
                  <th scope="col" class="px-6 py-3">Status</th>
                  <th scope="col" class="px-6 py-3 text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="ip in quarantinedIps" :key="ip.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-900">
                    {{ ip.ip_address }}
                  </td>
                  <td class="px-6 py-4 text-xs text-gray-600 max-w-sm">
                    {{ ip.reason }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-rose-600">
                    {{ ip.threat_score }}%
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-mono font-medium text-slate-800">
                      {{ ip.mitigation_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                    {{ formatDate(ip.quarantined_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        ip.status === 'active' ? 'bg-rose-50 text-rose-700 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 capitalize'
                      ]"
                    >
                      {{ ip.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                    <button
                      v-if="ip.status === 'active'"
                      @click="unquarantineIp(ip)"
                      class="font-semibold text-emerald-600 hover:text-emerald-800"
                    >
                      Release & Whitelist
                    </button>
                    <span v-else class="text-gray-400 italic">Whitelisted</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 4: Real-Time Threat Simulator -->
      <div v-if="activeTab === 'simulator'" class="space-y-6">
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <div class="border-b border-gray-100 pb-4">
            <h3 class="text-base font-semibold text-gray-900">Real-Time Threat &amp; Velocity Evaluation Simulator</h3>
            <p class="mt-1 text-xs text-gray-500">
              Simulate call destination bursts, international premium rate spikes, and off-hours volume to observe composite risk calculation and automatic nftables quarantine.
            </p>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
              <label class="block text-xs font-medium text-gray-700">Originating Source IP</label>
              <input
                v-model="simThreat.source_ip"
                type="text"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Target Destination E.164 Number</label>
              <input
                v-model="simThreat.destination_number"
                type="text"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Call Velocity (Attempts / Minute)</label>
              <input
                v-model.number="simThreat.calls_per_minute"
                type="number"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Carrier Unit Rate ($ / Minute)</label>
              <input
                v-model.number="simThreat.rate_usd_per_min"
                type="number"
                step="0.05"
                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 font-mono"
              />
            </div>
          </div>

          <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
            <label class="flex items-center gap-2 text-xs text-gray-700">
              <input
                type="checkbox"
                v-model="simThreat.is_off_hours"
                class="rounded border-gray-300 text-rose-600 focus:ring-rose-500"
              />
              Simulate Off-Hours Outbound Traffic Pattern
            </label>
            <button
              @click="runThreatSimulation"
              :disabled="simulating"
              class="inline-flex items-center gap-2 rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 transition disabled:opacity-50"
            >
              <ExclamationTriangleIcon class="h-4 w-4" />
              {{ simulating ? 'Analyzing Threat...' : 'Evaluate Call Vector' }}
            </button>
          </div>

          <!-- Simulation Result Box -->
          <div v-if="simResult" class="mt-6 rounded-lg bg-gray-50 p-5 ring-1 ring-gray-200 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
              <div class="flex items-center gap-3">
                <span
                  :class="[
                    simResult.threat_level === 'CRITICAL' ? 'bg-rose-600 text-white' :
                    simResult.threat_level === 'HIGH' ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white',
                    'rounded-md px-2.5 py-1 font-mono text-xs font-bold'
                  ]"
                >
                  {{ simResult.threat_level }} RISK
                </span>
                <div>
                  <div class="text-sm font-bold text-gray-900">Composite Threat Score: {{ simResult.composite_risk_score }}%</div>
                  <div class="text-xs text-gray-500">Intervention latency: {{ simResult.mitigation_latency_ms }} ms</div>
                </div>
              </div>

              <div class="text-right">
                <div class="text-xs text-gray-500">Enforced Mitigation Action</div>
                <div class="font-mono text-sm font-bold text-rose-600">{{ simResult.recommended_action }}</div>
              </div>
            </div>

            <!-- Triggered Rules List -->
            <div>
              <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Triggered Risk Heuristics</div>
              <div class="space-y-2">
                <div
                  v-for="(tr, idx) in simResult.triggered_rules"
                  :key="idx"
                  class="flex items-center justify-between rounded-md bg-white p-3 text-xs ring-1 ring-gray-200"
                >
                  <div class="space-y-0.5">
                    <div class="font-semibold text-gray-900">{{ tr.rule }}</div>
                    <div class="text-gray-500">{{ tr.detail }}</div>
                  </div>
                  <span class="font-mono font-bold text-rose-600">{{ tr.weight }}</span>
                </div>
              </div>
            </div>

            <div v-if="simResult.auto_quarantined" class="rounded-md bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800 flex items-center gap-2">
              <ShieldExclamationIcon class="h-5 w-5 text-rose-600 flex-none" />
              <span>Critical threat detected. <strong>{{ simResult.source_ip }}</strong> was automatically quarantined and added to border nftables drop filter.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 5: Compliance Policy -->
      <div v-if="activeTab === 'compliance'" class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 space-y-6">
          <div class="border-b border-gray-100 pb-4">
            <h2 class="text-base font-semibold text-gray-900">Regulatory Compliance & Data Governance</h2>
            <p class="text-xs text-gray-500">Configure sovereign data retention, automated PCI-DSS cardholder audio masking, and Ray Baum's dispatchable location audit.</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-medium text-gray-700">Primary Regulatory Jurisdiction</label>
              <select
                v-model="policyForm.jurisdiction"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500 font-medium"
              >
                <option value="US_FCC_RAY_BAUM">United States (FCC Kari's Law & Ray Baum's Act Section 506)</option>
                <option value="EU_GDPR">European Union (GDPR & ePrivacy Directive)</option>
                <option value="HIPAA_HITECH">Healthcare HIPAA / HITECH (Encrypted Audio Vault)</option>
                <option value="PCI_DSS_3_2">Financial PCI-DSS Level 1 (DTMF Tokenization)</option>
                <option value="UK_OFCOM">United Kingdom (Ofcom General Conditions of Entitlement)</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700">Recording Retention (Days)</label>
                <input
                  v-model.number="policyForm.recording_retention_days"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700">CDR Ledger Retention (Days)</label>
                <input
                  v-model.number="policyForm.cdr_retention_days"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                />
              </div>
            </div>
          </div>

          <div class="border-t border-gray-100 pt-6 space-y-4">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Automated Governance Controls</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="flex items-start gap-3 rounded-lg border p-3 hover:bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="policyForm.enforce_pci_dtmf_masking"
                  class="mt-0.5 rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                />
                <div>
                  <div class="text-sm font-semibold text-gray-900">PCI-DSS DTMF Audio Muting</div>
                  <div class="text-xs text-gray-500">Automatically silence dual-tone audio frequencies during cardholder data capture.</div>
                </div>
              </label>

              <label class="flex items-start gap-3 rounded-lg border p-3 hover:bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="policyForm.gdpr_right_to_be_forgotten_enabled"
                  class="mt-0.5 rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                />
                <div>
                  <div class="text-sm font-semibold text-gray-900">GDPR Right to Erasure Pipeline</div>
                  <div class="text-xs text-gray-500">Provide cryptographically verifiable redaction for customer contact records upon request.</div>
                </div>
              </label>

              <label class="flex items-start gap-3 rounded-lg border p-3 hover:bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="policyForm.immutable_audit_logging"
                  class="mt-0.5 rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                />
                <div>
                  <div class="text-sm font-semibold text-gray-900">SHA-256 Immutable Audit Log</div>
                  <div class="text-xs text-gray-500">Cryptographically chain supervisor eavesdrop, listen, and recording download actions.</div>
                </div>
              </label>

              <label class="flex items-start gap-3 rounded-lg border p-3 hover:bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="policyForm.require_mfa_admin_actions"
                  class="mt-0.5 rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                />
                <div>
                  <div class="text-sm font-semibold text-gray-900">MFA for Destructive Administrative Actions</div>
                  <div class="text-xs text-gray-500">Require TOTP or FIDO2 key confirmation for route deletions and recording purges.</div>
                </div>
              </label>
            </div>
          </div>

          <div class="flex justify-end border-t border-gray-100 pt-4">
            <button
              @click="saveCompliancePolicy"
              :disabled="savingPolicy"
              class="rounded-lg bg-rose-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 transition disabled:opacity-50"
            >
              {{ savingPolicy ? 'Saving...' : 'Save Compliance Policy' }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: Add Fraud Rule -->
      <div v-if="showNewRuleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Create Fraud Prevention Rule</h3>
            <button @click="showNewRuleModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Rule Name</label>
              <input
                v-model="newRuleForm.rule_name"
                type="text"
                placeholder="e.g. Satellite Prefix Lockdown (+882)"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700">Rule Type</label>
                <select
                  v-model="newRuleForm.rule_type"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                >
                  <option value="velocity_destination">Velocity / Destination</option>
                  <option value="sip_bruteforce">SIP Brute-Force Auth</option>
                  <option value="spend_cap">Hourly Spend Cap</option>
                  <option value="off_hours_spike">Off-Hours Spike</option>
                  <option value="ip_reputation">IP Reputation Anomaly</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700">Severity</label>
                <select
                  v-model="newRuleForm.severity"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                >
                  <option value="critical">Critical</option>
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700">Threshold Value</label>
                <input
                  v-model.number="newRuleForm.threshold_value"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700">Time Window (Seconds)</label>
                <input
                  v-model.number="newRuleForm.time_window_sec"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700">Mitigation Action</label>
              <select
                v-model="newRuleForm.action"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
              >
                <option value="quarantine_ip">Quarantine Source IP</option>
                <option value="drop_call">Drop Call Immediately</option>
                <option value="alert_noc">Alert NOC On-Call</option>
                <option value="force_reauth">Force SIP Digest Re-Auth</option>
              </select>
            </div>

            <label class="flex items-center gap-2 text-xs text-gray-700">
              <input
                type="checkbox"
                v-model="newRuleForm.auto_mitigate"
                class="rounded border-gray-300 text-rose-600 focus:ring-rose-500"
              />
              Enable Automatic Kernel-Level Intervention
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="showNewRuleModal = false"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitNewRule"
              :disabled="savingRule || !newRuleForm.rule_name"
              class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 transition disabled:opacity-50"
            >
              {{ savingRule ? 'Saving...' : 'Create Rule' }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: Quarantine IP -->
      <div v-if="showQuarantineModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Manually Quarantine IP Address</h3>
            <button @click="showQuarantineModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">IP Address (IPv4 / IPv6)</label>
              <input
                v-model="quarantineForm.ip_address"
                type="text"
                placeholder="e.g. 198.51.100.42"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500 font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Quarantine Reason</label>
              <textarea
                v-model="quarantineForm.reason"
                rows="2"
                placeholder="State the observed malicious behavior or threat vector"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700">Mitigation Type</label>
                <select
                  v-model="quarantineForm.mitigation_type"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                >
                  <option value="fail2ban_nftables">fail2ban (Kernel nftables drop)</option>
                  <option value="sip_challenge_drop">FreeSWITCH SIP 403 Drop</option>
                  <option value="api_block">REST API HTTP 429 Block</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700">Duration (Days)</label>
                <input
                  v-model.number="quarantineForm.expiry_days"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                />
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="showQuarantineModal = false"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitQuarantine"
              :disabled="savingQuarantine || !quarantineForm.ip_address || !quarantineForm.reason"
              class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 transition disabled:opacity-50"
            >
              {{ savingQuarantine ? 'Quarantining...' : 'Quarantine IP' }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: Resolve Incident -->
      <div v-if="activeIncidentToResolve" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
              <h3 class="text-lg font-bold text-gray-900">Resolve Security Incident</h3>
              <p class="text-xs text-gray-500 font-mono">{{ activeIncidentToResolve.incident_code }}: {{ activeIncidentToResolve.title }}</p>
            </div>
            <button @click="activeIncidentToResolve = null" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Resolution & Postmortem Notes</label>
              <textarea
                v-model="resolveNotes"
                rows="4"
                placeholder="Describe corrective actions taken, trunk verification, or customer contact notes..."
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="activeIncidentToResolve = null"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitResolveIncident"
              :disabled="savingResolve || !resolveNotes"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
            >
              {{ savingResolve ? 'Resolving...' : 'Confirm Resolution' }}
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
  ShieldCheckIcon,
  NoSymbolIcon,
  ExclamationTriangleIcon,
  ShieldExclamationIcon,
  DocumentCheckIcon,
  CpuChipIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rules: { type: Array, default: () => [] },
  quarantinedIps: { type: Array, default: () => [] },
  incidents: { type: Array, default: () => [] },
  policy: { type: Object, default: () => ({}) },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('incidents')

const tabs = [
  { id: 'incidents', name: 'Security Incidents', icon: ShieldExclamationIcon, count: props.incidents.length },
  { id: 'rules', name: 'Fraud & Velocity Rules', icon: ShieldCheckIcon, count: props.rules.length },
  { id: 'quarantine', name: 'IP Quarantine & ACLs', icon: NoSymbolIcon, count: props.quarantinedIps.filter(i => i.status === 'active').length },
  { id: 'simulator', name: 'Threat Simulator', icon: CpuChipIcon },
  { id: 'compliance', name: 'Compliance & Governance', icon: DocumentCheckIcon },
]

// Modal states
const showNewRuleModal = ref(false)
const savingRule = ref(false)
const newRuleForm = ref({
  rule_name: '',
  rule_type: 'velocity_destination',
  threshold_value: 30,
  time_window_sec: 60,
  action: 'quarantine_ip',
  severity: 'critical',
  auto_mitigate: true,
})

const showQuarantineModal = ref(false)
const savingQuarantine = ref(false)
const quarantineForm = ref({
  ip_address: '',
  reason: '',
  mitigation_type: 'fail2ban_nftables',
  expiry_days: 7,
})

const activeIncidentToResolve = ref(null)
const resolveNotes = ref('')
const savingResolve = ref(false)

// Simulator states
const simThreat = ref({
  source_ip: '198.51.100.74',
  destination_number: '+23276123456',
  calls_per_minute: 45,
  is_off_hours: true,
  rate_usd_per_min: 1.25,
})
const simulating = ref(false)
const simResult = ref(null)

// Compliance policy states
const savingPolicy = ref(false)
const policyForm = ref({
  jurisdiction: props.policy?.jurisdiction || 'US_FCC_RAY_BAUM',
  recording_retention_days: props.policy?.recording_retention_days || 90,
  cdr_retention_days: props.policy?.cdr_retention_days || 365,
  enforce_pci_dtmf_masking: props.policy?.enforce_pci_dtmf_masking ?? true,
  gdpr_right_to_be_forgotten_enabled: props.policy?.gdpr_right_to_be_forgotten_enabled ?? true,
  immutable_audit_logging: props.policy?.immutable_audit_logging ?? true,
  require_mfa_admin_actions: props.policy?.require_mfa_admin_actions ?? true,
})

const formatDate = (dateStr) => {
  if (!dateStr) return 'Never'
  const d = new Date(dateStr)
  return d.toLocaleString()
}

const openNewRuleModal = () => {
  newRuleForm.value = {
    rule_name: '',
    rule_type: 'velocity_destination',
    threshold_value: 30,
    time_window_sec: 60,
    action: 'quarantine_ip',
    severity: 'critical',
    auto_mitigate: true,
  }
  showNewRuleModal.value = true
}

const submitNewRule = async () => {
  savingRule.value = true
  try {
    const res = await axios.post('/api/security/fraud-rules', newRuleForm.value)
    props.rules.unshift(res.data)
    showNewRuleModal.value = false
  } catch (err) {
    alert('Failed to create rule: ' + (err.response?.data?.message || err.message))
  } finally {
    savingRule.value = false
  }
}

const toggleRule = async (rule) => {
  try {
    const res = await axios.post(`/api/security/fraud-rules/${rule.uuid}/toggle`)
    rule.is_enabled = res.data.is_enabled
  } catch (err) {
    alert('Failed to toggle rule: ' + (err.response?.data?.message || err.message))
  }
}

const openQuarantineModal = () => {
  quarantineForm.value = {
    ip_address: '',
    reason: '',
    mitigation_type: 'fail2ban_nftables',
    expiry_days: 7,
  }
  showQuarantineModal.value = true
}

const submitQuarantine = async () => {
  savingQuarantine.value = true
  try {
    const res = await axios.post('/api/security/ip-quarantine', quarantineForm.value)
    props.quarantinedIps.unshift(res.data)
    showQuarantineModal.value = false
  } catch (err) {
    alert('Failed to quarantine IP: ' + (err.response?.data?.message || err.message))
  } finally {
    savingQuarantine.value = false
  }
}

const unquarantineIp = async (ip) => {
  if (!confirm(`Release and whitelist IP ${ip.ip_address}?`)) return
  try {
    await axios.post(`/api/security/ip-quarantine/${ip.uuid}/unquarantine`)
    ip.status = 'whitelisted'
  } catch (err) {
    alert('Failed to whitelist IP: ' + (err.response?.data?.message || err.message))
  }
}

const openResolveModal = (inc) => {
  activeIncidentToResolve.value = inc
  resolveNotes.value = 'Investigated and confirmed mitigated at border firewall.'
}

const submitResolveIncident = async () => {
  if (!activeIncidentToResolve.value) return
  savingResolve.value = true
  try {
    const res = await axios.post(`/api/security/incidents/${activeIncidentToResolve.value.uuid}/resolve`, {
      audit_notes: resolveNotes.value,
    })
    activeIncidentToResolve.value.status = 'resolved'
    activeIncidentToResolve.value.audit_notes = res.data.audit_notes
    activeIncidentToResolve.value = null
  } catch (err) {
    alert('Failed to resolve incident: ' + (err.response?.data?.message || err.message))
  } finally {
    savingResolve.value = false
  }
}

const runThreatSimulation = async () => {
  simulating.value = true
  try {
    const res = await axios.post('/api/security/simulate-threat-evaluation', simThreat.value)
    simResult.value = res.data
  } catch (err) {
    alert('Simulation error: ' + (err.response?.data?.message || err.message))
  } finally {
    simulating.value = false
  }
}

const saveCompliancePolicy = async () => {
  savingPolicy.value = true
  try {
    await axios.post('/api/security/compliance-policy', policyForm.value)
    alert('Regulatory compliance policy saved successfully.')
  } catch (err) {
    alert('Failed to save compliance policy: ' + (err.response?.data?.message || err.message))
  } finally {
    savingPolicy.value = false
  }
}
</script>
