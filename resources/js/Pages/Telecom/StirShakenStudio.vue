<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Dark Spotlight Header Banner -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 shadow-xl text-white">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-semibold text-indigo-300 ring-1 ring-inset ring-indigo-500/30">
                <ShieldCheckIcon class="h-4 w-4 text-indigo-400" />
                FCC &amp; CRTC Mandate Compliant (RFC 8588)
              </span>
              <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-300 ring-1 ring-inset ring-emerald-500/30">
                FreeSWITCH ESL Connected
              </span>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
              STIR/SHAKEN Caller Identity &amp; PASSporT Studio
            </h1>
            <p class="max-w-3xl text-sm text-slate-300">
              Cryptographic call authentication, ES256 PASSporT signing, attestation policy scoring (A, B, C), and automated SIP <code class="font-mono text-indigo-300">Identity</code> header injection for FreeSWITCH carrier bridges.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <button
              @click="openCertModal = true"
              class="inline-flex items-center gap-2 rounded-xl bg-slate-800/90 px-4 py-2.5 text-xs font-semibold text-white shadow-sm ring-1 ring-white/10 hover:bg-slate-700 transition"
            >
              <KeyIcon class="h-4 w-4 text-indigo-400" />
              New EC P-256 Keypair
            </button>
            <button
              @click="activeTab = 'simulator'"
              class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md hover:bg-indigo-500 transition"
            >
              <SparklesIcon class="h-4 w-4" />
              Live PASSporT Generator
            </button>
          </div>
        </div>

        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-64 w-64 rounded-full bg-sky-500/10 blur-3xl"></div>
      </div>

      <!-- Quick Telemetry KPI Matrix -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">Full Attestation Rate (Level A)</span>
            <span class="rounded-md bg-emerald-50 p-1 text-emerald-600 ring-1 ring-emerald-500/20">
              <CheckBadgeIcon class="h-5 w-5" />
            </span>
          </div>
          <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900">{{ metrics.full_attestation_pct }}%</p>
          <p class="mt-1 text-xs text-gray-500">{{ metrics.attestation_a_count }} of {{ metrics.total_signed_calls }} calls fully attested</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">Active X.509 Certificates</span>
            <span class="rounded-md bg-indigo-50 p-1 text-indigo-600 ring-1 ring-indigo-500/20">
              <KeyIcon class="h-5 w-5" />
            </span>
          </div>
          <p class="mt-2 text-2xl font-bold tracking-tight text-indigo-600">{{ metrics.active_certificates }} Active</p>
          <p class="mt-1 text-xs text-gray-500">ECDSA prime256v1 (ES256)</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">Total Signed Outbound Calls</span>
            <span class="rounded-md bg-sky-50 p-1 text-sky-600 ring-1 ring-sky-500/20">
              <PhoneArrowUpRightIcon class="h-5 w-5" />
            </span>
          </div>
          <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900">{{ metrics.total_signed_calls }}</p>
          <p class="mt-1 text-xs text-gray-500">A: {{ metrics.attestation_a_count }} | B: {{ metrics.attestation_b_count }} | C: {{ metrics.attestation_c_count }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">Standard &amp; Compliance</span>
            <span class="rounded-md bg-purple-50 p-1 text-purple-600 ring-1 ring-purple-500/20">
              <ShieldCheckIcon class="h-5 w-5" />
            </span>
          </div>
          <p class="mt-2 text-sm font-bold tracking-tight text-purple-700">RFC 8588 / ATIS-1000074</p>
          <p class="mt-1 text-xs text-gray-500">Automatic Anti-Replay Defense</p>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-indigo-500 text-indigo-600 font-bold'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-4 px-1 text-sm flex items-center gap-2'
            ]"
          >
            <component :is="tab.icon" class="h-5 w-5" />
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- TAB 1: Live PASSporT Generator & SIP Header Simulator -->
      <div v-if="activeTab === 'simulator'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 space-y-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <SparklesIcon class="h-5 w-5 text-indigo-600" />
              Sign Outbound Call
            </h3>
            <p class="text-xs text-gray-500">
              Generates an RFC 8588 PASSporT JWT and builds the SIP <code class="font-mono text-indigo-600">Identity</code> header for FreeSWITCH bridge.
            </p>

            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700">Caller ID (Originating TN)</label>
                <input
                  v-model="simForm.caller_id"
                  type="text"
                  placeholder="+12125550100"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700">Destination (Called TN)</label>
                <input
                  v-model="simForm.destination_number"
                  type="text"
                  placeholder="+14155550199"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700">Attestation Policy</label>
                <select
                  v-model="simForm.attestation"
                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">Auto-Detect from DID Vault</option>
                  <option value="A">Attestation A (Full Attestation - Verified DID)</option>
                  <option value="B">Attestation B (Partial Attestation - Customer Origin)</option>
                  <option value="C">Attestation C (Gateway Attestation - Transit Source)</option>
                </select>
              </div>

              <button
                @click="submitSimulate"
                :disabled="simulating"
                class="w-full rounded-lg bg-indigo-600 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <ArrowPathIcon v-if="simulating" class="h-4 w-4 animate-spin" />
                {{ simulating ? 'Signing Cryptographic Token...' : 'Generate PASSporT Token' }}
              </button>
            </div>
          </div>

          <!-- Token Inspector View -->
          <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <CodeBracketIcon class="h-5 w-5 text-indigo-600" />
                PASSporT Token &amp; SIP Identity Header Inspector
              </h3>
              <span v-if="simResult" class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                Attestation: Level {{ simResult.attestation }}
              </span>
            </div>

            <div v-if="simResult" class="space-y-4">
              <!-- Full SIP Identity Header -->
              <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Injected SIP Identity Header:</span>
                <div class="mt-1 relative rounded-lg bg-slate-900 p-3 font-mono text-xs text-indigo-300 break-all select-all">
                  Identity: {{ simResult.identity_header }}
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Header -->
                <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-slate-200">
                  <span class="text-xs font-bold text-slate-700">PASSporT Header (JOSE)</span>
                  <pre class="mt-2 text-xs font-mono text-slate-800 bg-white p-2.5 rounded border border-slate-200">{{ JSON.stringify(simResult.header, null, 2) }}</pre>
                </div>

                <!-- Payload -->
                <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-slate-200">
                  <span class="text-xs font-bold text-slate-700">PASSporT Payload (Claims)</span>
                  <pre class="mt-2 text-xs font-mono text-slate-800 bg-white p-2.5 rounded border border-slate-200">{{ JSON.stringify(simResult.payload, null, 2) }}</pre>
                </div>
              </div>

              <div class="flex items-center justify-between text-xs text-gray-500 pt-2 border-t border-gray-100">
                <span>Signing Certificate: <strong class="text-gray-900">{{ simResult.cert_name }}</strong></span>
                <span>Cryptographic Latency: <strong class="text-indigo-600">{{ simResult.latency_ms }} ms</strong></span>
              </div>
            </div>

            <div v-else class="py-16 text-center text-gray-400">
              <SparklesIcon class="mx-auto h-12 w-12 text-gray-300" />
              <p class="mt-2 text-sm font-medium">Enter phone numbers and generate a live PASSporT token to inspect.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: Attestation Policy Radar (A / B / C) -->
      <div v-if="activeTab === 'policies'" class="space-y-6">
        <div>
          <h2 class="text-lg font-bold text-gray-900">ATIS / FCC Attestation Level Matrix</h2>
          <p class="text-sm text-gray-500">Automated criteria used by the ZneroPBX signing service before originating calls.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-emerald-200 border-t-4 border-emerald-500 space-y-3">
            <div class="flex items-center justify-between">
              <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-800">
                Level A — Full Attestation
              </span>
              <CheckBadgeIcon class="h-6 w-6 text-emerald-600" />
            </div>
            <h3 class="text-base font-bold text-gray-900">Verified DID Allocation</h3>
            <p class="text-xs text-gray-600">
              The service provider authenticated the caller, and the caller is authoritatively allocated this telephone number in the PBX registry.
            </p>
            <div class="rounded bg-emerald-50 p-2.5 text-[11px] font-mono text-emerald-900">
              Condition: Caller ID is in verified TNAuthList or v_destinations.
            </div>
          </div>

          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-amber-200 border-t-4 border-amber-500 space-y-3">
            <div class="flex items-center justify-between">
              <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-800">
                Level B — Partial Attestation
              </span>
              <ShieldCheckIcon class="h-6 w-6 text-amber-600" />
            </div>
            <h3 class="text-base font-bold text-gray-900">Authenticated Customer Origin</h3>
            <p class="text-xs text-gray-600">
              The customer is authenticated on the PBX extension/trunk, but the caller ID number cannot be authoritatively proven as assigned to them.
            </p>
            <div class="rounded bg-amber-50 p-2.5 text-[11px] font-mono text-amber-900">
              Condition: Valid SIP registration, non-allocated outbound CID.
            </div>
          </div>

          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 border-t-4 border-slate-500 space-y-3">
            <div class="flex items-center justify-between">
              <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-800">
                Level C — Gateway Attestation
              </span>
              <ArrowPathIcon class="h-6 w-6 text-slate-600" />
            </div>
            <h3 class="text-base font-bold text-gray-900">International / Transit Origin</h3>
            <p class="text-xs text-gray-600">
              The call arrived via an international carrier gateway or external transit trunk where the originating caller cannot be verified.
            </p>
            <div class="rounded bg-slate-50 p-2.5 text-[11px] font-mono text-slate-900">
              Condition: Gateway ingress or non-authenticated forwarding.
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: X.509 Certificate Vault & Keypairs -->
      <div v-if="activeTab === 'certificates'" class="space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-gray-900">X.509 Identity Certificates &amp; EC P-256 Keypairs</h2>
            <p class="text-sm text-gray-500">Cryptographic certificates registered with the STI-PA (Secure Telephone Identity Policy Administrator).</p>
          </div>
          <button
            @click="openCertModal = true"
            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
          >
            <KeyIcon class="h-4 w-4" />
            Generate EC P-256 Keypair
          </button>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-gray-900">Certificate Name</th>
                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">SPC Code</th>
                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Public X5U URL</th>
                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">TN Auth Ranges</th>
                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Expires</th>
                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="cert in certificatesList" :key="cert.certificate_uuid" class="hover:bg-gray-50 transition">
                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-bold text-gray-900 flex items-center gap-2">
                  <KeyIcon class="h-5 w-5 text-indigo-600 flex-shrink-0" />
                  {{ cert.cert_name }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-xs font-mono font-bold text-indigo-700">
                  {{ cert.spc_code }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-xs font-mono text-gray-600">
                  {{ cert.x5u_url }}
                </td>
                <td class="px-3 py-4 text-xs">
                  <span class="inline-flex flex-wrap gap-1">
                    <span v-for="tn in (cert.tn_ranges || []).slice(0, 3)" :key="tn" class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-700">
                      {{ tn }}
                    </span>
                    <span v-if="(cert.tn_ranges || []).length > 3" class="text-[10px] text-gray-400">
                      +{{ cert.tn_ranges.length - 3 }} more
                    </span>
                  </span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-600">
                  {{ formatDate(cert.expires_at) }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-xs">
                  <span v-if="cert.is_active" class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                    ACTIVE
                  </span>
                  <span v-else class="inline-flex items-center rounded-full bg-slate-50 px-2 py-1 font-semibold text-slate-700 ring-1 ring-slate-600/20">
                    DISABLED
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 4: Cryptographic Verification & Decode Inspector -->
      <div v-if="activeTab === 'verifier'" class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 space-y-4">
          <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <ShieldCheckIcon class="h-5 w-5 text-indigo-600" />
            Verify Inbound SIP Identity Header
          </h3>
          <p class="text-xs text-gray-500">
            Paste any incoming SIP <code class="font-mono text-indigo-600">Identity</code> header or compact PASSporT JWT to verify cryptographic authenticity and replay expiration.
          </p>

          <div class="space-y-3">
            <textarea
              v-model="verifyInput"
              rows="3"
              placeholder="eyJhbGciOiJFUzI1NiIsInBwdCI6InNoYWtlbiIsInR5cCI6InBhc3Nwb3J0IiwieDV1IjoiaHR0cHM6Ly9jZXJ0cy56bmVyby5jb20vc2hha2VuL2NlcnQtcHJpbWFyeS5wZW0ifQ...;info=<...>;alg=ES256;ppt=shaken"
              class="w-full rounded-lg border-gray-300 font-mono text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            ></textarea>

            <button
              @click="submitVerify"
              :disabled="verifying"
              class="rounded-lg bg-indigo-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50 flex items-center gap-2"
            >
              <ArrowPathIcon v-if="verifying" class="h-4 w-4 animate-spin" />
              {{ verifying ? 'Verifying Signature...' : 'Cryptographically Verify Header' }}
            </button>
          </div>

          <div v-if="verifyResult" class="mt-4 rounded-xl p-5 border" :class="verifyResult.valid ? 'bg-emerald-50/60 border-emerald-200' : 'bg-rose-50/60 border-rose-200'">
            <div class="flex items-center gap-2">
              <CheckCircleIcon v-if="verifyResult.valid" class="h-6 w-6 text-emerald-600" />
              <XCircleIcon v-else class="h-6 w-6 text-rose-600" />
              <h4 class="text-sm font-bold" :class="verifyResult.valid ? 'text-emerald-900' : 'text-rose-900'">
                {{ verifyResult.valid ? 'Cryptographically Valid PASSporT Token (ES256 Signature Verified)' : 'Verification Failed' }}
              </h4>
            </div>

            <p v-if="verifyResult.error" class="mt-1 text-xs text-rose-700 font-semibold">{{ verifyResult.error }}</p>

            <div v-if="verifyResult.valid" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
              <div class="bg-white p-2.5 rounded shadow-sm">
                <span class="text-gray-500">Attestation:</span>
                <p class="font-bold text-emerald-700">Level {{ verifyResult.attestation }}</p>
              </div>
              <div class="bg-white p-2.5 rounded shadow-sm">
                <span class="text-gray-500">Originating TN:</span>
                <p class="font-bold text-gray-900 font-mono">{{ verifyResult.orig_tn }}</p>
              </div>
              <div class="bg-white p-2.5 rounded shadow-sm">
                <span class="text-gray-500">Destination TN:</span>
                <p class="font-bold text-gray-900 font-mono">{{ verifyResult.dest_tn }}</p>
              </div>
              <div class="bg-white p-2.5 rounded shadow-sm">
                <span class="text-gray-500">Token Age / Latency:</span>
                <p class="font-bold text-gray-900">{{ verifyResult.age_seconds }}s / {{ verifyResult.latency_ms }}ms</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 5: Live Outbound Signing History -->
      <div v-if="activeTab === 'history'" class="space-y-6">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Live PASSporT Outbound Signing Audit Trail</h2>
          <p class="text-sm text-gray-500">Real-time log of SIP Identity headers generated and injected into carrier trunks.</p>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="py-3 pl-6 pr-3 text-left text-xs font-semibold text-gray-900">Signed At</th>
                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-900">Caller ID</th>
                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-900">Destination</th>
                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-900">Attestation</th>
                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-900">OrigID UUID</th>
                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-900">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="log in logsList" :key="log.log_uuid" class="hover:bg-gray-50 transition">
                <td class="whitespace-nowrap py-3.5 pl-6 pr-3 text-xs text-gray-600">
                  {{ formatDate(log.signed_at) }}
                </td>
                <td class="whitespace-nowrap px-3 py-3.5 text-xs font-mono font-bold text-gray-900">
                  {{ log.orig_tn }}
                </td>
                <td class="whitespace-nowrap px-3 py-3.5 text-xs font-mono text-gray-700">
                  {{ log.dest_tn }}
                </td>
                <td class="whitespace-nowrap px-3 py-3.5 text-xs">
                  <span
                    :class="[
                      log.attestation === 'A' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' :
                      log.attestation === 'B' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' :
                      'bg-slate-50 text-slate-700 ring-slate-600/20',
                      'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold ring-1'
                    ]"
                  >
                    Level {{ log.attestation }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-3 py-3.5 text-xs font-mono text-gray-500">
                  {{ log.origid_uuid.substring(0, 8) }}...
                </td>
                <td class="whitespace-nowrap px-3 py-3.5 text-xs">
                  <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                    SIGNED
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal: Generate New Keypair -->
      <div v-if="openCertModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
          <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <KeyIcon class="h-5 w-5 text-indigo-600" />
              Generate STIR/SHAKEN EC P-256 Keypair
            </h3>
            <button @click="openCertModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>

          <div class="space-y-4 py-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700">Certificate Name</label>
              <input
                v-model="certForm.cert_name"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700">Public X5U URL</label>
              <input
                v-model="certForm.x5u_url"
                type="url"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700">Service Provider Code (SPC)</label>
              <input
                v-model="certForm.spc_code"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700">Authorized DIDs (comma-separated)</label>
              <input
                v-model="certForm.tn_ranges_text"
                type="text"
                placeholder="+12125550100, +12125550101"
                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              @click="openCertModal = false"
              class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              @click="submitCreateCert"
              :disabled="creatingCert"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition disabled:opacity-50 flex items-center gap-1.5"
            >
              <ArrowPathIcon v-if="creatingCert" class="h-4 w-4 animate-spin" />
              {{ creatingCert ? 'Generating EC P-256...' : 'Generate Certificate' }}
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
  ShieldCheckIcon,
  CheckBadgeIcon,
  KeyIcon,
  SparklesIcon,
  PhoneArrowUpRightIcon,
  CodeBracketIcon,
  ClockIcon,
  ArrowPathIcon,
  XMarkIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  certificates: { type: Array, default: () => [] },
  recentLogs: { type: Array, default: () => [] },
  metrics: { type: Object, default: () => ({}) },
})

const activeTab = ref('simulator')
const certificatesList = ref(props.certificates)
const logsList = ref(props.recentLogs)

const tabs = [
  { id: 'simulator', name: 'PASSporT Generator & SIP Header', icon: SparklesIcon },
  { id: 'policies', name: 'Attestation Policy Radar', icon: CheckBadgeIcon },
  { id: 'certificates', name: 'X.509 Certificate Vault', icon: KeyIcon },
  { id: 'verifier', name: 'Signature Verifier', icon: ShieldCheckIcon },
  { id: 'history', name: 'Outbound Signing History', icon: ClockIcon },
]

// Simulator Form
const simForm = ref({
  caller_id: '+12125550100',
  destination_number: '+14155550199',
  attestation: '',
})
const simulating = ref(false)
const simResult = ref(null)

const submitSimulate = async () => {
  simulating.value = true
  try {
    const res = await axios.post('/api/telecom/stir-shaken/simulate-signing', simForm.value)
    simResult.value = res.data
    // Prepend to logs
    logsList.value.unshift({
      log_uuid: res.data.log_uuid,
      signed_at: new Date().toISOString(),
      orig_tn: res.data.payload.orig.tn,
      dest_tn: res.data.payload.dest.tn[0],
      attestation: res.data.attestation,
      origid_uuid: res.data.origid,
    })
  } catch (err) {
    console.error('Signing simulation failed', err)
  } finally {
    simulating.value = false
  }
}

// Verifier Form
const verifyInput = ref('')
const verifying = ref(false)
const verifyResult = ref(null)

const submitVerify = async () => {
  if (!verifyInput.value) return
  verifying.value = true
  try {
    const res = await axios.post('/api/telecom/stir-shaken/verify-passport', {
      identity_header: verifyInput.value,
    })
    verifyResult.value = res.data
  } catch (err) {
    console.error('Verification failed', err)
  } finally {
    verifying.value = false
  }
}

// Certificate Generation Form
const openCertModal = ref(false)
const creatingCert = ref(false)
const certForm = ref({
  cert_name: 'Znero Secondary Carrier Identity (EC P-256)',
  x5u_url: 'https://certs.znero.com/shaken/secondary.pem',
  spc_code: 'ZN02',
  tn_ranges_text: '+12125550200, +12125550201, +12125550299',
})

const submitCreateCert = async () => {
  creatingCert.value = true
  try {
    const ranges = certForm.value.tn_ranges_text
      .split(',')
      .map(s => s.trim())
      .filter(Boolean)

    const res = await axios.post('/api/telecom/stir-shaken/generate-cert', {
      cert_name: certForm.value.cert_name,
      x5u_url: certForm.value.x5u_url,
      spc_code: certForm.value.spc_code,
      tn_ranges: ranges,
    })
    if (res.data.certificate) {
      certificatesList.value.unshift(res.data.certificate)
    }
    openCertModal.value = false
  } catch (err) {
    console.error('Cert creation failed', err)
  } finally {
    creatingCert.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString()
}
</script>
