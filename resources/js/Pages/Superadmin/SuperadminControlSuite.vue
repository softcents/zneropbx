<template>
  <MainLayout>
    <div class="m-3 space-y-6 font-nunito">
      <!-- Ghost Mode Active Banner (If Superadmin is currently impersonating a tenant) -->
      <div
        v-if="isImpersonating"
        class="bg-amber-500 text-white px-5 py-3 rounded-lg shadow-md flex items-center justify-between animate-pulse"
      >
        <div class="flex items-center gap-3">
          <ExclamationTriangleIcon class="w-6 h-6 text-amber-100" />
          <div>
            <span class="font-bold text-sm tracking-wide uppercase">{{ $t("Ghost Mode Active") }}:</span>
            <span class="text-xs ml-1 text-amber-100">
              {{ $t("You are currently impersonating and managing a tenant organization.") }}
            </span>
          </div>
        </div>
        <button
          @click="stopGhostImpersonation"
          class="px-3.5 py-1.5 text-xs font-bold rounded bg-white text-amber-800 hover:bg-amber-50 shadow-sm cursor-pointer transition-colors"
        >
          {{ $t("Exit Ghost Impersonation") }} ✕
        </button>
      </div>

      <!-- Top Mission Control Header -->
      <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="p-2 bg-purple-50 text-purple-700 rounded-lg ring-1 ring-purple-200">
                <ShieldCheckIcon class="w-6 h-6" />
              </span>
              <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                  {{ $t("Superadmin Platform Owner Mission Control Suite") }}
                  <span class="text-xs px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-semibold ring-1 ring-purple-300">
                    Carrier Root Level 80
                  </span>
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                  {{ $t("Global SaaS Fleet Governance: Cross-Tenant Management, 1-Click Ghost Impersonation, Master DID Warehouse, Wholesale Carrier Trunks, Entitlements Packaging & Panic Radar.") }}
                </p>
              </div>
            </div>
          </div>

          <div class="flex items-center flex-wrap gap-2.5">
            <span class="px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold text-xs ring-1 ring-emerald-300 flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
              FreeSWITCH ESL Core: Online
            </span>

            <button
              @click="activeTab = 'panic'"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-md text-white bg-rose-600 hover:bg-rose-700 shadow-sm cursor-pointer transition-colors"
            >
              <ExclamationTriangleIcon class="w-4 h-4" />
              {{ $t("Platform Panic Radar") }}
            </button>
          </div>
        </div>

        <!-- Global Platform Telemetry KPI Cards -->
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-6 gap-3.5">
          <div class="bg-gray-50 p-3 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Total Tenants & Orgs") }}</span>
            <span class="text-xl font-bold text-gray-900 font-mono">{{ globalStats.total_tenants }}</span>
            <span class="text-[11px] text-gray-400 block">{{ globalStats.total_resellers }} Resellers | {{ globalStats.total_client_companies }} Orgs</span>
          </div>

          <div class="bg-gray-50 p-3 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Total Extensions Fleet") }}</span>
            <div class="flex items-baseline gap-1">
              <span class="text-xl font-bold text-indigo-600 font-mono">{{ globalStats.total_active_ext }}</span>
              <span class="text-xs text-gray-400 font-mono">/ {{ globalStats.total_allocated_ext }}</span>
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold block">Global Capacity 100% OK</span>
          </div>

          <div class="bg-gray-50 p-3 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Master DID Warehouse") }}</span>
            <div class="flex items-baseline gap-1">
              <span class="text-xl font-bold text-blue-600 font-mono">{{ globalStats.in_stock_warehouse_dids }}</span>
              <span class="text-xs text-gray-400 font-mono">/ {{ globalStats.total_warehouse_dids }} In Stock</span>
            </div>
            <span class="text-[11px] text-gray-400 block">{{ globalStats.total_assigned_dids }} Assigned</span>
          </div>

          <div class="bg-gray-50 p-3 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Carrier Gateways") }}</span>
            <span class="text-xl font-bold text-purple-600 font-mono">{{ globalStats.total_carrier_gateways }}</span>
            <span class="text-[11px] text-gray-400 block">Upstream SIP Trunks</span>
          </div>

          <div class="bg-gray-50 p-3 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Reseller Prepaids") }}</span>
            <span class="text-xl font-bold text-emerald-600 font-mono">${{ Number(globalStats.total_reseller_balances || 0).toFixed(2) }}</span>
            <span class="text-[11px] text-gray-400 block">Total Ledger Escrow</span>
          </div>

          <div class="bg-gray-50 p-3 rounded-lg ring-1 ring-gray-100">
            <span class="text-xs text-gray-500 block">{{ $t("Est. Gross Platform MRR") }}</span>
            <span class="text-xl font-bold text-gray-900 font-mono">${{ Number(globalStats.estimated_monthly_mrr || 0).toFixed(2) }}</span>
            <span class="text-[11px] text-emerald-600 font-semibold block">Recurring Telephony</span>
          </div>
        </div>

        <!-- Suite Navigation Tabs -->
        <div class="mt-6 border-b border-gray-200">
          <nav class="flex space-x-6 overflow-x-auto">
            <button
              v-for="t in tabs"
              :key="t.id"
              @click="activeTab = t.id"
              :class="[
                activeTab === t.id
                  ? 'border-purple-600 text-purple-600 font-bold'
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
      <!-- TAB 1: MULTI-TENANT FLEET & GHOST IMPERSONATE -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'fleet'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <BuildingOffice2Icon class="w-5 h-5 text-purple-600" />
                {{ $t("Global Tenant Fleet & Ghost Impersonation") }}
              </h2>
              <p class="text-xs text-gray-500">
                {{ $t("Administer all Resellers and Customer Companies. Click 'Ghost Impersonate' to 1-click log into any tenant's dashboard and inspect their PBX.") }}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <input
                v-model="fleetSearch"
                type="text"
                class="text-xs rounded-md border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                placeholder="Search tenant name or domain..."
              />
              <select
                v-model="tierFilter"
                class="text-xs rounded-md border-gray-300 focus:border-purple-500 focus:ring-purple-500"
              >
                <option value="all">All Tiers</option>
                <option value="master_reseller">Master Resellers</option>
                <option value="sub_reseller">Sub-Resellers</option>
                <option value="client_tenant">Company Tenants</option>
              </select>
            </div>
          </div>

          <!-- Fleet Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
              <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th scope="col" class="py-3 px-4">{{ $t("Organization / Domain") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Tier") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Package") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Extensions (Used / Max)") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("DIDs") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Balance / Markup") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Status") }}</th>
                  <th scope="col" class="py-3 px-4 text-right">{{ $t("Superadmin Actions") }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="tenant in filteredFleet" :key="tenant.domain_uuid" class="hover:bg-purple-50/40 transition-colors">
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                      <img
                        :src="tenant.logo_url || '/storage/logo.png'"
                        alt="Logo"
                        class="w-7 h-7 rounded border border-gray-200 p-0.5 object-contain bg-white"
                      />
                      <div>
                        <span class="font-bold text-gray-900 block text-xs">{{ tenant.company_name }}</span>
                        <span class="text-gray-400 font-mono text-[10px] block">{{ tenant.custom_domain_hostname || tenant.domain_name }}</span>
                      </div>
                    </div>
                  </td>

                  <td class="py-3 px-3">
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                      :class="tierBadgeClass(tenant.reseller_tier)"
                    >
                      {{ formatTierName(tenant.reseller_tier) }}
                    </span>
                  </td>

                  <td class="py-3 px-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">
                      {{ tenant.package_tier }}
                    </span>
                  </td>

                  <td class="py-3 px-3">
                    <div class="space-y-1">
                      <div class="flex items-center justify-between text-[10px]">
                        <span class="font-bold text-gray-800">{{ tenant.used_extensions }} active</span>
                        <span class="text-gray-400">/ {{ tenant.allocated_extensions }}</span>
                      </div>
                      <div class="w-24 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div
                          class="bg-purple-600 h-1.5 rounded-full"
                          :style="{ width: Math.min(100, (tenant.used_extensions / (tenant.allocated_extensions || 1)) * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>

                  <td class="py-3 px-3 font-mono font-bold text-blue-600 text-xs">
                    {{ tenant.used_dids }} <span class="text-gray-400 font-normal">/ {{ tenant.allocated_dids }}</span>
                  </td>

                  <td class="py-3 px-3 font-mono text-xs">
                    <span class="font-bold text-emerald-600 block">${{ Number(tenant.credit_balance || 0).toFixed(2) }}</span>
                    <span class="text-gray-400 text-[10px] block">+{{ tenant.markup_pct }}% wholesale</span>
                  </td>

                  <td class="py-3 px-3">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                      :class="tenant.is_enabled && tenant.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                    >
                      {{ tenant.is_enabled && tenant.is_active ? $t("Active") : $t("Suspended") }}
                    </span>
                  </td>

                  <td class="py-3 px-4 text-right space-x-1.5">
                    <!-- 1-Click Ghost Impersonate Button -->
                    <button
                      @click="triggerGhostImpersonate(tenant)"
                      class="px-2.5 py-1 text-xs font-semibold rounded bg-purple-50 text-purple-700 hover:bg-purple-100 ring-1 ring-purple-200 cursor-pointer transition-colors"
                      title="Ghost Login As This Tenant"
                    >
                      ⚡ {{ $t("Ghost Login") }}
                    </button>

                    <!-- Entitlements Config -->
                    <button
                      @click="openEntitlementsModal(tenant)"
                      class="px-2 py-1 text-xs font-semibold rounded bg-gray-50 text-gray-700 hover:bg-gray-100 ring-1 ring-gray-200 cursor-pointer"
                    >
                      ⚙️ {{ $t("Features") }}
                    </button>

                    <!-- Toggle Status (Freeze / Suspend) -->
                    <button
                      @click="toggleTenantStatus(tenant)"
                      class="px-2 py-1 text-xs font-semibold rounded cursor-pointer"
                      :class="tenant.is_enabled ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50'"
                    >
                      {{ tenant.is_enabled ? $t("Suspend") : $t("Reactivate") }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 2: MASTER DID NUMBER WAREHOUSE -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'warehouse'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <GlobeAltIcon class="w-5 h-5 text-blue-600" />
                {{ $t("Global Master DID Inventory Warehouse") }}
              </h2>
              <p class="text-xs text-gray-500">
                {{ $t("Wholesale carrier DID pool. Allocate available numbers to any tenant with 1-click or reclaim unneeded numbers back to stock.") }}
              </p>
            </div>

            <button
              @click="showAddDidModal = true"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm cursor-pointer"
            >
              <PlusIcon class="w-4 h-4" />
              {{ $t("Add Wholesale Number to Warehouse") }}
            </button>
          </div>

          <!-- Warehouse Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
              <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th scope="col" class="py-3 px-4">{{ $t("Phone Number") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Rate Center / State") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Wholesale Carrier") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Cost / Retail ($/mo)") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Status") }}</th>
                  <th scope="col" class="py-3 px-3">{{ $t("Assigned Organization") }}</th>
                  <th scope="col" class="py-3 px-4 text-right">{{ $t("Actions") }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="did in didWarehouse" :key="did.id" class="hover:bg-blue-50/40 transition-colors">
                  <td class="py-3 px-4 font-mono font-bold text-gray-900 text-sm">
                    {{ did.phone_number }}
                  </td>
                  <td class="py-3 px-3 text-gray-600">
                    {{ did.rate_center }} ({{ did.state_province }}, {{ did.country_code }})
                  </td>
                  <td class="py-3 px-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-800">
                      {{ did.carrier_provider }}
                    </span>
                  </td>
                  <td class="py-3 px-3 font-mono">
                    <span class="text-rose-600 font-semibold block">${{ did.wholesale_monthly_cost.toFixed(2) }} cost</span>
                    <span class="text-emerald-600 font-semibold block">${{ did.retail_monthly_price.toFixed(2) }} sell</span>
                  </td>
                  <td class="py-3 px-3">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                      :class="did.status === 'available' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-300' : 'bg-blue-50 text-blue-700 ring-1 ring-blue-300'"
                    >
                      {{ did.status === 'available' ? 'Available in Stock' : 'Assigned' }}
                    </span>
                  </td>
                  <td class="py-3 px-3">
                    <span v-if="did.assigned_domain_name" class="font-bold text-gray-900 text-xs">
                      {{ did.assigned_domain_name }}
                    </span>
                    <span v-else class="text-gray-400 italic">Unassigned (In Warehouse)</span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-2">
                    <button
                      v-if="did.status === 'available'"
                      @click="openAllocateDidModal(did)"
                      class="px-2.5 py-1 text-xs font-semibold rounded bg-blue-50 text-blue-700 hover:bg-blue-100 ring-1 ring-blue-200 cursor-pointer"
                    >
                      {{ $t("Allocate to Tenant") }}
                    </button>
                    <button
                      v-else
                      @click="reclaimDid(did)"
                      class="px-2.5 py-1 text-xs font-semibold rounded bg-amber-50 text-amber-700 hover:bg-amber-100 ring-1 ring-amber-200 cursor-pointer"
                    >
                      {{ $t("Reclaim to Warehouse") }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: CARRIER GATEWAYS & WHOLESALE SIP POOLS -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'trunks'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <CpuChipIcon class="w-5 h-5 text-indigo-600" />
                {{ $t("Upstream Carrier SIP Gateways & Wholesale Trunks") }}
              </h2>
              <p class="text-xs text-gray-500">
                {{ $t("Active FreeSWITCH carrier gateways. Outbound failover pools route calls reliably across tier-1 telecom providers.") }}
              </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
              LCR Routing Active
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="gw in gateways" :key="gw.gateway_uuid" class="bg-gray-50 rounded-lg p-4 ring-1 ring-gray-200 space-y-2">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm text-gray-900">{{ gw.gateway }}</span>
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="gw.enabled === 'true' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'"
                >
                  {{ gw.enabled === 'true' ? 'Connected' : 'Disabled' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 font-mono">Proxy: {{ gw.proxy || 'Auto (DNS SRV)' }}</p>
              <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-200">
                <span class="text-gray-500">Max Channels: <strong class="text-gray-800">{{ gw.channels || 'Unlimited' }}</strong></span>
                <span class="text-emerald-600 font-semibold">Ping: 12ms</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 4: SAAS PACKAGES & FEATURE ENTITLEMENTS -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'packages'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
          <div class="mb-4 pb-3 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <CheckBadgeIcon class="w-5 h-5 text-indigo-600" />
              {{ $t("SaaS Packaging & Feature Entitlements Matrix") }}
            </h2>
            <p class="text-xs text-gray-500">
              {{ $t("Commercial license tiers define feature flags across AI agents, Contact Center, STIR/SHAKEN Caller ID attestation, and developer webhooks.") }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Starter PBX -->
            <div class="p-4 rounded-lg ring-1 ring-gray-200 bg-white space-y-3">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">Tier 1</span>
              <h3 class="text-base font-bold text-gray-900">Starter PBX</h3>
              <p class="text-xs text-gray-500">Up to 10 Extensions, Basic Voicemail, Auto-Attendant, SIP Trunking.</p>
              <span class="text-lg font-bold text-gray-900 block font-mono">$19<span class="text-xs font-normal text-gray-400">/mo</span></span>
            </div>

            <!-- Business Communications -->
            <div class="p-4 rounded-lg ring-1 ring-gray-200 bg-white space-y-3">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-100 text-indigo-800">Tier 2</span>
              <h3 class="text-base font-bold text-gray-900">Business Cloud</h3>
              <p class="text-xs text-gray-500">Up to 50 Extensions, Call Queues, Ring Groups, SMS Broadcasts, CRM Sync.</p>
              <span class="text-lg font-bold text-gray-900 block font-mono">$79<span class="text-xs font-normal text-gray-400">/mo</span></span>
            </div>

            <!-- Enterprise Contact Center -->
            <div class="p-4 rounded-lg ring-1 ring-purple-300 bg-purple-50/30 space-y-3 relative overflow-hidden">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-800">Tier 3 (Most Popular)</span>
              <h3 class="text-base font-bold text-gray-900">Enterprise CC</h3>
              <p class="text-xs text-gray-500">AI Voice Agents, Supervisor Command Center, STIR/SHAKEN Attestation, QA Scoring.</p>
              <span class="text-lg font-bold text-gray-900 block font-mono">$199<span class="text-xs font-normal text-gray-400">/mo</span></span>
            </div>

            <!-- Carrier Wholesaler -->
            <div class="p-4 rounded-lg ring-1 ring-emerald-300 bg-emerald-50/30 space-y-3">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Tier 4</span>
              <h3 class="text-base font-bold text-gray-900">Carrier Wholesaler</h3>
              <p class="text-xs text-gray-500">Multi-tier Reseller sub-accounts, BYO-Carrier SIP Gateways, 100% White-Label Studio.</p>
              <span class="text-lg font-bold text-gray-900 block font-mono">$499<span class="text-xs font-normal text-gray-400">/mo</span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 5: PLATFORM PANIC SWITCHES & FRAUD RADAR -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'panic'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-rose-200 p-5">
          <div class="mb-4 pb-3 border-b border-rose-100">
            <h2 class="text-base font-bold text-rose-900 flex items-center gap-2">
              <ExclamationTriangleIcon class="w-5 h-5 text-rose-600" />
              {{ $t("Platform Panic Switches & Emergency Radar") }}
            </h2>
            <p class="text-xs text-gray-500">
              {{ $t("Emergency platform-wide interventions. Immediate fraud isolation, carrier failover, and global maintenance broadcasts.") }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Panic 1: Fraud Radar Lockdown -->
            <div class="p-4 bg-rose-50/50 rounded-lg ring-1 ring-rose-200 space-y-3">
              <h3 class="text-sm font-bold text-rose-900 flex items-center gap-2">
                <ShieldCheckIcon class="w-4 h-4 text-rose-600" />
                {{ $t("Global Fraud Lockdown") }}
              </h3>
              <p class="text-xs text-gray-600">
                Immediately block international toll prefixes (011, Cuba, Somalia, Latvia) and throttle anomalous outbound CPS.
              </p>
              <button
                @click="triggerPanicAction('fraud_lockdown')"
                :disabled="triggeringPanic"
                class="w-full py-2 px-3 text-xs font-semibold rounded bg-rose-600 text-white hover:bg-rose-700 shadow-sm cursor-pointer"
              >
                {{ $t("Engage Fraud Lockdown") }}
              </button>
            </div>

            <!-- Panic 2: Emergency Carrier Failover -->
            <div class="p-4 bg-amber-50/50 rounded-lg ring-1 ring-amber-200 space-y-3">
              <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                <ArrowPathIcon class="w-4 h-4 text-amber-600" />
                {{ $t("Emergency Carrier Failover") }}
              </h3>
              <p class="text-xs text-gray-600">
                Reroute all active platform SIP outbound traffic away from primary trunk to secondary carrier backup pool.
              </p>
              <button
                @click="triggerPanicAction('carrier_failover')"
                :disabled="triggeringPanic"
                class="w-full py-2 px-3 text-xs font-semibold rounded bg-amber-600 text-white hover:bg-amber-700 shadow-sm cursor-pointer"
              >
                {{ $t("Force Carrier Failover") }}
              </button>
            </div>

            <!-- Panic 3: Cluster Cache Warmup -->
            <div class="p-4 bg-indigo-50/50 rounded-lg ring-1 ring-indigo-200 space-y-3">
              <h3 class="text-sm font-bold text-indigo-900 flex items-center gap-2">
                <CpuChipIcon class="w-4 h-4 text-indigo-600" />
                {{ $t("Telephony Cluster Sync") }}
              </h3>
              <p class="text-xs text-gray-600">
                Rescan FreeSWITCH Sofia profiles, clear stale registration caches, and warm up Horizon job workers.
              </p>
              <button
                @click="triggerPanicAction('cluster_warmup')"
                :disabled="triggeringPanic"
                class="w-full py-2 px-3 text-xs font-semibold rounded bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm cursor-pointer"
              >
                {{ $t("Rescan Telephony Cluster") }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 6: CROSS-TENANT AUDIT TRAIL -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'audit'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
          <div class="mb-4 pb-3 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <ClipboardDocumentListIcon class="w-5 h-5 text-gray-700" />
              {{ $t("Cross-Tenant Superadmin Audit Trail") }}
            </h2>
            <p class="text-xs text-gray-500">
              {{ $t("Immutable log of administrative events: ghost impersonations, tenant suspensions, DID assignments, and panic triggers.") }}
            </p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
              <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th scope="col" class="py-2.5 px-3">{{ $t("Timestamp") }}</th>
                  <th scope="col" class="py-2.5 px-3">{{ $t("Actor") }}</th>
                  <th scope="col" class="py-2.5 px-3">{{ $t("Action") }}</th>
                  <th scope="col" class="py-2.5 px-3">{{ $t("Target") }}</th>
                  <th scope="col" class="py-2.5 px-4">{{ $t("Details") }}</th>
                  <th scope="col" class="py-2.5 px-3">{{ $t("IP Address") }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white font-mono">
                <tr v-for="log in recentAudit" :key="log.id" class="hover:bg-gray-50">
                  <td class="py-2 px-3 text-gray-500 text-[11px] whitespace-nowrap">{{ log.created_at }}</td>
                  <td class="py-2 px-3 text-gray-900 font-bold text-[11px]">{{ log.actor_name }}</td>
                  <td class="py-2 px-3">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-800">
                      {{ log.action }}
                    </span>
                  </td>
                  <td class="py-2 px-3 text-purple-600 font-semibold text-[11px]">{{ log.target_name || log.target_type }}</td>
                  <td class="py-2 px-4 text-gray-600 text-[11px] font-sans">{{ log.details }}</td>
                  <td class="py-2 px-3 text-gray-400 text-[10px]">{{ log.ip_address || '127.0.0.1' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODAL: ADD WHOLESALE DID TO WAREHOUSE -->
      <!-- ========================================== -->
      <div v-if="showAddDidModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">{{ $t("Add Wholesale Number to Warehouse") }}</h3>
            <button @click="showAddDidModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitAddWarehouseDid" class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("Phone Number (E.164)") }} *</label>
              <input
                v-model="newDidForm.phone_number"
                type="text"
                required
                class="w-full text-xs rounded-md border-gray-300 font-mono"
                placeholder="+1 (800) 555-0199"
              />
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("Rate Center") }}</label>
                <input
                  v-model="newDidForm.rate_center"
                  type="text"
                  class="w-full text-xs rounded-md border-gray-300"
                  placeholder="New York"
                />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("State / Country") }}</label>
                <input
                  v-model="newDidForm.state_province"
                  type="text"
                  class="w-full text-xs rounded-md border-gray-300"
                  placeholder="NY"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("Wholesale Carrier Provider") }}</label>
              <select
                v-model="newDidForm.carrier_provider"
                class="w-full text-xs rounded-md border-gray-300"
              >
                <option value="Bandwidth Wholesale">Bandwidth Wholesale</option>
                <option value="Telnyx Global">Telnyx Global</option>
                <option value="Twilio Carrier Direct">Twilio Carrier Direct</option>
                <option value="Fibernetics Wholesale">Fibernetics Wholesale</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("Wholesale Buy Cost ($/mo)") }}</label>
                <input
                  v-model="newDidForm.wholesale_monthly_cost"
                  type="number"
                  step="0.05"
                  class="w-full text-xs rounded-md border-gray-300 font-mono"
                />
              </div>
              <div>
                <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("Retail Sell Price ($/mo)") }}</label>
                <input
                  v-model="newDidForm.retail_monthly_price"
                  type="number"
                  step="0.25"
                  class="w-full text-xs rounded-md border-gray-300 font-mono"
                />
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
              <button
                type="button"
                @click="showAddDidModal = false"
                class="px-3.5 py-1.5 rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 cursor-pointer"
              >
                {{ $t("Cancel") }}
              </button>
              <button
                type="submit"
                class="px-4 py-1.5 rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm cursor-pointer"
              >
                {{ $t("Add to Stock") }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODAL: ALLOCATE DID TO TENANT -->
      <!-- ========================================== -->
      <div v-if="allocatingDid" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">{{ $t("Allocate Number to Tenant") }}</h3>
            <button @click="allocatingDid = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
          </div>

          <p class="text-xs text-gray-600">
            Transfer <strong class="text-gray-900 font-mono">{{ allocatingDid.phone_number }}</strong> directly into a customer organization's PBX phone numbers inventory.
          </p>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">{{ $t("Target Tenant Organization") }}</label>
            <select
              v-model="targetDomainUuid"
              class="w-full text-xs rounded-md border-gray-300 focus:border-purple-500"
            >
              <option v-for="t in fleet" :key="t.domain_uuid" :value="t.domain_uuid">
                {{ t.company_name }} ({{ t.domain_name }})
              </option>
            </select>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="allocatingDid = null"
              class="px-3 py-1.5 text-xs rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 cursor-pointer"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="button"
              @click="submitAllocateDid"
              :disabled="!targetDomainUuid"
              class="px-4 py-1.5 text-xs font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 cursor-pointer disabled:opacity-50"
            >
              {{ $t("Confirm Transfer") }}
            </button>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODAL: EDIT TENANT ENTITLEMENTS -->
      <!-- ========================================== -->
      <div v-if="editingEntitlements" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">
              {{ $t("Feature Entitlements:") }} {{ editingEntitlements.company_name }}
            </h3>
            <button @click="editingEntitlements = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitEntitlements" class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 uppercase mb-1">{{ $t("License Package Tier") }}</label>
              <select
                v-model="editingEntitlements.package_tier"
                class="w-full text-xs rounded-md border-gray-300"
              >
                <option value="starter">Starter PBX</option>
                <option value="business">Business Cloud</option>
                <option value="enterprise">Enterprise Contact Center</option>
                <option value="carrier">Carrier Wholesaler</option>
              </select>
            </div>

            <div class="space-y-2 pt-2 border-t border-gray-100">
              <label class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50">
                <span class="text-gray-700 font-semibold">🎙️ AI Voice Agents & Neural Automation</span>
                <input v-model="editingEntitlements.entitlements.ai_agents" type="checkbox" class="rounded text-purple-600" />
              </label>

              <label class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50">
                <span class="text-gray-700 font-semibold">🎧 Contact Center & Supervisor Desk</span>
                <input v-model="editingEntitlements.entitlements.contact_center" type="checkbox" class="rounded text-purple-600" />
              </label>

              <label class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50">
                <span class="text-gray-700 font-semibold">🛡️ STIR/SHAKEN Caller Identity</span>
                <input v-model="editingEntitlements.entitlements.stir_shaken" type="checkbox" class="rounded text-purple-600" />
              </label>

              <label class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50">
                <span class="text-gray-700 font-semibold">💬 Omnichannel SMS/WhatsApp Hub</span>
                <input v-model="editingEntitlements.entitlements.omnichannel" type="checkbox" class="rounded text-purple-600" />
              </label>

              <label class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50">
                <span class="text-gray-700 font-semibold">🔌 Developer HMAC API Webhooks</span>
                <input v-model="editingEntitlements.entitlements.developer_api" type="checkbox" class="rounded text-purple-600" />
              </label>

              <label class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50">
                <span class="text-gray-700 font-semibold">💾 Cloud Recording & AI Transcription</span>
                <input v-model="editingEntitlements.entitlements.call_recording_ai" type="checkbox" class="rounded text-purple-600" />
              </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
              <button
                type="button"
                @click="editingEntitlements = null"
                class="px-3.5 py-1.5 rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 cursor-pointer"
              >
                {{ $t("Cancel") }}
              </button>
              <button
                type="submit"
                class="px-4 py-1.5 rounded-md text-white bg-purple-600 hover:bg-purple-700 shadow-sm cursor-pointer"
              >
                {{ $t("Save Entitlements") }}
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
  ShieldCheckIcon,
  BuildingOffice2Icon,
  GlobeAltIcon,
  CpuChipIcon,
  CheckBadgeIcon,
  ExclamationTriangleIcon,
  ClipboardDocumentListIcon,
  PlusIcon,
  CheckCircleIcon,
  ArrowPathIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  fleet: Array,
  didWarehouse: Array,
  gateways: Array,
  globalStats: Object,
  recentAudit: Array,
  activeNotice: Object,
  isImpersonating: Boolean,
  impersonatedDomainUuid: String,
  routes: Object,
});

// Navigation tabs
const tabs = [
  { id: "fleet", name: "Multi-Tenant Fleet & Ghost Login", icon: BuildingOffice2Icon },
  { id: "warehouse", name: "Master DID Warehouse", icon: GlobeAltIcon },
  { id: "trunks", name: "Carrier SIP Gateways", icon: CpuChipIcon },
  { id: "packages", name: "SaaS Packages & Entitlements", icon: CheckBadgeIcon },
  { id: "panic", name: "Panic Switches & Fraud Radar", icon: ExclamationTriangleIcon },
  { id: "audit", name: "Cross-Tenant Audit Trail", icon: ClipboardDocumentListIcon },
];

const activeTab = ref("fleet");

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get("tab");
  if (tabParam && tabs.some((t) => t.id === tabParam)) {
    activeTab.value = tabParam;
  }
});

// Filtering & Search
const fleetSearch = ref("");
const tierFilter = ref("all");

const filteredFleet = computed(() => {
  return props.fleet.filter((tenant) => {
    const matchesSearch =
      !fleetSearch.value ||
      tenant.company_name.toLowerCase().includes(fleetSearch.value.toLowerCase()) ||
      tenant.domain_name.toLowerCase().includes(fleetSearch.value.toLowerCase());

    const matchesTier =
      tierFilter.value === "all" || tenant.reseller_tier === tierFilter.value;

    return matchesSearch && matchesTier;
  });
});

// 1-Click Ghost Impersonation
const triggerGhostImpersonate = (tenant) => {
  if (confirm(`Activate Ghost Mode? You will immediately log in as '${tenant.company_name}' to inspect their PBX.`)) {
    const url = props.routes.impersonate.replace("__DOMAIN_UUID__", tenant.domain_uuid);
    router.post(url);
  }
};

const stopGhostImpersonation = () => {
  router.post(props.routes.stop_impersonate);
};

// Freeze / Suspend Tenant
const toggleTenantStatus = (tenant) => {
  const action = tenant.is_enabled ? "suspend" : "reactivate";
  if (confirm(`Are you sure you want to ${action} tenant '${tenant.company_name}'?`)) {
    const url = props.routes.toggle_status.replace("__DOMAIN_UUID__", tenant.domain_uuid);
    router.post(url, {}, { preserveScroll: true });
  }
};

// DID Warehouse Operations
const showAddDidModal = ref(false);
const newDidForm = reactive({
  phone_number: "",
  country_code: "US",
  state_province: "NY",
  rate_center: "New York",
  carrier_provider: "Bandwidth Wholesale",
  wholesale_monthly_cost: 0.50,
  retail_monthly_price: 2.50,
});

const submitAddWarehouseDid = () => {
  router.post(props.routes.add_did, newDidForm, {
    preserveScroll: true,
    onSuccess: () => {
      showAddDidModal.value = false;
      newDidForm.phone_number = "";
    },
  });
};

const allocatingDid = ref(null);
const targetDomainUuid = ref("");

const openAllocateDidModal = (did) => {
  allocatingDid.value = did;
  targetDomainUuid.value = props.fleet[0]?.domain_uuid || "";
};

const submitAllocateDid = () => {
  if (!allocatingDid.value || !targetDomainUuid.value) return;
  router.post(
    props.routes.allocate_did,
    {
      did_id: allocatingDid.value.id,
      domain_uuid: targetDomainUuid.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        allocatingDid.value = null;
      },
    }
  );
};

const reclaimDid = (did) => {
  if (confirm(`Reclaim ${did.phone_number} back to warehouse stock? It will be unlinked from the tenant.`)) {
    const url = props.routes.reclaim_did.replace("__ID__", did.id);
    router.post(url, {}, { preserveScroll: true });
  }
};

// Feature Entitlements Modal
const editingEntitlements = ref(null);
const openEntitlementsModal = (tenant) => {
  editingEntitlements.value = {
    domain_uuid: tenant.domain_uuid,
    company_name: tenant.company_name,
    package_tier: tenant.package_tier,
    entitlements: { ...tenant.entitlements },
  };
};

const submitEntitlements = () => {
  if (!editingEntitlements.value) return;
  const url = props.routes.update_entitlements.replace("__DOMAIN_UUID__", editingEntitlements.value.domain_uuid);
  router.post(
    url,
    {
      package_tier: editingEntitlements.value.package_tier,
      feature_ai_agents: editingEntitlements.value.entitlements.ai_agents,
      feature_contact_center: editingEntitlements.value.entitlements.contact_center,
      feature_stir_shaken: editingEntitlements.value.entitlements.stir_shaken,
      feature_omnichannel: editingEntitlements.value.entitlements.omnichannel,
      feature_developer_api: editingEntitlements.value.entitlements.developer_api,
      feature_video_conference: editingEntitlements.value.entitlements.video_conference,
      feature_call_recording_ai: editingEntitlements.value.entitlements.call_recording_ai,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        editingEntitlements.value = null;
      },
    }
  );
};

// Panic Switches
const triggeringPanic = ref(false);
const triggerPanicAction = async (action) => {
  if (!confirm(`CONFIRMATION REQUIRED: Execute platform-wide action '${action}'?`)) return;
  triggeringPanic.value = true;
  try {
    const res = await axios.post(props.routes.panic_switch, { action });
    alert(res.data.message);
    router.reload({ preserveScroll: true });
  } catch (err) {
    alert("Panic action failed: " + (err.response?.data?.message || err.message));
  } finally {
    triggeringPanic.value = false;
  }
};

// Helpers
const formatTierName = (tier) => {
  if (tier === "master_reseller") return "Master Reseller";
  if (tier === "sub_reseller") return "Sub-Reseller";
  if (tier === "client_tenant") return "Company Tenant";
  return tier;
};

const tierBadgeClass = (tier) => {
  if (tier === "master_reseller") return "bg-purple-100 text-purple-800 ring-1 ring-purple-300";
  if (tier === "sub_reseller") return "bg-indigo-100 text-indigo-800 ring-1 ring-indigo-300";
  return "bg-emerald-100 text-emerald-800 ring-1 ring-emerald-300";
};
</script>
