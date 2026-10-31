<template>
  <MainLayout>
    <div class="m-3 space-y-6 font-nunito">
      <!-- Top Studio Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="p-2 bg-indigo-50 text-indigo-700 rounded-lg ring-1 ring-indigo-200">
                <PaintBrushIcon class="w-6 h-6" />
              </span>
              <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                  {{ $t("Reseller & 100% White-Label Studio") }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                  {{ $t("Enterprise B2B SaaS Hierarchy: Master Reseller → Sub-Reseller → Customer Companies with isolated brand identity, dedicated SMTP, SMS Sender ID, and commercial quotas.") }}
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center flex-wrap gap-2.5">
            <!-- Domain Context Badge -->
            <div class="flex items-center bg-gray-50 rounded-lg ring-1 ring-gray-200 px-3 py-1.5 text-xs text-gray-700">
              <GlobeAltIcon class="w-4 h-4 text-gray-500 mr-1.5" />
              <span class="font-semibold mr-1">{{ $t("Domain:") }}</span>
              <span class="font-mono text-indigo-600 font-bold">{{ profile.custom_domain_hostname || 'Default Host' }}</span>
            </div>

            <!-- Reseller Tier Pill -->
            <span
              class="px-2.5 py-1 rounded-md text-xs font-semibold uppercase tracking-wider"
              :class="tierBadgeClass(profile.reseller_tier)"
            >
              {{ formatTierName(profile.reseller_tier) }}
            </span>

            <button
              @click="openNewTenantModal"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors cursor-pointer"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("Create Sub-Reseller / Company") }}
            </button>
          </div>
        </div>

        <!-- KPI Telemetry Grid -->
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-5 gap-4">
          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Total Tenants & Orgs") }}</span>
            <span class="text-xl font-bold text-gray-900 font-mono">{{ stats.total_tenants }}</span>
            <span class="text-[11px] text-gray-400 block">{{ stats.sub_resellers }} Sub-Resellers | {{ stats.client_companies }} Companies</span>
          </div>

          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Extensions Allocation") }}</span>
            <div class="flex items-baseline gap-1">
              <span class="text-xl font-bold text-indigo-600 font-mono">{{ stats.total_used_ext }}</span>
              <span class="text-xs text-gray-500 font-mono">/ {{ stats.total_allocated_ext }}</span>
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold block">Quota Available</span>
          </div>

          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Phone Numbers / DIDs") }}</span>
            <div class="flex items-baseline gap-1">
              <span class="text-xl font-bold text-blue-600 font-mono">{{ stats.total_used_dids }}</span>
              <span class="text-xs text-gray-500 font-mono">/ {{ stats.total_allocated_dids }}</span>
            </div>
            <span class="text-[11px] text-gray-400 block">{{ $t("Assigned DIDs") }}</span>
          </div>

          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Reseller Credit Balance") }}</span>
            <span class="text-xl font-bold text-emerald-600 font-mono">${{ Number(profile.credit_balance || 0).toFixed(2) }}</span>
            <span class="text-[11px] text-gray-400 block">{{ profile.markup_pct }}% Wholesale Markup</span>
          </div>

          <div class="bg-gray-50 p-3.5 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("White-Label SMTP") }}</span>
            <div class="flex items-center gap-1.5 mt-1">
              <span
                class="w-2.5 h-2.5 rounded-full"
                :class="profile.smtp_enabled ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'"
              ></span>
              <span class="text-sm font-bold text-gray-900">
                {{ profile.smtp_enabled ? $t("Dedicated Active") : $t("Inherited Default") }}
              </span>
            </div>
            <span class="text-[11px] text-gray-400 block">Zero Vendor Leakage</span>
          </div>
        </div>

        <!-- Studio Navigation Tabs -->
        <div class="mt-6 border-b border-gray-200">
          <nav class="flex space-x-6">
            <button
              v-for="t in tabs"
              :key="t.id"
              @click="activeTab = t.id"
              :class="[
                activeTab === t.id
                  ? 'border-indigo-600 text-indigo-600 font-bold'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium',
                'whitespace-nowrap py-3 px-1 border-b-2 text-sm flex items-center gap-2 cursor-pointer transition-colors',
              ]"
            >
              <component :is="t.icon" class="w-4 h-4" />
              {{ t.name }}
            </button>
          </nav>
        </div>
      </div>

      <!-- Flash Notifications -->
      <div v-if="$page.props.flash?.message" class="p-4 bg-emerald-50 rounded-lg ring-1 ring-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
          <CheckCircleIcon class="w-5 h-5 text-emerald-600 flex-shrink-0" />
          <span>{{ $page.props.flash.message }}</span>
        </div>
        <button @click="$page.props.flash.message = null" class="text-emerald-500 hover:text-emerald-700 text-xs">✕</button>
      </div>

      <!-- ========================================== -->
      <!-- TAB 1: BRAND ASSETS & IDENTITY -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'brand'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <form @submit.prevent="submitProfile" class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3">
              <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <BuildingOffice2Icon class="w-5 h-5 text-indigo-600" />
                {{ $t("Platform Brand & Identity") }}
              </h2>
              <p class="text-xs text-gray-500">{{ $t("Custom branding applied to the top navigation, page titles, footer, and emails.") }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Company / Platform Name") }} *</label>
                <input
                  v-model="profileForm.company_name"
                  type="text"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="e.g. Acme Cloud Voice"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Custom Domain Hostname") }}</label>
                <input
                  v-model="profileForm.custom_domain_hostname"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                  placeholder="e.g. pbx.acmevoice.com"
                />
                <span class="text-[11px] text-gray-400 mt-0.5 block">When customers access this hostname, this branding loads automatically.</span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Support Email Address") }}</label>
                <input
                  v-model="profileForm.support_email"
                  type="email"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="support@acmevoice.com"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Support Phone Helpline") }}</label>
                <input
                  v-model="profileForm.support_phone"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="+1 (800) 555-0199"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Copyright Footer Notice") }}</label>
                <input
                  v-model="profileForm.copyright_text"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="© 2026 Acme Cloud Voice. All rights reserved."
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Terms of Service Link") }}</label>
                <input
                  v-model="profileForm.terms_url"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="https://acmevoice.com/terms"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Privacy Policy Link") }}</label>
                <input
                  v-model="profileForm.privacy_url"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="https://acmevoice.com/privacy"
                />
              </div>
            </div>

            <div class="flex justify-end pt-3 border-t border-gray-100">
              <button
                type="submit"
                :disabled="savingProfile"
                class="px-5 py-2 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm cursor-pointer disabled:opacity-50"
              >
                {{ savingProfile ? $t("Saving...") : $t("Save Brand Changes") }}
              </button>
            </div>
          </form>
        </div>

        <!-- Asset Upload Cards -->
        <div class="space-y-6">
          <!-- Logo Upload Box -->
          <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
              <PhotoIcon class="w-4 h-4 text-indigo-600" />
              {{ $t("Brand Logo (Navbar & Auth)") }}
            </h3>
            <div class="p-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg text-center">
              <img
                :src="profileForm.logo_url || '/storage/logo.png'"
                alt="Brand Logo"
                class="max-h-12 mx-auto object-contain mb-3"
              />
              <input
                type="file"
                ref="logoInput"
                accept="image/*"
                @change="handleFileUpload($event, 'logo')"
                class="hidden"
              />
              <button
                type="button"
                @click="$refs.logoInput.click()"
                :disabled="uploadingLogo"
                class="px-3 py-1.5 text-xs font-semibold rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm cursor-pointer"
              >
                {{ uploadingLogo ? $t("Uploading...") : $t("Upload Custom Logo") }}
              </button>
              <p class="text-[10px] text-gray-400 mt-1">PNG, SVG or WEBP with transparent background (Max 5MB)</p>
            </div>
          </div>

          <!-- Favicon Upload Box -->
          <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
              <GlobeAltIcon class="w-4 h-4 text-indigo-600" />
              {{ $t("Browser Favicon (32x32)") }}
            </h3>
            <div class="p-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg flex items-center justify-between">
              <div class="flex items-center gap-3">
                <img
                  :src="profileForm.favicon_url || '/storage/favicon-32x32.png'"
                  alt="Favicon"
                  class="w-8 h-8 rounded border border-gray-200 bg-white p-0.5 object-contain"
                />
                <div class="text-left">
                  <span class="text-xs font-semibold text-gray-700 block">Active Favicon</span>
                  <span class="text-[10px] text-gray-400">Shown in browser tab bar</span>
                </div>
              </div>
              <input
                type="file"
                ref="faviconInput"
                accept="image/png,image/x-icon,image/svg+xml"
                @change="handleFileUpload($event, 'favicon')"
                class="hidden"
              />
              <button
                type="button"
                @click="$refs.faviconInput.click()"
                :disabled="uploadingFavicon"
                class="px-3 py-1.5 text-xs font-semibold rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm cursor-pointer"
              >
                {{ uploadingFavicon ? $t("Uploading...") : $t("Replace") }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 2: LOGIN DESIGN STUDIO -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'login'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Login Settings Controls -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 space-y-5">
          <div class="border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
              <ComputerDesktopIcon class="w-5 h-5 text-indigo-600" />
              {{ $t("Login Page Studio & Accent Styling") }}
            </h2>
            <p class="text-xs text-gray-500">{{ $t("Customize how users and clients experience the sign-in portal on your domain.") }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Login Header Title") }}</label>
            <input
              v-model="profileForm.login_heading"
              type="text"
              class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="e.g. Sign in to your account"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Login Subheading Description") }}</label>
            <input
              v-model="profileForm.login_subheading"
              type="text"
              class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="e.g. Enterprise Cloud Telephony & Contact Center"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Brand Primary Accent Color") }}</label>
            <div class="flex items-center gap-3">
              <input
                v-model="profileForm.login_accent_color"
                type="color"
                class="h-9 w-12 rounded border border-gray-300 cursor-pointer p-0.5"
              />
              <input
                v-model="profileForm.login_accent_color"
                type="text"
                class="w-32 text-sm rounded-md border-gray-300 font-mono uppercase"
                placeholder="#4F46E5"
              />
              <!-- Preset Palette Dots -->
              <div class="flex items-center gap-1.5 ml-2">
                <button
                  v-for="color in presetColors"
                  :key="color"
                  type="button"
                  @click="profileForm.login_accent_color = color"
                  class="w-6 h-6 rounded-full border border-gray-300 shadow-xs cursor-pointer hover:scale-110 transition-transform"
                  :style="{ backgroundColor: color }"
                ></button>
              </div>
            </div>
            <span class="text-[11px] text-gray-400 mt-1 block">Applied to the sign-in submit button, focus highlights, and active elements.</span>
          </div>

          <div class="pt-2">
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Login Background Image URL (Optional)") }}</label>
            <div class="flex gap-2">
              <input
                v-model="profileForm.login_bg_image_url"
                type="text"
                class="flex-1 text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="https://.../bg-pattern.jpg"
              />
              <input
                type="file"
                ref="bgInput"
                accept="image/*"
                @change="handleFileUpload($event, 'login_bg')"
                class="hidden"
              />
              <button
                type="button"
                @click="$refs.bgInput.click()"
                class="px-3 py-1.5 text-xs font-semibold rounded-md bg-gray-100 border border-gray-300 text-gray-700 hover:bg-gray-200 cursor-pointer"
              >
                {{ $t("Upload Image") }}
              </button>
            </div>
          </div>

          <div class="flex justify-end pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="submitProfile"
              :disabled="savingProfile"
              class="px-5 py-2 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm cursor-pointer"
            >
              {{ savingProfile ? $t("Saving...") : $t("Save Login Design") }}
            </button>
          </div>
        </div>

        <!-- Live Interactive Login Preview -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 flex flex-col items-center justify-center">
          <div class="w-full flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
              <SwatchIcon class="w-4 h-4 text-indigo-600" />
              {{ $t("Live Interactive Customer Preview") }}
            </span>
            <span class="text-[11px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Real-Time Sync</span>
          </div>

          <!-- Scaled Simulated Login View -->
          <div
            class="w-full max-w-sm rounded-xl border border-gray-200 shadow-md p-6 bg-white relative overflow-hidden"
            :style="profileForm.login_bg_image_url ? { backgroundImage: `url(${profileForm.login_bg_image_url})`, backgroundSize: 'cover' } : {}"
          >
            <!-- Logo in Preview -->
            <div class="text-center mb-5">
              <img
                :src="profileForm.logo_url || '/storage/logo.png'"
                alt="Logo Preview"
                class="h-10 mx-auto object-contain"
              />
              <h2 class="text-base font-bold text-gray-900 mt-3">{{ profileForm.login_heading || 'Sign in to your account' }}</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ profileForm.login_subheading || 'Enterprise Cloud Telephony & Contact Center' }}</p>
            </div>

            <!-- Fake Form Inputs -->
            <div class="space-y-3">
              <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1">Username or Email</label>
                <div class="h-8 rounded border border-gray-300 bg-gray-50 px-2.5 flex items-center text-xs text-gray-400">user@company.com</div>
              </div>
              <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1">Password</label>
                <div class="h-8 rounded border border-gray-300 bg-gray-50 px-2.5 flex items-center text-xs text-gray-400">••••••••••••</div>
              </div>

              <!-- Submit button preview with dynamic accent color -->
              <button
                type="button"
                class="w-full py-2 rounded-md text-xs font-semibold text-white shadow-sm transition-opacity hover:opacity-90 mt-2"
                :style="{ backgroundColor: profileForm.login_accent_color || '#4f46e5' }"
              >
                Sign in
              </button>
            </div>

            <!-- Footer links in preview -->
            <div class="mt-4 pt-3 border-t border-gray-100 text-center">
              <span class="text-[10px] text-gray-400 block">{{ profileForm.copyright_text || '© 2026 Acme Cloud Voice' }}</span>
              <span class="text-[10px] text-indigo-600 mt-0.5 inline-block">Need help? {{ profileForm.support_email || 'support@company.com' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: CUSTOM SMTP & EMAIL DELIVERABILITY -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'smtp'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <form @submit.prevent="submitSmtp" class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                  <EnvelopeIcon class="w-5 h-5 text-indigo-600" />
                  {{ $t("Dedicated Outgoing SMTP Mail Server") }}
                </h2>
                <p class="text-xs text-gray-500">{{ $t("100% Zero-Vendor Leakage: Send voicemails, password resets, and notifications through your own email domain.") }}</p>
              </div>

              <!-- SMTP Enabled Toggle -->
              <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-600">{{ smtpForm.smtp_enabled ? $t("Enabled") : $t("Disabled") }}</span>
                <button
                  type="button"
                  @click="smtpForm.smtp_enabled = !smtpForm.smtp_enabled"
                  :class="smtpForm.smtp_enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                >
                  <span
                    :class="smtpForm.smtp_enabled ? 'translate-x-5' : 'translate-x-0'"
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                  />
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("SMTP Host") }} *</label>
                <input
                  v-model="smtpForm.smtp_host"
                  type="text"
                  :required="smtpForm.smtp_enabled"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                  placeholder="smtp.mailgun.org or mail.yourdomain.com"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("SMTP Port") }} *</label>
                <input
                  v-model="smtpForm.smtp_port"
                  type="number"
                  :required="smtpForm.smtp_enabled"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                  placeholder="587"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Encryption Protocol") }}</label>
                <select
                  v-model="smtpForm.smtp_encryption"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="tls">TLS (Recommended - Port 587)</option>
                  <option value="ssl">SSL (Port 465)</option>
                  <option value="none">None (Port 25)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("SMTP Username") }}</label>
                <input
                  v-model="smtpForm.smtp_username"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                  placeholder="postmaster@yourdomain.com"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("SMTP Password") }}</label>
                <input
                  v-model="smtpForm.smtp_password"
                  type="password"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="••••••••••••"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Sender From Email Address") }} *</label>
                <input
                  v-model="smtpForm.smtp_from_address"
                  type="email"
                  :required="smtpForm.smtp_enabled"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="no-reply@yourdomain.com"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Sender From Name") }} *</label>
                <input
                  v-model="smtpForm.smtp_from_name"
                  type="text"
                  :required="smtpForm.smtp_enabled"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Acme PBX Notifications"
                />
              </div>
            </div>

            <div class="flex justify-end pt-3 border-t border-gray-100">
              <button
                type="submit"
                :disabled="savingSmtp"
                class="px-5 py-2 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm cursor-pointer disabled:opacity-50"
              >
                {{ savingSmtp ? $t("Saving...") : $t("Save SMTP Settings") }}
              </button>
            </div>
          </form>
        </div>

        <!-- Live SMTP Diagnostic Box -->
        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
              <ShieldCheckIcon class="w-4 h-4 text-emerald-600" />
              {{ $t("Live SMTP Diagnostic Validator") }}
            </h3>
            <p class="text-xs text-gray-500">
              {{ $t("Send an instant test email to verify DNS authentication, STARTTLS handshake, and inbox delivery.") }}
            </p>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Recipient Test Email") }}</label>
              <input
                v-model="testEmailRecipient"
                type="email"
                class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="your-admin@example.com"
              />
            </div>

            <button
              type="button"
              @click="runSmtpTest"
              :disabled="runningSmtpTest || !testEmailRecipient"
              class="w-full py-2.5 px-4 text-xs font-semibold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <ArrowPathIcon v-if="runningSmtpTest" class="w-4 h-4 animate-spin" />
              <EnvelopeIcon v-else class="w-4 h-4" />
              {{ runningSmtpTest ? $t("Testing Connection...") : $t("Send Verification Test Email") }}
            </button>

            <!-- Test Result Banner -->
            <div
              v-if="smtpTestResult"
              class="p-3 rounded-lg text-xs"
              :class="smtpTestResult.success ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-800 ring-1 ring-rose-200'"
            >
              <div class="font-bold flex items-center gap-1.5 mb-1">
                <CheckCircleIcon v-if="smtpTestResult.success" class="w-4 h-4 text-emerald-600" />
                <ExclamationTriangleIcon v-else class="w-4 h-4 text-rose-600" />
                {{ smtpTestResult.success ? $t("SMTP Handshake Succeeded") : $t("SMTP Connection Failed") }}
              </div>
              <p class="text-[11px] leading-relaxed break-words font-mono">{{ smtpTestResult.message }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 4: SMS SENDER ID & FOOTERS -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'sms'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 space-y-5">
          <div class="border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
              <ChatBubbleBottomCenterTextIcon class="w-5 h-5 text-indigo-600" />
              {{ $t("Alphanumeric SMS Sender ID & Templates") }}
            </h2>
            <p class="text-xs text-gray-500">{{ $t("Deliver 2FA codes and text alerts with your company name instead of generic numbers.") }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Alphanumeric Sender ID (Max 11 chars)") }}</label>
            <input
              v-model="profileForm.sms_sender_id"
              type="text"
              maxlength="11"
              class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono uppercase"
              placeholder="e.g. ACME_PBX"
            />
            <span class="text-[11px] text-gray-400 mt-1 block">Displayed on customer mobile handsets as the sender header.</span>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("SMS Compliance Footer Text") }}</label>
            <textarea
              v-model="profileForm.sms_footer_text"
              rows="3"
              class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="e.g. Sent via Acme Voice. Reply STOP to unsubscribe."
            ></textarea>
            <span class="text-[11px] text-gray-400 mt-1 block">Appended to automated messages for carrier 10DLC compliance.</span>
          </div>

          <div class="flex justify-end pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="submitProfile"
              :disabled="savingProfile"
              class="px-5 py-2 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm cursor-pointer"
            >
              {{ savingProfile ? $t("Saving...") : $t("Save SMS Configuration") }}
            </button>
          </div>
        </div>

        <!-- Phone Preview -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 flex flex-col items-center justify-center">
          <div class="w-full flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $t("SMS Mobile Handset Preview") }}</span>
            <span class="text-[11px] text-gray-400">10DLC A2P Verified</span>
          </div>

          <!-- Smartphone Shell Simulation -->
          <div class="w-64 rounded-3xl border-4 border-gray-700 bg-gray-100 shadow-xl p-3">
            <div class="w-16 h-3 bg-gray-700 rounded-full mx-auto mb-3"></div>
            <!-- Header on phone -->
            <div class="text-center border-b border-gray-200 pb-2 mb-3">
              <span class="text-xs font-bold text-gray-800 tracking-wider font-mono">{{ profileForm.sms_sender_id || 'ZNERO_AUTH' }}</span>
              <span class="text-[9px] text-gray-400 block">Text Message</span>
            </div>

            <!-- Message bubble -->
            <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tl-xs text-xs shadow-xs space-y-1">
              <p>Your {{ profileForm.company_name || 'Acme Voice' }} verification code is: <span class="font-bold tracking-widest">849201</span></p>
              <p class="text-[10px] text-indigo-100 pt-1 border-t border-indigo-500/50">
                {{ profileForm.sms_footer_text || 'Reply STOP to cancel.' }}
              </p>
            </div>
            <span class="text-[9px] text-gray-400 block text-right mt-1 mr-1">Just now</span>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 5: RESELLER HIERARCHY & TENANTS -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'tenants'" class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5 pb-4 border-b border-gray-100">
            <div>
              <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <UsersIcon class="w-5 h-5 text-indigo-600" />
                {{ $t("Multi-Tier SaaS Hierarchy & Commercial Quotas") }}
              </h2>
              <p class="text-xs text-gray-500">
                {{ $t("Manage Sub-Resellers and Customer Companies with allocated extensions, DIDs, credit limits, and wholesale markup percentages.") }}
              </p>
            </div>

            <button
              @click="openNewTenantModal"
              class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors cursor-pointer"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("Add Sub-Reseller / Company Tenant") }}
            </button>
          </div>

          <!-- Hierarchy Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
              <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th scope="col" class="py-3 px-4">{{ $t("Organization & Domain") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Tier") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Extensions (Used / Allocated)") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Phone Numbers / DIDs") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Prepaid Balance") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Markup %") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Status") }}</th>
                  <th scope="col" class="py-3 px-4 text-right">{{ $t("Actions") }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-if="subResellers.length === 0">
                  <td colspan="8" class="text-center py-8 text-gray-400">
                    {{ $t("No sub-resellers or client company tenants found. Click 'Add Sub-Reseller / Company Tenant' to create your first client organization.") }}
                  </td>
                </tr>
                <tr v-for="tenant in subResellers" :key="tenant.uuid" class="hover:bg-gray-50/75 transition-colors">
                  <td class="py-3.5 px-4">
                    <div class="flex items-center gap-3">
                      <img
                        :src="tenant.logo_url || '/storage/logo.png'"
                        alt="Logo"
                        class="w-8 h-8 rounded border border-gray-200 p-0.5 object-contain bg-white"
                      />
                      <div>
                        <span class="font-bold text-gray-900 block text-sm">{{ tenant.company_name }}</span>
                        <span class="text-gray-400 font-mono text-[11px] block">{{ tenant.custom_domain_hostname || tenant.domain_name }}</span>
                      </div>
                    </div>
                  </td>

                  <td class="py-3.5 px-3">
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                      :class="tierBadgeClass(tenant.reseller_tier)"
                    >
                      {{ formatTierName(tenant.reseller_tier) }}
                    </span>
                  </td>

                  <td class="py-3.5 px-3">
                    <div class="space-y-1">
                      <div class="flex items-center justify-between text-[11px]">
                        <span class="font-bold text-gray-800">{{ tenant.used_extensions }} active</span>
                        <span class="text-gray-400">max {{ tenant.allocated_extensions }}</span>
                      </div>
                      <div class="w-28 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div
                          class="bg-indigo-600 h-1.5 rounded-full"
                          :style="{ width: Math.min(100, (tenant.used_extensions / (tenant.allocated_extensions || 1)) * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>

                  <td class="py-3.5 px-3">
                    <div class="space-y-1">
                      <div class="flex items-center justify-between text-[11px]">
                        <span class="font-bold text-gray-800">{{ tenant.used_dids }} active</span>
                        <span class="text-gray-400">max {{ tenant.allocated_did_numbers }}</span>
                      </div>
                      <div class="w-24 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div
                          class="bg-blue-600 h-1.5 rounded-full"
                          :style="{ width: Math.min(100, (tenant.used_dids / (tenant.allocated_did_numbers || 1)) * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>

                  <td class="py-3.5 px-3 font-mono font-bold text-emerald-600 text-sm">
                    ${{ Number(tenant.credit_balance || 0).toFixed(2) }}
                  </td>

                  <td class="py-3.5 px-3 font-mono text-gray-700">
                    +{{ tenant.markup_pct }}%
                  </td>

                  <td class="py-3.5 px-3">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                      :class="tenant.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                    >
                      {{ tenant.is_active ? $t("Active") : $t("Suspended") }}
                    </span>
                  </td>

                  <td class="py-3.5 px-4 text-right space-x-2">
                    <button
                      @click="editTenant(tenant)"
                      class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs cursor-pointer"
                    >
                      {{ $t("Manage Quotas") }}
                    </button>
                    <button
                      @click="deleteTenant(tenant)"
                      class="text-rose-600 hover:text-rose-900 font-semibold text-xs cursor-pointer"
                    >
                      {{ tenant.is_active ? $t("Suspend") : $t("Deactivate") }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODAL: CREATE NEW SUB-RESELLER / TENANT -->
      <!-- ========================================== -->
      <div v-if="showNewTenantModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
              <BuildingOffice2Icon class="w-5 h-5 text-indigo-600" />
              {{ $t("Create Sub-Reseller / Company Tenant") }}
            </h3>
            <button @click="showNewTenantModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitCreateTenant" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Organization Name") }} *</label>
                <input
                  v-model="newTenantForm.company_name"
                  type="text"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Apex Telecom LLC"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Reseller Tier") }} *</label>
                <select
                  v-model="newTenantForm.reseller_tier"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="sub_reseller">Sub-Reseller (Can resell further)</option>
                  <option value="client_tenant">Customer Company (End Organization)</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Domain Name (PBX)") }} *</label>
                <input
                  v-model="newTenantForm.domain_name"
                  type="text"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                  placeholder="apex.pbx.net"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Custom Hostname (Optional)") }}</label>
                <input
                  v-model="newTenantForm.custom_domain_hostname"
                  type="text"
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                  placeholder="phone.apextelecom.com"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Allocated Extensions Quota") }} *</label>
                <input
                  v-model="newTenantForm.allocated_extensions"
                  type="number"
                  min="1"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Allocated Phone Numbers (DIDs)") }} *</label>
                <input
                  v-model="newTenantForm.allocated_did_numbers"
                  type="number"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Prepaid Credit Balance ($)") }} *</label>
                <input
                  v-model="newTenantForm.credit_balance"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ $t("Wholesale Markup Rate (%)") }} *</label>
                <input
                  v-model="newTenantForm.markup_pct"
                  type="number"
                  step="0.5"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                />
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
              <button
                type="button"
                @click="showNewTenantModal = false"
                class="px-4 py-2 text-xs font-semibold rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 cursor-pointer"
              >
                {{ $t("Cancel") }}
              </button>
              <button
                type="submit"
                :disabled="creatingTenant"
                class="px-5 py-2 text-xs font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm cursor-pointer disabled:opacity-50"
              >
                {{ creatingTenant ? $t("Creating...") : $t("Provision Tenant") }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODAL: EDIT TENANT QUOTAS -->
      <!-- ========================================== -->
      <div v-if="editingTenantData" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">
              {{ $t("Edit Quotas:") }} {{ editingTenantData.company_name }}
            </h3>
            <button @click="editingTenantData = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitUpdateTenant" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">{{ $t("Extensions Quota") }}</label>
                <input
                  v-model="editingTenantData.allocated_extensions"
                  type="number"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 font-mono"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">{{ $t("DIDs Quota") }}</label>
                <input
                  v-model="editingTenantData.allocated_did_numbers"
                  type="number"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 font-mono"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">{{ $t("Credit Balance ($)") }}</label>
                <input
                  v-model="editingTenantData.credit_balance"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 font-mono"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">{{ $t("Markup %") }}</label>
                <input
                  v-model="editingTenantData.markup_pct"
                  type="number"
                  step="0.5"
                  min="0"
                  required
                  class="w-full text-sm rounded-md border-gray-300 font-mono"
                />
              </div>
            </div>

            <div class="flex items-center justify-between pt-2">
              <span class="text-xs font-semibold text-gray-700">{{ $t("Account Active Status") }}</span>
              <input
                v-model="editingTenantData.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-indigo-600 h-4 w-4"
              />
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
              <button
                type="button"
                @click="editingTenantData = null"
                class="px-4 py-2 text-xs font-semibold rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 cursor-pointer"
              >
                {{ $t("Cancel") }}
              </button>
              <button
                type="submit"
                :disabled="updatingTenant"
                class="px-5 py-2 text-xs font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer"
              >
                {{ updatingTenant ? $t("Updating...") : $t("Save Quotas") }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import MainLayout from "../../Layouts/MainLayout.vue";
import {
  PaintBrushIcon,
  GlobeAltIcon,
  BuildingOffice2Icon,
  PhotoIcon,
  ComputerDesktopIcon,
  EnvelopeIcon,
  ShieldCheckIcon,
  ChatBubbleBottomCenterTextIcon,
  UsersIcon,
  PlusIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ArrowPathIcon,
  SwatchIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  profile: Object,
  subResellers: Array,
  stats: Object,
  domains: Array,
  currentDomainUuid: String,
  routes: Object,
});

// Navigation tabs
const tabs = [
  { id: "brand", name: "Brand & Identity", icon: BuildingOffice2Icon },
  { id: "login", name: "Login Design Studio", icon: ComputerDesktopIcon },
  { id: "smtp", name: "Custom SMTP & Email", icon: EnvelopeIcon },
  { id: "sms", name: "SMS Sender ID", icon: ChatBubbleBottomCenterTextIcon },
  { id: "tenants", name: "Sub-Resellers & Tenants", icon: UsersIcon },
];

const activeTab = ref("brand");

// Check URL query param for default tab
onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get("tab");
  if (tabParam && tabs.some((t) => t.id === tabParam)) {
    activeTab.value = tabParam;
  }
});

// Preset Color Swatches
const presetColors = ["#4f46e5", "#0284c7", "#059669", "#d97706", "#dc2626", "#7c3aed", "#0f172a"];

// Brand Profile Form
const profileForm = reactive({
  uuid: props.profile.uuid,
  company_name: props.profile.company_name || "",
  custom_domain_hostname: props.profile.custom_domain_hostname || "",
  logo_url: props.profile.logo_url || "",
  favicon_url: props.profile.favicon_url || "",
  login_heading: props.profile.login_heading || "Sign in to your account",
  login_subheading: props.profile.login_subheading || "Enterprise Cloud Telephony & Contact Center",
  login_accent_color: props.profile.login_accent_color || "#4f46e5",
  login_bg_image_url: props.profile.login_bg_image_url || "",
  support_email: props.profile.support_email || "",
  support_phone: props.profile.support_phone || "",
  copyright_text: props.profile.copyright_text || "",
  terms_url: props.profile.terms_url || "",
  privacy_url: props.profile.privacy_url || "",
  sms_sender_id: props.profile.sms_sender_id || "",
  sms_footer_text: props.profile.sms_footer_text || "",
  markup_pct: props.profile.markup_pct ?? 25,
});

const savingProfile = ref(false);
const submitProfile = () => {
  savingProfile.value = true;
  router.post(props.routes.update_profile, profileForm, {
    preserveScroll: true,
    onFinish: () => {
      savingProfile.value = false;
    },
  });
};

// Asset Uploads
const uploadingLogo = ref(false);
const uploadingFavicon = ref(false);

const handleFileUpload = async (event, type) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("asset", file);
  formData.append("type", type);
  formData.append("profile_uuid", props.profile.uuid);

  if (type === "logo") uploadingLogo.value = true;
  else if (type === "favicon") uploadingFavicon.value = true;

  try {
    const res = await axios.post(props.routes.upload_asset, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    if (res.data?.success) {
      if (type === "logo") profileForm.logo_url = res.data.url;
      else if (type === "favicon") profileForm.favicon_url = res.data.url;
      else if (type === "login_bg") profileForm.login_bg_image_url = res.data.url;
    }
  } catch (err) {
    alert("Asset upload failed: " + (err.response?.data?.message || err.message));
  } finally {
    uploadingLogo.value = false;
    uploadingFavicon.value = false;
  }
};

// Dedicated SMTP Form
const smtpForm = reactive({
  uuid: props.profile.uuid,
  smtp_enabled: !!props.profile.smtp_enabled,
  smtp_host: props.profile.smtp_host || "",
  smtp_port: props.profile.smtp_port || 587,
  smtp_username: props.profile.smtp_username || "",
  smtp_password: "",
  smtp_encryption: props.profile.smtp_encryption || "tls",
  smtp_from_address: props.profile.smtp_from_address || "",
  smtp_from_name: props.profile.smtp_from_name || props.profile.company_name || "",
});

const savingSmtp = ref(false);
const submitSmtp = () => {
  savingSmtp.value = true;
  router.post(props.routes.update_smtp, smtpForm, {
    preserveScroll: true,
    onFinish: () => {
      savingSmtp.value = false;
    },
  });
};

// SMTP Diagnostic Validator
const testEmailRecipient = ref("");
const runningSmtpTest = ref(false);
const smtpTestResult = ref(null);

const runSmtpTest = async () => {
  if (!testEmailRecipient.value) return;
  runningSmtpTest.value = true;
  smtpTestResult.value = null;

  try {
    const res = await axios.post(props.routes.test_smtp, {
      uuid: props.profile.uuid,
      test_email: testEmailRecipient.value,
      smtp_host: smtpForm.smtp_host,
      smtp_port: smtpForm.smtp_port,
      smtp_username: smtpForm.smtp_username,
      smtp_password: smtpForm.smtp_password,
      smtp_encryption: smtpForm.smtp_encryption,
      smtp_from_address: smtpForm.smtp_from_address,
      smtp_from_name: smtpForm.smtp_from_name,
    });
    smtpTestResult.value = res.data;
  } catch (err) {
    smtpTestResult.value = {
      success: false,
      message: err.response?.data?.message || err.message,
    };
  } finally {
    runningSmtpTest.value = false;
  }
};

// Sub-Reseller & Tenant Management
const showNewTenantModal = ref(false);
const creatingTenant = ref(false);
const newTenantForm = reactive({
  parent_uuid: props.profile.uuid,
  company_name: "",
  domain_name: "",
  custom_domain_hostname: "",
  reseller_tier: "client_tenant",
  allocated_extensions: 20,
  allocated_did_numbers: 5,
  credit_balance: 100.0,
  markup_pct: 25.0,
});

const openNewTenantModal = () => {
  showNewTenantModal.value = true;
};

const submitCreateTenant = () => {
  creatingTenant.value = true;
  router.post(props.routes.create_tenant, newTenantForm, {
    preserveScroll: true,
    onSuccess: () => {
      showNewTenantModal.value = false;
      newTenantForm.company_name = "";
      newTenantForm.domain_name = "";
      newTenantForm.custom_domain_hostname = "";
    },
    onFinish: () => {
      creatingTenant.value = false;
    },
  });
};

// Edit Tenant Quotas
const editingTenantData = ref(null);
const updatingTenant = ref(false);

const editTenant = (tenant) => {
  editingTenantData.value = { ...tenant };
};

const submitUpdateTenant = () => {
  if (!editingTenantData.value) return;
  updatingTenant.value = true;
  const updateUrl = props.routes.update_tenant.replace("__UUID__", editingTenantData.value.uuid);

  router.put(
    updateUrl,
    {
      allocated_extensions: editingTenantData.value.allocated_extensions,
      allocated_did_numbers: editingTenantData.value.allocated_did_numbers,
      credit_balance: editingTenantData.value.credit_balance,
      markup_pct: editingTenantData.value.markup_pct,
      is_active: editingTenantData.value.is_active,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        editingTenantData.value = null;
      },
      onFinish: () => {
        updatingTenant.value = false;
      },
    }
  );
};

const deleteTenant = (tenant) => {
  if (!confirm(`Are you sure you want to deactivate tenant '${tenant.company_name}'?`)) return;
  const deleteUrl = props.routes.delete_tenant.replace("__UUID__", tenant.uuid);
  router.delete(deleteUrl, { preserveScroll: true });
};

// Utilities
const formatTierName = (tier) => {
  if (tier === "master_reseller") return "Master Reseller";
  if (tier === "sub_reseller") return "Sub-Reseller";
  if (tier === "client_tenant") return "Client Tenant";
  return tier;
};

const tierBadgeClass = (tier) => {
  if (tier === "master_reseller") return "bg-purple-100 text-purple-800 ring-1 ring-purple-300";
  if (tier === "sub_reseller") return "bg-indigo-100 text-indigo-800 ring-1 ring-indigo-300";
  return "bg-emerald-100 text-emerald-800 ring-1 ring-emerald-300";
};
</script>
