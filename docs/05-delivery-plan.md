# Delivery plan, launch gates and decisions

All schedule, cost and performance figures here are planning assumptions. Confirm against team capacity, content supply and vendor onboarding.

## 1. Milestones

Indicative MVP duration: **10–14 weeks** for two experienced Laravel developers, a part-time designer/QA contributor, and committed teacher/editor availability. A solo build or delayed content/payment onboarding takes longer. P1 is a separate estimated 6–10 weeks depending on scope. These estimates are not a fixed-price quote.

| Milestone | Indicative window | Deliverable | Exit condition |
|---|---|---|---|
| Discovery and architecture spike | Weeks 1–2 | Existing-site inventory, user interviews, curriculum selection, dependency proof, payment sandbox choice | Three subjects selected; rights supply agreed; stack resolves and builds |
| Identity and catalogue | Weeks 2–4 | Registration, profile, curriculum, Filament roles/resources, student shell | Authorization matrix passes; taxonomy accepted by faculty |
| Content and library | Weeks 4–7 | Quarantine/processing/review, search, protected reader, bookmarks | Seed content readable and reviewed; no preview leakage |
| Assessment and progress | Weeks 6–9 | Question bank, immutable exams, autosave, scoring, results | Timing/concurrency/recovery tests pass; paper blueprints approved |
| Commerce and operations | Weeks 8–10 | One gateway, pass entitlements, receipts, refunds/reconciliation, support | Sandbox edge cases and merchant onboarding complete |
| Pilot and hardening | Weeks 10–14 | Mobile QA, accessibility, performance, restore drill, pilot feedback | Launch gates below met and material pilot issues resolved |

Sequence dependencies matter: reviewed content before learner pilot; entitlement rules before paid reader; immutable exam versions before scored attempts; merchant credentials before production payment testing. Calendar overlap assumes parallel staff capacity, not automatic speedup.

## 2. First implementation backlog

| Ticket | Outcome | Depends on | Completion evidence |
|---|---|---|---|
| PBN-001 | Confirm PHP/Composer/node and resolve chosen versions | None | Lockfiles, clean build, compatibility notes |
| PBN-002 | Configure environments, CI and database | 001 | Staging health check and repeatable CI |
| PBN-003 | Implement identity and role policies | 002 | Ownership/role tests and staff MFA |
| PBN-004 | Create curriculum and localized labels | 002 | Faculty-approved sample catalogue |
| PBN-005 | Build student shell and component tokens | 001 | Phone/desktop keyboard walkthrough |
| PBN-006 | Implement private upload processing | 003,004 | Failure/retry/quarantine tests |
| PBN-007 | Add reviewer workflow and rights records | 006 | Author cannot self-approve |
| PBN-008 | Build search, reader and saved library | 005,007 | Preview/full-access tests and useful search results |
| PBN-009 | Build question bank and exam versions | 004,007 | Immutable published papers |
| PBN-010 | Implement attempt saving and scoring | 009 | Deadline/duplicate-submit/reconnect evidence |
| PBN-011 | Implement orders, gateway and entitlements | 003 | Idempotent verified payment and access grant |
| PBN-012 | Add dashboard and result recommendations | 008,010 | Evidence-based progress and empty states |
| PBN-013 | Add reports, support and operational alerts | 007,011 | Staff can resolve realistic cases |
| PBN-014 | Pilot and release checklist | All MVP | Recorded gate review and rollback rehearsal |

## 3. Content operating model

Proposed seed target: 60–90 original/licensed, reviewed resources and 300–450 reviewed practice questions across the three selected subjects, plus two complete reviewed mock papers per subject. Adjust counts to chapter coverage and faculty capacity; volume does not replace quality.

Each subject needs a named editor and independent reviewer. A content checklist covers syllabus version, correct chapter, answer correctness, accessible formatting, source/rights, explanation clarity, file readability and review date. Track empty chapters explicitly. Re-review after curriculum changes and significant reports.

Moderation target for the pilot: acknowledge reports within one business day and triage urgent rights/privacy reports promptly during published support hours. Publish only support windows that the team can staff. Maintain processing failure and review-backlog dashboards.

## 4. Monetization and cost validation

Recommend a useful Free tier and one 30-day Study Pass, with optional exam bundles later. Keep free examples sufficient to demonstrate content quality. Set actual NPR pricing after willingness-to-pay interviews, gateway fees, content compensation and support costs are known. Contribution credits and teacher-question allowances should be separate from cash balances.

Monthly operating model:

`hosting + database + backups + object storage + transfer + email/SMS + monitoring + content/review + support + payment fees + optional AI usage`

Per-paid-user contribution:

`collected revenue − taxes/refunds − gateway costs − variable delivery/AI costs − allocated teacher service costs`

Break-even paying learners:

`monthly fixed costs ÷ positive contribution per paying learner`

Obtain real vendor quotes and usage assumptions before filling this model. AI and tutoring must have usage limits aligned to gross margin. Do not advertise unlimited human help or unbounded AI under an untested low-price plan.

## 5. Analytics and success measures

North-star proposal: **weekly learners completing a study-and-practice loop**, defined as viewing an eligible resource and submitting a related practice attempt in the same week. This measures a learning behavior rather than raw page views.

Events: registration_completed, onboarding_completed, search_submitted, search_zero_results, resource_opened, resource_saved, practice_started, answer_saved, practice_submitted, payment_initiated, payment_verified, entitlement_granted, report_submitted. Use stable event IDs to avoid duplicate counting. Avoid private query text and personal document contents in analytics by default.

Monitor activation, seven-day return, practice completion, chapter coverage, search zero-result rate, report rate, payment verification success and support response age. Separate browser payment returns from actually verified revenue. Report objective-score improvements by comparable assessments, without claiming causal learning outcomes from engagement alone.

Initial pilot targets for discussion: 60% of onboarded pilot learners complete one study/practice loop; fewer than 10% of curated test searches have no relevant result; no unresolved critical payment/access defects. Pilot targets are hypotheses to revise after observing baseline behavior.

## 6. Release gates

- Rights evidence and faculty approval for all published seed material.
- Correct syllabus mapping and accessible preview/reader for all launch resources.
- Server-side authorization validated across files, search, Livewire and admin operations.
- Payment verification, replay/reconciliation and refund cases pass in sandbox; approved production smoke test uses a controlled real transaction when merchant account is ready.
- Timed-attempt recovery, answer-key protection and deterministic scoring verified.
- Mobile, keyboard, contrast, zoom, Nepali and math rendering checked.
- Backup restoration, operational alerts, worker supervision and rollback rehearsed.
- Support owner, content-report procedure, retention policy and applicable local legal/tax review completed.
- Existing-domain migration plan, URL redirects and rollback destination documented before switching traffic.

## 7. Risks and responses

| Risk | Impact | Response / owner |
|---|---|---|
| Empty or unreliable library | Students do not return | Seed content and teacher review before acquisition; content lead |
| Incorrect syllabus mapping | Misleading exam preparation | Versioned curriculum and faculty sign-off; subject editors |
| Content ownership disputes | Withdrawal and trust loss | Evidence records, reports and rapid removal path; operations |
| Payment replay/lost return | Duplicate or missing access | Transactional idempotency and scheduled lookup; engineering/finance |
| Unstable mobile internet | Lost work and abandonment | Save-status UX, server deadline and reconnection recovery; engineering |
| Bad Nepali OCR | Poor search and explanations | Human quality gate and accessible source text; content team |
| Premature AI/tutor expansion | High cost and unreliable service | P1 gates, quotas and staffed service windows; product owner |
| Scope exceeds staffing | Delayed launch | Hold three-subject MVP; make P1 optional; product owner |
| Unknown current-site state | Broken migration or data loss | Inventory, backup and redirect map before replacement; site owner |

## 8. Decisions still needed

Working defaults allow design and engineering preparation without blocking this documentation. Resolve these before dependent production work:

| Decision | Working default | Needed before |
|---|---|---|
| First learners/subjects | Grade 12, three faculty-supported subjects | Curriculum/content sprint |
| Existing site replacement or migration | Unknown; preserve current system until inventoried | Production architecture/migration |
| Content ownership and reviewer staffing | Teacher-owned/licensed supply | Ingestion and publication |
| Price and paid scope | One 30-day study pass; price unset | Checkout copy/merchant production |
| Primary payment gateway | Whichever has completed merchant onboarding | Gateway integration |
| Brand assets and language scope | Proposed navy/blue design; English/Nepali capable | Final design QA |
| AI and tutor availability | Disabled for MVP | P1 contracts and evaluation |
| Hosting budget and expected concurrency | Managed app/database/storage; load target to validate | Provisioning |

## 9. Handoff instruction

Implement the MVP from these documents in small verified increments. First resolve dependencies and create the Laravel shell; then identity/curriculum, ingestion/review, library, assessment, and commerce. Use the visual concept as design direction, translate components into Blade/Livewire/Tailwind, and retain business rules in shared application actions. Do not present prototype metrics or simulated functionality as live product behavior.
