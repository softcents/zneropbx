<template>
  <MainLayout>
    <div class="p-4 sm:p-6 space-y-5 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-emerald-500"></span>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <ChatBubbleLeftRightIcon class="w-7 h-7 text-emerald-600" />
                {{ $t("Omnichannel Messaging & Conversation Hub") }}
              </h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              {{ $t("Unified conversation threads across WhatsApp, SMS, Web Chat, and Email with canned shortcuts and Customer 360 context.") }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="openSimulateModal = true"
              class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition"
            >
              <PaperAirplaneIcon class="w-4 h-4" />
              {{ $t("Simulate Inbound Message") }}
            </button>
          </div>
        </div>
      </div>

      <!-- 3-Column Enterprise Omnichannel Inbox Workspace -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-[760px] bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Col 1: Channels & Queues Sidebar (2 cols) -->
        <div class="lg:col-span-2 border-r border-gray-200 p-3.5 space-y-4 bg-gray-50/60 overflow-y-auto">
          <div>
            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">
              {{ $t("Channels") }}
            </h3>
            <div class="space-y-1">
              <button
                v-for="ch in channelsList"
                :key="ch.key"
                @click="filterChannel(ch.key)"
                :class="[
                  'w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold transition',
                  activeChannel === ch.key
                    ? 'bg-indigo-600 text-white font-bold shadow-sm'
                    : 'text-gray-700 hover:bg-gray-100',
                ]"
              >
                <div class="flex items-center gap-2 truncate">
                  <span>{{ ch.icon }}</span>
                  <span class="truncate">{{ ch.label }}</span>
                </div>
                <span
                  :class="[
                    'text-[10px] px-1.5 py-0.5 rounded-full font-bold',
                    activeChannel === ch.key ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'
                  ]"
                >
                  {{ getChannelCount(ch.key) }}
                </span>
              </button>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-200/60">
            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">
              {{ $t("Queue Status") }}
            </h3>
            <div class="space-y-1">
              <button
                v-for="st in statusFilters"
                :key="st.key"
                @click="filterStatus(st.key)"
                :class="[
                  'w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition',
                  activeStatus === st.key
                    ? 'bg-gray-200 text-gray-900 font-bold'
                    : 'text-gray-600 hover:bg-gray-100',
                ]"
              >
                <span>{{ st.label }}</span>
                <span
                  v-if="getStatusCount(st.key) > 0"
                  class="text-[10px] px-1.5 py-0.2 rounded-full bg-indigo-50 text-indigo-700 font-bold"
                >
                  {{ getStatusCount(st.key) }}
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Col 2: Conversation List (4 cols) -->
        <div class="lg:col-span-4 border-r border-gray-200 flex flex-col h-full bg-white overflow-hidden">
          <!-- Search Header -->
          <div class="p-3 border-b border-gray-200 bg-gray-50/40">
            <div class="relative">
              <MagnifyingGlassIcon class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" />
              <input
                v-model="searchQuery"
                @input="applySearch"
                type="text"
                :placeholder="$t('Search conversations...')"
                class="w-full pl-9 pr-3 py-1.5 rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
          </div>

          <!-- Conversation Cards -->
          <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
            <div
              v-if="filteredConversations.length === 0"
              class="p-8 text-center text-xs text-gray-400"
            >
              {{ $t("No conversations found in this filter.") }}
            </div>

            <div
              v-for="conv in filteredConversations"
              :key="conv.conversation_uuid"
              @click="selectConversation(conv)"
              :class="[
                'p-3.5 cursor-pointer transition flex items-start gap-3',
                currentConversation?.conversation_uuid === conv.conversation_uuid
                  ? 'bg-indigo-50/70 border-l-4 border-indigo-600'
                  : 'hover:bg-gray-50',
              ]"
            >
              <!-- Channel Icon Avatar -->
              <div
                :class="[
                  'w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 shadow-sm text-white',
                  conv.channel === 'whatsapp' ? 'bg-emerald-600' :
                  conv.channel === 'sms' ? 'bg-blue-600' :
                  conv.channel === 'web_chat' ? 'bg-purple-600' : 'bg-amber-600'
                ]"
              >
                <span v-if="conv.channel === 'whatsapp'">💬</span>
                <span v-else-if="conv.channel === 'sms'">📱</span>
                <span v-else-if="conv.channel === 'web_chat'">🌐</span>
                <span v-else>✉️</span>
              </div>

              <!-- Message preview -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                  <h4 class="text-xs font-bold text-gray-900 truncate">
                    {{ conv.customer_name || conv.customer_identifier }}
                  </h4>
                  <span class="text-[10px] text-gray-400 shrink-0">
                    {{ formatTime(conv.last_message_at) }}
                  </span>
                </div>

                <div class="flex items-center gap-1.5 text-[11px] text-gray-500 mt-0.5 truncate">
                  <span class="font-mono text-gray-700">{{ conv.customer_identifier }}</span>
                  <span v-if="conv.priority === 'urgent'" class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-rose-100 text-rose-800 animate-pulse">
                    VIP
                  </span>
                </div>

                <p class="text-xs text-gray-600 truncate mt-1">
                  {{ conv.last_message_preview || 'No messages yet' }}
                </p>

                <div class="flex items-center justify-between mt-2 text-[10px]">
                  <span
                    :class="[
                      'px-1.5 py-0.5 rounded font-bold uppercase tracking-wider',
                      conv.status === 'queued' ? 'bg-amber-100 text-amber-800' :
                      conv.status === 'assigned' || conv.status === 'open' ? 'bg-emerald-100 text-emerald-800' :
                      conv.status === 'waiting_customer' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'
                    ]"
                  >
                    {{ conv.status }}
                  </span>

                  <span
                    v-if="conv.unread_count > 0"
                    class="w-4 h-4 rounded-full bg-rose-600 text-white font-black text-[9px] flex items-center justify-center shadow-sm"
                  >
                    {{ conv.unread_count }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Col 3: Active Thread Chat Stream (6 cols) -->
        <div class="lg:col-span-6 flex flex-col h-full bg-gray-50/30 overflow-hidden">
          <div v-if="!currentConversation" class="flex-1 flex items-center justify-center text-center p-8 text-gray-400 text-xs">
            {{ $t("Select a conversation thread to start replying.") }}
          </div>

          <div v-else class="flex flex-col h-full">
            <!-- Thread Header -->
            <div class="p-3.5 bg-white border-b border-gray-200 flex flex-wrap items-center justify-between gap-3 shadow-sm shrink-0">
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs text-white shadow-sm',
                    currentConversation.channel === 'whatsapp' ? 'bg-emerald-600' :
                    currentConversation.channel === 'sms' ? 'bg-blue-600' :
                    currentConversation.channel === 'web_chat' ? 'bg-purple-600' : 'bg-amber-600'
                  ]"
                >
                  <span v-if="currentConversation.channel === 'whatsapp'">💬</span>
                  <span v-else-if="currentConversation.channel === 'sms'">📱</span>
                  <span v-else-if="currentConversation.channel === 'web_chat'">🌐</span>
                  <span v-else>✉️</span>
                </div>

                <div>
                  <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-gray-900">
                      {{ currentConversation.customer_name || currentConversation.customer_identifier }}
                    </h3>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">
                      {{ currentConversation.channel }}
                    </span>
                  </div>
                  <div class="text-[11px] text-gray-500 font-mono">
                    {{ currentConversation.customer_identifier }}
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <!-- Assign Agent -->
                <select
                  v-model="currentConversation.assigned_agent_uuid"
                  @change="assignAgent"
                  class="rounded text-[11px] py-1 border-gray-300 font-medium focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">{{ $t("Unassigned") }}</option>
                  <option v-for="ag in agents" :key="ag.call_center_agent_uuid" :value="ag.call_center_agent_uuid">
                    {{ ag.agent_name }}
                  </option>
                </select>

                <!-- Resolve button -->
                <button
                  @click="toggleResolved"
                  class="px-2.5 py-1 rounded text-[11px] font-bold border transition flex items-center gap-1"
                  :class="[
                    currentConversation.status === 'resolved'
                      ? 'bg-emerald-50 text-emerald-700 border-emerald-300'
                      : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                  ]"
                >
                  <CheckIcon class="w-3.5 h-3.5" />
                  {{ currentConversation.status === 'resolved' ? $t("Resolved") : $t("Resolve") }}
                </button>

                <!-- Link to Customer 360 -->
                <a
                  v-if="currentCustomer?.customer_uuid"
                  :href="`/contact-center/customer-360?customer_uuid=${currentCustomer.customer_uuid}`"
                  target="_blank"
                  class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded transition"
                  :title="$t('Open in Customer 360 Workspace')"
                >
                  <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                </a>
              </div>
            </div>

            <!-- Messages Stream -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
              <div
                v-for="msg in messagesList"
                :key="msg.conversation_message_uuid"
                :class="[
                  'flex items-end gap-2 max-w-[85%]',
                  msg.sender_type === 'agent' ? 'ml-auto flex-row-reverse' : 'mr-auto'
                ]"
              >
                <!-- Avatar -->
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0',
                    msg.sender_type === 'agent' ? 'bg-indigo-600' : 'bg-gray-400'
                  ]"
                >
                  {{ msg.sender_type === 'agent' ? 'A' : 'C' }}
                </div>

                <!-- Bubble -->
                <div
                  :class="[
                    'p-3 rounded-2xl text-xs shadow-sm space-y-1',
                    msg.sender_type === 'agent'
                      ? 'bg-indigo-600 text-white rounded-br-xs'
                      : 'bg-white text-gray-900 border border-gray-200 rounded-bl-xs'
                  ]"
                >
                  <div class="flex items-center justify-between gap-3 text-[10px]" :class="msg.sender_type === 'agent' ? 'text-indigo-200' : 'text-gray-400'">
                    <span class="font-bold">{{ msg.sender_name || (msg.sender_type === 'agent' ? 'Support Agent' : 'Customer') }}</span>
                    <span>{{ formatTime(msg.created_at) }}</span>
                  </div>
                  <p class="whitespace-pre-wrap leading-relaxed">{{ msg.body }}</p>
                </div>
              </div>
            </div>

            <!-- Canned Responses Quick Chips -->
            <div class="px-3.5 py-2 bg-gray-100/70 border-t border-gray-200 flex items-center gap-1.5 overflow-x-auto text-[11px] shrink-0">
              <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider shrink-0 mr-1">
                {{ $t("Templates:") }}
              </span>
              <button
                v-for="cr in cannedResponses"
                :key="cr.canned_response_uuid"
                @click="applyCannedResponse(cr)"
                class="px-2 py-0.5 rounded-full bg-white border border-gray-300 hover:border-indigo-500 hover:text-indigo-600 text-gray-700 font-semibold shrink-0 transition"
                :title="cr.content"
              >
                {{ cr.shortcut }} ({{ cr.title }})
              </button>
            </div>

            <!-- Message Composer -->
            <div class="p-3 bg-white border-t border-gray-200 shrink-0">
              <div class="flex items-end gap-2">
                <textarea
                  v-model="replyText"
                  @keydown.enter.exact.prevent="sendReply"
                  rows="2"
                  :placeholder="$t('Type a message or click a template... (Press Enter to send)')"
                  class="flex-1 rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 p-2.5 resize-none"
                ></textarea>

                <button
                  @click="sendReply"
                  :disabled="!replyText.trim()"
                  class="p-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition disabled:opacity-50"
                  :title="$t('Send Message')"
                >
                  <PaperAirplaneIcon class="w-5 h-5" />
                </button>
              </div>
              <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1 px-1">
                <span>Channel: {{ currentConversation.channel.toUpperCase() }} &bull; Provider: Telnyx / WhatsApp</span>
                <span>Enter to Send, Shift+Enter for new line</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Simulate Inbound Customer Message -->
    <div
      v-if="openSimulateModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <ChatBubbleBottomCenterTextIcon class="w-5 h-5 text-indigo-600" />
            {{ $t("Simulate Inbound Customer Message") }}
          </h3>
          <button @click="openSimulateModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitSimulate" class="space-y-3 text-xs">
          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Channel") }}</label>
            <select v-model="simulateForm.channel" class="w-full rounded-md border-gray-300 text-xs font-semibold">
              <option value="whatsapp">💬 WhatsApp</option>
              <option value="sms">📱 SMS Text</option>
              <option value="web_chat">🌐 Web Chat Widget</option>
              <option value="email">✉️ Email</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("Customer Name") }}</label>
              <input v-model="simulateForm.name" required type="text" class="w-full rounded-md border-gray-300 text-xs" />
            </div>
            <div>
              <label class="block font-medium text-gray-700 mb-1">{{ $t("From Phone / Identifier") }}</label>
              <input v-model="simulateForm.from" required type="text" class="w-full rounded-md border-gray-300 text-xs font-mono" />
            </div>
          </div>

          <div>
            <label class="block font-medium text-gray-700 mb-1">{{ $t("Incoming Message Text") }}</label>
            <textarea v-model="simulateForm.body" required rows="3" class="w-full rounded-md border-gray-300 text-xs"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t">
            <button
              type="button"
              @click="openSimulateModal = false"
              class="px-3 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-xs"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="submit"
              class="px-4 py-1.5 text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-bold text-xs"
            >
              {{ $t("Send to Queue") }}
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
  ChatBubbleLeftRightIcon,
  PaperAirplaneIcon,
  MagnifyingGlassIcon,
  CheckIcon,
  ArrowTopRightOnSquareIcon,
  XMarkIcon,
  ChatBubbleBottomCenterTextIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  conversations: {
    type: Array,
    default: () => [],
  },
  activeThread: {
    type: Object,
    default: null,
  },
  cannedResponses: {
    type: Array,
    default: () => [],
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

const conversationsList = ref([...props.conversations]);
const currentConversation = ref(props.activeThread?.conversation || props.conversations[0] || null);
const messagesList = ref(props.activeThread?.messages || []);
const currentCustomer = ref(props.activeThread?.customer || null);

const activeChannel = ref(props.filters.channel || "all");
const activeStatus = ref(props.filters.status || "all");
const searchQuery = ref("");
const replyText = ref("");

const openSimulateModal = ref(false);
const simulateForm = ref({
  channel: "whatsapp",
  name: "Alexander Wright",
  from: "+15552345678",
  body: "Can you confirm if our SIP trunk backup route is active?",
});

const channelsList = [
  { key: "all", label: "All Channels", icon: "📬" },
  { key: "whatsapp", label: "WhatsApp", icon: "💬" },
  { key: "sms", label: "SMS Text", icon: "📱" },
  { key: "web_chat", label: "Web Chat", icon: "🌐" },
  { key: "email", label: "Email", icon: "✉️" },
];

const statusFilters = [
  { key: "all", label: "All Statuses" },
  { key: "queued", label: "Queued (Unassigned)" },
  { key: "assigned", label: "Assigned" },
  { key: "open", label: "Open" },
  { key: "waiting_customer", label: "Waiting on Customer" },
  { key: "resolved", label: "Resolved" },
];

const filteredConversations = computed(() => {
  return conversationsList.value.filter((c) => {
    const matchesChannel = activeChannel.value === "all" || c.channel === activeChannel.value;
    const matchesStatus = activeStatus.value === "all" || c.status === activeStatus.value;
    const q = searchQuery.value.toLowerCase();
    const matchesSearch =
      !q ||
      c.customer_name?.toLowerCase().includes(q) ||
      c.customer_identifier?.toLowerCase().includes(q) ||
      c.last_message_preview?.toLowerCase().includes(q);
    return matchesChannel && matchesStatus && matchesSearch;
  });
});

function getChannelCount(chKey) {
  if (chKey === "all") return conversationsList.value.length;
  return conversationsList.value.filter((c) => c.channel === chKey).length;
}

function getStatusCount(stKey) {
  if (stKey === "all") return 0;
  return conversationsList.value.filter((c) => c.status === stKey).length;
}

function filterChannel(key) {
  activeChannel.value = key;
}

function filterStatus(key) {
  activeStatus.value = key;
}

function applySearch() {
  // handled by computed
}

async function selectConversation(conv) {
  try {
    const res = await axios.get(`/api/contact-center/omnichannel/${conv.conversation_uuid}`);
    currentConversation.value = res.data.conversation;
    messagesList.value = res.data.messages || [];
    currentCustomer.value = res.data.customer || null;
    conv.unread_count = 0;
  } catch (err) {
    currentConversation.value = conv;
  }
}

async function sendReply() {
  if (!replyText.value.trim() || !currentConversation.value) return;
  const text = replyText.value.trim();
  replyText.value = "";

  try {
    const res = await axios.post(`/api/contact-center/omnichannel/${currentConversation.value.conversation_uuid}/reply`, {
      body: text,
      agent_uuid: currentConversation.value.assigned_agent_uuid || undefined,
    });

    if (res.data?.success) {
      messagesList.value.push(res.data.message);
      currentConversation.value.status = "waiting_customer";
      currentConversation.value.last_message_preview = text;
    }
  } catch (err) {
    alert("Error sending message.");
  }
}

function applyCannedResponse(cr) {
  replyText.value = cr.content;
}

async function assignAgent() {
  if (!currentConversation.value) return;
  try {
    await axios.post(`/api/contact-center/omnichannel/${currentConversation.value.conversation_uuid}/assign`, {
      agent_uuid: currentConversation.value.assigned_agent_uuid,
    });
  } catch (err) {
    alert("Error assigning agent.");
  }
}

async function toggleResolved() {
  if (!currentConversation.value) return;
  const newStatus = currentConversation.value.status === "resolved" ? "open" : "resolved";
  try {
    const res = await axios.post(`/api/contact-center/omnichannel/${currentConversation.value.conversation_uuid}/status`, {
      status: newStatus,
    });
    if (res.data?.success) {
      currentConversation.value.status = newStatus;
    }
  } catch (err) {
    alert("Error updating status.");
  }
}

async function submitSimulate() {
  try {
    const res = await axios.post("/api/contact-center/omnichannel/simulate", simulateForm.value);
    if (res.data?.success) {
      conversationsList.value.unshift(res.data.conversation);
      currentConversation.value = res.data.conversation;
      selectConversation(res.data.conversation);
      openSimulateModal.value = false;
      alert("Simulated message queued!");
    }
  } catch (err) {
    alert("Simulation failed.");
  }
}

function formatTime(isoStr) {
  if (!isoStr) return "";
  const d = new Date(isoStr);
  return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}
</script>
