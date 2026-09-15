# UI/UX and design system

Status: proposed visual direction, not an audit of current branding. The accompanying [interactive concept](../prototype/index.html) demonstrates a subset using illustrative content and local browser state.

## 1. Direction

Create a calm, editorial learning workspace: warm off-white background, deep navy navigation, strong blue actions, generous whitespace, readable cards, and small subject-color accents. A student should immediately understand the next useful action. The design system was informed by the UI/UX Pro Max education/dashboard guidance and adapted for a bilingual web product.

Use a distinct Preboard Nepal wordmark concept until official brand assets are provided. Avoid stock-photo hero clutter, decorative animation, dense admin tables on student pages, unexplained credit counters and invented social proof.

## 2. Tokens

| Token | Proposed value | Use |
|---|---|---|
| brand/navy | `#132C47` | Navigation, headings on light background |
| action/primary | `#2454D6` | Primary button with white text |
| action/hover | `#1D43AD` | Hover/pressed action |
| surface/page | `#F5F6F8` | Application background |
| surface/card | `#FFFFFF` | Reader, cards, forms |
| text/primary | `#182C42` | Main body |
| text/secondary | `#536579` | Secondary text; verify contrast in context |
| border/subtle | `#E0E5ED` | Layout divisions; not the sole input boundary |
| success | `#176548` | Saved/verified states with icon/text |
| warning | `#875600` | Pending/review states |
| danger | `#B42332` | Validation/failure states |
| accent/lilac | `#EFEBFB` | Physics/subject surface; dark text |
| accent/mint | `#E5F3EE` | Biology/positive surface; dark text |
| accent/sand | `#FFF1D9` | Practice/reminder surface; dark text |

Use semantic CSS variables shared by Tailwind components and Filament theme. Body text 16px, line-height 1.6; small metadata 13–14px; card headings 18–22px; page titles 30–36px desktop, 26–30px mobile. Use a readable sans-serif with a Devanagari-capable fallback; proposed production fonts are Inter and Noto Sans Devanagari, self-hosted after license/performance checks. The portable concept uses system fonts.

Spacing scale: 4, 8, 12, 16, 24, 32, 48, 64px. Cards 16–20px radius; controls 10–12px radius; buttons at least 44px high. Text blocks generally 60–75 characters wide. Shadows subtle, no movement on hover. Motion 120–180ms for state changes, disabled with reduced-motion preference.

## 3. Navigation and information architecture

Public: Home → Browse resources → Subjects → Practice → Plans → Sign in. Footer: About, Help, Contact, Content policy, Privacy, Terms, Accessibility. Keep published resource and subject pages crawlable with server-rendered metadata.

Student: Overview, Library, Practice, Saved; account and support available consistently. Add AI help and teacher questions only when enabled. Desktop sidebar approximately 232px; tablet collapses; mobile uses a compact menu or four labelled navigation items. Avoid duplicated fixed navigation that hides content.

Admin: a separate Filament layout with operational navigation. Teacher role sees assigned review work; finance sees commerce. Navigation visibility reflects policies, but does not replace authorization.

## 4. Screen specifications

| Screen | Main layout and action | Important states |
|---|---|---|
| Landing | Clear promise, grade/subject search, sample resources, benefits, transparent plan link | No fabricated counts/testimonials; accessible registration |
| Onboarding | Three short steps: grade, subjects, language/optional date | Skip optional fields, back without losing data |
| Dashboard | Greeting/context; one continue-study action; subjects; recent resources; compact progress | First visit, no selected subjects, exam date unknown |
| Library | Search above URL-backed filters; readable result cards; curriculum/access labels | Loading skeleton, zero results, reset filters, pagination |
| Subject hub | Syllabus selector, chapter list, notes/practice/papers tabs | Archived version, chapter without reviewed content |
| Resource reader | Title and trust metadata; document canvas; related practice; save/download/report | Preview, entitled, expired, withdrawn, rendering failed |
| Practice catalogue | Duration, question count, chapter coverage, scoring and access | Locked exam, sample available, old version |
| Exam runner | Minimal header, countdown, question content, answer choices, save status, navigation | Unsaved changes, offline, deadline, flagged question |
| Results | Score where valid, pending subjective grade, topic breakdown, explanations, next steps | No grade yet, partial review, invalidated question |
| Saved | Resource cards with remove action and subject filters | Empty helpful state, withdrawn resource marker |
| Upload, P1 | Metadata wizard, rights declaration, dropzone, processing status | Type/size error, retry, duplicate, review requested |
| AI, P1 | Resource context selector, conversation, citations, quota | No source, timeout, restricted content, feedback |
| Tutor, P1 | Question editor, expectations/credits, ticket timeline | Queue full, clarification, answered, dispute |
| Checkout | Offer scope, NPR total, access duration, payment method | Pending verification, canceled, verified, retry |
| Account | Profile, subjects, language, access expiry, receipts and data controls | Verification needed, deletion pending |

### Dashboard desktop composition

```text
┌──────────────┬────────────────────────────────────────────────┐
│ Preboard     │ Search resources                   Profile     │
│              ├────────────────────────────────────────────────┤
│ Overview     │ YOUR LEARNING SPACE                            │
│ Library      │ A little progress, every day.                  │
│ Practice     ├──────────────────────────────┬─────────────────┤
│ Saved        │ Continue your revision       │ Weekly activity │
│              │ Chapter + context + CTA      │ Meaningful data │
│              ├──────────────────────────────┴─────────────────┤
│              │ Your subjects                                  │
│              │ Subject card  /  Subject card  /  Subject card │
│              ├────────────────────────────────────────────────┤
│              │ Recommended resources                          │
│              │ Title, type, chapter, language, review status  │
└──────────────┴────────────────────────────────────────────────┘
```

On phones, preserve the order: next action, subjects, recent resources, progress. Collapse supplementary progress rather than squeezing all cards into a small screen. Exam runner hides promotional material and uses a single-column question view.

## 5. Reusable components

AppShell, PublicHeader, MobileNavigation, SearchField, FilterChip, FilterDrawer, SubjectCard, ResourceCard, AccessBadge, ReviewBadge, EmptyState, InlineError, LoadingSkeleton, PdfReader, CitationLink, QuestionCard, SaveStatus, ExamNavigator, ResultSummary, PlanCard, PaymentStatus, UploadDropzone and ConfirmationDialog.

Each component needs keyboard/focus behavior, mobile layout, loading/disabled state, validation and screen-reader naming where interactive. Icon-only buttons require labels; use a consistent Heroicons SVG set in production. Keep long titles wrapping instead of hiding important information in tooltips.

## 6. Copy and learning behavior

Suggested headline: “Your next exam. A clearer plan.” Suggested CTA: “Find study resources.” Use “Teacher reviewed · Updated [date]” only when backed by review records. Use “Practice accuracy” for objective questions instead of an unsupported “Exam readiness” percentage.

Examples: “Your answer is saved”; “Connection lost. Your latest change has not reached the server”; “We are checking your payment. You will not be charged again by refreshing”; “This explanation is AI-generated. Check the linked source.” Avoid shame-based streak messages and guaranteed-grade claims.

English/Nepali interface language and document language are separate settings. English text may accompany a Nepali explanation. Preserve equations, mixed scripts and line spacing. Do not silently machine-translate reviewed exam questions and present them as reviewed translations.

## 7. Accessibility and low-bandwidth requirements

Target WCAG 2.2 AA. Verify 4.5:1 normal text contrast, 3:1 large text and meaningful controls, visible focus, logical headings, labelled inputs, keyboard-operated dialogs, and no color-only status. Use at least 44px interaction areas as a usability target. Preserve browser zoom; do not disable pinch zoom.

Load document pages progressively, defer heavy viewer/AI assets, reserve image space, compress thumbnails and show file size before downloading. Provide an accessible text alternative where rights permit. Do not cache paid documents in a service worker by default. An offline indicator is not a promise that a timed exam works offline; authoritative saved state remains on the server.

## 8. Prototype boundaries

The HTML concept includes dashboard/library/practice/saved navigation, text and subject filtering, document preview, bookmark toggling and a three-question practice sample with feedback. Sample numbers, resource names and review badges are illustrative. It has no real accounts, document files, payment, backend, production security or real exam autosave. It is a visual handoff reference for the Laravel implementation, not code to deploy as the full product.
# 2026 palette refinement

The implemented interface uses a warm, optimistic education palette inspired by the supplied Literate reference while retaining Preboard Nepal's own content, components and navigation. The primary action color is accessible violet (`#6D28D9`), supported by coral (`#D94752` for readable text, `#FF676D` for decorative fills), amber (`#A96000` for readable text, `#FFB522` for decorative fills), warm canvas (`#FCFBF8`), lavender surface (`#F3EFFD`), charcoal ink (`#29272D`) and muted plum-gray (`#675F6F`). Violet anchors navigation, focus, progress and selected states; coral and amber are reserved for emphasis and categorical accents.
