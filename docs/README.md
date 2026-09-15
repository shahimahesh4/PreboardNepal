# Preboard Nepal — product and engineering blueprint

Prepared 14 September 2026 • Version 1.0 • Status: proposed build specification

## Recommendation

Build a Nepal-focused learning platform around **find → understand → practise → improve**. Use Course Hero's resource discovery and study workflows as inspiration, then differentiate through curriculum mapping, teacher-reviewed explanations, realistic mock exams, Nepali/English support, and accessible local payment options.

Start with one well-supported academic segment: **Grade 12, three subjects selected according to available teacher expertise**. This is a proposed starting scope, not a claim about Preboard Nepal's existing audience. Expand to SEE, Grade 11, and higher education after validating content quality and repeat usage.

## Documents

1. [Course Hero research](01-coursehero-research.md): verified public features, business model, workflows, limitations, and adaptation decisions.
2. [Product requirements](02-product-requirements.md): personas, feature priorities, user journeys, detailed business rules, and acceptance criteria.
3. [Technical architecture](03-technical-architecture.md): Laravel stack, domain model, database tables, authorization, payment flow, queues, tests, and deployment.
4. [UI/UX specification](04-ui-ux-specification.md): navigation, page layouts, design tokens, responsive behavior, accessibility, and component inventory.
5. [Delivery plan](05-delivery-plan.md): milestones, launch gates, operating model, measurement, risks, and decisions.
6. [Interactive visual concept](../prototype/index.html): dashboard, searchable library, document preview, saved items, and a short practice interaction. Open locally in a browser. All content and metrics are illustrative.

7. [Nepal payment integrations](07-nepal-payment-integrations.md): eSewa/Khalti setup, verification rules and additional gateway options.

## What exists in this delivery

The repository now contains the first working Laravel application in addition to this research and the standalone visual prototype. See [implementation status](06-implementation-status.md) for completed functionality, local adaptations and remaining MVP work. The application runs locally; it has not been deployed to the public domain. The older prototype remains a design reference.

The starting repository contained README and LICENSE files, with no Laravel application. The public Preboard Nepal site could not be retrieved by the research tool, and searches did not provide a reliable site inventory. Its current pages, hosting, users, content, analytics, and integrations remain unverified. Do not use this document as an audit of the existing production system.

## Architecture decision

Use a Laravel modular monolith, custom Blade/Livewire/Tailwind student interfaces, and Filament for staff administration. Keep payment, entitlement, document processing, and exam scoring rules in reusable application services. Prefer a reliable learning experience and reviewed content over launching every competitor feature at once.

## Reading conventions

**Verified** means supported by a linked public source. **Proposed** means a recommendation for Preboard Nepal. **Unverified** means inaccessible, account-dependent, or not established by this research. Estimates and example product limits are planning assumptions, not vendor quotes or production commitments.

## Delivery verification

Checked local documentation links, JavaScript syntax and Git whitespace. Browser checks confirmed search for circuits returns two sample resources, a saved resource appears in Saved, the reader opens, and selecting the correct first practice answer displays its explanation. Dashboard layouts were visually checked at 375px and 1440px widths. This is a prototype smoke check, not full accessibility certification or end-to-end application testing. No production deployment or external account changes were made.
