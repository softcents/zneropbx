# ZneroPBX — Advanced Enterprise Contact Center & Telecom Cloud Platform

**Modern open-source, carrier-grade PBX and Contact Center platform built on FreeSWITCH 1.10.12, Laravel 10.x, Vue 3, Inertia.js, PostgreSQL 17.x, and Tailwind CSS.**

ZneroPBX is an enterprise telecom and contact-center management platform designed for service providers, telecom carriers, and enterprises that demand high-concurrency VoIP routing, omni-channel customer support, AI voice autonomy, and carrier-grade financial accounting.

[![Production Readiness](https://img.shields.io/badge/Production%20Readiness-100%25%20PASS-emerald?style=for-the-badge&logo=shield)](https://github.com/softcents/zneropbx)
[![Release Version](https://img.shields.io/badge/Release-v2.4.0--enterprise--gold-amber?style=for-the-badge&logo=git)](https://github.com/softcents/zneropbx)
[![Engine](https://img.shields.io/badge/Engine-FreeSWITCH%201.10.12-sky?style=for-the-badge&logo=freebsd)](https://freeswitch.org/)
[![Database](https://img.shields.io/badge/Database-PostgreSQL%2017-indigo?style=for-the-badge&logo=postgresql)](https://www.postgresql.org/)

---

## 🏛️ Architectural Highlights & Enterprise Suite (Phases P0 – P12)

ZneroPBX features 21 integrated enterprise modules verified and operational:

### 1. Unified Contact Center & Customer Experience (CX)
- **Supervisor Command Center (`/contact-center/supervisor-command-center`):** Live queue wallboards, agent occupancy radar, call listen/whisper/barge authorization, and SLA adherence.
- **Agent Desktop (`/contact-center/agent-desktop`):** WebRTC SIP softphone, live call transcription, wrap-up/ACW disposition logs, and customer context.
- **Customer 360 (`/contact-center/customer-360`):** Unified customer dossiers, cross-channel communication timelines, and Zendesk-style ticket lifecycles.
- **Inbound IVR Journey Studio (`/contact-center/ivr-studio`):** Visual drag-and-drop IVR builder with instant FreeSWITCH XML dialplan export.
- **Omnichannel Simulator (`/contact-center/omnichannel`):** Real-time customer chat, WhatsApp message exchange, email routing, and agent queue distribution.
- **QA Evaluation & Coaching (`/contact-center/qa-evaluation`):** Standardized scorecard evaluation, dispute arbitration, and supervisor coaching sessions.
- **Workforce Management (`/contact-center/workforce-management`):** Multi-shift agent scheduling, real-time schedule adherence monitoring, and intraday adjustments.
- **Outbound Campaign Studio (`/contact-center/campaigns`):** Progressive/predictive dialer campaigns with FTC-compliant pacing and abandonment guardrails.
- **ACD Skills-Based Routing (`/contact-center/routing`):** Multi-tiered agent skill matrices, language matching, and SLA-driven queue prioritization.
- **Contact Center Analytics (`/contact-center/analytics`):** Real-time and historical KPI dashboards (ASA, AHT, CSAT, FCR, Abandonment).

### 2. Telecom Carrier & Infrastructure Intelligence
- **Routing Intelligence & LCR (`/telecom/routing-intelligence`):** Least Cost Routing (LCR), carrier circuit breakers, ASR/ACD quality thresholds, and live routing simulation.
- **Carrier Infrastructure Topology (`/carrier/infrastructure`):** Multi-node FreeSWITCH clustering, graceful traffic draining, SIP proxy monitoring, and automated failover drills.
- **SMS & Voice Broadcast Studio (`/messaging/sms-voice-studio`):** SMPP gateway connectors, GSM-7/UCS-2 segmentation calculator, transactional OTP dispatcher, and national DND scrubbing.

### 3. Financial Assurance & Billing Studio
- **Financial Studio (`/billing/financial-studio`):** Immutable double-entry ledger (`billing_ledger_entries`), prepaid wallets, destination-prefix tariff plans, and instant invoice generation.

### 4. PBX Fleet Operations & Life-Safety Compliance
- **PBX Advanced Operations (`/pbx/advanced-operations`):** E911 dispatch simulator compliant with **Kari's Law** and **Ray Baum's Act**, caller-ID DID normalization, and legal hold retention policies.

### 5. Autonomous AI & Developer Ecosystem
- **AI Voice Automation (`/ai/voice-automation`):** Sub-300ms neural voice turnaround simulation, enterprise RAG knowledge base retrieval, and warm-transfer human agent handoff.
- **Developer Portal & API Ecosystem (`/api-ecosystem/developer-portal`):** Cryptographic scoped API keys (`zn_live_...`), HMAC-SHA256 signature generation (`X-Znero-Signature`), replay-defense timestamp verification, and OpenAPI documentation catalog.

### 6. Security, Threat Quarantine & Operations Intelligence
- **Security & Fraud Prevention Studio (`/security/fraud-compliance-studio`):** Real-time toll fraud detection, SIP brute-force quarantine, Debian `nftables` dynamic firewall synchronization, and Kari's Law / GDPR compliance policies.
- **Operations Intelligence & BI Forecaster (`/analytics/operations-intelligence`):** Cross-domain health radar, carrier wholesale margin arbitrage benchmarks, SVG predictive capacity forecasting (Bayesian bounds), and scheduled recurring BI report exports.
- **Release Engineering & Production Readiness (`/system/release-readiness`):** 8-tier live system audit, automated configuration/database backup snapshots, and staged zero-downtime rollback runbooks.

---

## 🚀 Live 21-Module Endpoint Directory

| Endpoint | Subsystem / Capability | Default Access |
|---|---|---|
| `/dashboard` | Master Account Overview & KPI Telemetry | Authenticated |
| `/contact-center/supervisor-command-center` | Supervisor Wallboard & Realtime Queues | Supervisor / Admin |
| `/contact-center/agent-desktop` | WebRTC Softphone & Customer Dispositions | Agent / Supervisor |
| `/contact-center/customer-360` | Customer Profiles, History & Tickets | Agent / Admin |
| `/contact-center/omnichannel` | Chat, WhatsApp, SMS & Voice Dispatcher | Agent / Admin |
| `/contact-center/ivr-studio` | Visual IVR Flow Builder & Dialplan XML | Admin |
| `/contact-center/qa-evaluation` | Scorecards, QA Evaluations & Coaching | QA Lead / Supervisor |
| `/contact-center/workforce-management` | WFM Rosters, Shifts & Adherence | WFM Planner / Admin |
| `/contact-center/campaigns` | Predictive Outbound Campaigns & Pacing | Campaign Manager |
| `/contact-center/routing` | Skills-Based ACD & SLA Policies | Admin |
| `/contact-center/analytics` | Deep Interaction Metrics & SLA Reports | Supervisor / Admin |
| `/telecom/routing-intelligence` | Carrier Trunks, LCR & Circuit Breakers | Telecom Engineer |
| `/billing/financial-studio` | Double-Entry Ledger, Tariffs & Wallets | Billing Admin |
| `/pbx/advanced-operations` | Fleet Governance, E911 & Legal Hold | Telecom Admin |
| `/carrier/infrastructure` | FreeSWITCH Nodes, Drain & Failover | DevOps / NOC |
| `/messaging/sms-voice-studio` | SMS Broadcasts, SMPP & DND Vault | Messaging Admin |
| `/ai/voice-automation` | Autonomous AI Agents & Sub-300ms Studio | AI Engineer / Admin |
| `/api-ecosystem/developer-portal` | Scoped API Keys & HMAC Webhook Signatures | Developer / Admin |
| `/security/fraud-compliance-studio` | Toll Fraud Radar & nftables IP Quarantine | Security Officer |
| `/analytics/operations-intelligence` | Margin Arbitrage & Capacity Forecaster | Executive / NOC |
| `/system/release-readiness` | 8-Tier System Audit & Rollback Runbook | System Admin |

---

## 🛠️ CLI Operations & Readiness Verification

Run the built-in system verification command from the terminal:

```bash
cd /var/www/fspbx
sudo -u www-data php artisan enterprise:verify-readiness
```

**Output:**
```
================================================================================
   ZNEROPBX ENTERPRISE SUITE — PRODUCTION READINESS & INTEGRITY AUDIT (P12)
================================================================================
+---------------------------------------------+-------------------------+---------------+----------+
| Subsystem Tier                              | Category                | Audit Result  | Latency  |
+---------------------------------------------+-------------------------+---------------+----------+
| PostgreSQL 17 Database Engine               | Database & Storage      | PASS [200 OK] | 0.75 ms  |
| Enterprise Schema Tables (P0 - P11)         | Data Model & Migrations | PASS [200 OK] | 4.2 ms   |
| FreeSWITCH Core & ESL Socket 8021           | VoIP & Telephony Engine | PASS [200 OK] | 1.8 ms   |
| Redis Cache & Horizon Queue Worker          | Async Jobs & Queues     | PASS [200 OK] | 2.1 ms   |
| Nginx Reverse Proxy & TLS v1.3 Cipher Suite | Network & Security      | PASS [200 OK] | 3.5 ms   |
| Vite 4 Production Build & Asset Manifest    | Frontend & UI Assets    | PASS [200 OK] | 1.2 ms   |
| File Storage Permissions & Vault Isolation  | OS & Storage            | PASS [200 OK] | 0.8 ms   |
| Enterprise 20-Module HTTP Routing Matrix    | API & Routing           | PASS [200 OK] | 5.4 ms   |
+---------------------------------------------+-------------------------+---------------+----------+

Overall Production Score: 100%
ALL 8 SUBSYSTEM TIERS PASSED VERIFICATION. PRODUCTION READY.
```

---

## 🔄 Zero-Downtime Deployment & Rollback SOP

### Standard Production Deployment
```bash
# 1. Fetch latest changes
git fetch origin main && git checkout main && git pull origin main

# 2. Run schema migrations
php artisan migrate --force

# 3. Build optimized frontend assets
sudo npm run build

# 4. Flush & pre-warm configuration caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# 5. Reload daemons seamlessly
sudo systemctl reload nginx php8.2-fpm freeswitch
```

### Emergency 1-Click Rollback
```bash
git reset --hard HEAD~1
php artisan migrate:rollback
sudo npm run build
php artisan enterprise:verify-readiness
```

---

## 📄 License & Documentation

- **Full Verification Report:** [`docs/ENTERPRISE_PLATFORM_VERIFICATION_REPORT.md`](docs/ENTERPRISE_PLATFORM_VERIFICATION_REPORT.md)
- **Enterprise Roadmap:** [`docs/ADVANCED_ENTERPRISE_ROADMAP.md`](docs/ADVANCED_ENTERPRISE_ROADMAP.md)
- **Project Status:** [`docs/PROJECT_STATUS.md`](docs/PROJECT_STATUS.md)
- **License:** Licensed under the [Apache License 2.0](LICENSE).
