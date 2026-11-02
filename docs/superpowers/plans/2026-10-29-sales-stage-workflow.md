# Sales Stage Workflow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the CRM's sales-stage workflow with the nine stages requested in the review deck, while keeping imported records readable and preserving the existing role boundary for stage changes.

**Architecture:** Make `SalesStages` the code-level catalogue of writable stages, their internal probability values, and contract-eligible codes. Read models, request validation, board grouping, engineering handoff, notifications, and translations consume that catalogue rather than maintaining inconsistent stage lists. Existing imported or previously saved codes remain visible as legacy values, but users can only write the nine approved codes.

**Tech Stack:** Laravel 13, PHP 8.3, PHPUnit, Blade, Alpine.js

**Spec:** `C:\Users\User\Downloads\crm fix.pptx` (slides 5 and 7), plus the approved phase-one plan in this conversation.

## Global Constraints

- Edit only `D:\xampp\htdocs\gsb-sunpower`; do not edit the legacy `gsb-ev-crm` project.
- Do not migrate, overwrite, or otherwise change real customer/imported data.
- Preserve optimistic concurrency, stage history, notification dispatch, and the current `pipeline.stage` permission boundary.
- Keep Thai, English, and Chinese labels complete; no locale may render a translation key such as `stages.sales.REVIEW`.
- Do not add dependencies, credentials, or `.env` contents.

## Review Focus

- An imported pre-change code must remain readable and filterable without becoming writable.
- A staged change must reject every obsolete code with HTTP 422.
- A stale version must still reject a stage update with HTTP 409.
- Moving into contract signing or contract signed must create at most one engineering project and one outbox event.
- Staff without `pipeline.stage` must remain unable to call the stage-update route.

---

### Task 1: Define the approved sales-stage catalogue

**Files:**
- Modify: `app/Crm/Support/SalesStages.php`
- Modify: `app/Crm/ReadModels/LegacyPipeline.php`
- Test: `tests/Feature/CrmMigrated/Admin/PipelineStageTest.php`

**Interfaces:**
- Produces: `SalesStages::codes(): array`, `SalesStages::probability(string): ?int`, and `SalesStages::createsEngineeringProject(string): bool`.
- Consumed by the stage controller, pipeline read model, stage-change action, and later contract filtering.

- [ ] **Step 1: Write failing feature tests for the nine approved codes and obsolete-code rejection**

Assert that the writable codes are `NEW`, `INTERNAL_REVIEW`, `INTERNAL_APPROVED`, `MOU_SIGNING`, `MOU_SIGNED`, `CONTRACT_SIGNING`, `CONTRACT_SIGNED`, `ON_HOLD`, and `CANCELLED`; verify an old code such as `REVIEW` cannot be written.

- [ ] **Step 2: Run the focused test to verify it fails**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineStageTest.php --filter=approved`

Expected: FAIL because the new catalogue and validation do not yet exist.

- [ ] **Step 3: Implement the central catalogue and have `LegacyPipeline::STAGES` use its order**

Retain the existing stored probability field for backward compatibility, using 10, 30, 45, 65, 70, 82, 100, 15, and 0 respectively. Treat it as internal compatibility data only; its removal from the UI belongs to the later site/customer work.

- [ ] **Step 4: Run the focused test to verify it passes**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineStageTest.php --filter=approved`

Expected: PASS.

### Task 2: Apply the catalogue to writes, handoff, and board grouping

**Files:**
- Modify: `app/Crm/Http/Controllers/Admin/PipelineStageController.php`
- Modify: `app/Crm/Actions/ChangeSalesStage.php`
- Modify: `app/Crm/Http/Controllers/Admin/PipelineController.php`
- Modify: `tests/Feature/CrmMigrated/Admin/PipelineStageTest.php`
- Modify: `tests/Feature/CrmMigrated/Admin/PipelineTest.php`

**Interfaces:**
- Consumes: `SalesStages::codes()` and `SalesStages::createsEngineeringProject()` from Task 1.
- Produces: stage API behavior and board groups based on the requested workflow.

- [ ] **Step 1: Write failing tests for new stage validation, board placement, and engineering creation**

Use a `MOU_SIGNING` fixture for the MOU group; assert `CONTRACT_SIGNING` creates an engineering project exactly once, `CONTRACT_SIGNED` reuses it, and a legacy `REVIEW` import remains displayed as a legacy/other value.

- [ ] **Step 2: Run the focused tests to verify they fail**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineStageTest.php tests/Feature/CrmMigrated/Admin/PipelineTest.php --filter='stage|board'`

Expected: FAIL because validation, handoff, and groups still use the previous eight codes.

- [ ] **Step 3: Validate only approved codes and update stage-dependent behavior**

Use the central catalogue for request validation and probabilities. Group `NEW` and `INTERNAL_REVIEW` as early work, `INTERNAL_APPROVED`, `MOU_SIGNING`, and `MOU_SIGNED` as review/MOU, `CONTRACT_SIGNING` and `CONTRACT_SIGNED` as contract/signing, and `ON_HOLD`/`CANCELLED` as paused/closed. Create engineering work only for the two contract stages.

- [ ] **Step 4: Run the focused tests to verify they pass**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineStageTest.php tests/Feature/CrmMigrated/Admin/PipelineTest.php --filter='stage|board'`

Expected: PASS.

### Task 3: Complete three-language labels and eliminate leaked translation keys

**Files:**
- Modify: `lang/th/pipeline_status.php`
- Modify: `lang/en/pipeline_status.php`
- Modify: `lang/zh/pipeline_status.php`
- Modify: `lang/th/stages.php`
- Modify: `lang/en/stages.php`
- Modify: `lang/zh/stages.php`
- Modify: `lang/th/pipeline_page.php`
- Modify: `lang/en/pipeline_page.php`
- Modify: `lang/zh/pipeline_page.php`
- Modify: `tests/Feature/CrmMigrated/Admin/PipelineTest.php`

**Interfaces:**
- Consumes: the exact stage codes in `SalesStages::codes()`.
- Produces: consistent stage labels in the table, board, detail drawer, contracts filter, and translated page groups.

- [ ] **Step 1: Write failing locale tests**

Assert that each locale renders its label for `INTERNAL_REVIEW` and never emits `stages.sales.` in the pipeline response.

- [ ] **Step 2: Run the locale tests to verify they fail**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineTest.php --filter='chinese|locale'`

Expected: FAIL because the translations and legacy `stages.php` catalogue disagree.

- [ ] **Step 3: Add complete matching labels and revise group labels**

Use plain workflow wording: Thai `ลีดใหม่`, `ตรวจสอบภายใน`, `ผ่านการตรวจสอบภายใน`, `ขั้นตอนลงนาม MOU`, `MOU ลงนามแล้ว`, `ขั้นตอนลงนามสัญญาความร่วมมือ`, `สัญญาความร่วมมือลงนามแล้ว`, `พักไว้`, `ยกเลิก / ไม่ผ่าน`; translate the same meaning in English and Chinese.

- [ ] **Step 4: Run the locale tests to verify they pass**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineTest.php --filter='chinese|locale'`

Expected: PASS.

### Task 4: Verify permission, notification, and regression behavior

**Files:**
- Modify: `tests/Feature/CrmMigrated/Admin/PipelineStageTest.php`
- Modify: `tests/Feature/CrmMigrated/Admin/RoleAccessTest.php` (only if coverage is missing)

**Interfaces:**
- Consumes: Tasks 1–3.
- Produces: evidence that Admin/Manager retain `pipeline.stage`, Staff cannot change a stage, and notifications use the new translated labels.

- [ ] **Step 1: Write failing authorization and notification-label tests**

Verify a Staff account receives 403 from the stage route and an approved stage change records the correct new code/label in its stage history and notification payload.

- [ ] **Step 2: Run the focused tests to verify they fail**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineStageTest.php tests/Feature/CrmMigrated/Admin/RoleAccessTest.php --filter='stage|permission|notification'`

Expected: FAIL until the new workflow is wired throughout the change path.

- [ ] **Step 3: Make only the minimal compatibility changes required by the tests**

Keep the existing role-permission assignment unchanged: Super Admin, Admin, and Manager may use `pipeline.stage`; Staff may view/log only. Make the notification label resolve through the same stage catalogue used by the UI.

- [ ] **Step 4: Run the focused tests and CRM regression suite**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/PipelineStageTest.php tests/Feature/CrmMigrated/Admin/PipelineTest.php tests/Feature/CrmMigrated/Admin/RoleAccessTest.php`

Expected: PASS.

- [ ] **Step 5: Format PHP and run the full test suite**

Run: `vendor/bin/pint --dirty --format agent` then `vendor/bin/phpunit`

Expected: formatter succeeds and the full suite passes.
