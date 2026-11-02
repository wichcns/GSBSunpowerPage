# Global CRM Search Design

## Purpose

Add one global CRM search form in the authenticated admin top bar. It resolves the slide requirement to merge searchable content and gives users a reliable starting point instead of making them discover separate filters in individual modules.

## User experience

1. A signed-in user enters one to 100 characters in the top-bar search field and submits it.
2. `/crm/admin/search?q=...` shows grouped result sections for Places & Contacts, Sales Pipeline, and Contracts. A group is omitted when the user lacks that module's existing view permission.
3. Each result links to the existing detail workflow: a Place opens its existing editor drawer, a Pipeline item opens the existing pipeline row, and a Contract opens the existing contract row.
4. No results page is shown for a blank query; it redirects to the CRM dashboard. The top-bar form itself prevents blank submission.

## Matching rules

- Places: site name, site code, area, contact name, phone, LINE name and email.
- Pipeline and Contracts: site name, site code and area, exactly matching their existing list-filter vocabulary.
- Matching is case-insensitive substring matching. Results are limited to 10 per group, ordered consistently with each source's normal list.

## Authorization and safety

- The route requires normal CRM authentication.
- A result group is shown only when its corresponding `*.view` permission is held.
- Pipeline results use the same assigned-record restriction as the Pipeline list for users without `pipeline.stage`; no result may reveal an inaccessible source ID.
- Search text is trimmed and limited to 100 characters; it is rendered through Blade escaping.
- The feature reads existing records only. It creates no data, changes no role, and touches no public site page.

## Non-goals

- Do not replace the page-level filter forms; they remain for detailed filtering.
- Do not add full-text indexes, a third-party search engine, or search attachments, notifications, engineering tasks, or external LINE data in this iteration.
- Do not change CRM data or legacy source payloads.

## UI and localization

- Add translated labels and empty-state copy to the existing `ui` language files for Thai, English, and Chinese.
- Use existing CRM responsive classes and keep the search usable on desktop and mobile.

## Validation

- Feature tests cover success paths, permissions, assigned-pipeline isolation, blank query handling, and Blade output of the top-bar form/results.
- Keep focused PHPUnit tests green and report any unrelated full-suite failures separately.
