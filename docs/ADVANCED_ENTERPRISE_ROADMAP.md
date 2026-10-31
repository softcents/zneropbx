# Znero PBX — Advanced Enterprise Roadmap

> Status: planning baseline. This document is not a claim that the listed modules are already implemented.
> Repository: https://github.com/softcents/zneropbx
> Default branch: main
> Updated: 2026-10-31

## Non-negotiable engineering constraints

- Preserve the existing Laravel/PHP backend, Vue 3 + Inertia.js frontend, current database choice/schema, Redis, Laravel Queue/Horizon, FreeSWITCH, Nginx, and aaPanel deployment model.
- Do not rewrite the application or migrate to a different framework/language/database.
- Keep existing PBX routes, APIs, tenant/domain behavior, FreeSWITCH integration, and working UI backward-compatible.
- Prefer modular-monolith modules and existing conventions. Introduce a separately deployed worker/service only when a measured scaling or isolation requirement justifies it.
- Every change must use additive, reversible migrations where possible; include authorization, validation, tests, observability, and docs.
- Never mark a feature complete based only on a UI, stub, migration, or documentation. Verify end-to-end behavior.

## Baseline known from repository README

The project describes itself as a FreeSWITCH + Laravel + Vue PBX platform and lists extensions/devices, CDR, voicemail, ring groups, IVR/virtual receptionist, time-based routing, contact-center functionality, fax, firewall management, provisioning/device management, and multi-language UI. This is a README-level inventory, not a code-level verification of completeness.

## Delivery phases

Each phase begins with a code audit and dependency map. Report each capability as: Complete / Partial / Missing / Broken / Not verified.

### P0 — Repository baseline, quality gates, and architecture records
- Inventory routes, controllers, services, models, migrations, policies, jobs, events, Vue pages/components, tests, config, and FreeSWITCH integration points.
- Map current tenant/domain scoping and permission enforcement; identify cross-tenant access risks.
- Record supported PHP/Laravel/Vue/database versions from lockfiles and runtime requirements.
- Add architecture decision records, module ownership, coding conventions, migration/rollback strategy, and threat model.
- Establish CI: formatting/lint, static analysis where already compatible, unit/feature tests, migration checks, dependency/security audit.
- Add a verified feature matrix and release/change log.

Acceptance: baseline report references actual paths/classes/tests; existing test suite and deployment commands are recorded; no runtime behavior changed.

### P1 — Tenant, organization, reseller, and entitlement foundations
- Verify current domain/tenant model before extending it.
- Organization and account hierarchy with configurable depth, parent-child relationships, lifecycle, and ownership transfer.
- Fine-grained RBAC with role templates, permission policies, scoped administrators, and audit history.
- Tenant feature entitlements, quotas, concurrent-call limits, extension limits, API limits, and usage counters.
- Tenant branding, custom domains, locale/timezone, and notification preferences.
- Tenant-aware jobs, cache keys, broadcasts, exports, and scheduled tasks.
- Explicit tenant-isolation test suite, including IDOR and background-job cases.

Acceptance: automated tests prove one tenant cannot read/change another tenant's records or resources.

### P2 — Telecom routing intelligence
- Carrier/trunk registry, normalized destination/prefix tables, route groups, priorities, and effective dates.
- LCR, quality-based, percentage/weighted, time/day, geography, customer, and service-class routing policies.
- Route health probes and metrics: ASR, ACD, PDD, failure codes, capacity, and recent quality windows.
- Circuit breaker, provider quarantine, retry policy, and controlled failover with loop prevention.
- Concurrent channel/TPS capacity controls and per-tenant/provider limits.
- Route simulation, dry-run explanation, change approval, versioning, rollback, and decision logs.
- Fraud/risk checks before route selection.

Acceptance: deterministic test matrix proves expected route selection, policy precedence, failover bounds, and no routing loops.

### P3 — Financial rating, wallet, and revenue assurance
- Immutable double-entry ledger; never use a mutable wallet balance as the accounting source of truth.
- Separate available, reserved, pending, and credit exposure balances.
- Prepaid, postpaid, and hybrid account policies; credit limits and grace/suspension rules.
- Versioned tariff plans with destination/prefix, customer/reseller, time bands, minimum charge, increments, rounding, taxes, and effective dates.
- Real-time usage rating for calls and other metered products; idempotent CDR/usage ingestion.
- Reseller price lists, markup/discount, commission, transfer, adjustment, refund, and approval workflows.
- Invoice generation, payment allocation, reconciliation, dispute records, and revenue assurance.
- Usage-rating simulator and explainable rating trace.

Acceptance: financial invariants, duplicate-event idempotency, ledger balancing, and reconciliation tests pass.

### P4 — PBX advanced operations
- Audit existing extensions, devices, trunks, inbound/outbound routes, IVR, ring groups, queues, voicemail, recordings, and CDR workflows.
- Add only verified missing capabilities: advanced call permissions, schedules/holidays, emergency routing, call limits, caller-ID policy, number normalization, and tenant templates.
- WebRTC/SIP endpoint lifecycle, provisioning profiles, device inventory, firmware/config history.
- Recording retention, legal hold, access controls, encrypted storage options, and audit trails.
- Real-time presence and operational event handling with reconnect/recovery behavior.

Acceptance: regression tests protect current call flows and validated end-to-end test calls cover inbound, outbound, transfer, queue, IVR, recording, and CDR.

### P5 — Contact center enterprise capabilities
- Skills/priority-based queue routing, VIP treatment, callback queue, overflow, service-level targets, and business-hour policies.
- Agent states, reason codes, breaks, capacity, skills, teams, supervisor scopes, and audit.
- Supervisor live wallboard; listen/whisper/barge only where FreeSWITCH permissions and applicable law permit.
- Dialer modes (preview/progressive/predictive) with consent, pacing, abandonment controls, and compliance safeguards.
- QA forms, scorecards, coaching, dispute/appeal, and call sampling.
- Workforce schedules, adherence, staffing forecasts, and SLA reports.

Acceptance: queue/agent state machine tests, permissions tests, and load tests for real-time dashboards.

### P6 — Carrier infrastructure and resilience
- Multi-node FreeSWITCH inventory, node capabilities, tenant placement, and capacity telemetry.
- Optional SIP proxy integration (OpenSIPS/Kamailio) only after architecture review; do not replace FreeSWITCH.
- Node health, graceful drain, call admission control, and controlled failover.
- IP address/pool management, ACL generation, certificate lifecycle, and firewall posture checks.
- Backup/restore drills, off-site replication, recovery objectives, configuration snapshots, and disaster-recovery runbooks.
- Geographic placement and active-active/standby topology evaluation; document state synchronization limitations.

Acceptance: repeatable failover and restore drills with measured RTO/RPO; no claim of HA before tests.

### P7 — SMS, Voice SMS, and omnichannel communications
- Provider adapter interface and credential vault; support HTTP/API and SMPP connectors as provider needs dictate.
- Bind/session lifecycle, TPS windows, enquire_link, submit_sm, delivery receipt normalization, retry/backoff, and provider health.
- Routing policies: priority, cost, quality, prefix, MNO/MNP, sender ID, DND/compliance, and controlled failover.
- Separate priority queues and fair scheduling; protect urgent/transactional messages from bulk campaign starvation.
- Campaign pacing, scheduling, deduplication, idempotency, suppression lists, delivery analytics, MO processing, and webhooks.
- Voice SMS and WhatsApp adapters with product-specific policy, consent, templates, and delivery status.
- Usage rating and cost/revenue reconciliation.

Acceptance: provider simulator tests, throttling tests, queue fairness tests, duplicate prevention, and DLR lifecycle tests.

### P8 — AI voice and automation platform
- Provider-neutral AI adapter contracts; BYOK secret handling, tenant policy, model selection, limits, and provider failover.
- Voice-agent configuration, versioned prompts, visual flow, knowledge sources, and controlled publishing.
- Streaming speech recognition/synthesis integration with latency, interruption, and fallback handling.
- Human transfer, business-hour routing, escalation, consent/recording policy, and safe failure behavior.
- Call transcripts, summaries, sentiment/QA signals with provenance and retention controls.
- AI usage metering, quotas, cost allocation, and audit trails.
- RAG knowledge ingestion with access control, source attribution, freshness, and tenant isolation.

Acceptance: latency/error budgets, human fallback, prompt/version audit, tenant data isolation, and usage reconciliation tests.

### P9 — API and integration ecosystem
- Versioned REST APIs, scoped API keys/OAuth where appropriate, rotation, expiry, and revocation.
- Webhook subscriptions, signing, retries, replay protection, delivery logs, and dead-letter handling.
- Idempotency keys for billing, provisioning, and message/call submission endpoints.
- Rate limits and quotas by tenant, credential, and endpoint.
- Developer docs, examples, sandbox/test fixtures, and API changelog.

Acceptance: contract tests, auth/tenant isolation tests, replay/idempotency tests, and published API lifecycle policy.

### P10 — Security, fraud, and compliance
- Threat model for SIP, web app, API, background jobs, uploads, secrets, recordings, and tenant boundary.
- SIP brute-force/registration anomaly detection, destination velocity rules, spend caps, and unusual traffic alerts.
- Risk policies with explainable triggers, alerting, manual review, and configurable auto-restrictions.
- Secrets encryption, rotation, least privilege, dependency scanning, secure headers, CSRF/XSS/SQLi checks.
- Immutable security audit events and incident response playbooks.
- Data retention, export, deletion, consent, and regional compliance policies configurable to deployment jurisdiction.

Acceptance: security test results and documented incident/restore drills; do not claim regulatory compliance without independent assessment.

### P11 — Analytics and operations intelligence
- Operational dashboards for node, trunk, queue, agent, call, SMS, AI, and job/queue health.
- Revenue, cost, margin, reseller, route-quality, delivery, and customer usage reports.
- ASR/ACD/PDD, completion/failure reasons, DLR, latency, and provider comparison.
- Custom report filters, scheduled exports, access-controlled saved views.
- Capacity forecasting and anomaly detection with explainable signals.

Acceptance: metrics reconcile to source records and reports enforce tenant scope.

### P12 — Release engineering and production readiness
- CI quality gates, automated test matrix, dependency review, migration validation, and build artifact provenance.
- Staged deployment compatible with current aaPanel/Nginx operations and documented manual rollback.
- Backup verification, pre/post-deploy health checks, queue worker restart strategy, and maintenance mode procedures.
- Structured logs, correlation IDs, metrics, alert thresholds, and redaction of secrets/PII.
- Load/soak tests, concurrency tests, restore tests, and operational runbooks.
- Release notes, semantic versioning policy, upgrade guide, and support matrix.

Acceptance: production-readiness checklist signed off with actual test evidence.

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
