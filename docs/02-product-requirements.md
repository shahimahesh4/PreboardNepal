# Preboard Nepal product requirements

Everything below is proposed unless explicitly linked as external evidence. Product owner decisions are captured in the delivery plan.

## 1. Product promise and audience

**Study the right material. Practise with confidence. Know what to improve.**

Primary persona: a Grade 12 learner using a phone, preparing for school and board examinations, needing trustworthy material and a manageable revision path. Secondary personas: teachers contributing and reviewing resources; moderators managing quality; operations staff handling payments and support. Later: institutions managing classes and tutor specialists resolving doubts.

The initial segment is a planning assumption. Interview 8–12 students and 3–5 teachers before selecting three launch subjects. Use licensed teacher material as the initial supply; do not rely on students to populate an empty library.

Curriculum records must identify their source and version. Nepal's Curriculum Development Center publishes grade-specific curricula; individual subjects must be checked against the applicable source before content is labelled aligned. [CDC curriculum example](https://moecdc.gov.np/content/158/secondary-education-curriculum-2076-grade-11-12-part-3/)

## 2. Roles and permissions

| Role | Allowed scope |
|---|---|
| Guest | Browse published metadata, open free previews and public sample resources |
| Student | Manage own profile, saves and attempts; access entitled content; pay; report problems |
| Contributor | Student permissions plus own submissions and revision history; no self-publication |
| Teacher/reviewer | Assigned subjects and review queues; create questions and explanations; no self-approval |
| Moderator | Review and remove assigned content; handle reports; cannot change financial records |
| Support | Limited account/payment status and support actions; private files only through audited case access |
| Finance | Reconcile payments and approve refunds; cannot edit exam answers |
| Administrator | Manage configuration and staff assignments; privileged actions audited; MFA mandatory |
| Institution manager, later | Own institution memberships/classes and permitted aggregate reports only |

Users may hold several roles, but permissions remain resource-specific. A role alone does not grant access to every private resource. Staff cannot read private AI conversations by default.

## 3. Scope matrix

| ID | Module | MVP | Later |
|---|---|---|---|
| IDN | Identity | Email registration, verification, password reset, profile, locale, grade, subjects | Optional verified phone login, social login |
| CUR | Curriculum | Versioned boards/programs, grade, subject, chapter, learning outcomes | Additional academic segments |
| LIB | Library | Search, filters, document preview, protected access, bookmarks, reporting | Collections, highlights, collaborative lists |
| CMS | Content operations | Staff PDF upload, processing, review, publication, withdrawal | Student uploads and reward credits |
| EXM | Assessment | Chapter MCQs and reviewed mixed-format mock papers, attempt history | Adaptive practice, class assignments |
| PRG | Progress | Recent activity, objective scores, weak-topic recommendations | Spaced repetition and personalized planner |
| PAY | Commerce | Free tier, one paid pass, one local gateway, receipts, manual renewal | Second gateway, approved coupons, institution seats |
| OPS | Administration | Filament curriculum/content/users/payments/reports and audit log | Teacher portal and institutional panel |
| AID | AI study helper | Not required for launch | Authorized document Q&A with citations and limits |
| TUT | Teacher help | Not required for launch | Asynchronous questions, review, disputes, limited capacity |
| CRD | Contribution economy | Not required for launch | Review-gated credits, expiry and reversals |
| EXT | Extra tools | Not required for launch | Flashcards, PWA, original textbook guides; native apps only if justified |

## 4. Core journeys

### Discover and study

Guest selects grade and subject → enters query → filters to a chapter/type → reads document metadata and preview → creates an account if needed → opens free content or purchases access → saves resource → starts related practice → sees explanations and relevant next material.

Never force registration before search. Preserve the requested destination through authentication and checkout. Show syllabus version, language, page count, resource type, review status, and whether access is free or paid before purchase.

### Onboard and resume

Verified student selects grade, preferred explanation language, subjects, and optional exam date. School is optional. The dashboard offers one primary next action and recent work. Changing grade does not delete older saves or attempt history; label older work clearly.

### Teacher publication

Teacher creates metadata and uploads → quarantine and processing → receives actionable errors if processing fails → reviewer checks rights, quality and curriculum → approves a version → resource is published and indexed. Revisions create a new version; the old approved version stays available until replacement is approved unless withdrawn for safety/rights reasons.

### Practice and improve

Student sees duration, question types and scoring → starts an immutable exam version → answers with autosave and visible save status → submits or reaches deadline → receives objective results and reviewed explanations → sees subjective work marked as awaiting review or self-assessment → chooses a recommended chapter.

## 5. Detailed requirements and acceptance criteria

### IDN-01 — identity and profile

Collect only display name, email, password, locale and academic preferences initially. Keep school, phone and exact birth date optional unless a documented requirement exists. Record policy version and acceptance time. Enforce staff MFA and rate-limit sign-in/recovery.

Acceptance: email verification gates uploads and purchases; password reset responses do not expose account existence; user A cannot alter user B's profile; account deletion removes access and initiates the documented data-retention process.

### CUR-01 — curriculum catalogue

Hierarchy: program/board → curriculum version → grade/course → subject → chapter → learning outcome. Each published resource references the exact relevant version. Support Nepali/English display names, subject aliases, publication years with an explicit calendar, and archived syllabi.

Acceptance: changing a chapter in a new curriculum cannot silently reclassify old resources; an empty chapter presents useful alternatives; admins cannot delete referenced taxonomy without reassignment or archival.

### LIB-01 — discovery

Search titles, approved descriptions and permitted extracted text. Filters: grade, subject, chapter, type, syllabus version, language, exam year and free/paid. Sort by relevance, recently reviewed, or newest. Persist filters in the URL; use pagination. Proposed ranking boosts exact subject/chapter match, valid curriculum and reviewed content; relevance remains measurable rather than guaranteed.

Acceptance: unpublished/withdrawn/private resources never appear to guests; paid-content snippets contain only approved preview text; changing grade clears incompatible filters; zero-results view offers reset and related chapters; keyboard users can submit and navigate results.

### LIB-02 — reader and access

Reader supports pagination, zoom, readable metadata, related practice, save and report. Offer an accessible text rendition when permitted. Keep originals private. Deliver only designated preview pages to unauthorized users; CSS blur is not access control. Download permission is separate from read permission and shown explicitly.

Acceptance: a guessed storage URL and direct Livewire action cannot fetch full paid content; withdrawn resources are denied even with an old entitlement; expired access receives a clear renewal path; readable preview does not contain hidden full-text payloads.

### CMS-01 — ingestion and moderation

Proposed initial upload limit: PDF only, 25 MB, maximum 200 pages. Validate actual file type; reject encrypted PDFs, executable content and corrupt files. Use quarantined storage, malware scanning, isolated rendering, text extraction/OCR, checksum matching and metadata validation. Low-confidence OCR needs review, especially for Devanagari, equations and tables. Add DOCX/images only after safe conversion and quality workflows exist.

States: draft → uploaded → processing → needs_review → published. Alternate states: processing_failed, revision_requested, rejected, withdrawn. Record every transition, reviewer, reason and published version. Approval requires evidence of ownership/license, accurate mapping and readable pages.

Acceptance: retries cannot publish twice; failed OCR remains visible to staff with retry options; duplicate files do not earn rewards; non-reviewers cannot approve; an author cannot approve their own submission; withdrawal invalidates search/cache and future file access.

### EXM-01 — practice and mock exams

Question types: single-select objective questions, typed short answers and downloadable/written-paper exercises. A full mock can have objective scoring plus a separate subjective rubric; do not imply MCQs reproduce every examination format. Faculty validates the paper blueprint, chapter weighting, marks and instructions against the selected curriculum.

Version exam definitions, questions, options, answer keys and rubrics. Store an attempt's selected question order and scoring rules at start. Server stores deadline and checks every save. Client countdown is display only. Autosave uses answer revisions to reject stale writes. A background finalizer submits overdue attempts even if the browser closes.

Acceptance: refresh restores saved answers and deadline; repeated submission produces one result; late answers cannot change a submitted attempt; disconnect message differentiates saved and unsaved work; answer keys are absent from active exam responses; accommodations use an authorized per-attempt duration. No unsupported automatic essay grading.

### PRG-01 — learning progress

Show attempted questions, accuracy, subject practice history and topic coverage separately. Reading a document is activity, not proof of mastery. Proposed weak-topic rule: at least five objective responses in a topic and accuracy below 60%; otherwise display insufficient evidence. Repeated questions should not artificially count as broad syllabus coverage.

Acceptance: result recalculation preserves the scoring version; incomplete subjective marking is visible; a new user sees a useful first-study action; recommendations link to eligible published resources.

### PAY-01 — study passes and payments

Launch with Free and a fixed-duration Study Pass. Free includes selected full resources and sample practice. The pass includes a clearly enumerated resource/exam scope for a stated duration. Proposed duration: 30 days; price is unresolved pending interviews and unit economics. Do not advertise automatic renewal until the payment provider and merchant agreement support it.

Create the order server-side, snapshot the offer/price, initiate the gateway transaction, verify the result from the provider and grant access atomically. Pending transactions stay pending. A success URL, screenshot or client request is insufficient evidence of payment.

Acceptance: duplicate callbacks grant one entitlement; incorrect amount or order reference grants none; an abandoned browser return can still be reconciled; receipt shows amount/currency/period; renewal of an active identical pass extends its existing end date; refund is tracked and explicitly adjusts the affected entitlement.

### AID-01 — AI helper, P1

Modes: explain a selected concept, ask about an authorized document, produce a draft practice set, summarize a chapter. Retrieve only approved content that the user may read, attach page references and distinguish generated text from teacher-reviewed content. Default to hints and explanations. Avoid live-assessment answer delivery; do not promise infallibility.

Bound usage per account, request length, retrieval size and daily spend. Treat uploaded instructions as untrusted document text. If reliable evidence is absent, say so and point to reviewed resources. Human review is required before generated questions enter the public bank. AI failure must not disable the reader or practice system.

Acceptance: no cross-user/private/locked material enters retrieval; every displayed citation resolves to an authorized page; usage reservations reconcile on provider failure; poor responses can be reported; deletion removes associated retrieval data. Proposed evaluation gate: faculty approves at least 90% of a representative 100-question test set, with zero observed authorization leaks; this is a launch target, not an achieved result.

### TUT-01 — teacher questions, P1

Student submits one clear question, subject and permitted attachments. Reserve a question credit, assign an available verified teacher, request clarification if necessary, deliver reviewed answer and allow a bounded follow-up. States: submitted, triaged, assigned, clarification_needed, answered, resolved, disputed, canceled. Release reservations on cancellation before work starts or expiry without service.

Set service hours and capacity before selling help. A proposed one-business-day initial response target is not a 24/7 promise. Finance reviews disputes; any future teacher payout requires separate payable records and onboarding. Public reuse of a Q&A requires explicit consent and redaction.

### CRD-01 — contribution credits, P1

Introduce only after moderation can handle volume. Example policy for testing: one document credit per eligible reviewed original contribution, capped at five per week. This is a Preboard Nepal proposal, not Course Hero's policy. Credits have no cash value. Configure reward and expiry policy with a version; show expiry before earning/spending.

Use append-only ledger events, a unique reward event per approved contribution, transactional spending, and explicit reversal entries. Reopening a previously unlocked version must not charge again. If a later takedown reverses an earned reward already spent, prevent additional spending and send the case to review rather than silently generating a cash debt.

## 6. Support, privacy and content governance

Provide report categories for inaccurate content, outdated syllabus, privacy and rights. Track status and resolution; hide material immediately when a reviewer determines removal is required. Maintain a rights-evidence record and appeal route. Explain what “teacher reviewed” means and display review date. Do not use a badge for unreviewed uploads.

Because the intended audience may include minors, collect minimal data, keep profiles private, and avoid unsolicited direct messaging. Before launch, obtain local professional review of Nepal-specific privacy, consumer, tax, child-user and educational-content obligations. These are implementation dependencies, not legal conclusions in this document.

## 7. Nonfunctional targets

Proposed release targets: 99.5% monthly availability; p95 cached catalogue/search responses under 1 second in the agreed load environment; mobile LCP at or below 2.5 seconds on an explicitly documented test profile; no unauthorized full-content access in the security test suite. Define measured network, device, data size and concurrency before treating these as acceptance results.

Support 375px phones through desktop, keyboard navigation, 200% text zoom, reduced motion, readable math, and Nepali text. Provide recovery states for failed upload, queued processing, offline attempt, pending payment, empty search, removed resource and unavailable AI.
