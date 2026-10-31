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
