<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- Header & Filter Bar -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-blue-500"></span>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <ChartBarSquareIcon class="w-7 h-7 text-blue-600" />
                {{ $t("Contact Center Analytics & Historical SLA Intelligence") }}
              </h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Executive SLA compliance metrics, interval volume distribution, inbound/outbound conversion funnels, and agent productivity leaderboard.") }}
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <!-- Period Selector Tabs -->
            <div class="inline-flex rounded-lg bg-gray-100 p-1 border border-gray-200 text-xs font-medium">
              <button
                v-for="p in periods"
                :key="p.key"
                @click="changePeriod(p.key)"
                :class="[
                  'px-3 py-1.5 rounded-md transition-all',
                  currentPeriod === p.key
                    ? 'bg-white text-gray-900 font-bold shadow-sm'
                    : 'text-gray-600 hover:text-gray-900',
                ]"
              >
                {{ p.label }}
              </button>
            </div>

            <!-- Queue Selector -->
            <select
              v-model="selectedQueueUuid"
              @change="applyQueueFilter"
              class="rounded-lg border-gray-300 text-xs py-1.5 font-medium focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">{{ $t("All Queues") }}</option>
              <option v-for="q in queues" :key="q.call_center_queue_uuid" :value="q.call_center_queue_uuid">
                {{ q.queue_name }} ({{ q.queue_extension }})
              </option>
            </select>

            <!-- Export CSV -->
            <a
              :href="`/contact-center/analytics/export?period=${currentPeriod}&queue_uuid=${selectedQueueUuid || ''}`"
              class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition"
            >
              <ArrowDownTrayIcon class="w-4 h-4 text-gray-500" />
              {{ $t("Export CSV") }}
            </a>

            <!-- Refresh -->
            <button
              @click="refreshData"
              class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition"
              :title="$t('Refresh Analytics')"
            >
              <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': isRefreshing }" />
            </button>
          </div>
        </div>
      </div>

      <!-- Top Executive KPIs Grid (P5D KPI Dictionary) -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Service Level Card -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("Service Level (SL)") }}</span>
            <span
              :class="[
                'px-1.5 py-0.5 rounded text-[10px] font-bold',
                kpis.service_level >= kpis.service_level_target
                  ? 'bg-emerald-100 text-emerald-700'
                  : 'bg-amber-100 text-amber-700',
              ]"
            >
              Target {{ kpis.service_level_target }}%
            </span>
          </div>
          <div class="text-2xl font-black text-gray-900 tracking-tight">
            {{ kpis.service_level }}%
          </div>
          <div class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
            <span class="font-medium text-emerald-600">{{ kpis.total_answered }}</span>
            <span>/ {{ kpis.total_offered }} offered</span>
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100">
            <div
              class="h-full bg-emerald-500"
              :style="{ width: `${Math.min(100, kpis.service_level)}%` }"
            ></div>
          </div>
        </div>

        <!-- ASA (Average Speed of Answer) -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("Avg Speed Answer (ASA)") }}</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">
              Target &lt;20s
            </span>
          </div>
          <div class="text-2xl font-black text-gray-900 tracking-tight">
            {{ kpis.asa_seconds }}s
          </div>
          <div class="text-[11px] text-gray-500 mt-1">
            Queue wait before bridge
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100">
            <div
              class="h-full bg-blue-500"
              :style="{ width: `${Math.min(100, (kpis.asa_seconds / 30) * 100)}%` }"
            ></div>
          </div>
        </div>

        <!-- AHT (Average Handle Time) -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("Avg Handle Time (AHT)") }}</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700">
              Talk + ACW
            </span>
          </div>
          <div class="text-2xl font-black text-gray-900 tracking-tight">
            {{ formatDuration(kpis.aht_seconds) }}
          </div>
          <div class="text-[11px] text-gray-500 mt-1">
            Total Talk: {{ kpis.total_talk_time_formatted }}
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100">
            <div class="h-full bg-purple-500" style="width: 70%"></div>
          </div>
        </div>

        <!-- Abandonment Rate -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("Abandon Rate") }}</span>
            <span
              :class="[
                'px-1.5 py-0.5 rounded text-[10px] font-bold',
                kpis.abandon_rate <= kpis.abandon_threshold
                  ? 'bg-emerald-50 text-emerald-700'
                  : 'bg-rose-100 text-rose-700',
              ]"
            >
              Ceiling {{ kpis.abandon_threshold }}%
            </span>
          </div>
          <div
            :class="[
              'text-2xl font-black tracking-tight',
              kpis.abandon_rate <= kpis.abandon_threshold ? 'text-gray-900' : 'text-rose-600',
            ]"
          >
            {{ kpis.abandon_rate }}%
          </div>
          <div class="text-[11px] text-gray-500 mt-1">
            {{ kpis.total_abandoned }} abandoned calls
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100">
            <div
              :class="['h-full', kpis.abandon_rate <= 5 ? 'bg-emerald-500' : 'bg-rose-500']"
              :style="{ width: `${Math.min(100, kpis.abandon_rate * 10)}%` }"
            ></div>
          </div>
        </div>

        <!-- First Contact Resolution (FCR) -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("First Contact Res.") }}</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700">
              FCR
            </span>
          </div>
          <div class="text-2xl font-black text-gray-900 tracking-tight">
            {{ kpis.fcr_rate }}%
          </div>
          <div class="text-[11px] text-gray-500 mt-1">
            Resolved on initial contact
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100">
            <div class="h-full bg-teal-500" :style="{ width: `${kpis.fcr_rate}%` }"></div>
          </div>
        </div>

        <!-- Agent Occupancy -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex items-center justify-between text-xs text-gray-500 font-semibold mb-1">
            <span>{{ $t("Agent Occupancy") }}</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">
              Staff Utilization
            </span>
          </div>
          <div class="text-2xl font-black text-gray-900 tracking-tight">
            {{ kpis.occupancy_rate }}%
          </div>
          <div class="text-[11px] text-gray-500 mt-1">
            {{ kpis.active_agents_count }} agents scheduled
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100">
            <div class="h-full bg-indigo-500" :style="{ width: `${kpis.occupancy_rate}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Main Visual Trends Chart -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
          <div>
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <PresentationChartLineIcon class="w-5 h-5 text-indigo-600" />
              {{ $t("Interval Volume & Service Level Compliance") }}
            </h2>
            <p class="text-xs text-gray-500">
              {{ $t("Hourly / Daily offered, answered, and abandoned interactions mapped against target SLA.") }}
            </p>
          </div>

          <!-- Legend -->
          <div class="flex items-center gap-4 text-xs font-medium">
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-indigo-500"></span>
              <span class="text-gray-600">{{ $t("Offered") }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-emerald-500"></span>
              <span class="text-gray-600">{{ $t("Answered") }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-rose-500"></span>
              <span class="text-gray-600">{{ $t("Abandoned") }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-1 bg-amber-500 rounded"></span>
              <span class="text-gray-600">{{ $t("SLA % Line") }}</span>
            </div>
          </div>
        </div>

        <!-- SVG Visual Chart -->
        <div class="w-full overflow-x-auto">
          <div class="min-w-[700px] h-64 relative flex items-end pt-8 pb-8 px-4 border-b border-l border-gray-200">
            <!-- Background Grid Lines -->
            <div class="absolute inset-x-0 top-8 border-b border-dashed border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
              <span>Max Volume</span>
              <span class="text-amber-600 font-bold">100% SLA</span>
            </div>
            <div class="absolute inset-x-0 top-1/2 border-b border-dashed border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
              <span>50%</span>
              <span class="text-amber-600">50% SLA</span>
            </div>

            <!-- Interval Bars & Points -->
            <div class="w-full flex items-end justify-between gap-1 z-10 h-48">
              <div
                v-for="(item, idx) in intervals"
                :key="idx"
                class="flex-1 flex flex-col items-center h-full justify-end group relative"
              >
                <!-- Tooltip -->
                <div
                  class="absolute -top-16 opacity-0 group-hover:opacity-100 bg-gray-900 text-white text-[10px] py-1.5 px-2.5 rounded shadow-lg pointer-events-none transition z-30 whitespace-nowrap"
                >
                  <div class="font-bold border-b border-gray-700 pb-0.5 mb-1">{{ item.label }}</div>
                  <div>Offered: {{ item.offered }} | Answered: {{ item.answered }}</div>
                  <div>Abandoned: {{ item.abandoned }} | SLA: {{ item.sla_percent }}%</div>
                  <div>Avg Wait: {{ item.avg_wait_sec }}s | Handle: {{ item.avg_handle_sec }}s</div>
                </div>

                <!-- Combined Grouped Bars -->
                <div class="w-full max-w-[28px] flex items-end gap-0.5 justify-center h-full">
                  <!-- Offered Bar -->
                  <div
                    class="w-2 bg-indigo-400 group-hover:bg-indigo-600 rounded-t transition-all"
                    :style="{ height: `${Math.min(100, (item.offered / maxOffered) * 100)}%` }"
                  ></div>
                  <!-- Answered Bar -->
                  <div
                    class="w-2 bg-emerald-400 group-hover:bg-emerald-600 rounded-t transition-all"
                    :style="{ height: `${Math.min(100, (item.answered / maxOffered) * 100)}%` }"
                  ></div>
                  <!-- Abandoned Bar -->
                  <div
                    class="w-1.5 bg-rose-400 group-hover:bg-rose-600 rounded-t transition-all"
                    :style="{ height: `${Math.min(100, (item.abandoned / maxOffered) * 100)}%` }"
                  ></div>
                </div>

                <!-- SLA Point Indicator -->
                <div
                  v-if="item.sla_percent > 0"
                  class="absolute w-2 h-2 rounded-full bg-amber-500 border border-white shadow-sm z-20 pointer-events-none"
                  :style="{ bottom: `${(item.sla_percent / 100) * 100}%` }"
                ></div>

                <!-- X Axis Label -->
                <div class="absolute -bottom-6 text-[10px] text-gray-500 truncate max-w-[36px]">
                  {{ item.label }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Funnels Row: Inbound Customer Funnel & Outbound Dialer Funnel -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Inbound Funnel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <ArrowTrendingDownIcon class="w-5 h-5 text-blue-600" />
                {{ $t("Inbound Interaction Funnel") }}
              </h3>
              <p class="text-xs text-gray-500">{{ $t("From DID/IVR ingress to agent resolution and queue abandonment.") }}</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">100% Inbound</span>
          </div>

          <div class="space-y-3">
            <div v-for="(f, i) in inboundFunnel" :key="i" class="space-y-1">
              <div class="flex items-center justify-between text-xs font-semibold">
                <span class="text-gray-700">{{ f.stage }}</span>
                <span class="text-gray-900 font-bold">{{ f.count }} ({{ f.percent }}%)</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div
                  :class="[
                    'h-full rounded-full transition-all duration-500',
                    f.color === 'indigo' ? 'bg-indigo-600' :
                    f.color === 'blue' ? 'bg-blue-600' :
                    f.color === 'emerald' ? 'bg-emerald-600' :
                    f.color === 'teal' ? 'bg-teal-600' :
                    f.color === 'amber' ? 'bg-amber-500' : 'bg-rose-500'
                  ]"
                  :style="{ width: `${f.percent}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Outbound Campaign Dialer Funnel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <MegaphoneIcon class="w-5 h-5 text-indigo-600" />
                {{ $t("Outbound Campaign Dialer Funnel") }}
              </h3>
              <p class="text-xs text-gray-500">{{ $t("Predictive & progressive pacing through human contact and conversion.") }}</p>
            </div>
            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Dialer Studio</span>
          </div>

          <div class="space-y-3">
            <div v-for="(f, i) in outboundFunnel" :key="i" class="space-y-1">
              <div class="flex items-center justify-between text-xs font-semibold">
                <span class="text-gray-700">{{ f.stage }}</span>
                <span class="text-gray-900 font-bold">{{ f.count }} ({{ f.percent }}%)</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div
                  :class="[
                    'h-full rounded-full transition-all duration-500',
                    f.color === 'slate' ? 'bg-slate-600' :
                    f.color === 'indigo' ? 'bg-indigo-600' :
                    f.color === 'sky' ? 'bg-sky-500' :
                    f.color === 'emerald' ? 'bg-emerald-500' :
                    f.color === 'green' ? 'bg-green-600' : 'bg-rose-600'
                  ]"
                  :style="{ width: `${Math.min(100, f.percent)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabbed Tables: Queue SLA Performance vs Agent Leaderboard -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-5 pt-4">
          <div class="flex items-center justify-between">
            <div class="flex space-x-6">
              <button
                @click="activeTableTab = 'queues'"
                :class="[
                  'pb-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2',
                  activeTableTab === 'queues'
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700',
                ]"
              >
                <QueueListIcon class="w-4 h-4" />
                {{ $t("Queue SLA Compliance Matrix") }}
                <span class="ml-1 text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full font-semibold">
                  {{ queuePerformance.length }}
                </span>
              </button>

              <button
                @click="activeTableTab = 'agents'"
                :class="[
                  'pb-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2',
                  activeTableTab === 'agents'
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700',
                ]"
              >
                <TrophyIcon class="w-4 h-4 text-amber-500" />
                {{ $t("Agent Productivity & Quality Leaderboard") }}
                <span class="ml-1 text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full font-semibold">
                  {{ agentLeaderboard.length }}
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Queue SLA Compliance Table -->
        <div v-if="activeTableTab === 'queues'" class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/75 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                <th class="p-3.5">{{ $t("Queue Name & Ext") }}</th>
                <th class="p-3.5 text-center">{{ $t("Offered") }}</th>
                <th class="p-3.5 text-center">{{ $t("Answered") }}</th>
                <th class="p-3.5 text-center">{{ $t("Abandoned") }}</th>
                <th class="p-3.5 text-center">{{ $t("Abandon %") }}</th>
                <th class="p-3.5 text-center">{{ $t("Target SLA") }}</th>
                <th class="p-3.5 text-center">{{ $t("Actual SLA") }}</th>
                <th class="p-3.5 text-center">{{ $t("SLA Status") }}</th>
                <th class="p-3.5 text-center">{{ $t("ASA") }}</th>
                <th class="p-3.5 text-center">{{ $t("AHT") }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 font-medium text-gray-700">
              <tr v-for="q in queuePerformance" :key="q.queue_uuid" class="hover:bg-gray-50 transition">
                <td class="p-3.5">
                  <div class="font-bold text-gray-900">{{ q.queue_name }}</div>
                  <div class="text-[11px] text-gray-400">Ext {{ q.queue_extension }}</div>
                </td>
                <td class="p-3.5 text-center font-bold">{{ q.offered }}</td>
                <td class="p-3.5 text-center text-emerald-600 font-bold">{{ q.answered }}</td>
                <td class="p-3.5 text-center text-rose-600 font-bold">{{ q.abandoned }}</td>
                <td class="p-3.5 text-center">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded font-bold',
                      q.abandon_rate <= 5 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'
                    ]"
                  >
                    {{ q.abandon_rate }}%
                  </span>
                </td>
                <td class="p-3.5 text-center text-gray-500 font-semibold">{{ q.sla_target }}%</td>
                <td class="p-3.5 text-center font-black text-gray-900">{{ q.actual_sla }}%</td>
                <td class="p-3.5 text-center">
                  <span
                    v-if="q.sla_status === 'compliant'"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"
                  >
                    <CheckCircleIcon class="w-3.5 h-3.5" />
                    {{ $t("Compliant") }}
                  </span>
                  <span
                    v-else-if="q.sla_status === 'warning'"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"
                  >
                    <ExclamationTriangleIcon class="w-3.5 h-3.5" />
                    {{ $t("At Risk") }}
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"
                  >
                    <XCircleIcon class="w-3.5 h-3.5" />
                    {{ $t("Breached") }}
                  </span>
                </td>
                <td class="p-3.5 text-center font-semibold text-gray-800">{{ q.asa_seconds }}s</td>
                <td class="p-3.5 text-center font-semibold text-gray-800">{{ formatDuration(q.aht_seconds) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Agent Leaderboard Table -->
        <div v-if="activeTableTab === 'agents'" class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/75 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                <th class="p-3.5 text-center">{{ $t("Rank") }}</th>
                <th class="p-3.5">{{ $t("Agent & ID") }}</th>
                <th class="p-3.5 text-center">{{ $t("Current State") }}</th>
                <th class="p-3.5 text-center">{{ $t("Handled Calls") }}</th>
                <th class="p-3.5 text-center">{{ $t("Missed Calls") }}</th>
                <th class="p-3.5 text-center">{{ $t("Total Talk Time") }}</th>
                <th class="p-3.5 text-center">{{ $t("Avg Handle (AHT)") }}</th>
                <th class="p-3.5 text-center">{{ $t("QA Score") }}</th>
                <th class="p-3.5 text-center">{{ $t("Performance") }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 font-medium text-gray-700">
              <tr v-for="a in agentLeaderboard" :key="a.agent_uuid" class="hover:bg-gray-50 transition">
                <td class="p-3.5 text-center">
                  <span
                    v-if="a.rank === 1"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-black text-xs shadow-sm"
                  >
                    🥇
                  </span>
                  <span
                    v-else-if="a.rank === 2"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-black text-xs shadow-sm"
                  >
                    🥈
                  </span>
                  <span
                    v-else-if="a.rank === 3"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-50 text-amber-900 font-black text-xs shadow-sm"
                  >
                    🥉
                  </span>
                  <span v-else class="text-gray-400 font-bold">#{{ a.rank }}</span>
                </td>
                <td class="p-3.5">
                  <div class="font-bold text-gray-900">{{ a.agent_name }}</div>
                  <div class="text-[11px] text-gray-400">ID: {{ a.agent_id }}</div>
                </td>
                <td class="p-3.5 text-center">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-[11px] font-bold',
                      a.status === 'Available' ? 'bg-emerald-100 text-emerald-800' :
                      a.status === 'Logged Out' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800'
                    ]"
                  >
                    {{ a.status }}
                  </span>
                </td>
                <td class="p-3.5 text-center font-bold text-gray-900">{{ a.calls_handled }}</td>
                <td class="p-3.5 text-center font-semibold text-rose-600">{{ a.calls_missed }}</td>
                <td class="p-3.5 text-center font-semibold text-gray-700">{{ a.total_talk_time_formatted }}</td>
                <td class="p-3.5 text-center font-semibold text-gray-700">{{ a.avg_handle_time_formatted }}</td>
                <td class="p-3.5 text-center">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded font-black',
                      a.qa_score >= 90 ? 'bg-emerald-50 text-emerald-700' :
                      a.qa_score >= 80 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'
                    ]"
                  >
                    {{ a.qa_score }}%
                  </span>
                </td>
                <td class="p-3.5 text-center">
                  <span
                    v-if="a.qa_score >= 92 && a.calls_handled >= 25"
                    class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500 text-white"
                  >
                    Top Tier
                  </span>
                  <span
                    v-else
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-gray-200 text-gray-700"
                  >
                    Standard
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  ChartBarSquareIcon,
  PresentationChartLineIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon,
  ArrowTrendingDownIcon,
  MegaphoneIcon,
  QueueListIcon,
  TrophyIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  kpis: {
    type: Object,
    required: true,
  },
  intervals: {
    type: Array,
    default: () => [],
  },
  inboundFunnel: {
    type: Array,
    default: () => [],
  },
  outboundFunnel: {
    type: Array,
    default: () => [],
  },
  queuePerformance: {
    type: Array,
    default: () => [],
  },
  agentLeaderboard: {
    type: Array,
    default: () => [],
  },
  queues: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({
      period: "today",
      queue_uuid: "",
    }),
  },
});

const currentPeriod = ref(props.filters.period || "today");
const selectedQueueUuid = ref(props.filters.queue_uuid || "");
const activeTableTab = ref("queues");
const isRefreshing = ref(false);

const periods = [
  { key: "today", label: "Today" },
  { key: "yesterday", label: "Yesterday" },
  { key: "7d", label: "Last 7 Days" },
  { key: "30d", label: "Last 30 Days" },
];

const maxOffered = computed(() => {
  const max = Math.max(...props.intervals.map((i) => i.offered || 0), 10);
  return max;
});

function changePeriod(p) {
  currentPeriod.value = p;
  router.get(
    "/contact-center/analytics",
    {
      period: currentPeriod.value,
      queue_uuid: selectedQueueUuid.value || undefined,
    },
    { preserveState: true }
  );
}

function applyQueueFilter() {
  router.get(
    "/contact-center/analytics",
    {
      period: currentPeriod.value,
      queue_uuid: selectedQueueUuid.value || undefined,
    },
    { preserveState: true }
  );
}

function refreshData() {
  isRefreshing.value = true;
  router.reload({
    onFinish: () => {
      isRefreshing.value = false;
    },
  });
}

function formatDuration(sec) {
  if (!sec) return "0s";
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  if (m > 0) return `${m}m ${s}s`;
  return `${s}s`;
}
</script>
