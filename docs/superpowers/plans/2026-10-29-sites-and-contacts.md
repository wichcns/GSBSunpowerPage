# Sites and Contacts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the existing CRM Sites page a clear, safe single workspace for sites and their multiple contact people.

**Architecture:** Retain `crm_sites` as the parent record and `crm_contacts` as its owned contacts. Extend the existing `SaveSite` transaction and optimistic fingerprint guard to support explicit contact removal; do not add tables or migrate imported data. Improve directory search and presentation through the current controller, Blade view, translations, and existing Alpine state.

**Tech Stack:** Laravel 13, PHP 8.3, Blade, Alpine.js, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-29-sites-and-contacts-design.md`

## Global Constraints

- Edit only `D:\xampp\htdocs\gsb-sunpower`; never modify `gsb-ev-crm`.
- Do not migrate, overwrite, or create customer/imported data as part of this work.
- Keep `/crm/admin/sites` as the only master-data page; do not add a Customer module or a new database table.
- Preserve site ownership, audits, optimistic concurrency, permission checks, legacy contact preview, and Thai/English/Chinese support.
- Do not add dependencies, credentials, or `.env` contents.

## Review Focus

- Contact IDs from another site must be rejected before any site or contact changes are saved — Task 1 test.
- A stale fingerprint must block both a removal and all accompanying edits — Task 1 test.
- Deleting the primary contact must promote the first remaining contact, while deleting the final contact leaves the site intact — Task 1 test.
- Search by LINE name and email must not lose existing site/name/phone matching — Task 2 test.
- Imported contact preview must remain read-only when no current contacts exist — Task 3 regression test.

---

### Task 1: Add transactional, ownership-safe contact removal

**Files:**
- Modify: `app/Crm/Http/Requests/SaveSiteRequest.php`
- Modify: `app/Crm/Actions/SaveSite.php`
- Test: `tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

**Interfaces:**
- Consumes: update payload field `deleted_contact_ids: int[]` and the existing `site_fingerprint`.
- Produces: `SaveSite::update()` deletes only contacts that belong to its locked `Site`, records `contacts` in the audit, and preserves a valid primary contact.

- [ ] **Step 1: Write failing feature tests for safe removal**

Add tests proving: removing one of two contacts deletes only that contact and audits the update; removal of the primary promotes the remaining contact; removal of the final contact leaves the `crm_sites` row; cross-site IDs receive a validation error; and a stale fingerprint does not remove a contact.

- [ ] **Step 2: Run the Site management tests to verify the removal cases fail**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

Expected: FAIL because `deleted_contact_ids` is not validated or applied.

- [ ] **Step 3: Extend the request and `SaveSite::update()`**

Validate every `deleted_contact_ids.*` with `Rule::exists('crm_contacts', 'id')->where('site_id', $site->id)`. Inside the existing lock/transaction and only after the fingerprint check, delete the requested current-site contacts, save submitted contacts, then ensure the first remaining contact becomes primary when none is primary. Add `contacts` to changed fields when deletion occurs.

- [ ] **Step 4: Run the Site management tests to verify they pass**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

Expected: PASS.

### Task 2: Improve contact search and the directory contact preview

**Files:**
- Modify: `app/Crm/Http/Controllers/Admin/SiteController.php`
- Modify: `resources/views/crm/admin/sites/index.blade.php`
- Modify: `lang/th/sites.php`
- Modify: `lang/en/sites.php`
- Modify: `lang/zh/sites.php`
- Test: `tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

**Interfaces:**
- Consumes: current `Site::contacts()` relationship.
- Produces: directory search by contact name, phone, LINE name, and email; primary-contact presentation includes a single safe communication detail when available.

- [ ] **Step 1: Write failing directory tests**

Create sites with contacts that have only LINE names and only email addresses. Assert each is discoverable via `q`, while unrelated records do not appear. Assert the directory displays the primary contact and chosen communication preview.

- [ ] **Step 2: Run the focused test to verify it fails**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php --filter=search`

Expected: FAIL because LINE name/email are not currently searched or rendered as a contact preview.

- [ ] **Step 3: Extend the existing query and compact preview markup**

Add `line_name` and `email` to the nested contact search condition. In the directory's primary-contact cell, render primary then first contact as today, followed by one secondary value in the order phone, LINE name, email. Add exactly matching labels/copy in all three locale files.

- [ ] **Step 4: Run focused and full Site management tests**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

Expected: PASS.

### Task 3: Make existing-contact removal usable in the Sites drawer

**Files:**
- Modify: `resources/views/crm/admin/sites/index.blade.php`
- Modify: `resources/js/crm-sites.js` (or the actual current `siteDirectory` source discovered during implementation)
- Modify: `lang/th/sites.php`
- Modify: `lang/en/sites.php`
- Modify: `lang/zh/sites.php`
- Test: `tests/Feature/CrmMigrated/Admin/SiteManagementTest.php`

**Interfaces:**
- Consumes: Task 1's `deleted_contact_ids` request field.
- Produces: drawer state containing `deletedContactIds`, submitted as `deleted_contact_ids[]`, with an explicit remove action for saved contacts and no automatic change to imported previews.

- [ ] **Step 1: Write failing UI/response regressions**

Assert the Sites page includes the removal field and localized remove/confirmation copy for saved contacts. Keep the existing imported-preview test or add one proving it remains visible when `contacts` is empty.

- [ ] **Step 2: Run the focused UI test to verify it fails**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php --filter='contact|legacy'`

Expected: FAIL because existing contacts have no remove operation or deletion payload.

- [ ] **Step 3: Implement drawer state and controls**

When removing an unsaved contact, remove it locally. When removing an existing contact, append its ID to `deletedContactIds` and remove it from the rendered array; submit each queued ID in hidden inputs. Use a localized confirmation before queuing deletion. Keep the primary radio behavior and ensure the next visible contact is selected locally if the primary is removed.

- [ ] **Step 4: Run focused CRM regression tests**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php tests/Feature/CrmMigrated/Admin/UnifiedLeadEditorTest.php tests/Feature/CrmMigrated/Admin/ContractsTest.php`

Expected: PASS.

### Task 4: Verify permissions, formatting, and non-regression

**Files:**
- Modify: `tests/Feature/CrmMigrated/Admin/SiteManagementTest.php` (only for any uncovered test above)

**Interfaces:**
- Consumes: Tasks 1–3.
- Produces: evidence that only existing `sites.write` users may persist contact changes, while viewers can still read safely.

- [ ] **Step 1: Add or confirm a write-permission regression test**

Use the existing role fixture pattern to assert a Staff/view-only user cannot submit an update that includes `deleted_contact_ids`.

- [ ] **Step 2: Run permission and focused CRM tests**

Run: `vendor/bin/phpunit tests/Feature/CrmMigrated/Admin/SiteManagementTest.php tests/Feature/CrmMigrated/Admin/RoleAccessTest.php`

Expected: PASS.

- [ ] **Step 3: Format changed PHP and run final verification**

Run: `vendor/bin/pint --format agent <changed PHP files>` then `git diff --check`.

Expected: formatter and whitespace check pass. Run `vendor/bin/phpunit` separately and report any pre-existing fixture/environment failures distinctly from this work.
