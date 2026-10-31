<template>
    <MainLayout>
        <div class="m-3 space-y-4">
        <!-- Header & Top Navigation -->
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">{{ $t("Supervisor Command Center") }}</h1>
                    <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                        {{ $t("Live Pro Ops") }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $t("Real-time Queue Intelligence, Agent State Management, and Telephony Interventions (Listen, Whisper, Barge).") }}
                </p>
            </div>

            <!-- Controls: Supervisor Ext & Auto Refresh -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-700">{{ $t("My Extension:") }}</label>
                    <select v-model="selectedSupervisorExt"
                        class="rounded-md border-0 py-1.5 pl-3 pr-8 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                        <option value="">{{ $t("-- Select Device --") }}</option>
                        <option v-for="ext in extensions" :key="ext.extension_uuid" :value="ext.extension">
                            {{ ext.extension }} {{ ext.description ? `(${ext.description})` : '' }}
                        </option>
                    </select>
                </div>

                <div class="flex items-center rounded-md bg-gray-50 p-1 ring-1 ring-inset ring-gray-200">
                    <button type="button" @click="toggleAutoRefresh"
                        :class="[
                            autoRefresh
                                ? 'bg-emerald-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-100'
                        ]"
                        class="flex items-center gap-1.5 rounded px-2.5 py-1 text-xs font-semibold shadow-sm transition-all">
                        <span class="inline-block h-2 w-2 rounded-full" :class="autoRefresh ? 'bg-white' : 'bg-gray-400'"></span>
                        {{ autoRefresh ? $t("Live (3s)") : $t("Paused") }}
                    </button>
                    <button type="button" @click="fetchLiveData" :disabled="loading"
                        class="ml-1 rounded px-2 py-1 text-xs font-medium text-gray-600 hover:text-gray-900">
                        <svg class="h-4 w-4" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>

                <a :href="'/active-basic-queues'"
                    class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    {{ $t("Standard Queue View") }}
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Active Calls -->
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $t("Active Calls") }}</span>
                    <span class="flex h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">{{ summary.total_active_calls ?? 0 }}</span>
                    <span class="text-xs font-medium text-gray-500">{{ $t("talking now") }}</span>
                </div>
                <div class="mt-2 text-xs text-blue-600 font-medium">
                    {{ $t("Across :count Queues", { count: summary.total_queues ?? 0 }) }}
                </div>
            </div>

            <!-- Waiting Callers -->
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $t("Waiting Callers") }}</span>
                    <span class="flex h-2.5 w-2.5 rounded-full" :class="(summary.total_waiting ?? 0) > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold" :class="(summary.total_waiting ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900'">
                        {{ summary.total_waiting ?? 0 }}
                    </span>
                    <span class="text-xs font-medium text-gray-500">{{ $t("in queue") }}</span>
                </div>
                <div class="mt-2 text-xs font-medium" :class="(summary.total_waiting ?? 0) > 3 ? 'text-red-600' : 'text-gray-500'">
                    {{ (summary.total_waiting ?? 0) > 3 ? $t("High Queue Pressure") : $t("Queue Load Normal") }}
                </div>
            </div>

            <!-- Longest Wait Time -->
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $t("Longest Wait") }}</span>
                    <span class="flex h-2.5 w-2.5 rounded-full" :class="(summary.longest_wait_seconds ?? 0) > 60 ? 'bg-red-500 animate-bounce' : 'bg-emerald-500'"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold" :class="(summary.longest_wait_seconds ?? 0) > 60 ? 'text-red-600' : 'text-gray-900'">
                        {{ summary.longest_wait_formatted || '00:00' }}
                    </span>
                    <span class="text-xs font-medium text-gray-500">{{ $t("max duration") }}</span>
                </div>
                <div class="mt-2 text-xs font-medium" :class="(summary.longest_wait_seconds ?? 0) > 20 ? 'text-amber-600' : 'text-emerald-600'">
                    {{ (summary.longest_wait_seconds ?? 0) > 20 ? $t("SLA Threshold Approached") : $t("Within SLA Target") }}
                </div>
            </div>

            <!-- Agent Distribution -->
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $t("Agent Workload") }}</span>
                    <span class="text-xs font-bold text-gray-900">{{ summary.agents?.total ?? 0 }} {{ $t("Total") }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs">
                    <span class="text-emerald-600 font-bold">{{ summary.agents?.available ?? 0 }} {{ $t("Ready") }}</span>
                    <span class="text-blue-600 font-bold">{{ summary.agents?.in_call ?? 0 }} {{ $t("In Call") }}</span>
                    <span class="text-amber-600 font-bold">{{ summary.agents?.on_break ?? 0 }} {{ $t("Break") }}</span>
                    <span class="text-gray-400 font-bold">{{ summary.agents?.logged_out ?? 0 }} {{ $t("Offline") }}</span>
                </div>
                <!-- Progress bar -->
                <div class="mt-3 flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div :style="{ width: agentPercent('available') + '%' }" class="bg-emerald-500" :title="$t('Ready')"></div>
                    <div :style="{ width: agentPercent('in_call') + '%' }" class="bg-blue-500" :title="$t('In Call')"></div>
                    <div :style="{ width: agentPercent('on_break') + '%' }" class="bg-amber-400" :title="$t('On Break')"></div>
                    <div :style="{ width: agentPercent('logged_out') + '%' }" class="bg-gray-300" :title="$t('Offline')"></div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200 bg-white px-4 rounded-t-xl shadow-sm ring-1 ring-gray-200 ring-b-0">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" @click="activeTab = 'queues'"
                    :class="[
                        activeTab === 'queues'
                            ? 'border-indigo-600 text-indigo-600 font-bold'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-medium'
                    ]"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    {{ $t("Live Queues & Interventions") }}
                    <span v-if="(summary.total_waiting ?? 0) > 0"
                        class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">
                        {{ summary.total_waiting }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'agents'"
                    :class="[
                        activeTab === 'agents'
                            ? 'border-indigo-600 text-indigo-600 font-bold'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-medium'
                    ]"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ $t("Agent State Management") }}
                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                        {{ agents.length }}
                    </span>
                </button>

                <button type="button" @click="fetchAuditLogs"
                    :class="[
                        activeTab === 'audit'
                            ? 'border-indigo-600 text-indigo-600 font-bold'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-medium'
                    ]"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $t("Supervisor Audit Trail") }}
                </button>
            </nav>
        </div>

        <!-- TAB 1: Live Queues & Calls with Live Control Interventions -->
        <div v-show="activeTab === 'queues'" class="space-y-4">
            <div v-if="queues.length === 0" class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200">
                <p class="text-sm text-gray-500">{{ $t("No active queues found in this domain.") }}</p>
            </div>

            <div v-for="q in queues" :key="q.queue_uuid"
                class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 transition-all hover:shadow-md">
                <!-- Queue Card Header -->
                <div class="flex flex-wrap items-center justify-between border-b border-gray-100 bg-gray-50/75 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 font-bold text-white text-xs">
                            {{ q.queue_extension }}
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">{{ q.queue_name }}</h2>
                            <p class="text-xs text-gray-500">{{ $t("Strategy:") }} {{ q.queue_strategy }} | {{ $t("SLA Target:") }} {{ q.sla_target_seconds }}s</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="text-gray-500">{{ $t("Waiting:") }}</span>
                            <span class="font-bold rounded px-1.5 py-0.5"
                                :class="q.waiting_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700'">
                                {{ q.waiting_count }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-gray-500">{{ $t("Active:") }}</span>
                            <span class="font-bold rounded bg-blue-100 px-1.5 py-0.5 text-blue-800">
                                {{ q.active_calls_count }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-gray-500">{{ $t("Longest:") }}</span>
                            <span class="font-bold rounded px-1.5 py-0.5"
                                :class="q.longest_wait_seconds > 20 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700'">
                                {{ q.longest_wait_formatted }}
                            </span>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="{
                                'bg-emerald-100 text-emerald-800': q.abandon_risk === 'LOW',
                                'bg-amber-100 text-amber-800': q.abandon_risk === 'MEDIUM',
                                'bg-orange-100 text-orange-800': q.abandon_risk === 'HIGH',
                                'bg-red-100 text-red-800 animate-pulse': q.abandon_risk === 'CRITICAL'
                            }">
                            {{ $t("Risk: ") }} {{ q.abandon_risk }}
                        </span>
                    </div>
                </div>

                <!-- Active Calls Table for Queue -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50/50 text-gray-500 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-2.5 text-left">{{ $t("Caller ID") }}</th>
                                <th class="px-3 py-2.5 text-left">{{ $t("State") }}</th>
                                <th class="px-3 py-2.5 text-left">{{ $t("Wait Duration") }}</th>
                                <th class="px-3 py-2.5 text-left">{{ $t("Serving Agent") }}</th>
                                <th class="px-4 py-2.5 text-right">{{ $t("Supervisor Live Controls") }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-if="!q.calls || q.calls.length === 0">
                                <td colspan="5" class="px-4 py-3 text-center text-gray-400 italic">
                                    {{ $t("No callers currently waiting or active in this queue.") }}
                                </td>
                            </tr>
                            <tr v-for="call in q.calls" :key="call.channel_uuid" class="hover:bg-gray-50/75 transition-colors">
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    <div>{{ call.caller_number }}</div>
                                    <div class="text-[11px] font-normal text-gray-500">{{ call.caller_name }}</div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-amber-100 text-amber-800': call.state === 'Waiting',
                                            'bg-blue-100 text-blue-800': call.state === 'Trying',
                                            'bg-emerald-100 text-emerald-800': call.state === 'Answered'
                                        }">
                                        {{ call.state }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 font-medium" :class="call.sla_breached ? 'text-red-600 font-bold' : 'text-gray-700'">
                                    {{ call.wait_formatted }}
                                    <span v-if="call.sla_breached" class="ml-1 text-[10px] text-red-500 font-semibold uppercase">
                                        {{ $t("SLA Exceeded") }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900">
                                    {{ call.serving_agent_name || $t("Unassigned") }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <!-- Action Buttons: Listen, Whisper, Barge, Drop -->
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="handleAction('listen', call, q)"
                                            class="rounded bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 ring-1 ring-inset ring-indigo-700/10"
                                            :title="$t('Secretly listen to call')">
                                            🎧 {{ $t("Listen") }}
                                        </button>
                                        <button type="button" @click="handleAction('whisper', call, q)"
                                            class="rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 ring-1 ring-inset ring-emerald-700/10"
                                            :title="$t('Coach agent (whisper)')">
                                            🗣️ {{ $t("Whisper") }}
                                        </button>
                                        <button type="button" @click="handleAction('barge', call, q)"
                                            class="rounded bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-700 hover:bg-purple-100 ring-1 ring-inset ring-purple-700/10"
                                            :title="$t('Join conversation (3-way)')">
                                            👥 {{ $t("Barge") }}
                                        </button>
                                        <button type="button" @click="promptTakeover(call, q)"
                                            class="rounded bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 ring-1 ring-inset ring-amber-700/10"
                                            :title="$t('Transfer call to another agent')">
                                            🔄 {{ $t("Transfer") }}
                                        </button>
                                        <button type="button" @click="handleAction('force_drop', call, q)"
                                            class="rounded bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 ring-1 ring-inset ring-red-700/10"
                                            :title="$t('Force disconnect call')">
                                            🛑 {{ $t("Drop") }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: Agent State Management & Lifecycle -->
        <div v-show="activeTab === 'agents'" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ $t("Live Agent Fleet & Status Controls") }}</h2>
                    <p class="text-xs text-gray-500">{{ $t("Control agent availability, track break reason codes, and force logouts.") }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ $t("Agent Name") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("Extension") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("Current Status") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("Telephony State") }}</th>
                            <th class="px-4 py-3 text-right">{{ $t("Change Status") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-if="agents.length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">
                                {{ $t("No agents configured in this domain.") }}
                            </td>
                        </tr>
                        <tr v-for="a in agents" :key="a.agent_uuid" class="hover:bg-gray-50/75">
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ a.name }}
                            </td>
                            <td class="px-3 py-3 text-gray-600 font-mono">
                                {{ a.extension || '-' }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800': a.effective_state === 'Available',
                                        'bg-blue-100 text-blue-800': a.effective_state === 'In Call',
                                        'bg-amber-100 text-amber-800': a.effective_state === 'On Break',
                                        'bg-gray-100 text-gray-600': a.effective_state === 'Logged Out'
                                    }">
                                    <span class="h-1.5 w-1.5 rounded-full"
                                        :class="{
                                            'bg-emerald-500': a.effective_state === 'Available',
                                            'bg-blue-500': a.effective_state === 'In Call',
                                            'bg-amber-500': a.effective_state === 'On Break',
                                            'bg-gray-400': a.effective_state === 'Logged Out'
                                        }"></span>
                                    {{ a.effective_state }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-gray-600">
                                {{ a.state }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button v-if="a.effective_state !== 'Available'" type="button"
                                        @click="setAgentStatus(a.agent_uuid, 'Available')"
                                        class="rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                        {{ $t("Make Ready") }}
                                    </button>

                                    <!-- Break Reason Picker -->
                                    <select @change="handleBreakSelect(a.agent_uuid, $event)"
                                        class="rounded border-0 py-1 pl-2 pr-6 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-300">
                                        <option value="">{{ $t("Put On Break...") }}</option>
                                        <option v-for="r in breakReasons" :key="r.break_reason_uuid" :value="r.code">
                                            {{ r.name }} ({{ r.max_minutes }}m)
                                        </option>
                                    </select>

                                    <button v-if="a.effective_state !== 'Logged Out'" type="button"
                                        @click="handleForceLogout(a)"
                                        class="rounded bg-gray-50 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 ring-1 ring-inset ring-gray-200">
                                        {{ $t("Logout") }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: Supervisor Audit Trail -->
        <div v-show="activeTab === 'audit'" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ $t("Immutable Supervisor Action History") }}</h2>
                    <p class="text-xs text-gray-500">{{ $t("Security audit log tracking listen, whisper, barge, takeover, and logout events.") }}</p>
                </div>
                <button type="button" @click="fetchAuditLogs" class="text-xs text-indigo-600 font-semibold hover:text-indigo-500">
                    {{ $t("Refresh Logs") }}
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ $t("Timestamp") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("Action") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("Supervisor Ext") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("Target Agent / Channel") }}</th>
                            <th class="px-3 py-3 text-left">{{ $t("IP Address") }}</th>
                            <th class="px-4 py-3 text-right">{{ $t("Status") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-if="auditLogs.length === 0">
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400 italic">
                                {{ $t("No audit records found.") }}
                            </td>
                        </tr>
                        <tr v-for="log in auditLogs" :key="log.audit_uuid" class="hover:bg-gray-50/75">
                            <td class="px-4 py-3 font-mono text-gray-500">
                                {{ formatDate(log.created_at) }}
                            </td>
                            <td class="px-3 py-3 font-bold">
                                <span class="rounded px-1.5 py-0.5 text-[11px]"
                                    :class="{
                                        'bg-indigo-100 text-indigo-800': log.action_type === 'LISTEN',
                                        'bg-emerald-100 text-emerald-800': log.action_type === 'WHISPER',
                                        'bg-purple-100 text-purple-800': log.action_type === 'BARGE',
                                        'bg-amber-100 text-amber-800': log.action_type === 'TAKEOVER',
                                        'bg-red-100 text-red-800': log.action_type === 'FORCE_LOGOUT' || log.action_type === 'FORCE_DROP'
                                    }">
                                    {{ log.action_type }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-gray-700 font-medium">
                                {{ log.supervisor_extension || '-' }}
                            </td>
                            <td class="px-3 py-3 text-gray-900 font-medium">
                                {{ log.target_agent_name || log.target_channel_uuid || '-' }}
                            </td>
                            <td class="px-3 py-3 text-gray-500 font-mono text-[11px]">
                                {{ log.ip_address || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold"
                                :class="log.status === 'SUCCESS' ? 'text-emerald-600' : 'text-red-600'">
                                {{ log.status }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Takeover / Transfer Modal -->
        <div v-if="transferModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
                <h3 class="text-base font-bold text-gray-900">{{ $t("Takeover / Transfer Call") }}</h3>
                <p class="mt-1 text-xs text-gray-500">{{ $t("Enter extension or number to transfer this caller to:") }}</p>
                <input type="text" v-model="transferDestination" :placeholder="$t('Destination Extension (e.g. 101)')"
                    class="mt-3 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600" />
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="transferModalOpen = false"
                        class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        {{ $t("Cancel") }}
                    </button>
                    <button type="button" @click="confirmTakeover"
                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ $t("Transfer Now") }}
                    </button>
                </div>
            </div>
        </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";

const props = defineProps({
    initialData: Object,
    breakReasons: Array,
    extensions: Array,
    routes: Object,
    permissions: Object,
});

const activeTab = ref("queues");
const autoRefresh = ref(true);
const loading = ref(false);
const timer = ref(null);

const summary = ref(props.initialData?.summary || {});
const queues = ref(props.initialData?.queues || []);
const agents = ref(props.initialData?.agents || []);
const breakReasons = ref(props.breakReasons || []);
const extensions = ref(props.extensions || []);
const auditLogs = ref([]);

const selectedSupervisorExt = ref(extensions.value.length > 0 ? extensions.value[0].extension : "");

// Transfer Modal
const transferModalOpen = ref(false);
const transferDestination = ref("");
const activeTargetCall = ref(null);
const activeTargetQueue = ref(null);

onMounted(() => {
    startAutoRefresh();
});

onUnmounted(() => {
    stopAutoRefresh();
});

function toggleAutoRefresh() {
    autoRefresh.value = !autoRefresh.value;
    if (autoRefresh.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
}

function startAutoRefresh() {
    stopAutoRefresh();
    timer.value = setInterval(() => {
        if (autoRefresh.value && !loading.value) {
            fetchLiveData();
        }
    }, 3000);
}

function stopAutoRefresh() {
    if (timer.value) {
        clearInterval(timer.value);
        timer.value = null;
    }
}

async function fetchLiveData() {
    loading.value = true;
    try {
        const res = await axios.get(props.routes.live_data);
        if (res.data?.success && res.data?.data) {
            summary.value = res.data.data.summary || {};
            queues.value = res.data.data.queues || [];
            agents.value = res.data.data.agents || [];
        }
    } catch (e) {
        console.error("Live wallboard poll error:", e);
    } finally {
        loading.value = false;
    }
}

async function handleAction(action, call, queue) {
    if (!selectedSupervisorExt.value && (action === 'listen' || action === 'whisper' || action === 'barge')) {
        alert("অনুগ্রহ করে উপরে 'My Extension' সিলেক্ট করুন যাতে ফ্রি-সুইচ আপনার ফোনে রিং পাঠাতে পারে।");
        return;
    }

    try {
        const payload = {
            action: action,
            channel_uuid: call.channel_uuid,
            supervisor_extension: selectedSupervisorExt.value,
            agent_uuid: call.serving_agent_uuid,
            agent_name: call.serving_agent_name,
            queue_uuid: queue.queue_uuid,
        };

        const res = await axios.post(props.routes.action, payload);
        if (res.data?.success) {
            alert(`অ্যাকশন সফল: ${action.toUpperCase()} কমান্ড এক্সিকিউট হয়েছে।`);
            fetchLiveData();
        } else {
            alert(`ত্রুটি: ${res.data?.error || 'অ্যাকশন ব্যর্থ হয়েছে।'}`);
        }
    } catch (e) {
        alert("সার্ভার ত্রুটি: অ্যাকশন সম্পন্ন করা সম্ভব হয়নি।");
    }
}

function promptTakeover(call, queue) {
    activeTargetCall.value = call;
    activeTargetQueue.value = queue;
    transferDestination.value = selectedSupervisorExt.value || "";
    transferModalOpen.value = true;
}

async function confirmTakeover() {
    if (!transferDestination.value) {
        alert("দয়া করে ডেস্টিনেশন এক্সটেনশন প্রদান করুন।");
        return;
    }

    transferModalOpen.value = false;
    await handleAction('takeover', {
        ...activeTargetCall.value,
        destination_extension: transferDestination.value
    }, activeTargetQueue.value);
}

async function setAgentStatus(agentUuid, status, breakReasonCode = null) {
    try {
        const res = await axios.post(props.routes.set_status, {
            agent_uuid: agentUuid,
            status: status,
            break_reason_code: breakReasonCode,
        });

        if (res.data?.success) {
            fetchLiveData();
        } else {
            alert(res.data?.error || "স্ট্যাটাস আপডেট ব্যর্থ হয়েছে।");
        }
    } catch (e) {
        alert("সার্ভার ত্রুটি: স্ট্যাটাস আপডেট করা সম্ভব হয়নি।");
    }
}

function handleBreakSelect(agentUuid, event) {
    const code = event.target.value;
    if (!code) return;
    setAgentStatus(agentUuid, 'On Break', code);
    event.target.value = "";
}

function handleForceLogout(agent) {
    if (confirm(`আপনি কি সত্যিই এজেন্ট ${agent.name} কে জোরপূর্বক Logout করতে চান?`)) {
        setAgentStatus(agent.agent_uuid, 'Logged Out');
    }
}

async function fetchAuditLogs() {
    activeTab.value = 'audit';
    try {
        const res = await axios.get(props.routes.audit_logs);
        if (res.data?.success && res.data?.logs?.data) {
            auditLogs.value = res.data.logs.data;
        }
    } catch (e) {
        console.error("Audit logs error:", e);
    }
}

function agentPercent(statusKey) {
    const total = summary.value.agents?.total || 0;
    if (total === 0) return 0;
    const count = summary.value.agents?.[statusKey] || 0;
    return Math.round((count / total) * 100);
}

function formatDate(isoStr) {
    if (!isoStr) return '-';
    const d = new Date(isoStr);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
</script>
