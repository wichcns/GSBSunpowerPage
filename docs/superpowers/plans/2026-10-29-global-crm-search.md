# Global CRM Search Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give authenticated CRM users one permission-aware global search that groups place/contact, pipeline, and contract results.

**Architecture:** A dedicated read-only `GlobalCrmSearch` service will combine the existing Site Eloquent model and legacy Pipeline/Contracts read models without changing their data sources. A thin controller validates the query and renders a new Blade page; the existing top-bar component supplies the one shared form.

**Tech Stack:** Laravel, Blade, Eloquent, existing CRM read models, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-29-global-crm-search-design.md`

## Global Constraints

- Edit only `D:\xampp\htdocs\gsb-sunpower`; do not modify the legacy project.
- Read-only CRM search; do not change customer data, public pages, `.env`, or secret values.
- Preserve existing page-level filters and existing module permissions.
- Support Thai, English, and Chinese UI copy.
- Limit query input to 100 characters and results to 10 per group.

## Review Focus

- Blank or whitespace-only query redirects to dashboard; cover in Task 2.
- A user without `sites.view`, `pipeline.view`, or `contracts.view` sees no corresponding result group; cover in Task 2.
- A non-stage pipeline user never sees unassigned pipeline IDs; cover in Task 1.
- Contact-only matches locate their owning site without duplicate site cards; cover in Task 1.
- Result labels and a malicious query are escaped in rendered Blade; cover in Task 3.

### Task 1: Permission-aware search service

**Files:**
- Create: `app/Crm/Services/GlobalCrmSearch.php`
- Test: `tests/Feature/CrmMigrated/Admin/GlobalSearchTest.php`

**Interfaces:**
- Consumes: `User $user`, `string $query`, `Site`, `LegacyPipeline`, `LegacyContracts`, `AssignedCrmRecords`.
- Produces: `GlobalCrmSearch::for(User $user, string $query): array` with `sites`, `pipeline`, and `contracts` keys; each key contains at most 10 normalized rows or is absent when unauthorized.

- [ ] **Step 1: Write failing tests for matching, contact deduplication, and assigned pipeline isolation**
- [ ] **Step 2: Run `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/GlobalSearchTest.php` and verify the expected missing-service failure**
- [ ] **Step 3: Implement `GlobalCrmSearch::for(User $user, string $query): array` with only the specified matching, limit, and permission rules**
- [ ] **Step 4: Re-run the focused test and verify it passes**

### Task 2: Global search route and controller

**Files:**
- Create: `app/Crm/Http/Controllers/Admin/GlobalSearchController.php`
- Modify: `routes/crm.php`
- Modify: `tests/Feature/CrmMigrated/Admin/GlobalSearchTest.php`

**Interfaces:**
- Consumes: `GlobalCrmSearch::for(User $user, string $query): array`.
- Produces: named route `crm.admin.search` at `GET /crm/admin/search`, rendering `crm.admin.search.index` for a nonblank query.

- [ ] **Step 1: Write failing request tests for authenticated result rendering, blank query dashboard redirect, and permission-hidden groups**
- [ ] **Step 2: Run the focused test and verify it fails because the route/controller do not exist**
- [ ] **Step 3: Add the authenticated `crm.admin.search` route and controller validation/redirect behavior**
- [ ] **Step 4: Re-run the focused test and verify it passes**

### Task 3: Responsive search form, results page, and translations

**Files:**
- Modify: `resources/views/crm/components/admin/topbar.blade.php`
- Create: `resources/views/crm/admin/search/index.blade.php`
- Modify: `lang/th/ui.php`
- Modify: `lang/en/ui.php`
- Modify: `lang/zh/ui.php`
- Modify: `tests/Feature/CrmMigrated/Admin/GlobalSearchTest.php`

**Interfaces:**
- Consumes: `$query` and the normalized `$results` returned by `GlobalSearchController`.
- Produces: an accessible GET form using route `crm.admin.search`, group headings, result links, and translated empty-state output.

- [ ] **Step 1: Write a failing rendered-response test for the top-bar form, escaped query/result output, and direct result links**
- [ ] **Step 2: Run the focused test and verify it fails because the search view/form is absent**
- [ ] **Step 3: Add the responsive Blade form, result groups, and three-language `ui` copy using existing CRM classes**
- [ ] **Step 4: Re-run the focused test and verify it passes**

### Task 4: Focused regression verification

**Files:**
- Modify: `.superpowers/sdd/2026-10-29-global-crm-search/progress.md`

- [ ] **Step 1: Run `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/GlobalSearchTest.php tests/Feature/CrmMigrated/Admin/PipelineTest.php tests/Feature/CrmMigrated/Admin/SiteManagementTest.php tests/Feature/CrmMigrated/Admin/ContractsTest.php`**
- [ ] **Step 2: Run `vendor/bin/pint --format agent` on changed PHP files and `git diff --check`**
- [ ] **Step 3: Run `vendor/bin/phpunit`; record any existing environment or fixture failure distinctly from this change**
- [ ] **Step 4: Record test results, decisions, and scope deviations in the SDD ledger**

## Self-review

- The three specified result domains, the one global form, localized responsive UI, existing page filters, authorization, query limits, and no-write guarantee each map to Tasks 1–3.
- Interfaces are deliberately small: controller owns HTTP validation and service owns read/query authorization.
- The five likely user-facing failure modes are listed above and assigned tests.
- The plan does not introduce an external search system or unrequested result domains.
