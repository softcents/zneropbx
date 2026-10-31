<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-indigo-500"></span>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <UserGroupIcon class="w-7 h-7 text-indigo-600" />
                {{ $t("Customer 360 & Ticket Workspace Hub") }}
              </h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Unified customer intelligence, VIP tier priority boost, ticket lifecycle management, telecom call history, and softphone screen-pop.") }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="openCreateCustomerModal = true"
              class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition"
            >
              <UserPlusIcon class="w-4 h-4" />
              {{ $t("New Customer") }}
            </button>
            <button
              v-if="currentCustomer"
              @click="openCreateTicketModal = true"
              class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition"
            >
              <TicketIcon class="w-4 h-4 text-indigo-600" />
              {{ $t("Create Ticket") }}
            </button>
          </div>
        </div>
      </div>

      <!-- Master-Detail 2-Column Workspace -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Customer Directory (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[780px]">
          <!-- Search & Filters Header -->
          <div class="p-4 border-b border-gray-200 space-y-3 bg-gray-50/50">
            <div class="relative">
              <MagnifyingGlassIcon class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" />
              <input
                v-model="searchQuery"
                @input="handleSearch"
                type="text"
                :placeholder="$t('Search name, phone, email, account...')"
                class="w-full pl-9 pr-3 py-1.5 rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>

            <!-- Tier Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px] font-semibold">
              <button
                v-for="t in tierFilters"
                :key="t.key"
                @click="filterTier(t.key)"
                :class="[
                  'px-2.5 py-1 rounded-full transition whitespace-nowrap',
                  selectedTier === t.key
                    ? 'bg-indigo-600 text-white font-bold'
                    : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100',
                ]"
              >
                {{ t.label }}
              </button>
            </div>
          </div>

          <!-- Customer Cards List -->
          <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
            <div
              v-if="customersList.length === 0"
              class="p-8 text-center text-xs text-gray-400"
            >
              {{ $t("No customers found matching filter.") }}
            </div>

            <div
              v-for="c in customersList"
              :key="c.customer_uuid"
              @click="selectCustomer(c)"
              :class="[
                'p-3.5 cursor-pointer transition flex items-start gap-3',
                currentCustomer?.customer_uuid === c.customer_uuid
                  ? 'bg-indigo-50/70 border-l-4 border-indigo-600'
                  : 'hover:bg-gray-50',
              ]"
            >
              <!-- Avatar with Tier Color -->
              <div
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 text-white shadow-sm',
                  c.tier === 'vip' ? 'bg-gradient-to-tr from-purple-600 to-indigo-600' :
                  c.tier === 'gold' ? 'bg-gradient-to-tr from-amber-500 to-yellow-600' :
                  c.tier === 'silver' ? 'bg-gradient-to-tr from-slate-500 to-gray-600' : 'bg-gray-400'
                ]"
              >
                {{ getInitials(c.full_name) }}
              </div>

              <!-- Customer Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                  <h3 class="text-xs font-bold text-gray-900 truncate">{{ c.full_name }}</h3>
                  <!-- Tier Badge -->
                  <span
                    :class="[
                      'px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider',
                      c.tier === 'vip' ? 'bg-purple-100 text-purple-800' :
                      c.tier === 'gold' ? 'bg-amber-100 text-amber-800' :
                      c.tier === 'silver' ? 'bg-slate-100 text-slate-800' : 'bg-gray-100 text-gray-600'
                    ]"
                  >
                    {{ c.tier }}
                  </span>
                </div>

                <div class="text-[11px] text-gray-500 truncate">{{ c.company || 'Private Client' }}</div>
                <div class="text-[11px] text-gray-600 font-mono mt-0.5">{{ c.phone }}</div>

                <div class="flex items-center justify-between mt-2 text-[10px]">
                  <span
                    v-if="c.tickets_count > 0"
                    class="inline-flex items-center gap-1 font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded"
                  >
                    <TicketIcon class="w-3 h-3" />
                    {{ c.tickets_count }} Open
                  </span>
                  <span v-else class="text-gray-400">No open cases</span>

                  <span class="text-gray-400 flex items-center gap-1">
                    <span v-if="c.sentiment === 'positive'">😊 Positive</span>
                    <span v-else-if="c.sentiment === 'negative'">😟 High Risk</span>
                    <span v-else>😐 Neutral</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Customer 360 Workspace (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
          <div v-if="!currentCustomer" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-400 text-sm">
            {{ $t("Select a customer from the left directory to view full profile, tickets, and call history.") }}
          </div>

          <div v-else class="space-y-6">
            <!-- Customer Hero Banner -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 relative overflow-hidden">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start gap-4">
                  <div
                    :class="[
                      'w-14 h-14 rounded-2xl flex items-center justify-center font-black text-lg text-white shadow-md shrink-0',
                      currentCustomer.tier === 'vip' ? 'bg-gradient-to-tr from-purple-600 to-indigo-600' :
                      currentCustomer.tier === 'gold' ? 'bg-gradient-to-tr from-amber-500 to-yellow-600' :
                      currentCustomer.tier === 'silver' ? 'bg-gradient-to-tr from-slate-500 to-gray-600' : 'bg-gray-500'
                    ]"
                  >
                    {{ getInitials(currentCustomer.full_name) }}
                  </div>

                  <div>
                    <div class="flex items-center gap-2">
                      <h2 class="text-xl font-bold text-gray-900">{{ currentCustomer.full_name }}</h2>
                      <span
                        :class="[
                          'px-2 py-0.5 rounded-full text-xs font-black uppercase tracking-wider',
                          currentCustomer.tier === 'vip' ? 'bg-purple-100 text-purple-800' :
                          currentCustomer.tier === 'gold' ? 'bg-amber-100 text-amber-800' :
                          currentCustomer.tier === 'silver' ? 'bg-slate-100 text-slate-800' : 'bg-gray-100 text-gray-700'
                        ]"
                      >
                        {{ currentCustomer.tier }} Tier
                      </span>
                      <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        {{ currentCustomer.status }}
                      </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 mt-1">
                      <span v-if="currentCustomer.company" class="font-semibold text-gray-700">{{ currentCustomer.company }}</span>
                      <span>Account: <span class="font-mono text-gray-800 font-semibold">{{ currentCustomer.account_number || '-' }}</span></span>
                      <span>Email: <span class="text-indigo-600">{{ currentCustomer.email || '-' }}</span></span>
                    </div>

                    <div class="flex items-center gap-2 mt-2">
                      <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded-md">
                        <PhoneIcon class="w-3.5 h-3.5 text-gray-500" />
                        {{ currentCustomer.phone }}
                      </span>
                      <a
                        :href="`tel:${currentCustomer.phone}`"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md transition"
                      >
                        {{ $t("Click-to-Call") }}
                      </a>
                    </div>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <button
                    @click="openEditCustomerModal = true"
                    class="inline-flex items-center gap-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition"
                  >
                    <PencilSquareIcon class="w-4 h-4 text-gray-500" />
                    {{ $t("Edit Profile") }}
                  </button>
                  <button
                    @click="openCreateTicketModal = true"
                    class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition"
                  >
                    <PlusIcon class="w-4 h-4" />
                    {{ $t("New Ticket") }}
                  </button>
                </div>
              </div>

              <!-- Customer Stat Tiles -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-4 border-t border-gray-100 text-xs">
                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                  <div class="text-gray-500 text-[11px] font-medium">{{ $t("Total Spend") }}</div>
                  <div class="text-base font-bold text-gray-900">${{ Number(currentCustomer.total_spend || 0).toLocaleString() }}</div>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                  <div class="text-gray-500 text-[11px] font-medium">{{ $t("Total Handled Calls") }}</div>
                  <div class="text-base font-bold text-gray-900">{{ currentCustomer.total_calls_count }} calls</div>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                  <div class="text-gray-500 text-[11px] font-medium">{{ $t("Customer Sentiment") }}</div>
                  <div class="text-base font-bold capitalize flex items-center gap-1"
                       :class="currentCustomer.sentiment === 'positive' ? 'text-emerald-600' : currentCustomer.sentiment === 'negative' ? 'text-rose-600' : 'text-gray-700'">
                    {{ currentCustomer.sentiment }}
                  </div>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                  <div class="text-gray-500 text-[11px] font-medium">{{ $t("Open Tickets") }}</div>
                  <div class="text-base font-bold text-rose-600">{{ activeTickets.length }} active</div>
                </div>
              </div>
            </div>

            <!-- Workspace Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="border-b border-gray-200 px-5 pt-3">
                <div class="flex space-x-6 text-xs font-bold">
                  <button
                    @click="activeTab = 'tickets'"
                    :class="[
                      'pb-3 border-b-2 transition flex items-center gap-1.5',
                      activeTab === 'tickets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'
                    ]"
                  >
                    <TicketIcon class="w-4 h-4" />
                    {{ $t("Support Tickets & Cases") }}
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-700">
                      {{ ticketsList.length }}
                    </span>
                  </button>

                  <button
                    @click="activeTab = 'calls'"
                    :class="[
                      'pb-3 border-b-2 transition flex items-center gap-1.5',
                      activeTab === 'calls' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'
                    ]"
                  >
                    <PhoneArrowDownLeftIcon class="w-4 h-4" />
                    {{ $t("Telephony Call History (CDR)") }}
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-700">
                      {{ recentCallsList.length }}
                    </span>
                  </button>

                  <button
                    @click="activeTab = 'timeline'"
                    :class="[
                      'pb-3 border-b-2 transition flex items-center gap-1.5',
                      activeTab === 'timeline' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'
                    ]"
                  >
                    <ClockIcon class="w-4 h-4" />
                    {{ $t("Omnichannel Timeline") }}
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-700">
                      {{ interactionsList.length }}
                    </span>
                  </button>

                  <button
                    @click="activeTab = 'notes'"
                    :class="[
                      'pb-3 border-b-2 transition flex items-center gap-1.5',
                      activeTab === 'notes' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'
                    ]"
                  >
                    <DocumentTextIcon class="w-4 h-4" />
                    {{ $t("Notes & Preferences") }}
                  </button>
                </div>
              </div>

              <!-- Tab 1: Tickets -->
              <div v-if="activeTab === 'tickets'" class="p-5 space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t("Active & Resolved Cases") }}</h3>
                  <button
                    @click="openCreateTicketModal = true"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1"
                  >
                    <PlusIcon class="w-3.5 h-3.5" />
                    {{ $t("Create Ticket") }}
                  </button>
                </div>

                <div v-if="ticketsList.length === 0" class="py-8 text-center text-xs text-gray-400">
                  {{ $t("No support tickets recorded for this customer.") }}
                </div>

                <div v-else class="space-y-3">
                  <div
                    v-for="t in ticketsList"
                    :key="t.ticket_uuid"
                    class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-gray-50 transition space-y-3"
                  >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-gray-900 bg-white px-2 py-0.5 rounded border border-gray-200">
                          {{ t.ticket_number }}
                        </span>
                        <h4 class="text-xs font-bold text-gray-900">{{ t.subject }}</h4>
                        <!-- Priority -->
                        <span
                          :class="[
                            'px-2 py-0.5 rounded text-[10px] font-black uppercase',
                            t.priority === 'urgent' ? 'bg-rose-100 text-rose-800 animate-pulse' :
                            t.priority === 'high' ? 'bg-amber-100 text-amber-800' :
                            t.priority === 'medium' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'
                          ]"
                        >
                          {{ t.priority }}
                        </span>
                        <!-- Category -->
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700">
                          {{ t.category }}
                        </span>
                      </div>

                      <!-- Status Dropdown for Live Update -->
                      <div class="flex items-center gap-2">
                        <select
                          v-model="t.status"
                          @change="updateTicketStatus(t)"
                          class="rounded text-[11px] py-1 border-gray-300 font-bold focus:border-indigo-500 focus:ring-indigo-500"
                          :class="[
                            t.status === 'resolved' || t.status === 'closed' ? 'text-emerald-700 bg-emerald-50' :
                            t.status === 'in_progress' ? 'text-indigo-700 bg-indigo-50' : 'text-amber-700 bg-amber-50'
                          ]"
                        >
                          <option value="open">Open</option>
                          <option value="in_progress">In Progress</option>
                          <option value="pending_customer">Pending Customer</option>
                          <option value="resolved">Resolved</option>
                          <option value="closed">Closed</option>
                        </select>
                      </div>
                    </div>

                    <p v-if="t.description" class="text-xs text-gray-600">{{ t.description }}</p>

                    <div class="flex flex-wrap items-center justify-between gap-3 text-[11px] text-gray-500 pt-2 border-t border-gray-200/60">
                      <div>
                        Assigned Agent:
                        <span class="font-semibold text-gray-800">
                          {{ t.assigned_agent?.agent_name || 'Unassigned' }}
                        </span>
                      </div>
                      <div>
                        SLA Due:
                        <span class="font-semibold text-gray-800">
                          {{ formatDate(t.sla_due_at) }}
                        </span>
                      </div>
                      <div>
                        Created:
                        <span class="text-gray-600">{{ formatDate(t.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tab 2: Telecom CDR Calls -->
              <div v-if="activeTab === 'calls'" class="p-5 space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t("FreeSWITCH Telephony Interactions") }}</h3>
                  <span class="text-[11px] text-gray-500">Filtered by phone: {{ currentCustomer.phone }}</span>
                </div>

                <div v-if="recentCallsList.length === 0" class="py-8 text-center text-xs text-gray-400">
                  {{ $t("No phone records found in CDR for this caller.") }}
                </div>

                <div v-else class="overflow-x-auto">
                  <table class="w-full text-left border-collapse text-xs">
                    <thead>
                      <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase text-[10px]">
                        <th class="p-3">{{ $t("Date & Time") }}</th>
                        <th class="p-3">{{ $t("Direction") }}</th>
                        <th class="p-3">{{ $t("Caller ID") }}</th>
                        <th class="p-3">{{ $t("Destination") }}</th>
                        <th class="p-3">{{ $t("Duration") }}</th>
                        <th class="p-3">{{ $t("Billable Talk") }}</th>
                        <th class="p-3">{{ $t("Call Outcome") }}</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 font-medium text-gray-700">
                      <tr v-for="c in recentCallsList" :key="c.call_uuid" class="hover:bg-gray-50">
                        <td class="p-3 font-semibold text-gray-900">{{ c.start_stamp }}</td>
                        <td class="p-3">
                          <span
                            :class="[
                              'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                              c.direction === 'inbound' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'
                            ]"
                          >
                            {{ c.direction }}
                          </span>
                        </td>
                        <td class="p-3 font-mono">{{ c.caller_id_number }}</td>
                        <td class="p-3 font-mono">{{ c.destination_number }}</td>
                        <td class="p-3">{{ c.duration_formatted || '-' }}</td>
                        <td class="p-3 font-bold text-gray-900">{{ c.billsec_formatted || '00:00' }}</td>
                        <td class="p-3">
                          <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-800">
                            {{ c.cc_result || c.disposition }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Tab 3: Timeline & Interaction Logger -->
              <div v-if="activeTab === 'timeline'" class="p-5 space-y-6">
                <!-- Quick Interaction Logger -->
                <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 space-y-3">
                  <h4 class="text-xs font-bold text-indigo-900 flex items-center gap-1.5">
                    <ChatBubbleLeftRightIcon class="w-4 h-4 text-indigo-600" />
                    {{ $t("Log Interaction / Agent Note") }}
                  </h4>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                      <select v-model="newInteraction.channel" class="w-full rounded text-xs border-gray-300 py-1.5 font-medium">
                        <option value="voice">Voice Call</option>
                        <option value="sms">SMS Text</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="chat">Web Chat</option>
                        <option value="note">Internal Note</option>
                      </select>
                    </div>
                    <div>
                      <select v-model="newInteraction.direction" class="w-full rounded text-xs border-gray-300 py-1.5 font-medium">
                        <option value="inbound">Inbound</option>
                        <option value="outbound">Outbound</option>
                        <option value="internal">Internal Note</option>
                      </select>
                    </div>
                    <div>
                      <select v-model="newInteraction.sentiment" class="w-full rounded text-xs border-gray-300 py-1.5 font-medium">
                        <option value="positive">Positive 😊</option>
                        <option value="neutral">Neutral 😐</option>
                        <option value="negative">Negative 😟</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <input
                      v-model="newInteraction.summary"
                      type="text"
                      :placeholder="$t('Summary / Action taken...')"
                      class="w-full rounded text-xs border-gray-300 py-1.5 mb-2 font-semibold"
                    />
                    <textarea
                      v-model="newInteraction.details"
                      rows="2"
                      :placeholder="$t('Detailed notes or discussion points...')"
                      class="w-full rounded text-xs border-gray-300 py-1.5"
                    ></textarea>
                  </div>
                  <div class="flex justify-end">
                    <button
                      @click="submitInteraction"
                      :disabled="!newInteraction.summary"
                      class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold disabled:opacity-50 transition"
                    >
                      {{ $t("Save to Timeline") }}
                    </button>
                  </div>
                </div>

                <!-- Timeline List -->
                <div class="space-y-4">
                  <div
                    v-for="item in interactionsList"
                    :key="item.interaction_uuid"
                    class="flex items-start gap-3 text-xs"
                  >
                    <div
                      :class="[
                        'w-7 h-7 rounded-full flex items-center justify-center shrink-0 text-white shadow-sm',
                        item.channel === 'voice' ? 'bg-blue-600' :
                        item.channel === 'sms' ? 'bg-emerald-600' :
                        item.channel === 'email' ? 'bg-purple-600' : 'bg-gray-600'
                      ]"
                    >
                      <PhoneIcon v-if="item.channel === 'voice'" class="w-3.5 h-3.5" />
                      <EnvelopeIcon v-else-if="item.channel === 'email'" class="w-3.5 h-3.5" />
                      <ChatBubbleOvalLeftIcon v-else class="w-3.5 h-3.5" />
                    </div>

                    <div class="flex-1 bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1">
                      <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900">{{ item.summary }}</span>
                        <span class="text-[10px] text-gray-400">{{ formatDate(item.created_at) }}</span>
                      </div>
                      <p v-if="item.details" class="text-gray-600">{{ item.details }}</p>
                      <div class="flex items-center gap-2 text-[10px] text-gray-500 pt-1">
                        <span class="uppercase font-bold tracking-wider">{{ item.channel }}</span>
                        <span>•</span>
                        <span class="capitalize">{{ item.direction }}</span>
                        <span v-if="item.sentiment">• Sentiment: {{ item.sentiment }}</span>
                        <span v-if="item.agent">• Logged by: {{ item.agent.agent_name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tab 4: Customer Notes & Preferences -->
              <div v-if="activeTab === 'notes'" class="p-5 space-y-4">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                  <h4 class="text-xs font-bold text-gray-900">{{ $t("Internal Agent & Supervisor Notes") }}</h4>
                  <p class="text-xs text-gray-700 whitespace-pre-wrap leading-relaxed">
                    {{ currentCustomer.notes || $t("No notes entered for this customer profile.") }}
                  </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                  <div class="p-4 rounded-xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900">{{ $t("Communication Preferences & TCPA") }}</h4>
                    <div class="space-y-1 text-gray-600">
                      <div>Consent Status: <span class="font-bold text-emerald-600">Explicit Opt-In</span></div>
                      <div>Preferred Channel: <span class="font-bold text-gray-800">Voice &amp; SMS</span></div>
                      <div>Do Not Call (DNC): <span class="font-bold text-gray-800">Clear / Eligible</span></div>
                      <div>Calling Window: <span class="font-bold text-gray-800">09:00 - 20:00 Local</span></div>
                    </div>
                  </div>

                  <div class="p-4 rounded-xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900">{{ $t("ACD & VIP Routing Rules") }}</h4>
                    <div class="space-y-1 text-gray-600">
                      <div>Priority Boost: <span class="font-bold text-purple-700">+{{ currentCustomer.tier === 'vip' ? '100' : '20' }} points</span></div>
                      <div>Dedicated Tier: <span class="font-bold text-gray-800">{{ currentCustomer.tier.toUpperCase() }}</span></div>
                      <div>Assigned Account Manager: <span class="font-bold text-gray-800">Operations Desk</span></div>
                      <div>Auto Smart Callback: <span class="font-bold text-emerald-600">Enabled</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Create Customer -->
    <div
      v-if="openCreateCustomerModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <UserPlusIcon class="w-5 h-5 text-indigo-600" />
            {{ $t("Create Customer Profile") }}
          </h3>
          <button @click="openCreateCustomerModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitCreateCustomer" class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("First Name") }} *</label>
              <input v-model="newCustomerForm.first_name" required type="text" class="w-full rounded-md border-gray-300 text-xs" />
            </div>
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Last Name") }}</label>
              <input v-model="newCustomerForm.last_name" type="text" class="w-full rounded-md border-gray-300 text-xs" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Phone Number") }} *</label>
              <input v-model="newCustomerForm.phone" required type="text" placeholder="+1..." class="w-full rounded-md border-gray-300 text-xs font-mono" />
            </div>
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Email Address") }}</label>
              <input v-model="newCustomerForm.email" type="email" class="w-full rounded-md border-gray-300 text-xs" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Company / Organization") }}</label>
              <input v-model="newCustomerForm.company" type="text" class="w-full rounded-md border-gray-300 text-xs" />
            </div>
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Tier") }}</label>
              <select v-model="newCustomerForm.tier" class="w-full rounded-md border-gray-300 text-xs font-semibold">
                <option value="standard">Standard</option>
                <option value="silver">Silver</option>
                <option value="gold">Gold</option>
                <option value="vip">VIP (Priority Boost)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Account Notes") }}</label>
            <textarea v-model="newCustomerForm.notes" rows="2" class="w-full rounded-md border-gray-300 text-xs"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t">
            <button
              type="button"
              @click="openCreateCustomerModal = false"
              class="px-3 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-xs"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="submit"
              class="px-4 py-1.5 text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-bold text-xs"
            >
              {{ $t("Create Profile") }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Create Support Ticket -->
    <div
      v-if="openCreateTicketModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <TicketIcon class="w-5 h-5 text-indigo-600" />
            {{ $t("Create Support Ticket / Case") }}
          </h3>
          <button @click="openCreateTicketModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitCreateTicket" class="space-y-3 text-xs">
          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Customer") }}</label>
            <input
              type="text"
              disabled
              :value="currentCustomer?.full_name + ' (' + currentCustomer?.phone + ')'"
              class="w-full rounded-md border-gray-200 bg-gray-100 text-xs font-semibold text-gray-700"
            />
          </div>

          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Subject") }} *</label>
            <input v-model="newTicketForm.subject" required type="text" class="w-full rounded-md border-gray-300 text-xs font-semibold" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Category") }}</label>
              <select v-model="newTicketForm.category" class="w-full rounded-md border-gray-300 text-xs font-medium">
                <option value="Technical Support">Technical Support</option>
                <option value="Billing & Invoices">Billing & Invoices</option>
                <option value="Account Changes">Account Changes</option>
                <option value="Service Complaint">Service Complaint</option>
                <option value="Sales Inquiry">Sales Inquiry</option>
              </select>
            </div>
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Priority") }}</label>
              <select v-model="newTicketForm.priority" class="w-full rounded-md border-gray-300 text-xs font-semibold">
                <option value="low">Low (48h SLA)</option>
                <option value="medium">Medium (24h SLA)</option>
                <option value="high">High (8h SLA)</option>
                <option value="urgent">Urgent (4h SLA)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Assign To Agent") }}</label>
            <select v-model="newTicketForm.assigned_agent_uuid" class="w-full rounded-md border-gray-300 text-xs">
              <option value="">{{ $t("Unassigned (Queue Pool)") }}</option>
              <option v-for="ag in agents" :key="ag.call_center_agent_uuid" :value="ag.call_center_agent_uuid">
                {{ ag.agent_name }} (ID: {{ ag.agent_id }})
              </option>
            </select>
          </div>

          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Description & Details") }}</label>
            <textarea v-model="newTicketForm.description" rows="3" class="w-full rounded-md border-gray-300 text-xs"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t">
            <button
              type="button"
              @click="openCreateTicketModal = false"
              class="px-3 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-xs"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="submit"
              class="px-4 py-1.5 text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-bold text-xs"
            >
              {{ $t("Create Ticket") }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  UserGroupIcon,
  UserPlusIcon,
  TicketIcon,
  MagnifyingGlassIcon,
  PhoneIcon,
  PhoneArrowDownLeftIcon,
  ClockIcon,
  DocumentTextIcon,
  PlusIcon,
  PencilSquareIcon,
  XMarkIcon,
  ChatBubbleLeftRightIcon,
  EnvelopeIcon,
  ChatBubbleOvalLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  customers: {
    type: Array,
    default: () => [],
  },
  activeCustomer: {
    type: Object,
    default: null,
  },
  agents: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const customersList = ref([...props.customers]);
const currentCustomer = ref(props.activeCustomer?.customer || props.customers[0] || null);
const ticketsList = ref(props.activeCustomer?.tickets || []);
const interactionsList = ref(props.activeCustomer?.interactions || []);
const recentCallsList = ref(props.activeCustomer?.recent_calls || []);

const activeTab = ref("tickets");
const searchQuery = ref(props.filters.search || "");
const selectedTier = ref(props.filters.tier || "all");

const openCreateCustomerModal = ref(false);
const openCreateTicketModal = ref(false);
const openEditCustomerModal = ref(false);

const tierFilters = [
  { key: "all", label: "All Tiers" },
  { key: "vip", label: "VIP" },
  { key: "gold", label: "Gold" },
  { key: "silver", label: "Silver" },
  { key: "standard", label: "Standard" },
];

const newCustomerForm = ref({
  first_name: "",
  last_name: "",
  company: "",
  phone: "",
  email: "",
  tier: "standard",
  notes: "",
});

const newTicketForm = ref({
  subject: "",
  category: "Technical Support",
  priority: "medium",
  assigned_agent_uuid: "",
  description: "",
});

const newInteraction = ref({
  channel: "voice",
  direction: "inbound",
  summary: "",
  details: "",
  sentiment: "positive",
});

const activeTickets = computed(() => {
  return ticketsList.value.filter((t) => ["open", "in_progress", "pending_customer"].includes(t.status));
});

function getInitials(name) {
  if (!name) return "CU";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
}

async function selectCustomer(c) {
  try {
    const res = await axios.get(`/api/contact-center/customer-360/${c.customer_uuid}`);
    currentCustomer.value = res.data.customer;
    ticketsList.value = res.data.tickets || [];
    interactionsList.value = res.data.interactions || [];
    recentCallsList.value = res.data.recent_calls || [];
  } catch (err) {
    currentCustomer.value = c;
  }
}

function filterTier(key) {
  selectedTier.value = key;
  applyFilters();
}

function handleSearch() {
  applyFilters();
}

function applyFilters() {
  const query = searchQuery.value.toLowerCase();
  customersList.value = props.customers.filter((c) => {
    const matchesTier = selectedTier.value === "all" || c.tier === selectedTier.value;
    const matchesQuery =
      !query ||
      c.full_name?.toLowerCase().includes(query) ||
      c.phone?.includes(query) ||
      c.email?.toLowerCase().includes(query) ||
      c.company?.toLowerCase().includes(query) ||
      c.account_number?.toLowerCase().includes(query);
    return matchesTier && matchesQuery;
  });
}

async function submitCreateCustomer() {
  try {
    const res = await axios.post("/api/contact-center/customer-360", newCustomerForm.value);
    if (res.data?.success) {
      customersList.value.unshift(res.data.customer);
      currentCustomer.value = res.data.customer;
      ticketsList.value = [];
      interactionsList.value = [];
      recentCallsList.value = [];
      openCreateCustomerModal.value = false;
      newCustomerForm.value = { first_name: "", last_name: "", company: "", phone: "", email: "", tier: "standard", notes: "" };
    }
  } catch (err) {
    alert("Error creating customer profile.");
  }
}

async function submitCreateTicket() {
  if (!currentCustomer.value) return;
  try {
    const payload = {
      ...newTicketForm.value,
      customer_uuid: currentCustomer.value.customer_uuid,
    };
    const res = await axios.post("/api/contact-center/customer-360/tickets", payload);
    if (res.data?.success) {
      ticketsList.value.unshift(res.data.ticket);
      openCreateTicketModal.value = false;
      newTicketForm.value = { subject: "", category: "Technical Support", priority: "medium", assigned_agent_uuid: "", description: "" };
    }
  } catch (err) {
    alert("Error creating ticket.");
  }
}

async function updateTicketStatus(ticket) {
  try {
    await axios.patch(`/api/contact-center/customer-360/tickets/${ticket.ticket_uuid}`, {
      status: ticket.status,
    });
  } catch (err) {
    alert("Error updating ticket status.");
  }
}

async function submitInteraction() {
  if (!currentCustomer.value || !newInteraction.value.summary) return;
  try {
    const payload = {
      ...newInteraction.value,
      customer_uuid: currentCustomer.value.customer_uuid,
    };
    const res = await axios.post("/api/contact-center/customer-360/interactions", payload);
    if (res.data?.success) {
      interactionsList.value.unshift(res.data.interaction);
      newInteraction.value.summary = "";
      newInteraction.value.details = "";
    }
  } catch (err) {
    alert("Error logging interaction.");
  }
}

function formatDate(isoStr) {
  if (!isoStr) return "-";
  return new Date(isoStr).toLocaleString();
}
</script>
