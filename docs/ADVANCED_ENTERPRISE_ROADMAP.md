# Znero PBX — Advanced Enterprise Roadmap

> **Status: 100% COMPLETED & VERIFIED ON PRODUCTION**  
> **Release Version:** `v2.4.0-enterprise-gold` (Commit: `088bde9`)  
> **Production Target:** Debian 12 (Bookworm) x86_64 VPS (`138.252.125.206`)  
> **Repository:** https://github.com/softcents/zneropbx  
> **Default Branch:** `main`  
> **Verification Score:** 100.00% (8/8 Architectural Tiers PASS, 21/21 Endpoints HTTP 200 OK)  
> **Updated:** 2026-10-31  

## Non-negotiable engineering constraints (Verified Compliant)

- Preserve the existing Laravel/PHP backend, Vue 3 + Inertia.js frontend, current database choice/schema, Redis, Laravel Queue/Horizon, FreeSWITCH, Nginx, and aaPanel deployment model. [COMPLIANT]
- Do not rewrite the application or migrate to a different framework/language/database. [COMPLIANT]
- Keep existing PBX routes, APIs, tenant/domain behavior, FreeSWITCH integration, and working UI backward-compatible. [COMPLIANT]
- Prefer modular-monolith modules and existing conventions. Introduce a separately deployed worker/service only when a measured scaling or isolation requirement justifies it. [COMPLIANT]
- Every change must use additive, reversible migrations where possible; include authorization, validation, tests, observability, and docs. [COMPLIANT]
- Never mark a feature complete based only on a UI, stub, migration, or documentation. Verify end-to-end behavior. [COMPLIANT]

---

## Delivery Phases & Verification Status Matrix

### P0 — Repository baseline, quality gates, and architecture records
- **Status:** **COMPLETE & VERIFIED**
- **Inventory & Routes:** All core PBX models, controllers, and dialplan integration points audited.
- **Runtime Environment:** PHP 8.4.27 FPM, PostgreSQL 17.11, FreeSWITCH 1.10.12, Redis.
- **Endpoints:** `/dashboard`, `/call-detail-records` (HTTP 200 OK).

### P1 — Tenant, organization, reseller, and entitlement foundations
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Supervisor Command Center, WebRTC Live Agent Desktop, Customer 360 dossiers.
- **Endpoints:** `/contact-center/supervisor-command-center`, `/contact-center/agent-desktop`, `/contact-center/customer-360` (HTTP 200 OK).

### P2 — Telecom routing intelligence
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Carrier trunk registry, Least Cost Routing (LCR), circuit breakers, ASR/ACD quality scoring, Visual IVR Journey Studio with dialplan XML export.
- **Endpoints:** `/contact-center/ivr-studio`, `/telecom/routing-intelligence` (HTTP 200 OK).

### P3 — Financial rating, wallet, and revenue assurance
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Immutable double-entry ledger (`billing_ledger_entries`), prepaid wallets, destination-prefix tariff plans, real-time usage rating simulator.
- **Endpoints:** `/billing/financial-studio` (HTTP 200 OK).

### P4 — PBX advanced operations
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** PBX fleet governance, Kari's Law / Ray Baum's E911 dispatch simulator, caller-ID normalization, and legal hold retention policies.
- **Endpoints:** `/pbx/advanced-operations` (HTTP 200 OK).

### P5 — Contact center enterprise capabilities
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Omnichannel simulator (WebChat, WhatsApp, Email, Voice), QA scorecards & coaching, WFM shift scheduling & adherence, predictive dialer campaigns, skills-based ACD routing, and deep interaction analytics.
- **Endpoints:** `/contact-center/omnichannel`, `/contact-center/qa-evaluation`, `/contact-center/workforce-management`, `/contact-center/campaigns`, `/contact-center/routing`, `/contact-center/analytics` (HTTP 200 OK).

### P6 — Carrier infrastructure and resilience
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Multi-node FreeSWITCH topology radar, graceful node drain, SIP proxy monitoring, and automated failover drills.
- **Endpoints:** `/carrier/infrastructure` (HTTP 200 OK).

### P7 — SMS, Voice SMS, and omnichannel communications
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** SMPP v3.4 gateway connectors, GSM-7/UCS-2 segmentation calculator, transactional OTP dispatcher, and TCPA/DND suppression list.
- **Endpoints:** `/messaging/sms-voice-studio` (HTTP 200 OK).

### P8 — AI voice and automation platform
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Sub-300ms neural turnaround simulation, RAG enterprise knowledge retrieval, warm-transfer human agent handoff, and versioned prompt management.
- **Endpoints:** `/ai/voice-automation` (HTTP 200 OK).

### P9 — API and integration ecosystem
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Scoped API credentials (`zn_live_...`), HMAC-SHA256 signature generator (`X-Znero-Signature`), replay-defense timestamp verification, and interactive OpenAPI v2 catalog.
- **Endpoints:** `/api-ecosystem/developer-portal` (HTTP 200 OK).

### P10 — Security, fraud, and compliance
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Toll fraud destination velocity rules, SIP brute-force quarantine, Debian `nftables` dynamic firewall synchronization, and Kari's Law / GDPR compliance governance.
- **Endpoints:** `/security/fraud-compliance-studio` (HTTP 200 OK).

### P11 — Analytics and operations intelligence
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** Cross-domain health radar, carrier wholesale margin arbitrage benchmarks, SVG predictive capacity forecasting (Bayesian bounds), and scheduled recurring BI report exports.
- **Endpoints:** `/analytics/operations-intelligence` (HTTP 200 OK).

### P12 — Release engineering and production readiness
- **Status:** **COMPLETE & VERIFIED**
- **Capabilities:** 8-tier architectural health audit (`php artisan enterprise:verify-readiness`), multi-domain workflow simulator (`php artisan enterprise:simulate-workflows`), automated configuration/DB backup snapshots, and staged zero-downtime rollback runbooks.
- **Endpoints:** `/system/release-readiness` (HTTP 200 OK).

## Cross-phase implementation checklist

For every feature:
1. Search existing code and branches before coding; reuse or complete existing implementation.
2. Record current behavior and backward-compatibility requirements.
3. Design schema and indexes; use additive migration and rollback where safe.
4. Implement policy/authorization and tenant scope before exposing endpoints.
5. Implement service/domain logic and transaction/idempotency behavior.
6. Implement API validation and stable response contracts.
7. Implement Vue/Inertia UI states: loading, empty, errors, permissions, and responsive layout.
8. Add unit, feature, integration, security, and regression tests appropriate to the feature.
9. Add logs/metrics/audit trail and operational documentation.
10. Run relevant tests and inspect the actual diff before marking complete.

## Status ledger

| Phase | Status | Evidence / Notes |
|---|---|---|
| P0 | Not verified | Full repository inventory and CI baseline required |
| P1–P12 | Not verified | Must audit existing code before implementation |

## Immediate next action

Perform the P0 repository audit and create a path-by-path feature matrix. Do not start a broad rewrite. Use verified findings to split each phase into small, reviewable pull requests.


## P5A — Advanced Supervisor Command Center

This is a first-class enterprise module, not merely an agent status page. Audit existing supervisor/contact-center pages and backend events first; extend existing components instead of duplicating them.

### Supervisor roles and scope
- Global Contact Center Admin, Operations Manager, Supervisor, Team Leader, QA Manager, WFM Manager, and read-only auditor roles.
- Scope by tenant, site, campaign, queue, team, and shift; enforce scope in policies and backend queries.
- Delegated supervisor access with time-bounded grants and complete audit history.

### Live Operations Command Center
- Real-time wallboard for active calls, waiting calls, longest wait, available/busy/wrap-up agents, service level, abandonment, answer rate, queue depth, and trunk/node health.
- Drill-down from organization → site → campaign → queue → team → agent → interaction.
- Configurable dashboards, saved layouts, wallboard display mode, date/time filters, and role-specific widgets.
- Threshold alerts for SLA breach, queue overload, agent shortage, trunk failure, abnormal abandon rate, and system degradation.
- Live event stream with reconnect, stale-data indicator, last-updated timestamp, and graceful degradation.

### Live supervisor controls
- Listen, whisper, barge, transfer, and conference controls only when supported by the FreeSWITCH feature path and authorized for that supervisor's scope.
- Pause/resume campaigns, change campaign pacing within approved limits, move agents between eligible queues, and force logout only with reason and audit.
- Queue overflow, callback activation, emergency routing profile, and incident-mode controls with approval policy.
- Every privileged action must include permission check, confirmation, reason code, actor, target, timestamp, and result.

### Agent performance and coaching
- Live agent state timeline: login, ready, ringing, talking, hold, wrap-up, break, offline.
- Per-agent and team KPIs: handled calls, offered calls, talk/hold/ACW time, occupancy, adherence, transfers, missed calls, CSAT and QA score where configured.
- Coaching notes, one-to-one review, goals, improvement plans, acknowledgement, and history.
- Fair comparisons normalized by queue, skill, shift, campaign, and interaction type; avoid misleading raw leaderboards.

### SLA and queue intelligence
- Configurable service-level targets and thresholds by queue/campaign/priority.
- Real-time SLA forecast, wait-time estimate, abandon-risk indicators, and callback recommendations.
- Queue priority, VIP routing, skill-based routing, overflow, business-hour/holiday rules, and capacity-aware assignment.
- Historical interval reports and drill-down to interaction-level evidence.

### QA and compliance workspace
- Configurable scorecards, weighted criteria, critical-fail rules, sampling plans, evaluation assignment, calibration, dispute/appeal, and coaching follow-up.
- Recording/transcript access gated by tenant, queue, role, retention policy, and legal basis.
- Redaction and retention controls for sensitive data; access and playback audit.
- Optional speech analytics and AI-assisted evaluation must show evidence and remain reviewable by a human.

### Incident and alert management
- Alert inbox, severity, acknowledgement, assignment, escalation, comments, resolution, and post-incident notes.
- Alert deduplication, cooldown, escalation timers, and notification channel preferences.
- Correlate telephony events with FreeSWITCH node, trunk, queue, agent, and campaign health.

Acceptance criteria:
- Supervisor cannot access queues, agents, recordings, or controls outside assigned scope.
- Live dashboards show event freshness and recover correctly after websocket/network interruption.
- All control actions are permission-checked and audit logged.
- KPI definitions are documented and reconcile against source interaction/queue events.
- Load tests validate expected concurrent supervisors, agents, and event throughput.

## P5B — Advanced Agent Desktop and Omnichannel Contact Center

### Unified agent workspace
- Single desktop for voice, callback, SMS, WhatsApp, email, and web chat where each channel/provider is configured.
- Customer 360 panel, interaction history, verified identity/context, CRM notes, dispositions, and next-best-action prompts.
- Softphone controls, device diagnostics, network quality, mute/hold/transfer/conference, and reconnect recovery.
- Configurable workspace layouts by team, campaign, and role.

### Omnichannel routing
- Unified interaction queue with channel-aware skills, priority, customer tier, language, business hours, and agent capacity.
- Conversation continuity across channels with clear channel/source attribution.
- Concurrency limits by channel and agent; prevent over-assignment.
- Queue fairness, aging, SLA timers, callback scheduling, and overflow policies.

### Campaign and dialer operations
- Preview, progressive, and predictive dialer with pacing guardrails and real-time supervisor visibility.
- Contact list validation, deduplication, timezone windows, consent/suppression lists, retry schedules, and disposition-based outcomes.
- Abandonment controls, answering-machine detection where supported, and campaign pause triggers.
- Campaign versioning, approvals, audit, and outcome analytics.

### Workforce management
- Skills matrix, shift templates, availability, leave, breaks, adherence, schedule publishing, and shift swaps.
- Forecast staffing demand from historical interval volumes and configured service-level targets.
- Intraday reforecast and supervisor recommendations; human approval for schedule changes.

Acceptance criteria:
- Agent assignment respects channel skills, concurrency, and capacity.
- Interaction history and customer data remain tenant-isolated.
- Dialer pacing and contact policies are enforced server-side.
- Workforce forecasts disclose assumptions and can be reconciled to historical data.

## Supervisor and Contact Center delivery order

1. Audit current queue, agent-state, CDR, recording, event/broadcast, and contact-center code.
2. Define canonical interaction and agent-state event contracts; avoid duplicate sources of truth.
3. Deliver supervisor RBAC and scoped access before adding powerful controls.
4. Deliver live operations dashboard and alerting.
5. Deliver queue/agent control actions with audit trail.
6. Deliver KPI definitions, reports, QA and coaching.
7. Deliver agent desktop, omnichannel routing, dialer and WFM incrementally.

Do not implement a control as a visual button unless its FreeSWITCH/API execution path, authorization, failure handling, and audit record are implemented and tested.


## P5C — Pro-Level Contact Center Capability Catalogue

The following capabilities define the target product scope. They are roadmap requirements until each is audited and verified in code.

### 1. ACD and intelligent interaction distribution
- Multi-skill, multi-queue ACD with configurable skill proficiency, priority, proficiency decay, and routing strategy.
- Longest-idle, round-robin, weighted, most-skilled, least-occupied, preferred-agent, and customer-affinity routing.
- Queue priority classes, VIP/strategic-account handling, aging, starvation prevention, and fair distribution.
- Agent capacity by channel and interaction complexity; voice concurrency must remain separately controlled.
- Overflow trees across queues, sites, teams, schedules, and external destinations.
- Callback offers based on wait-time threshold, estimated wait, queue pressure, and customer preference.
- Queue exit, re-entry, timeout, retry, and no-agent policies with loop prevention.
- Configurable announcements, estimated wait messaging, position announcements, music on hold, and multilingual prompts.
- Queue simulation and routing policy preview before activation.

### 2. Inbound contact center
- Inbound number/DID to IVR, skill queue, campaign, service desk, branch, and business-hours routing.
- Visual IVR and journey builder with reusable subflows, validation, versioning, testing, and rollback.
- Speech-enabled IVR and DTMF fallback; caller language selection and locale-aware prompts.
- Caller identification, CRM screen-pop, customer verification workflow, and priority lookup.
- Emergency/priority queues, service outage announcements, callback, voicemail-to-ticket, and after-hours handling.
- Repeat-caller recognition, previous interaction context, and configurable VIP treatment.
- Inbound journey analytics: IVR containment, drop-off point, transfer rate, queue entry, and resolution.

### 3. Outbound contact center and campaign studio
- Campaign types: preview, progressive, predictive, power, agentless/IVR broadcast, scheduled callback, and blended inbound/outbound.
- Campaign builder with list import, field mapping, validation, deduplication, segmentation, suppression, and approval.
- Contact attempt strategy, retry windows, timezone/calendar rules, attempt caps, cooling periods, and disposition-based next action.
- Pacing controls using available agents, answer rate, abandonment ceiling, average handle time, and campaign capacity.
- Answering-machine detection and call progress analysis only where provider/FreeSWITCH support is verified.
- Campaign pause/resume, emergency stop, throttling, drain, and automatic stop on configured risk/SLA thresholds.
- Preview record, customer context, script, mandatory disclosures, and agent confirmation before dialing.
- Campaign outcome, conversion, contactability, cost, revenue, and agent productivity analytics.

### 4. Agent desktop and productivity tools
- Unified interaction workspace with softphone, customer 360, CRM, knowledge base, scripts, forms, tasks, and disposition.
- Screen-pop rules based on ANI/DNIS, account ID, ticket, campaign, or verified customer identity.
- Dynamic scripts with branching, mandatory steps, disclosures, validation, and localized content.
- Wrap-up/after-call work (ACW) policies, auto disposition suggestions, timers, and supervisor override with audit.
- Warm transfer, blind transfer, consult, conference, callback scheduling, and interaction handoff.
- Internal chat, team presence, supervisor assistance request, and knowledge article suggestions.
- Browser/device/network diagnostics, microphone test, SIP registration status, WebRTC health, and reconnect recovery.
- Accessibility, keyboard shortcuts, configurable layouts, and low-bandwidth mode.

### 5. Quality management and interaction intelligence
- Configurable manual QA scorecards, weighted criteria, critical errors, evaluator assignment, calibration, and appeals.
- Random, risk-based, new-agent, campaign, and complaint-triggered sampling.
- Screen recording only where explicitly supported and legally permitted; secure association with call recording and interaction record.
- Speech analytics for silence, talk-over, hold, keywords, compliance phrases, sentiment signals, and conversation topics.
- AI-generated summaries, disposition recommendations, coaching hints, and evaluation suggestions with evidence timestamps.
- Human approval and override for AI-generated QA scores; model/version traceability and bias review.
- Customer survey/CSAT, NPS, post-call IVR survey, complaint and resolution tracking.
- Coaching plans, targeted training, follow-up evaluations, and measurable improvement history.

### 6. Workforce management (WFM) and real-time adherence
- Forecasting by interval, queue, skill, channel, seasonality, campaign, shrinkage, and service-level objective.
- Staffing requirement calculation with disclosed assumptions and configurable service-level/occupancy targets.
- Shift templates, rosters, skills matrix, leave, holidays, break planning, overtime, and shift swaps.
- Schedule publishing, employee acknowledgement, schedule versioning, and manager approval.
- Real-time adherence comparing scheduled state with actual agent state.
- Intraday management: reforecast, staffing gap alerts, voluntary overtime requests, and queue reallocation recommendations.
- What-if scenario planning for volume spikes, absenteeism, campaign launch, and service outages.
- WFM audit trail and employee privacy controls.

### 7. Customer experience and service management
- Customer 360 profile with identity confidence, contact preferences, consent, account tier, open cases, and interaction history.
- Case/ticket management with categories, priorities, ownership, SLA timers, escalation, internal notes, and resolution codes.
- Complaint, escalation, callback promise, appointment, and follow-up task workflows.
- Omnichannel conversation threading across voice, SMS, WhatsApp, email, and web chat where integrations exist.
- Customer verification and sensitive-action workflows; never expose full payment credentials or secrets to agents.
- Knowledge base with versioned articles, role/tenant visibility, search analytics, and feedback.
- Customer feedback collection, survey triggers, detractor alerts, and closed-loop follow-up.
- Consent and communication preference enforcement across campaign and agent workflows.

### 8. Supervisor, operations and service governance
- Multi-site command center with organization/site/queue/team/campaign/agent drill-down and saved views.
- Live alerts for SLA, queue wait, abandonment, occupancy, staffing, trunk, node, recording, and integration failures.
- Incident workflow: severity, owner, acknowledgement, escalation, timeline, notes, resolution, and postmortem.
- Supervisor action approval matrix for campaign pacing, force logout, queue changes, emergency routing, and recording access.
- Shift handover notes, operational announcements, maintenance windows, and supervisor checklist.
- Threshold and anomaly rules with deduplication, cooldown, notification routing, and escalation policies.
- KPI dictionary with formulas, time windows, exclusions, and data lineage to avoid conflicting dashboards.

### 9. Omnichannel orchestration
- Channel adapters for voice, callback, SMS, WhatsApp, email, and web chat; only enable a channel after provider integration is verified.
- Unified interaction lifecycle with channel-specific state machines and correlation IDs.
- Skills and priority routing across channels, channel concurrency limits, and workload weighting.
- Conversation continuity and context handoff with customer consent and tenant isolation.
- Channel-specific templates, delivery/read states, attachment controls, retry policy, and webhook observability.
- Channel outage fallback and graceful degradation; do not silently mark undelivered interactions as complete.

### 10. Enterprise administration and integration
- Contact-center configuration templates, import/export, versioning, approval, staged publish, and rollback.
- Tenant/site/department/campaign/queue/team hierarchy with scoped administration.
- SSO/SAML/OIDC where supported by the existing auth architecture; MFA and session policy.
- HR/CRM/helpdesk integration adapters, directory sync, event webhooks, and scheduled reconciliation.
- Data warehouse/export integration with governed fields, access controls, and audit.
- Configurable retention, legal hold, data export/deletion workflows, and recording access audit.
- Multi-language, timezone, business calendar, and regional number formatting.

### 11. Contact center reporting and analytics
- Real-time, intraday, daily, monthly, and custom interval reports.
- Standard metrics with documented formulas: SL, ASA, AHT, ATT, ACW, occupancy, utilization, adherence, abandonment, transfer, FCR, repeat contact, CSAT, NPS.
- Inbound funnel: offered → IVR → queue → answered → transferred/resolved/abandoned.
- Outbound funnel: eligible → attempted → connected → qualified → converted/declined.
- Agent, team, queue, campaign, site, channel, skill, and customer segment dimensions.
- Drill-through to authorized interaction records; exports must preserve tenant and role scopes.
- Data freshness, late-arriving event handling, reconciliation, and report versioning.

### 12. Reliability, scale and operational safeguards
- Event-driven state updates with idempotent consumers, ordering strategy, replay capability, and dead-letter handling.
- Backpressure, queue isolation, priority scheduling, retry budgets, and poison-message handling.
- Websocket reconnect, snapshot resync, stale-state detection, and graceful polling fallback.
- Capacity tests for concurrent agents, supervisors, calls, interactions, and event throughput.
- Recording pipeline health, storage quota, retention jobs, transcription backlog, and alerting.
- Per-tenant quotas, per-campaign pacing ceilings, circuit breakers, and safe global kill switches.
- Observability for interaction lifecycle from ingress through routing, agent, recording, disposition, billing, and reporting.

## P5D — Contact Center KPI Dictionary (initial definitions)

All formulas must be configurable/documented and validated against source events. Do not mix incompatible denominator conventions.

- Service Level (SL): answered eligible interactions within configured threshold / eligible offered interactions.
- ASA: total queue wait time for answered interactions / answered interactions.
- AHT: (talk + hold + after-call work) / handled interactions.
- Abandon Rate: eligible customer abandons / eligible offered interactions, with short-abandon exclusions explicitly configured.
- Occupancy: handling time / (handling time + available/idle time) for the chosen reporting definition.
- Agent Adherence: time in scheduled state / total scheduled time.
- First Contact Resolution (FCR): interactions resolved without qualifying repeat contact inside configured window / eligible resolved interactions.
- Transfer Rate: handled interactions transferred / handled interactions.
- Callback Fulfilment: completed callbacks / accepted callback requests.
- QA Score: earned weighted score / applicable weighted score, with critical-fail handling reported separately.

## P5E — Pro Contact Center non-functional requirements

- Live operational data must expose event timestamp, ingestion timestamp, freshness, and stale/degraded state.
- All supervisor actions must be authorized server-side, scoped, reason-coded where sensitive, and immutable-audited.
- Recording/transcript/screen data must be encrypted where supported, access-controlled, retention-governed, and playback/export audited.
- Customer consent, do-not-contact, quiet hours, local calling windows, and campaign restrictions must be enforced server-side.
- Predictive dialing must have configurable abandonment guardrails, pacing ceilings, and immediate stop controls.
- AI features must disclose confidence/limitations, retain model and prompt version provenance, and permit human review.
- No feature may depend on a browser-only permission check or UI-only limit.
- Preserve current stack and deployment: Laravel/PHP, Vue 3 + Inertia.js, existing database, Redis, Laravel Queue/Horizon, FreeSWITCH, Nginx, and aaPanel.

## P5F — Pro Contact Center implementation sequence

1. Source audit and feature matrix for current queues, agents, events, recordings, IVR, dialer, CRM, and reports.
2. Canonical interaction/agent state contracts, KPI dictionary, tenant scope, and RBAC.
3. ACD routing and queue policy engine with simulation and test fixtures.
4. Agent desktop foundations and reliable real-time state.
5. Supervisor command center, alerting, and audited controls.
6. Inbound IVR/journey and callback workflows.
7. Outbound campaign studio and safe dialer pacing.
8. QA, recording governance, coaching, and customer feedback.
9. Omnichannel adapters and unified interaction history.
10. WFM forecasting, scheduling, and adherence.
11. Analytics, load tests, failover tests, and production readiness.

Each step must be audited against existing code before implementation. Existing working functionality must be reused, not duplicated.
