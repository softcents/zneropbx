# Znero PBX — Project Status

Updated: 2026-10-31
Repository: https://github.com/softcents/zneropbx
Default branch: main

## Verified at repository metadata / README level

- Public repository; default branch is `main`.
- Repository primary language metadata: PHP.
- README describes a FreeSWITCH-based PBX with Laravel backend and Vue frontend.
- README lists extensions/devices, CDR, voicemail, ring groups, IVRs/virtual receptionists, time-based routing, contact-center functionality, fax, firewall management, provisioning/device management, and multilingual UI.
- README mentions PostgreSQL as part of the platform stack.

## Not yet verified

- Exact Laravel, PHP, Vue, and package versions from lockfiles.
- Full model/controller/service/route/migration inventory.
- Current tenant/domain isolation implementation and enforcement.
- Which listed PBX modules are complete, partial, broken, or UI-only.
- Billing, reseller hierarchy, SMS/SMPP, WhatsApp, AI orchestration, multi-node HA, and fraud detection implementation status.
- Test coverage, CI workflows, deployment automation, backup/restore, and production-readiness evidence.

## Engineering guardrails

- Preserve the current repository stack, database choice, APIs, and working FreeSWITCH workflows.
- No framework rewrite, database migration, or replacement of Laravel Queue/Redis without an explicit architecture decision supported by measurements.
- Treat advanced modules as roadmap items until verified in source code and tests.
- Mark a feature complete only after end-to-end verification.
