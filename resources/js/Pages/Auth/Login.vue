<template>
    <Head :title="page.props.whitelabel?.company_name ? `${page.props.whitelabel.company_name} | ${headingText}` : $t('Sign in to your account')" />
    <div class="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <img class="mx-auto h-12 w-auto max-w-[240px] object-contain" :src="logoUrl" :alt="companyName" />
            <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
                {{ headingText }}
            </h2>
            <p v-if="subheadingText" class="mt-1 text-sm text-gray-500">
                {{ subheadingText }}
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-6 py-10 shadow-sm ring-1 ring-gray-200 sm:rounded-lg sm:px-10">
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                    {{ status }}
                </div>

                <div v-if="errorMessage" class="mb-4 font-medium text-sm text-red-600">
                    {{ errorMessage }}
                </div>

                <div v-if="page.props.flash.error" class="mb-4 font-medium text-sm text-red-600">
                    {{ page.props.flash.error }}
                </div>

                <form class="space-y-6" action="#" method="POST">
                    <div>
                        <label for="user_email" class="block text-sm font-medium leading-6 text-gray-900">{{ $t('Username or Email address') }}</label>
                        <div class="mt-2">
                            <input v-model="form.user_email" id="user_email" name="user_email" type="text"
                                autocomplete="username" required
                                :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', { 'ring-1 ring-inset ring-red-600': errors.user_email, 'ring-1 ring-inset ring-gray-300': !errors.user_email }]" />
                        </div>
                        <!-- Error message for user_email -->
                        <div v-if="errors.user_email" class="mt-2 text-sm text-red-600">
                            {{ errors.user_email }}
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">{{ $t('Password') }}</label>
                        <div class="mt-2">
                            <input v-model="form.password" id="password" name="password" type="password"
                                autocomplete="current-password" required
                                :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', { 'ring-1 ring-inset ring-red-600': errors.password, 'ring-1 ring-inset ring-gray-300': !errors.password }]" />
                        </div>
                        <!-- Error message for password -->
                        <div v-if="errors.password" class="mt-2 text-sm text-red-600">
                            {{ errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <div class="text-sm leading-6">
                            <Link :href="links['password-request']"
                                class="font-semibold text-indigo-600 hover:text-indigo-500">
                            {{ $t('Forgot your password?') }}
                            </Link>
                        </div>
                    </div>

                    <div>
                        <button @click.prevent="submitForm" type="submit"
                            :style="{ backgroundColor: accentColor }"
                            class="flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:opacity-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                            <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ $t('Sign in') }}
                        </button>
                    </div>
                </form>

            </div>

            <!-- White-Label Support & Copyright Footer -->
            <div class="mt-8 text-center space-y-1.5 text-xs text-gray-500">
                <p v-if="supportEmail">
                    {{ $t('Need assistance?') }} 
                    <a :href="'mailto:' + supportEmail" class="font-semibold text-indigo-600 hover:underline">
                        {{ supportEmail }}
                    </a>
                    <span v-if="supportPhone"> • {{ supportPhone }}</span>
                </p>
                <p>{{ copyrightText }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { trans } from '@i18n';

const page = usePage();

const props = defineProps({
    errors: Object,
    links: Object,
    status: String,
});

const companyName = computed(() => page.props.whitelabel?.company_name || 'ZneroPBX');
const logoUrl = computed(() => page.props.whitelabel?.logo_url || '/storage/logo.png');
const accentColor = computed(() => page.props.whitelabel?.login_accent_color || '#4f46e5');
const headingText = computed(() => page.props.whitelabel?.login_heading || trans('Sign in to your account'));
const subheadingText = computed(() => page.props.whitelabel?.login_subheading || '');
const supportEmail = computed(() => page.props.whitelabel?.support_email || '');
const supportPhone = computed(() => page.props.whitelabel?.support_phone || '');
const copyrightText = computed(() => page.props.whitelabel?.copyright_text || `© ${new Date().getFullYear()} ${companyName.value}. All rights reserved.`);

const isLoading = ref(false);
const errorMessage = ref('');

// Reactive variables for form inputs
const form = useForm({
    user_email: '',
    password: '',
    remember: false,
    _token: page.props.csrf_token,
});

// Function to handle form submission
const submitForm = () => {
    errorMessage.value = null;
    isLoading.value = true;

    axios.get('csrf-token/refresh')
        .then((response) => {
            // Update the form's token value
            form._token = response.data.token;
            axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.token;

            form.post('/login', {
                onFinish: () => {
                    form.reset('password');
                    isLoading.value = false;
                },
            });
        }).catch((error) => {
            errorMessage.value = trans('Invalid token. Refresh the page.');
            isLoading.value = false; // Reset loading state on error
        });
};
</script>
