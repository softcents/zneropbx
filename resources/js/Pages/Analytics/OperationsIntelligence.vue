<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Dark Spotlight Banner Header -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-950 via-emerald-950 to-slate-900 p-6 text-white shadow-xl ring-1 ring-white/10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-md bg-emerald-500/20 px-2 py-0.5 text-xs font-medium text-emerald-300 ring-1 ring-emerald-500/30">
                Phase 11 • Operations Intelligence
              </span>
              <span class="inline-flex items-center rounded-md bg-sky-500/20 px-2 py-0.5 text-xs font-medium text-sky-300 ring-1 ring-sky-500/30">
                Cross-Domain Telemetry & Forecasting
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
              Operations Intelligence & Analytics Studio
            </h1>
            <p class="mt-1 text-sm text-gray-300">
              Holistic observability across FreeSWITCH cluster nodes, carrier wholesale margin arbitrage, predictive capacity forecasting, and automated BI reporting.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="openNewExportModal"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition"
            >
              <DocumentArrowDownIcon class="h-4 w-4" />
              Schedule BI Export
            </button>
            <button
              @click="openNewViewModal"
              class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
            >
              <BookmarkSquareIcon class="h-4 w-4" />
              Save Query View
            </button>
          </div>
        </div>

        <!-- Telemetry KPI Summary Cards -->
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 border-t border-white/10 pt-4">
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Health Score</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-emerald-400">{{ metrics.composite_operational_health_score }}%</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">MTD Billed Revenue</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-cyan-400">${{ Number(metrics.monthly_billed_revenue_usd).toLocaleString() }}</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Gross Margin</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-emerald-400">{{ metrics.gross_margin_percent }}%</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Cluster Nodes</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-indigo-400">{{ metrics.active_cluster_nodes }} Online</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Avg Trunk ASR</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-teal-400">{{ metrics.avg_carrier_asr }}%</div>
          </div>
          <div class="rounded-lg bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
            <div class="text-xs font-medium text-gray-400">Contact Center SL</div>
            <div class="mt-1 text-xl font-bold tabular-nums text-amber-400">{{ metrics.contact_center_sl_pct }}%</div>
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
                ? 'border-emerald-600 text-emerald-600 font-semibold'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-3 px-1 text-sm flex items-center gap-2 transition'
            ]"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.name }}
            <span
              v-if="tab.count !== undefined"
              :class="[
                activeTab === tab.id ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-600',
                'rounded-full px-2 py-0.5 text-xs font-semibold'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- TAB 1: Cross-Domain Health Radar -->
      <div v-if="activeTab === 'telemetry'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Telecom Domain Card -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs">P2</span>
                <h3 class="font-bold text-gray-900 text-sm">Telecom Trunks & Routing</h3>
              </div>
              <span class="text-xs font-bold text-emerald-600">ASR {{ crossDomainMetrics.telecom.asr_percent }}%</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt class="text-gray-500">Average ACD</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.telecom.acd_seconds }}s</dd>
              </div>
              <div>
                <dt class="text-gray-500">Mean Opinion Score</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.telecom.mos_score }} / 5.0</dd>
              </div>
              <div class="col-span-2 pt-2 border-t">
                <dt class="text-gray-500">Total Billed Minutes</dt>
                <dd class="font-bold text-teal-700 text-base tabular-nums">{{ Number(crossDomainMetrics.telecom.total_billed_minutes).toLocaleString() }} mins</dd>
              </div>
            </dl>
          </div>

          <!-- Financial Domain Card -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-xs">P3</span>
                <h3 class="font-bold text-gray-900 text-sm">Financial Rating & Margin</h3>
              </div>
              <span class="text-xs font-bold text-emerald-600">{{ crossDomainMetrics.financial.gross_margin_pct }}% Margin</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt class="text-gray-500">Wholesale Carrier Cost</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">${{ Number(crossDomainMetrics.financial.wholesale_cost_usd).toLocaleString() }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Active Resellers</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.financial.active_resellers }} Accounts</dd>
              </div>
              <div class="col-span-2 pt-2 border-t">
                <dt class="text-gray-500">Gross Billed Revenue</dt>
                <dd class="font-bold text-emerald-700 text-base tabular-nums">${{ Number(crossDomainMetrics.financial.gross_revenue_usd).toLocaleString() }}</dd>
              </div>
            </dl>
          </div>

          <!-- Contact Center Domain Card -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-indigo-50 text-indigo-600 font-bold text-xs">P5</span>
                <h3 class="font-bold text-gray-900 text-sm">Contact Center ACD Queues</h3>
              </div>
              <span class="text-xs font-bold text-indigo-600">SL {{ crossDomainMetrics.contact_center.service_level_pct }}%</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt class="text-gray-500">Average Speed to Answer</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.contact_center.avg_speed_answer_sec }}s</dd>
              </div>
              <div>
                <dt class="text-gray-500">Abandonment Rate</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.contact_center.abandonment_rate_pct }}%</dd>
              </div>
              <div class="col-span-2 pt-2 border-t">
                <dt class="text-gray-500">First Contact Resolution</dt>
                <dd class="font-bold text-indigo-700 text-base tabular-nums">{{ crossDomainMetrics.contact_center.fcr_rate_pct }}% FCR</dd>
              </div>
            </dl>
          </div>

          <!-- Messaging & AI Domain Card -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-purple-50 text-purple-600 font-bold text-xs">P7/8</span>
                <h3 class="font-bold text-gray-900 text-sm">Messaging & AI Automation</h3>
              </div>
              <span class="text-xs font-bold text-purple-600">DLR {{ crossDomainMetrics.messaging_ai.sms_dlr_success_pct }}%</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt class="text-gray-500">SMS Dispatched</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ Number(crossDomainMetrics.messaging_ai.total_sms_dispatched).toLocaleString() }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">AI Latency Turnaround</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.messaging_ai.ai_voice_avg_turnaround_ms }} ms</dd>
              </div>
              <div class="col-span-2 pt-2 border-t">
                <dt class="text-gray-500">Monthly LLM Tokens</dt>
                <dd class="font-bold text-purple-700 text-base tabular-nums">{{ Number(crossDomainMetrics.messaging_ai.monthly_ai_tokens_consumed).toLocaleString() }}</dd>
              </div>
            </dl>
          </div>

          <!-- Carrier Infrastructure Domain Card -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-sky-50 text-sky-600 font-bold text-xs">P6</span>
                <h3 class="font-bold text-gray-900 text-sm">Carrier Topology & HA Nodes</h3>
              </div>
              <span class="text-xs font-bold text-sky-600">{{ crossDomainMetrics.infrastructure.cluster_nodes_online }} Online</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt class="text-gray-500">Cluster CPU Load</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.infrastructure.cluster_cpu_load_pct }}%</dd>
              </div>
              <div>
                <dt class="text-gray-500">Failover Drill RTO</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">{{ crossDomainMetrics.infrastructure.failover_rto_seconds }}s</dd>
              </div>
              <div class="col-span-2 pt-2 border-t">
                <dt class="text-gray-500">Network Packet Loss</dt>
                <dd class="font-bold text-emerald-600 text-base tabular-nums">{{ crossDomainMetrics.infrastructure.packet_loss_pct }}% (Lossless)</dd>
              </div>
            </dl>
          </div>

          <!-- Security & Fraud Posture Card -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-rose-50 text-rose-600 font-bold text-xs">P10</span>
                <h3 class="font-bold text-gray-900 text-sm">Security & Fraud Shield</h3>
              </div>
              <span class="text-xs font-bold text-emerald-600">Zero Breaches</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt class="text-gray-500">Attacks Mitigated</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">482 Attempts</dd>
              </div>
              <div>
                <dt class="text-gray-500">Border ACL Intervene</dt>
                <dd class="font-bold text-gray-900 text-sm tabular-nums">&lt; 8 ms</dd>
              </div>
              <div class="col-span-2 pt-2 border-t">
                <dt class="text-gray-500">Toll Fraud Capital Saved</dt>
                <dd class="font-bold text-cyan-700 text-base tabular-nums">$42,850.00 USD</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <!-- TAB 2: Carrier QoS & Margin Benchmark -->
      <div v-if="activeTab === 'carrier_qos'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Multi-Carrier QoS & Wholesale Margin Benchmark</h2>
              <p class="text-xs text-gray-500">Direct comparative matrix of answer-seizure ratios, post-dial delay, MOS audio quality, and gross margin.</p>
            </div>
            <span class="text-xs text-gray-500">Daily Historical Aggregation</span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Carrier Partner</th>
                  <th scope="col" class="px-6 py-3">ASR %</th>
                  <th scope="col" class="px-6 py-3">ACD (s)</th>
                  <th scope="col" class="px-6 py-3">PDD (ms)</th>
                  <th scope="col" class="px-6 py-3">MOS Score</th>
                  <th scope="col" class="px-6 py-3">Unit Cost ($/min)</th>
                  <th scope="col" class="px-6 py-3">Gross Margin</th>
                  <th scope="col" class="px-6 py-3 text-right">Billed Revenue</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="b in carrierBenchmarks" :key="b.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">
                    {{ b.carrier_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-teal-700">
                    {{ b.asr_percent }}%
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs">
                    {{ b.acd_seconds }}s
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                    {{ b.pdd_ms }} ms
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                      ★ {{ b.mos_score }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-700">
                    ${{ b.cost_per_minute_usd }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="w-16 h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" :style="{ width: b.margin_percent + '%' }"></div>
                      </div>
                      <span class="font-mono text-xs font-bold text-emerald-700">{{ b.margin_percent }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-sm font-bold text-gray-900">
                    ${{ Number(b.total_revenue_usd).toLocaleString() }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: Predictive Capacity Forecasting -->
      <div v-if="activeTab === 'forecasting'" class="space-y-6">
        <div class="rounded-xl bg-slate-900 p-6 text-white shadow-md ring-1 ring-white/10 space-y-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 pb-4">
            <div>
              <h3 class="text-base font-semibold text-white">Predictive Capacity & Anomaly Forecaster</h3>
              <p class="mt-1 text-xs text-gray-400">
                Machine-learning trend extrapolation to forecast peak SIP trunk concurrency, contact center queue staffing, and AI token run-rate.
              </p>
            </div>
            <div class="flex items-center gap-3">
              <select
                v-model="forecastQuery.metric"
                class="rounded-lg border-0 bg-slate-800 py-2 pl-3 pr-8 text-xs text-white shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-emerald-500"
              >
                <option value="concurrent_channels">SIP Trunk Concurrency (Channels)</option>
                <option value="agent_headcount">ACD Queue Headcount (FTEs)</option>
                <option value="sms_volume">Daily SMS Throughput (Messages)</option>
                <option value="ai_tokens">AI Voice Tokens (Monthly Run-Rate)</option>
              </select>

              <select
                v-model.number="forecastQuery.days_ahead"
                class="rounded-lg border-0 bg-slate-800 py-2 pl-3 pr-8 text-xs text-white shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-emerald-500"
              >
                <option :value="7">7 Days Ahead</option>
                <option :value="14">14 Days Ahead</option>
                <option :value="30">30 Days Ahead</option>
              </select>

              <button
                @click="runForecast"
                :disabled="loadingForecast"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition disabled:opacity-50"
              >
                <ArrowTrendingUpIcon class="h-4 w-4" />
                {{ loadingForecast ? 'Calculating...' : 'Run Projection' }}
              </button>
            </div>
          </div>

          <!-- Forecast Summary Cards -->
          <div v-if="currentForecast" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-lg bg-white/5 p-4 ring-1 ring-white/10">
              <div class="text-xs text-gray-400">Current Baseline</div>
              <div class="mt-1 text-xl font-bold font-mono text-white">{{ Number(currentForecast.current_baseline).toLocaleString() }} <span class="text-xs font-normal text-gray-400">{{ currentForecast.unit }}</span></div>
            </div>
            <div class="rounded-lg bg-white/5 p-4 ring-1 ring-white/10">
              <div class="text-xs text-gray-400">Projected Peak ({{ currentForecast.days_ahead }}d)</div>
              <div class="mt-1 text-xl font-bold font-mono text-amber-400">{{ Number(currentForecast.projected_peak).toLocaleString() }} <span class="text-xs font-normal text-gray-400">{{ currentForecast.unit }}</span></div>
            </div>
            <div class="rounded-lg bg-white/5 p-4 ring-1 ring-white/10">
              <div class="text-xs text-gray-400">Recommended Provisioning Ceiling</div>
              <div class="mt-1 text-xl font-bold font-mono text-emerald-400">{{ Number(currentForecast.recommended_provisioning_ceiling).toLocaleString() }} <span class="text-xs font-normal text-gray-400">{{ currentForecast.unit }}</span></div>
            </div>
            <div class="rounded-lg bg-white/5 p-4 ring-1 ring-white/10">
              <div class="text-xs text-gray-400">Confidence Band</div>
              <div class="mt-1 text-xl font-bold font-mono text-cyan-400">95% Bayesian</div>
            </div>
          </div>

          <!-- SVG Visual Trend Graph -->
          <div v-if="currentForecast && currentForecast.trend_points" class="rounded-xl bg-slate-950 p-6 ring-1 ring-white/10 space-y-3">
            <div class="flex items-center justify-between text-xs text-gray-400">
              <span>Demand Extrapolation & Upper Safety Ceiling Curve</span>
              <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Expected Trend</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span> 95% Upper Bound</span>
              </div>
            </div>

            <!-- Responsive SVG Chart -->
            <div class="overflow-x-auto">
              <svg viewBox="0 0 700 160" class="w-full h-44 overflow-visible">
                <!-- Grid Lines -->
                <line x1="20" y1="20" x2="680" y2="20" stroke="rgba(255,255,255,0.08)" stroke-dasharray="3,3" />
                <line x1="20" y1="70" x2="680" y2="70" stroke="rgba(255,255,255,0.08)" stroke-dasharray="3,3" />
                <line x1="20" y1="120" x2="680" y2="120" stroke="rgba(255,255,255,0.08)" stroke-dasharray="3,3" />

                <!-- Upper Bound Polygon Area -->
                <polygon
                  :points="generateSvgAreaPoints(currentForecast.trend_points)"
                  fill="rgba(16, 185, 129, 0.15)"
                />

                <!-- Predicted Trend Line -->
                <polyline
                  :points="generateSvgPolylinePoints(currentForecast.trend_points, 'predicted_demand')"
                  fill="none"
                  stroke="#10B981"
                  stroke-width="3"
                />

                <!-- Upper Bound Dash Line -->
                <polyline
                  :points="generateSvgPolylinePoints(currentForecast.trend_points, 'upper_bound')"
                  fill="none"
                  stroke="#F59E0B"
                  stroke-width="2"
                  stroke-dasharray="4,4"
                />

                <!-- Points -->
                <g v-for="(p, idx) in currentForecast.trend_points" :key="idx">
                  <circle
                    :cx="20 + (idx * (660 / (currentForecast.trend_points.length - 1)))"
                    :cy="calculateSvgY(p.predicted_demand, currentForecast)"
                    r="4"
                    fill="#10B981"
                    stroke="#022c22"
                    stroke-width="2"
                  />
                  <text
                    :x="20 + (idx * (660 / (currentForecast.trend_points.length - 1)))"
                    y="150"
                    fill="#9CA3AF"
                    font-size="10"
                    text-anchor="middle"
                    font-family="monospace"
                  >
                    {{ p.day }}
                  </text>
                </g>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: Scheduled BI Exports -->
      <div v-if="activeTab === 'exports'" class="space-y-6">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Scheduled BI Report Exports</h2>
              <p class="text-xs text-gray-500">Automated recurring data feeds delivered directly to operations and accounting recipients.</p>
            </div>
            <button
              @click="openNewExportModal"
              class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition"
            >
              + Create Export
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Report Title</th>
                  <th scope="col" class="px-6 py-3">Format</th>
                  <th scope="col" class="px-6 py-3">Frequency</th>
                  <th scope="col" class="px-6 py-3">Recipient Email</th>
                  <th scope="col" class="px-6 py-3">Last Executed</th>
                  <th scope="col" class="px-6 py-3">Next Scheduled Run</th>
                  <th scope="col" class="px-6 py-3 text-right">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="ex in scheduledExports" :key="ex.uuid" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">
                    {{ ex.report_title }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono font-bold text-gray-700 uppercase">
                      {{ ex.format }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 capitalize font-medium">
                    {{ ex.frequency }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-indigo-700">
                    {{ ex.recipient_email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                    {{ formatDate(ex.last_exported_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                    {{ formatDate(ex.next_run_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200 capitalize">
                      {{ ex.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 5: Saved Views -->
      <div v-if="activeTab === 'views'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="v in savedViews"
            :key="v.uuid"
            class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 flex flex-col justify-between space-y-4 hover:shadow-md transition"
          >
            <div>
              <div class="flex items-center justify-between">
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-200 capitalize">
                  {{ v.category }}
                </span>
                <span v-if="v.is_pinned" class="text-amber-500 text-xs font-bold flex items-center gap-1">
                  ★ Pinned View
                </span>
              </div>
              <h3 class="mt-2 font-bold text-gray-900 text-base">{{ v.view_name }}</h3>
              <div class="mt-3 rounded-lg bg-gray-50 p-2.5 font-mono text-xs text-gray-600 border border-gray-200">
                <pre>{{ JSON.stringify(v.filter_criteria_json, null, 2) }}</pre>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-3 flex justify-between items-center text-xs text-gray-500">
              <span>Saved {{ formatDate(v.created_at) }}</span>
              <button class="font-semibold text-emerald-600 hover:text-emerald-800">
                Load View →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL: Create Export -->
      <div v-if="showNewExportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Schedule BI Report Export</h3>
            <button @click="showNewExportModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Report Title</label>
              <input
                v-model="newExportForm.report_title"
                type="text"
                placeholder="e.g. Weekly Carrier Margin Arbitrage Summary"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700">Export Format</label>
                <select
                  v-model="newExportForm.format"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono"
                >
                  <option value="csv">CSV (Streaming Spreadsheet)</option>
                  <option value="xlsx">Excel (XLSX Workbook)</option>
                  <option value="json">JSON (API Feed)</option>
                  <option value="pdf">PDF (Executive Summary)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700">Frequency</label>
                <select
                  v-model="newExportForm.frequency"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                  <option value="daily">Daily Midnight</option>
                  <option value="weekly">Weekly (Monday 06:00 UTC)</option>
                  <option value="monthly">Monthly (1st of Month)</option>
                  <option value="ad_hoc">Ad-hoc Immediate</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700">Recipient Email Address</label>
              <input
                v-model="newExportForm.recipient_email"
                type="email"
                placeholder="operations-brief@corp.znero.com"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono"
              />
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="showNewExportModal = false"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitNewExport"
              :disabled="savingExport || !newExportForm.report_title || !newExportForm.recipient_email"
              class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition disabled:opacity-50"
            >
              {{ savingExport ? 'Scheduling...' : 'Schedule Export' }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: Save Custom View -->
      <div v-if="showNewViewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Save Query View</h3>
            <button @click="showNewViewModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">View Name</label>
              <input
                v-model="newViewForm.view_name"
                type="text"
                placeholder="e.g. Carrier PDD Outlier Filter"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700">Domain Category</label>
              <select
                v-model="newViewForm.category"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="financial">Financial & Ledger</option>
                <option value="carrier_qos">Carrier QoS & LCR</option>
                <option value="workforce">Workforce & ACD Queues</option>
                <option value="messaging_ai">Messaging & AI Automation</option>
                <option value="infrastructure">Infrastructure & Nodes</option>
              </select>
            </div>

            <label class="flex items-center gap-2 text-xs text-gray-700">
              <input
                type="checkbox"
                v-model="newViewForm.is_pinned"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              Pin to Top Executive Views
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="showNewViewModal = false"
              class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitNewView"
              :disabled="savingView || !newViewForm.view_name"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
            >
              {{ savingView ? 'Saving...' : 'Save View' }}
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
  ChartBarSquareIcon,
  GlobeAltIcon,
  ArrowTrendingUpIcon,
  DocumentArrowDownIcon,
  BookmarkSquareIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  crossDomainMetrics: { type: Object, default: () => ({}) },
  carrierBenchmarks: { type: Array, default: () => [] },
  savedViews: { type: Array, default: () => [] },
  scheduledExports: { type: Array, default: () => [] },
  initialForecast: { type: Object, default: () => null },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('telemetry')

const tabs = [
  { id: 'telemetry', name: 'Cross-Domain Radar', icon: ChartBarSquareIcon },
  { id: 'carrier_qos', name: 'Carrier QoS & Margin', icon: GlobeAltIcon, count: props.carrierBenchmarks.length },
  { id: 'forecasting', name: 'Predictive Forecaster', icon: ArrowTrendingUpIcon },
  { id: 'exports', name: 'Scheduled Exports', icon: DocumentArrowDownIcon, count: props.scheduledExports.length },
  { id: 'views', name: 'Executive Saved Views', icon: BookmarkSquareIcon, count: props.savedViews.length },
]

// Modal states
const showNewExportModal = ref(false)
const savingExport = ref(false)
const newExportForm = ref({
  report_title: '',
  format: 'csv',
  frequency: 'weekly',
  recipient_email: '',
})

const showNewViewModal = ref(false)
const savingView = ref(false)
const newViewForm = ref({
  view_name: '',
  category: 'financial',
  is_pinned: true,
})

// Forecasting states
const forecastQuery = ref({
  metric: 'concurrent_channels',
  days_ahead: 14,
})
const loadingForecast = ref(false)
const currentForecast = ref(props.initialForecast)

const formatDate = (dateStr) => {
  if (!dateStr) return 'Never'
  const d = new Date(dateStr)
  return d.toLocaleString()
}

const openNewExportModal = () => {
  newExportForm.value = {
    report_title: '',
    format: 'csv',
    frequency: 'weekly',
    recipient_email: '',
  }
  showNewExportModal.value = true
}

const submitNewExport = async () => {
  savingExport.value = true
  try {
    const res = await axios.post('/api/analytics/operations-intelligence/export-report', newExportForm.value)
    props.scheduledExports.unshift(res.data)
    showNewExportModal.value = false
  } catch (err) {
    alert('Failed to schedule export: ' + (err.response?.data?.message || err.message))
  } finally {
    savingExport.value = false
  }
}

const openNewViewModal = () => {
  newViewForm.value = {
    view_name: '',
    category: 'financial',
    is_pinned: true,
  }
  showNewViewModal.value = true
}

const submitNewView = async () => {
  savingView.value = true
  try {
    const res = await axios.post('/api/analytics/operations-intelligence/save-view', newViewForm.value)
    props.savedViews.unshift(res.data)
    showNewViewModal.value = false
  } catch (err) {
    alert('Failed to save view: ' + (err.response?.data?.message || err.message))
  } finally {
    savingView.value = false
  }
}

const runForecast = async () => {
  loadingForecast.value = true
  try {
    const res = await axios.post('/api/analytics/operations-intelligence/forecast-capacity', forecastQuery.value)
    currentForecast.value = res.data
  } catch (err) {
    alert('Failed to run forecast: ' + (err.response?.data?.message || err.message))
  } finally {
    loadingForecast.value = false
  }
}

// Helper SVG calculations
const calculateSvgY = (val, forecast) => {
  const minVal = forecast.current_baseline * 0.85
  const maxVal = forecast.recommended_provisioning_ceiling * 1.05
  const range = maxVal - minVal || 1
  const pct = (val - minVal) / range
  return 120 - (pct * 95)
}

const generateSvgPolylinePoints = (points, key) => {
  if (!points || points.length === 0) return ''
  const len = points.length - 1 || 1
  return points
    .map((p, idx) => {
      const x = 20 + idx * (660 / len)
      const y = calculateSvgY(p[key], currentForecast.value)
      return `${x},${y}`
    })
    .join(' ')
}

const generateSvgAreaPoints = (points) => {
  if (!points || points.length === 0) return ''
  const len = points.length - 1 || 1
  const topPoints = points.map((p, idx) => {
    const x = 20 + idx * (660 / len)
    const y = calculateSvgY(p.upper_bound, currentForecast.value)
    return `${x},${y}`
  })
  const bottomPoints = points.slice().reverse().map((p, idx) => {
    const actualIdx = points.length - 1 - idx
    const x = 20 + actualIdx * (660 / len)
    const y = calculateSvgY(p.lower_bound, currentForecast.value)
    return `${x},${y}`
  })
  return [...topPoints, ...bottomPoints].join(' ')
}
</script>
