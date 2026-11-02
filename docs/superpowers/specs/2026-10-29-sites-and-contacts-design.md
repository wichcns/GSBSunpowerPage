# Sites and Contacts Design

## Intent

Make the existing CRM screen unmistakably a single **Sites and contacts** workspace: every site owns zero or more contact people, with one optional primary contact. This addresses the review deck's concern about duplicate/unclear place and customer views without creating a separate customer master or moving imported customer data.

## Goals

- Keep `/crm/admin/sites` as the only master-data screen for this phase.
- Make the directory show the contact relationship clearly and make the detail drawer easy to use for multiple contacts.
- Preserve existing Site, Contact, ownership, audit, optimistic-concurrency, imported-data preview, permissions, and three-language behavior.
- Improve validation and tests around primary-contact selection and safe contact removal.

## Non-goals

- No separate Customer module, customer-to-many-site relationship, deduplication, or migration of real/imported records.
- No changes to the public website, legacy `gsb-ev-crm`, online database, or deployment archive.

## Existing design

`crm_sites` already has a one-to-many relation to `crm_contacts`. `SaveSite` creates/updates contacts transactionally and records site audits. The Sites page lists the primary contact, searches current contacts by name/phone, and has a drawer for adding/editing contacts. Imported contacts remain a read-only preview until staff add a current contact.

## Approaches considered

1. **Clarify and strengthen the existing Site + contacts workspace (selected).** Lowest risk: uses the current data model and does not alter imported data.
2. Create a distinct Customer master linked to multiple sites. More flexible later, but it requires new identity/deduplication rules and a data-migration decision that is out of scope.

## Proposed behavior

- Rename visible copy where needed to communicate “Sites and contacts”.
- Directory search accepts customer name, phone, LINE name, and email, while retaining site code/name/area search.
- Directory primary-contact column shows the person plus an available communication detail, without exposing unnecessary data on compact/mobile views.
- In the drawer, label the list as contact people for this site; retain add/remove, set-primary behavior, and imported-data preview.
- Contact removal is explicit and only removes contacts belonging to the current site; the last contact may be removed, leaving the site intact.
- When a primary contact is removed, choose the first remaining contact as primary; when none remain, the site has no primary contact.
- All validation remains server-side and no imported record is written during page viewing/searching.

## Validation and acceptance criteria

- A site can have multiple contacts and exactly one selected primary contact.
- Search returns a site by a contact's name, phone, LINE name, or email.
- A contact cannot be edited or removed through another site's request.
- Removing a contact persists and is audited; a site is never deleted as a side effect.
- Existing imported contact preview remains readable but is not converted or overwritten automatically.
- Thai, English, and Chinese render the same concepts without untranslated keys.

## Builder choices

- Keep the current `crm_sites` / `crm_contacts` tables and Laravel model relationship.
- Implement removals within the existing `SaveSite` transaction and fingerprint guard, rather than a new standalone endpoint.
- Update only CRM views, translations, actions, validation, and focused feature tests.
