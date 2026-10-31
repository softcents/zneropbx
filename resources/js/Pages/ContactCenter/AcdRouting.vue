<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-emerald-400"></span>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <CpuChipIcon class="w-7 h-7 text-emerald-600" />
                {{ $t("Skills-Based ACD & Intelligent Routing Engine") }}
              </h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Configure Agent Skills matrices, FreeSWITCH call center tiers, VIP caller priority boost, SLA thresholds, and Smart Callback queue overflow.") }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="openNewSkillModal = true"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("Create New Skill") }}
            </button>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mt-6 border-b border-gray-100 flex gap-6 text-sm font-semibold">
          <button
            @click="activeTab = 'policies'"
            :class="activeTab === 'policies' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-2.5' : 'text-gray-500 hover:text-gray-700 pb-2.5'"
          >
            {{ $t("Queue ACD Policies & SLA") }}
          </button>
          <button
            @click="activeTab = 'matrix'"
            :class="activeTab === 'matrix' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-2.5' : 'text-gray-500 hover:text-gray-700 pb-2.5'"
          >
            {{ $t("Agent Skills Matrix") }}
          </button>
          <button
            @click="activeTab = 'callbacks'"
            :class="activeTab === 'callbacks' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-2.5' : 'text-gray-500 hover:text-gray-700 pb-2.5'"
          >
            {{ $t("Smart Callback Queue") }}
            <span v-if="callbacksList.length" class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800">
              {{ callbacksList.length }}
            </span>
          </button>
          <button
            @click="activeTab = 'skills'"
            :class="activeTab === 'skills' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-2.5' : 'text-gray-500 hover:text-gray-700 pb-2.5'"
          >
            {{ $t("Skills Directory") }}
          </button>
        </div>
      </div>

      <!-- Tab 1: Queue ACD Policies & SLA Intelligence -->
      <div v-if="activeTab === 'policies'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
              <h2 class="text-base font-bold text-gray-900">{{ $t("Select Target Queue to Configure") }}</h2>
              <p class="text-xs text-gray-400 mt-0.5">{{ $t("Select an ACD queue to manage its required skills and SLA overflow parameters.") }}</p>
            </div>
            <select
              v-model="selectedQueueUuid"
              @change="loadQueuePolicy"
              class="rounded-lg border-gray-300 text-xs font-medium focus:ring-emerald-500 focus:border-emerald-500 min-w-[220px]"
            >
              <option v-for="q in queues" :key="q.queue_uuid" :value="q.queue_uuid">
                {{ q.queue_name }} (Ext: {{ q.queue_extension }})
              </option>
            </select>
          </div>

          <div v-if="activePolicy" class="mt-6 space-y-6">
            <!-- SLA & Priority Parameters Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $t("Target SLA (Answer Time)") }}</label>
                <div class="flex items-center gap-2">
                  <input
                    v-model.number="activePolicy.sla_seconds"
                    type="number"
                    min="5"
                    max="180"
                    class="w-full rounded-md border-gray-300 text-xs font-semibold"
                  />
                  <span class="text-xs text-gray-500">{{ $t("sec") }}</span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">{{ $t("Target threshold for service level") }}</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $t("SLA Target Percentage") }}</label>
                <div class="flex items-center gap-2">
                  <input
                    v-model.number="activePolicy.sla_target_percentage"
                    type="number"
                    step="0.5"
                    min="10"
                    max="100"
                    class="w-full rounded-md border-gray-300 text-xs font-semibold"
                  />
                  <span class="text-xs text-gray-500">%</span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">{{ $t("e.g. 80.0% answered on time") }}</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $t("VIP Caller Boost") }}</label>
                <div class="flex items-center gap-2">
                  <input
                    v-model.number="activePolicy.vip_boost_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-full rounded-md border-gray-300 text-xs font-semibold text-indigo-700"
                  />
                  <span class="text-xs text-gray-500">{{ $t("pts") }}</span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">{{ $t("Priority score added for VIP numbers") }}</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $t("Max Allowed Wait Time") }}</label>
                <div class="flex items-center gap-2">
                  <input
                    v-model.number="activePolicy.max_wait_time_seconds"
                    type="number"
                    min="15"
                    max="1800"
                    class="w-full rounded-md border-gray-300 text-xs font-semibold text-rose-600"
                  />
                  <span class="text-xs text-gray-500">{{ $t("sec") }}</span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">{{ $t("Triggers overflow/alert after limit") }}</p>
              </div>
            </div>

            <!-- Overflow & Smart Callback Rules -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="border border-emerald-100 bg-emerald-50/40 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <PhoneArrowDownLeftIcon class="w-5 h-5 text-emerald-600" />
                    <span class="text-sm font-bold text-gray-900">{{ $t("Smart Callback Queue Overflow") }}</span>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="activePolicy.smart_callback_enabled" class="sr-only peer" />
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                  </label>
                </div>
                <p class="text-xs text-gray-600">
                  {{ $t("Offers customers a virtual callback when wait time exceeds threshold, preserving their spot in queue without waiting on hold.") }}
                </p>
                <div class="flex items-center gap-3 pt-1">
                  <label class="text-xs font-medium text-gray-700">{{ $t("Offer Callback After:") }}</label>
                  <input
                    v-model.number="activePolicy.smart_callback_threshold_seconds"
                    type="number"
                    class="w-24 rounded-md border-gray-300 text-xs font-semibold"
                  />
                  <span class="text-xs text-gray-500">{{ $t("seconds") }}</span>
                </div>
              </div>

              <div class="border border-indigo-100 bg-indigo-50/40 rounded-xl p-4 space-y-3">
                <div class="flex items-center gap-2">
                  <ArrowPathRoundedSquareIcon class="w-5 h-5 text-indigo-600" />
                  <span class="text-sm font-bold text-gray-900">{{ $t("Secondary Queue Overflow") }}</span>
                </div>
                <p class="text-xs text-gray-600">
                  {{ $t("Automatically divert overflow callers to a backup queue when caller depth exceeds capacity.") }}
                </p>
                <div class="grid grid-cols-2 gap-3 pt-1">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ $t("Overflow Queue") }}</label>
                    <select
                      v-model="activePolicy.overflow_queue_uuid"
                      class="w-full rounded-md border-gray-300 text-xs font-medium"
                    >
                      <option value="">{{ $t("-- None (Keep in Queue) --") }}</option>
                      <option
                        v-for="oq in queues.filter(q => q.queue_uuid !== selectedQueueUuid)"
                        :key="oq.queue_uuid"
                        :value="oq.queue_uuid"
                      >
                        {{ oq.queue_name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ $t("Max Depth (Callers)") }}</label>
                    <input
                      v-model.number="activePolicy.overflow_callers_count"
                      type="number"
                      class="w-full rounded-md border-gray-300 text-xs font-semibold"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Required Skills for Queue -->
            <div class="border border-gray-200 rounded-xl p-4 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-bold text-gray-900">{{ $t("Skills-Based Agent Matching Criteria") }}</h3>
                  <p class="text-xs text-gray-500">{{ $t("FreeSWITCH tier priorities will auto-adjust so callers match agents possessing these required proficiencies.") }}</p>
                </div>
                <button
                  type="button"
                  @click="addSkillRequirement"
                  class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700"
                >
                  <PlusIcon class="w-4 h-4" />
                  {{ $t("Add Required Skill") }}
                </button>
              </div>

              <div v-if="activePolicy.required_skills.length === 0" class="py-4 text-center text-xs text-gray-400">
                {{ $t("No skills required. All queue agents will receive equal tier priority.") }}
              </div>

              <div v-else class="space-y-2">
                <div
                  v-for="(req, index) in activePolicy.required_skills"
                  :key="index"
                  class="flex flex-wrap items-center justify-between gap-3 bg-gray-50 p-2.5 rounded-lg border border-gray-200 text-xs"
                >
                  <div class="flex items-center gap-3">
                    <select
                      v-model="req.skill_uuid"
                      class="rounded-md border-gray-300 text-xs font-semibold"
                    >
                      <option v-for="sk in skills" :key="sk.skill_uuid" :value="sk.skill_uuid">
                        {{ sk.name }} ({{ sk.category }})
                      </option>
                    </select>

                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                      <input type="checkbox" v-model="req.is_mandatory" class="rounded border-gray-300 text-emerald-600" />
                      <span class="font-medium text-gray-700">{{ $t("Mandatory") }}</span>
                    </label>
                  </div>

                  <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                      <span class="text-gray-500">{{ $t("Min Level:") }}</span>
                      <input
                        type="range"
                        min="1"
                        max="10"
                        v-model.number="req.min_proficiency"
                        class="w-24 accent-emerald-600 cursor-pointer"
                      />
                      <span class="font-bold text-emerald-700 w-4">{{ req.min_proficiency }}</span>
                    </div>

                    <button
                      type="button"
                      @click="removeSkillRequirement(index)"
                      class="text-rose-500 hover:text-rose-700 font-bold ml-2"
                    >
                      ✕
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Save Policy Action -->
            <div class="flex justify-end pt-2">
              <button
                type="button"
                :disabled="isSavingPolicy"
                @click="saveCurrentQueuePolicy"
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition disabled:opacity-50"
              >
                {{ isSavingPolicy ? $t("Syncing FreeSWITCH Tiers...") : $t("Save & Apply Routing Policy") }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 2: Agent Skills Matrix -->
      <div v-if="activeTab === 'matrix'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-bold text-gray-900">{{ $t("Agent Skills & Proficiency Matrix") }}</h2>
            <p class="text-xs text-gray-500">{{ $t("Proficiency scale from 1 (Novice) to 10 (Master/Expert).") }}</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
              <tr>
                <th class="p-4">{{ $t("Agent") }}</th>
                <th class="p-4">{{ $t("Agent Status") }}</th>
                <th class="p-4">{{ $t("Assigned Skills & Proficiencies") }}</th>
                <th class="p-4 text-right">{{ $t("Action") }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="ag in agentsList" :key="ag.agent_uuid" class="hover:bg-gray-50/60 transition">
                <td class="p-4 font-bold text-gray-900">
                  {{ ag.name }}
                  <span class="text-xs font-normal text-gray-400 block">{{ $t("ID:") }} {{ ag.agent_id }}</span>
                </td>
                <td class="p-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="ag.status === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'"
                  >
                    {{ ag.status || 'Offline' }}
                  </span>
                </td>
                <td class="p-4">
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="sk in ag.skills"
                      :key="sk.skill_uuid"
                      class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 border border-slate-200 text-slate-800 text-xs"
                    >
                      <span class="font-medium">{{ sk.name }}</span>
                      <span class="bg-emerald-600 text-white text-[10px] font-bold px-1 rounded">{{ sk.proficiency_level }}</span>
                    </span>
                    <span v-if="!ag.skills.length" class="text-gray-400 italic">{{ $t("No skills assigned") }}</span>
                  </div>
                </td>
                <td class="p-4 text-right">
                  <button
                    type="button"
                    @click="openEditAgentSkills(ag)"
                    class="px-3 py-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded text-xs shadow-sm transition"
                  >
                    {{ $t("Edit Skills") }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 3: Smart Callback Queue Manager -->
      <div v-if="activeTab === 'callbacks'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-bold text-gray-900">{{ $t("Active Smart Callback Queue") }}</h2>
            <p class="text-xs text-gray-500">{{ $t("Customers awaiting virtual callback. Processed automatically by background engine.") }}</p>
          </div>
          <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
            {{ callbacksList.filter(c => c.status === 'pending').length }} {{ $t("pending calls") }}
          </span>
        </div>

        <div v-if="callbacksList.length === 0" class="p-10 text-center text-xs text-gray-400">
          {{ $t("No pending callbacks. All caller queues are running on schedule.") }}
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
              <tr>
                <th class="p-4">{{ $t("Caller Number") }}</th>
                <th class="p-4">{{ $t("Queue") }}</th>
                <th class="p-4">{{ $t("Status") }}</th>
                <th class="p-4">{{ $t("Attempts") }}</th>
                <th class="p-4">{{ $t("Scheduled At") }}</th>
                <th class="p-4 text-right">{{ $t("Action") }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="cb in callbacksList" :key="cb.callback_uuid" class="hover:bg-gray-50/60 transition">
                <td class="p-4 font-bold text-gray-900 font-mono">{{ cb.caller_number }}</td>
                <td class="p-4">{{ cb.queue?.queue_name || '-' }}</td>
                <td class="p-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="{
                      'bg-amber-100 text-amber-800': cb.status === 'pending',
                      'bg-emerald-100 text-emerald-800': cb.status === 'completed',
                      'bg-rose-100 text-rose-800': cb.status === 'failed' || cb.status === 'cancelled',
                    }"
                  >
                    {{ cb.status }}
                  </span>
                </td>
                <td class="p-4">{{ cb.attempts }}</td>
                <td class="p-4 font-mono text-[11px]">{{ formatDate(cb.scheduled_at) }}</td>
                <td class="p-4 text-right space-x-2">
                  <button
                    v-if="cb.status === 'pending'"
                    type="button"
                    @click="triggerCallbackAction(cb, 'trigger')"
                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-semibold text-[11px] shadow-sm transition"
                  >
                    {{ $t("Call Now") }}
                  </button>
                  <button
                    v-if="cb.status === 'pending'"
                    type="button"
                    @click="triggerCallbackAction(cb, 'cancel')"
                    class="px-2.5 py-1 bg-white border border-gray-300 hover:bg-gray-50 text-rose-600 rounded font-semibold text-[11px] shadow-sm transition"
                  >
                    {{ $t("Cancel") }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 4: Skills Directory -->
      <div v-if="activeTab === 'skills'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-bold text-gray-900">{{ $t("Skills Directory") }}</h2>
            <p class="text-xs text-gray-500">{{ $t("Enterprise skills, technical tiers, language proficiencies.") }}</p>
          </div>
          <button
            type="button"
            @click="openNewSkillModal = true"
            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition"
          >
            {{ $t("Add Skill") }}
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
          <div
            v-for="sk in skillsList"
            :key="sk.skill_uuid"
            class="p-4 rounded-xl border border-gray-200 hover:border-emerald-300 hover:shadow-sm transition bg-white space-y-2"
          >
            <div class="flex items-center justify-between">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700">
                {{ sk.category }}
              </span>
              <button @click="deleteSkill(sk)" class="text-gray-400 hover:text-rose-500 text-xs">✕</button>
            </div>
            <h3 class="text-sm font-bold text-gray-900">{{ sk.name }}</h3>
            <p class="text-xs text-gray-500 line-clamp-2">{{ sk.description || $t("No description provided.") }}</p>
            <div class="pt-2 border-t border-gray-100 flex justify-between text-xs text-gray-400">
              <span>{{ $t("Code:") }} <strong class="text-gray-700 font-mono">{{ sk.code }}</strong></span>
              <span>{{ sk.agent_skills_count || 0 }} {{ $t("Agents") }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal: Edit Agent Skills -->
      <div v-if="editingAgent" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">{{ $t("Edit Skills for Agent") }}: {{ editingAgent.name }}</h3>
            <button @click="editingAgent = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>

          <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
            <div
              v-for="sk in skillsList"
              :key="sk.skill_uuid"
              class="flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-gray-50 text-xs"
            >
              <div class="flex items-center gap-2">
                <input
                  type="checkbox"
                  :checked="hasAgentSkill(sk.skill_uuid)"
                  @change="toggleAgentSkill(sk.skill_uuid)"
                  class="rounded border-gray-300 text-emerald-600"
                />
                <div>
                  <div class="font-bold text-gray-800">{{ sk.name }}</div>
                  <div class="text-[10px] text-gray-400">{{ sk.category }}</div>
                </div>
              </div>

              <div v-if="hasAgentSkill(sk.skill_uuid)" class="flex items-center gap-2">
                <span class="text-gray-500">{{ $t("Level:") }}</span>
                <input
                  type="range"
                  min="1"
                  max="10"
                  :value="getAgentProficiency(sk.skill_uuid)"
                  @input="setAgentProficiency(sk.skill_uuid, $event.target.value)"
                  class="w-24 accent-emerald-600 cursor-pointer"
                />
                <span class="font-bold text-emerald-700 w-4">{{ getAgentProficiency(sk.skill_uuid) }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="editingAgent = null"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 text-xs"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="button"
              @click="saveAgentSkills"
              class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 text-xs"
            >
              {{ $t("Save Agent Skills") }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal: New Skill -->
      <div v-if="openNewSkillModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900">{{ $t("Create Enterprise Skill") }}</h3>
            <button @click="openNewSkillModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>

          <form @submit.prevent="submitCreateSkill" class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">{{ $t("Skill Name") }} *</label>
              <input v-model="newSkill.name" required type="text" placeholder="e.g. VIP Concierge Support" class="w-full rounded-lg border-gray-300 text-xs" />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Code / Short ID") }} *</label>
                <input v-model="newSkill.code" required type="text" placeholder="VIP_CONCIERGE" class="w-full rounded-lg border-gray-300 text-xs font-mono uppercase" />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 mb-1">{{ $t("Category") }} *</label>
                <select v-model="newSkill.category" class="w-full rounded-lg border-gray-300 text-xs">
                  <option value="Language">{{ $t("Language") }}</option>
                  <option value="Technical">{{ $t("Technical") }}</option>
                  <option value="Department">{{ $t("Department") }}</option>
                  <option value="Tier">{{ $t("Tier") }}</option>
                  <option value="General">{{ $t("General") }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 mb-1">{{ $t("Description") }}</label>
              <textarea v-model="newSkill.description" rows="3" placeholder="Brief summary of required competencies" class="w-full rounded-lg border-gray-300 text-xs"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
              <button type="button" @click="openNewSkillModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50">
                {{ $t("Cancel") }}
              </button>
              <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700">
                {{ $t("Create Skill") }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  CpuChipIcon,
  PlusIcon,
  PhoneArrowDownLeftIcon,
  ArrowPathRoundedSquareIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  queues: {
    type: Array,
    default: () => [],
  },
  skills: {
    type: Array,
    default: () => [],
  },
  agents: {
    type: Array,
    default: () => [],
  },
  callbacks: {
    type: Array,
    default: () => [],
  },
});

const activeTab = ref("policies");
const selectedQueueUuid = ref(props.queues[0]?.queue_uuid || "");
const activePolicy = ref(props.queues[0] ? JSON.parse(JSON.stringify(props.queues[0])) : null);
const skillsList = ref([...props.skills]);
const agentsList = ref([...props.agents]);
const callbacksList = ref([...props.callbacks]);

const isSavingPolicy = ref(false);
const openNewSkillModal = ref(false);
const editingAgent = ref(null);
const currentAgentSkills = ref([]);

const newSkill = ref({
  name: "",
  code: "",
  category: "Technical",
  description: "",
});

function loadQueuePolicy() {
  const found = props.queues.find((q) => q.queue_uuid === selectedQueueUuid.value);
  if (found) {
    activePolicy.value = JSON.parse(JSON.stringify(found));
  }
}

function addSkillRequirement() {
  if (!activePolicy.value) return;
  const firstSkill = skillsList.value[0]?.skill_uuid || "";
  activePolicy.value.required_skills.push({
    skill_uuid: firstSkill,
    min_proficiency: 5,
    is_mandatory: true,
  });
}

function removeSkillRequirement(index) {
  activePolicy.value.required_skills.splice(index, 1);
}

async function saveCurrentQueuePolicy() {
  if (!activePolicy.value) return;
  isSavingPolicy.value = true;
  try {
    const res = await axios.post("/api/contact-center/routing/queue-policy", activePolicy.value);
    if (res.data?.success) {
      alert("Queue routing policy and FreeSWITCH tiers updated successfully!");
    }
  } catch (err) {
    alert("Error saving policy: " + (err.response?.data?.message || err.message));
  } finally {
    isSavingPolicy.value = false;
  }
}

// Agent Skills
function openEditAgentSkills(ag) {
  editingAgent.value = ag;
  currentAgentSkills.value = ag.skills.map((s) => ({
    skill_uuid: s.skill_uuid,
    proficiency_level: s.proficiency_level,
  }));
}

function hasAgentSkill(skillUuid) {
  return currentAgentSkills.value.some((s) => s.skill_uuid === skillUuid);
}

function getAgentProficiency(skillUuid) {
  const found = currentAgentSkills.value.find((s) => s.skill_uuid === skillUuid);
  return found ? found.proficiency_level : 5;
}

function toggleAgentSkill(skillUuid) {
  const idx = currentAgentSkills.value.findIndex((s) => s.skill_uuid === skillUuid);
  if (idx >= 0) {
    currentAgentSkills.value.splice(idx, 1);
  } else {
    currentAgentSkills.value.push({ skill_uuid: skillUuid, proficiency_level: 5 });
  }
}

function setAgentProficiency(skillUuid, val) {
  const found = currentAgentSkills.value.find((s) => s.skill_uuid === skillUuid);
  if (found) {
    found.proficiency_level = parseInt(val, 10);
  }
}

async function saveAgentSkills() {
  if (!editingAgent.value) return;
  try {
    const res = await axios.post("/api/contact-center/routing/agent-skills", {
      agent_uuid: editingAgent.value.agent_uuid,
      skills: currentAgentSkills.value,
    });
    if (res.data?.success) {
      // update local
      editingAgent.value.skills = currentAgentSkills.value.map((s) => {
        const fullSkill = skillsList.value.find((sk) => sk.skill_uuid === s.skill_uuid);
        return {
          skill_uuid: s.skill_uuid,
          name: fullSkill?.name || "Skill",
          category: fullSkill?.category || "General",
          proficiency_level: s.proficiency_level,
        };
      });
      editingAgent.value = null;
    }
  } catch (err) {
    alert("Failed to update agent skills.");
  }
}

// Skills Management
async function submitCreateSkill() {
  try {
    const res = await axios.post("/api/contact-center/routing/skills", newSkill.value);
    if (res.data?.success) {
      skillsList.value.push(res.data.skill);
      openNewSkillModal.value = false;
      newSkill.value = { name: "", code: "", category: "Technical", description: "" };
    }
  } catch (err) {
    alert("Error creating skill.");
  }
}

async function deleteSkill(sk) {
  if (!confirm(`Delete skill "${sk.name}"?`)) return;
  try {
    const res = await axios.delete(`/api/contact-center/routing/skills/${sk.skill_uuid}`);
    if (res.data?.success) {
      skillsList.value = skillsList.value.filter((s) => s.skill_uuid !== sk.skill_uuid);
    }
  } catch (err) {
    alert("Could not delete skill.");
  }
}

// Callback actions
async function triggerCallbackAction(cb, action) {
  try {
    const res = await axios.post(`/api/contact-center/routing/callbacks/${cb.callback_uuid}/action`, { action });
    if (res.data?.success) {
      if (action === 'trigger') cb.status = 'dialing';
      if (action === 'cancel') cb.status = 'cancelled';
      alert(res.data.message);
    }
  } catch (err) {
    alert("Could not process callback action.");
  }
}

function formatDate(isoStr) {
  if (!isoStr) return "-";
  return new Date(isoStr).toLocaleString();
}
</script>
