# Working application — implementation status

Date: 14 September 2026. This records the first implementation increment against the larger product specification. It is a working local application, not a completed production launch.

## Implemented

- Laravel application with locked Composer/npm dependencies, SQLite migrations and repeatable seed data.
- Filament 5 staff panel with mandatory authenticator MFA enrollment, verified-admin access, server-side policies, and no public staff registration.
- Livewire 4 student library, filters, private bookmarks, reader, reporting, profile editor and timed practice runner.
- Tailwind 4/Vite theme with custom responsive landing page, subject hubs, dashboard, practice catalogue, results, account and help pages.
- Registration, sign-in, sign-out, password recovery and signed email verification. Development emails go to the Laravel log until a mail provider is configured.
- Nine original foundation resources across physics, biology and mathematics; three five-question practice sets. They are labelled as foundation examples awaiting formal curriculum review.
- Server-side content access checks, free/premium preview separation and expiry/revocation-aware entitlement lookup. Premium content is never sent as a hidden/blurred full body to unauthorized users.
- Practice attempt snapshots, owner authorization, persistent answer selection, stale-write detection, server deadlines, idempotent final submission, scoring and explanations after submission.
- Scheduler command to finalize overdue attempts. Browsing an overdue attempt also finalizes it, so recovery works when the browser has been closed.
- Filament management for subjects, chapters, editorial documents, exams, reports and basic user names; read-only audit listing and operational counts.
- Independent publication review: edits return content to draft, increase the revision/version, and require a different administrator to publish. Publication and withdrawal produce audit entries.
- Local-only, opt-in student demo. There is no hard-coded staff password, public administrator registration or production demo login.

## Deliberate local adaptations

The supplied runtime is PHP 8.2.12. This increment uses Laravel 12.69.2, Filament 5.8.1 and Livewire 4.4.4, with Tailwind 4 and Vite 6 resolved in the lockfiles. The proposed Laravel 13 baseline requires PHP 8.3 or later. Upgrade PHP and Laravel before production planning, re-resolve dependencies, and rerun the suite; platform requirements were not bypassed.

SQLite runs locally without additional services. PostgreSQL, Redis and object storage from the architecture specification are not provisioned. Content is safe-rendered editorial Markdown in the database, not a PDF ingestion pipeline. No live production system was replaced.

## Still to implement for the full MVP

1. Formal curriculum versions/learning outcomes, faculty validation, broader resource coverage and bilingual interface copy. A profile grade preference does not imply all grades have content.
2. Private PDF ingestion, malware scanning, isolated rendering, OCR, rights-evidence attachments, failed-job review and page delivery. Arbitrary file attachments are disabled in the editorial forms until that pipeline exists.
3. Document version history that keeps an old approved edition live while a new draft is reviewed. Current editorial changes take the resource out of public circulation until reapproved; exam attempts already retain their immutable snapshot.
4. Real merchant sandbox acceptance testing, finance-approved offers and policies, refund initiation workflows, downloadable tax receipts, and production payment activation. eSewa and Khalti order creation, server verification, access grants, renewal extension, refund revocation and reconciliation now exist; checkout remains disabled by default.
5. Full mock papers with subjective rubrics/marking, accommodations and complete curriculum coverage. Current scoring is objective, one mark per question, no negative marking.
6. Granular reviewer/finance/support roles, expanded audit events, support case management, privacy export/deletion and finalized production retention policies. This increment restricts the whole staff panel to verified admins.
7. Production mail, hosting, scheduler supervision, backups, security/performance validation and existing-site migration/redirects.

AI help, tutoring, student contribution rewards, institution accounts and PWA/native apps remain later phases as described in the original plan.

## Verification

The automated suite uses a separate in-memory SQLite database. It covers public/private routes, account creation, role injection rejection, search and bookmarks, paid-preview isolation, entitlement expiry and withdrawn content, stale saves, deadline finalization, snapshot scoring, attempt ownership, Livewire save/submit, reporting, staff policies, independent review, seed idempotency and Filament table/form rendering.

Browser checks confirm local student login, saved-resource persistence across navigation, practice answer persistence after refresh, and responsive layouts. These checks establish the first development milestone; they are not a production load test or accessibility certification.

## Membership increment

Products are managed in Filament and default to inactive. Verified learners can create orders for enabled offers, continue to eSewa or Khalti, inspect status and view order history. Price and duration are copied from the database into an immutable order snapshot. Callback parameters never grant access: server lookup must match the stored payment identifier and exact amount, report a successful provider status, and contain a transaction identifier. Duplicate submissions reuse an idempotency key; duplicate verification grants one pass. A full or partial refund revokes the associated pass; partial refunds require staff review. No refund is initiated by this application.

The scheduled reconciliation command checks pending orders and paid orders from the last 30 days. Older refunds require an explicit staff status check. Network failures leave access unchanged. Unknown initiation responses are not automatically retried: staff must investigate against the merchant dashboard before a learner attempts another order. Changing gateway environments does not verify orders from the other environment. Sandbox payments cannot grant access in production.

Automated billing checks use mocked HTTP responses, not a merchant account. The integrations follow the official gateway documentation. See [Nepal payment integrations](07-nepal-payment-integrations.md) for eSewa/Khalti configuration and the connectIPS/QR roadmap. Live gateway acceptance testing, concurrent database testing on the production database, operational alerting, and finance sign-off remain necessary before enabling checkout.
