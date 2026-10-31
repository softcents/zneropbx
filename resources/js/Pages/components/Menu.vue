<template>
    <Disclosure as="nav" class="bg-white shadow sticky top-0 z-40 border-b border-gray-100" v-slot="{ open }">
        <div class="mx-auto max-w-9xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <div class="flex items-center">
                    <div class="-ml-2 mr-2 flex items-center lg:hidden">
                        <!-- Mobile menu button -->
                        <DisclosureButton
                            class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                            <span class="absolute -inset-0.5" />
                            <span class="sr-only">{{ $t('Open main menu') }}</span>
                            <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
                            <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
                        </DisclosureButton>
                    </div>
                    <div class="flex flex-shrink-0 items-center">
                        <a href="/dashboard" class="flex items-center">
                            <img class="h-9 w-auto" :src="logoUrl" alt="ZneroPBX" />
                        </a>
                    </div>

                    <!-- Desktop Top Navigation Menu -->
                    <div class="hidden lg:ml-6 lg:flex lg:space-x-1 lg:items-center">
                        <div v-for="item in visibleMenus" :key="item.menu_item_uuid"
                            class="relative inline-flex items-center">
                            <!-- Menu With Children: Dropdown -->
                            <Menu v-if="hasChildren(item)" as="div" class="relative inline-block text-left" v-slot="{ open: menuOpen }">
                                <MenuButton
                                    :class="[
                                        isTopMenuActive(item)
                                            ? 'text-indigo-600 bg-indigo-50 font-semibold'
                                            : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50 font-medium',
                                        menuOpen ? 'bg-gray-100 text-indigo-700 shadow-sm' : '',
                                        'inline-flex items-center gap-x-1 rounded-lg px-3 py-2 text-sm transition-colors duration-150 cursor-pointer focus:outline-none'
                                    ]">
                                    <span class="font-nunito tracking-tight">{{ item.menu_item_title }}</span>
                                    <ChevronDownIcon :class="[menuOpen ? 'rotate-180 text-indigo-600' : 'text-gray-400', 'h-4 w-4 transition-transform duration-150']" />
                                </MenuButton>

                                <transition enter-active-class="transition ease-out duration-100"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95">
                                    <MenuItems
                                        class="absolute left-0 z-50 mt-1 min-w-[240px] max-w-[320px] max-h-[calc(100vh-5rem)] overflow-y-auto overscroll-contain origin-top-left rounded-xl bg-white p-1.5 shadow-2xl ring-1 ring-black/10 focus:outline-none">
                                        <div v-for="child in getChildren(item)" :key="child.menu_item_uuid">
                                            <MenuItem v-slot="{ active }">
                                                <a :href="child.menu_item_link"
                                                    :class="[
                                                        active ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50',
                                                        isChildActive(child) ? 'bg-indigo-50/80 font-semibold text-indigo-600' : '',
                                                        'flex items-center justify-between px-3 py-2 text-sm rounded-lg font-medium transition-colors cursor-pointer no-underline'
                                                    ]">
                                                    <span class="truncate">{{ child.menu_item_title }}</span>
                                                    <span v-if="child.menu_item_link === '/messages' && messageUnreadCount" class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700">{{ messageUnreadCount }}</span>
                                                </a>
                                            </MenuItem>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Menu Without Children: Direct Click Link -->
                            <a v-else :href="item.menu_item_link || '#'"
                                :class="[
                                    isTopMenuActive(item)
                                        ? 'text-indigo-600 bg-indigo-50 font-semibold'
                                        : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50 font-medium',
                                    'inline-flex items-center rounded-lg px-3 py-2 text-sm transition-colors duration-150 cursor-pointer no-underline'
                                ]">
                                {{ item.menu_item_title }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Action Bar: Domain Switcher & User Account -->
                <div class="flex items-center gap-x-2">
                    <!-- Domain Switcher -->
                    <div class="flex-shrink-0" v-if="page.props.domainSelectPermission">
                        <button type="button" @click="openDomainPanel"
                            class="relative inline-flex items-center gap-x-1.5 rounded-lg border border-gray-200 bg-gray-50/80 px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 hover:border-gray-300 transition-colors cursor-pointer focus:outline-none">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="hidden sm:inline max-w-[150px] truncate">{{ page.props.selectedDomain }}</span>
                            <svg class="h-4 w-4 text-gray-500 sm:hidden" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="2" x2="22" y1="12" y2="12" />
                                <path
                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Account Menu -->
                    <Menu as="div" class="relative flex-shrink-0" v-slot="{ open: userMenuOpen }">
                        <MenuButton
                            :class="[
                                userMenuOpen ? 'bg-gray-100 text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50',
                                'inline-flex items-center gap-x-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium transition-colors cursor-pointer focus:outline-none'
                            ]">
                            <UserCircleIcon class="h-6 w-6 text-gray-400" />
                            <span class="hidden sm:block font-medium">{{ userName }}</span>
                            <ChevronDownIcon :class="[userMenuOpen ? 'rotate-180' : '', 'h-4 w-4 text-gray-400 transition-transform duration-150']" />
                        </MenuButton>

                        <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                            <MenuItems
                                class="absolute right-0 z-50 mt-1.5 w-60 origin-top-right rounded-xl bg-white p-1.5 shadow-2xl ring-1 ring-black/10 focus:outline-none">
                                <div class="px-3 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">{{ $t('Signed in as') }}</p>
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ userEmail }}</p>
                                </div>
                                <MenuItem v-slot="{ active }">
                                    <a href="/logout"
                                        :class="[active ? 'bg-rose-50 text-rose-700' : 'text-gray-700 hover:bg-gray-50', 'flex items-center gap-x-2 px-3 py-2 text-sm rounded-lg font-medium transition-colors cursor-pointer no-underline']">
                                        <ArrowRightOnRectangleIcon class="h-4 w-4 text-rose-500" />
                                        {{ $t('Logout') }}
                                    </a>
                                </MenuItem>
                            </MenuItems>
                        </transition>
                    </Menu>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <DisclosurePanel class="lg:hidden border-t border-gray-100 bg-white shadow-inner">
            <nav class="flex flex-1 flex-col py-3 px-4">
                <ul role="list" class="flex flex-1 flex-col space-y-1">
                    <li v-for="item in visibleMenus" :key="item.menu_item_uuid">
                        <a v-if="!hasChildren(item)" :href="item.menu_item_link || '#'"
                            :class="[
                                isTopMenuActive(item) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50',
                                'block rounded-lg px-3 py-2 text-sm font-medium transition-colors'
                            ]">{{ item.menu_item_title }}</a>

                        <Disclosure as="div" v-else v-slot="{ open: mobileChildOpen }">
                            <DisclosureButton
                                :class="[
                                    isTopMenuActive(item) ? 'text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50',
                                    'flex items-center justify-between w-full text-left rounded-lg px-3 py-2 text-sm font-medium transition-colors'
                                ]">
                                <span>{{ item.menu_item_title }}</span>
                                <ChevronRightIcon
                                    :class="[mobileChildOpen ? 'rotate-90 text-indigo-600' : 'text-gray-400', 'h-4 w-4 shrink-0 transition-transform duration-150']"
                                    aria-hidden="true" />
                            </DisclosureButton>
                            <DisclosurePanel as="ul" class="mt-1 pl-3 pr-1 space-y-0.5 border-l-2 border-indigo-100 ml-3">
                                <li v-for="child in getChildren(item)" :key="child.menu_item_uuid">
                                    <DisclosureButton as="a" :href="child.menu_item_link"
                                        :class="[
                                            isChildActive(child) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                            'flex items-center justify-between rounded-md py-1.5 px-2.5 text-sm transition-colors'
                                        ]">
                                        <span class="truncate">{{ child.menu_item_title }}</span>
                                        <span v-if="child.menu_item_link === '/messages' && messageUnreadCount" class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700">{{ messageUnreadCount }}</span>
                                    </DisclosureButton>
                                </li>
                            </DisclosurePanel>
                        </Disclosure>
                    </li>
                </ul>
            </nav>
        </DisclosurePanel>
    </Disclosure>

    <!-- Domain Switcher Modal Panel -->
    <TransitionRoot as="template" :show="isDomainPanelVisible">
        <Dialog as="div" class="relative z-50" :initialFocus="searchFieldRef" @close="isDomainPanelVisible = false">
            <div class="fixed inset-0 bg-gray-500/50 transition-opacity" aria-hidden="true" />
            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <TransitionChild as="template"
                            enter="transform transition ease-in-out duration-300 sm:duration-500"
                            enter-from="translate-x-full" enter-to="translate-x-0"
                            leave="transform transition ease-in-out duration-300 sm:duration-500"
                            leave-from="translate-x-0" leave-to="translate-x-full">
                            <DialogPanel class="pointer-events-auto w-screen max-w-md">
                                <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                                    <div class="px-4 sm:px-6">
                                        <div class="flex items-start justify-between">
                                            <DialogTitle class="text-base font-semibold leading-6 text-gray-900">{{ $t('Select account') }}
                                            </DialogTitle>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button type="button"
                                                    class="relative rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                    @click="isDomainPanelVisible = false">
                                                    <span class="absolute -inset-2.5" />
                                                    <span class="sr-only">{{ $t('Close panel') }}</span>
                                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Search Field -->
                                    <div class="relative mt-4 px-4 sm:px-6">
                                        <input type="text" v-model="searchQuery" :placeholder="$t('Search domains...')"
                                            ref="searchFieldRef"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                                    </div>

                                    <!-- Domain List -->
                                    <div class="relative mt-4 flex-1 px-4 sm:px-6 overflow-y-auto">
                                        <div v-if="filteredDomains && filteredDomains.length > 0" id="domainSearchList" class="space-y-1">
                                            <div v-for="domain in filteredDomains" :key="domain.domain_uuid">
                                                <a href="#" @click.prevent="selectDomain(domain.domain_uuid)"
                                                    class="cursor-pointer no-underline block">
                                                    <div class="flex flex-col p-2.5 rounded-lg border transition-colors"
                                                        :class="page.props.selectedDomainUuid === domain.domain_uuid ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'">
                                                        <div class="text-sm font-semibold mb-0.5">{{ domain.domain_description || domain.domain_name }}</div>
                                                        <div class="text-xs" :class="page.props.selectedDomainUuid === domain.domain_uuid ? 'text-indigo-200' : 'text-gray-400'">{{ domain.domain_name }}</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-6 text-sm text-gray-400">
                                            {{ $t('No domains found') }}
                                        </div>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3'

import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, UserCircleIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const page = usePage()
defineProps({ messageUnreadCount: { type: Number, default: 0 } });

// Logged-in user info (shown in the top-right user menu)
const userName = computed(() => page.props.auth?.user?.name ?? '')
const userEmail = computed(() => page.props.auth?.user?.email ?? '')

// Safe children extractor
const getChildren = (item) => {
    if (!item || !item.child_menu) return [];
    if (Array.isArray(item.child_menu)) return item.child_menu;
    return Object.values(item.child_menu);
};

const hasChildren = (item) => {
    return getChildren(item).length > 0;
};

// URL tracking for active menu highlighting
const currentUrl = computed(() => page.url || '');

const isChildActive = (child) => {
    if (!child || !child.menu_item_link) return false;
    const link = child.menu_item_link;
    if (link === '/' && currentUrl.value === '/') return true;
    if (link !== '/' && (currentUrl.value === link || currentUrl.value.startsWith(link + '/') || currentUrl.value.startsWith(link + '?'))) return true;
    return false;
};

const isTopMenuActive = (item) => {
    const children = getChildren(item);
    if (!children.length) {
        return item.menu_item_link && (currentUrl.value === item.menu_item_link || currentUrl.value.startsWith(item.menu_item_link + '/'));
    }
    return children.some(child => isChildActive(child));
};

// Top navigation menus, excluding the legacy "Home" menu
const visibleMenus = computed(() => {
    const raw = page.props.menus ?? [];
    const list = Array.isArray(raw) ? raw : Object.values(raw);
    return list.filter(item => item && item.menu_item_title !== 'Home');
});

const isDomainPanelVisible = ref(false);

const openDomainPanel = () => {
    isDomainPanelVisible.value = true;
};

const searchFieldRef = ref(null);
const searchQuery = ref('');
const logoUrl = ref('/storage/logo.png');
const emit = defineEmits(['reset-filters']);

const selectDomain = async (domainUuid) => {
    try {
        const response = await axios.post('/domains/switch', {
            domain_uuid: domainUuid,
            _token: page.props.csrf_token,
        });
        emit('reset-filters');
        window.location.href = response.data.redirectUrl;
    } catch (error) {
        console.error(error);
    }
};

const filteredDomains = computed(() => {
    const domains = page.props.domains ?? [];
    const list = Array.isArray(domains) ? domains : Object.values(domains);
    if (!searchQuery.value) {
        return list;
    }
    const q = searchQuery.value.toLowerCase();
    return list.filter(domain =>
        (domain.domain_description && domain.domain_description.toLowerCase().includes(q)) ||
        (domain.domain_name && domain.domain_name.toLowerCase().includes(q))
    );
});
</script>
