# Site Map, Types, and Business Summary Design

## Intent

Make location records easier to use from the CRM directory and sales pipeline: open a saved map immediately, keep location types consistent for new edits, and show practical deal conditions instead of a sales-success percentage.

## Goals

- A valid saved `map_url` is available as a clear external-map link wherever a user views a site or its pipeline detail.
- New or edited site and pipeline profiles select from five controlled types: `PETROL_STATION`, `BUSINESS_DISTRICT`, `MALL`, `OPEN_LAND`, and `OTHER`.
- Existing free-text type values remain readable and are never mass-edited or silently remapped.
- Pipeline list/board summary replaces probability with cooperation model, rent, and parking-space count, taking the current business-term override before imported legacy values.
- All new user-facing copy is available in Thai, English, and Chinese.

## Non-goals

- Do not alter existing customer, site, contract, or legacy-import records in bulk.
- Do not change the sales-stage rules or remove their internally stored probability value.
- Do not introduce map APIs, map-coordinate storage, or a new public-site feature.

## Chosen approach

Use a controlled option set only at write points. Legacy values continue to render as historical/current data when a record has not been edited. This preserves real data while stopping new variations from accumulating.

## User experience

1. In the Sites workspace, the edit drawer presents the five localized type choices. Any legacy type not in the set is displayed as the existing value until the user deliberately changes it.
2. A site with an HTTP(S) map URL shows an “Open map” link that opens in a new tab with safe link attributes; no link appears for a missing value.
3. Pipeline profile editing presents the same five choices and its details keep the existing map link behavior.
4. Each sales list row and board card displays a compact conditions line: cooperation model, rent, and parking spaces. Missing values have an existing localized “unspecified” fallback. Probability is no longer shown in those summaries.

## Data and authorization

- Reuse `crm_sites.location_type` and current profile overrides; no migration is needed.
- Reuse existing business fields: `commercial_model`, `rental_cost`, `rental_unit`, and `parking_spaces`.
- Preserve existing Sites/Pipeline permissions. The change is read-only for users who cannot already edit the relevant records.
- Continue validating URLs as `http`/`https`; escape all display values.

## Approaches considered

1. **Chosen: controlled future writes, retain legacy display.** Lowest risk; avoids changing real data and gives immediate consistency going forward.
2. **Bulk normalize existing types.** Provides a uniform report filter immediately, but requires a user-approved mapping for every existing value and would mutate real customer records.

## Validation

- Feature tests prove only valid type codes are accepted for new writes, legacy types remain readable, and users can see/open valid map links.
- Pipeline tests prove current business overrides take precedence, summaries include the three requested conditions, and no probability text appears in the list/board summary.
- Run focused PHPUnit, Pint on changed PHP, and `git diff --check`; report unrelated suite failures separately.
