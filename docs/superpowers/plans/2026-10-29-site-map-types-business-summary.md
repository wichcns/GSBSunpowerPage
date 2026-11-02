# Site Map, Types, and Business Summary Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add immediate map access, controlled location types for future edits, and practical commercial-condition summaries in the sales pipeline.

**Architecture:** A shared `LocationTypes` support class provides canonical codes, localized labels, and legacy-display fallback to both site and pipeline write/read paths. Existing Site and LegacyPipeline read models remain the source of truth; the pipeline gains only the four business summary values required for rendering.

**Tech Stack:** Laravel, Blade, existing CRM read models, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-29-site-map-types-business-summary-design.md`

## Global Constraints

- Edit only the main `gsb-sunpower` project; never edit the legacy project.
- Do not bulk-edit real site/customer data or legacy payloads.
- Do not change stored sales probability or stage behavior.
- Permit only HTTP(S) map links and preserve all existing CRM permissions.
- Provide Thai, English, and Chinese UI copy.

## Review Focus

- A legacy free-text type must still render but cannot be newly submitted; test in Task 1.
- Missing or malformed map URLs must not produce a clickable external link; test in Task 2.
- Current business overrides must win over imported values; test in Task 3.
- Empty commercial fields must use localized fallback without displaying probability; test in Task 3.
- Site and pipeline editors must expose the same five choices; test in Task 2.

### Task 1: Canonical location-type contract

**Files:**
- Create: `app/Crm/Support/LocationTypes.php`
- Modify: `app/Crm/Http/Requests/SaveSiteRequest.php`
- Modify: `app/Crm/Http/Requests/SaveUnifiedLeadRequest.php`
- Modify: `lang/th/sites.php`, `lang/en/sites.php`, `lang/zh/sites.php`
- Test: `tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

**Interfaces:**
- Produces `LocationTypes::codes(): array` and `LocationTypes::label(string|null): string`.
- Both write requests validate `location_type` against `LocationTypes::codes()` while allowing null.

- [ ] Write failing tests for accepted canonical values, rejected ad-hoc values, and legacy type display.
- [ ] Run the focused test and confirm the expected validation failure.
- [ ] Implement the support class, request validation, and three-language labels.
- [ ] Re-run the focused test and confirm it passes.

### Task 2: Site and profile map/type UI

**Files:**
- Modify: `app/Crm/Http/Controllers/Admin/SiteController.php`
- Modify: `resources/views/crm/admin/sites/index.blade.php`
- Modify: `resources/views/crm/admin/pipeline/partials/detail-drawer.blade.php`
- Modify: `lang/th/sites.php`, `lang/en/sites.php`, `lang/zh/sites.php`
- Modify: `tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

**Interfaces:**
- Consumes `LocationTypes::codes()` and localized labels.
- Produces a select control for new/editable records, legacy value display, and safe new-tab map links.

- [ ] Write failing rendered-response tests for five options, safe map links, and missing-link behavior.
- [ ] Run focused tests and confirm they fail before UI changes.
- [ ] Render the controlled select and map action using existing drawer patterns; retain legacy display without changing stored data.
- [ ] Re-run focused tests and confirm they pass.

### Task 3: Pipeline commercial-condition summaries

**Files:**
- Modify: `app/Crm/ReadModels/LegacyPipeline.php`
- Modify: `resources/views/crm/admin/pipeline/index.blade.php`
- Modify: `lang/th/pipeline_page.php`, `lang/en/pipeline_page.php`, `lang/zh/pipeline_page.php`
- Test: `tests/Feature/CrmMigrated/Admin/PipelineTest.php`

**Interfaces:**
- `LegacyPipeline::all()` adds normalized `commercial_model`, `rental_cost`, `rental_unit`, and `parking_spaces` values, prioritizing `crm_business_terms.values` over legacy payload values.
- Pipeline list and board render the three-condition summary and omit `probability_display`.

- [ ] Write failing tests for current override precedence, condition display, no-probability summary, and missing-value fallback.
- [ ] Run `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineTest.php` and confirm failure.
- [ ] Implement the read-model enrichment and localized responsive summary markup.
- [ ] Re-run the focused test and confirm it passes.

### Task 4: Regression verification and ledger

**Files:**
- Create: `.superpowers/sdd/2026-10-29-site-map-types-business-summary/progress.md`

- [ ] Run site/pipeline business focused PHPUnit files.
- [ ] Run Pint on changed PHP and `git diff --check`.
- [ ] Run the full PHPUnit suite and record unrelated failures separately.
- [ ] Record verification, non-goals, and deviations in the ledger.

## Self-review

- The map link, five controlled type choices, legacy retention, commercial-condition summary, no-probability rendering, translations, permissions, and read-only data constraint all map to Tasks 1–3.
- Task interfaces share one source for canonical location types and keep existing persistence paths intact.
- Every review-focus condition has an owning task and test.
