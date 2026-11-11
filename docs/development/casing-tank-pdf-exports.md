# Casing-Tank PDF exports

Implemented locally on 2026-11-10 using the layout approved during the export planning interview. Database-backed verification remains pending because the configured disposable MySQL login is rejected (1045).

## Scope and agreed behavior

- Five-sample Casing-Tank forms only. Diaphragm and the ten-sample CSB641001P form are deferred.
- Nineteen reference workbooks are represented in `CasingTankPdfTemplates`, plus the existing `CSB29046P3-P` mapping for `CSB29046P3.xlsx` and the user-confirmed `CSB6401012` mapping to the approved `CSB290053P.xlsx` example. The latter shares its form only; its own item code remains in the PDF, query, and grouping.
- The blank `HPI-PR20` tab is the reference where available. Three workbooks have only monthly tabs, so their first reference month supplies the labels. Monthly edits to merged cells or table length are not additional template versions.
- Each item's printed depth/time labels match its reference, including labels without numeric limits. Current validation settings do not replace printed labels.
- A4 landscape, six record blocks per page, five inspection rows per block, and five sample columns. Headers, inspection labels, reminders, and the blank supervisor signing area repeat on every page.
- Individual export includes one saved record and five unused blocks.
- Monthly export selects one exact item code and production month and returns one combined PDF. This selection is independent of the list's search, machine, date-range, and status filters.
- All saved approval statuses are eligible. Soft-deleted records are excluded by the existing model scope.
- A different item, production month, machine, or job number starts a separate checksheet. Records within each group are ordered by production date and then record ID.
- Letter A starts a new checksheet. Incrementing letters B, C, and onward continue in successive blocks of that checksheet. For example, `A, B, C, A, B` produces `[A, B, C]` and `[A, B]`. Consecutive A records each start a new checksheet.
- A change in material lots or quantities alone occupies the next block, except when the record is letter A, which starts a new checksheet. A seventh record continues on another full form page. A partial selection beginning at B or a later letter starts on a fresh page without inserting missing records.
- Each record keeps its own quantities and samples. Quantities are not summed and sample results are not inferred.
- OK/P/PASS becomes `✓`, NG/F/FAIL becomes `X`, not applicable becomes `/`, and missing values remain blank. Numeric sample strings retain their stored decimal places, including trailing zeros.
- Operator, Technician, and Checked By have separate columns with wrapped names. Existing linked-user names take precedence, with saved raw names used as fallbacks.
- The Production Supervisor signing area is always blank, including for approved records.
- Existing Excel export stays available.

## Material-field mapping

The existing five-sample importer uses generic field keys. Export follows that same mapping to reproduce the printed reference headings:

| Saved field | Printed heading |
| --- | --- |
| `casing` | TANK |
| `valve_cover` | CD PARTITION |
| `air_valve` | VCR |
| `prod_qty` | PROD QTY. |
| `quantity` | QUANTITY |

## Entry points

- The Welding Checksheets list has **Monthly PDF**, with a dedicated item/month selector, download progress, and validation messages.
- The PDF selector and record filter share the same active registered item-code source. Codes without a linked five-sample reference are shown disabled with `Reference form needed`; Diaphragm and the ten-sample form show their deferred status. Registering a new item makes it visible automatically, while PDF export stays restricted to approved template mappings.
- Supported records have **Download PDF** on their details page.
- Both routes use existing authentication and `module.permission:welding,export`. There is no approval feature gate.
- Downloads use attachment disposition and private/no-store caching. Successful exports use the existing activity logger with the actual record count and PDF filename.
- Empty selections and unsupported items produce a clear validation error instead of an empty download. Extremely long values that cannot fit the form report the affected record instead of silently losing text. Longer material/job values use the height of their record block when needed.

## Runtime and source preservation

The renderer uses `dompdf/dompdf` 3.1.x and its bundled Unicode font; the dependency is recorded in Composer. It draws the fixed form directly onto PDF pages. Remote fetching and PHP execution are disabled. Font caches use the existing writable framework cache directory.

No Python, desktop Excel, Windows fonts, new database schema, or additional container service is required by the application. The original Excel workbooks are unchanged. Their relevant labels are recorded in the template catalog, so runtime export does not need to open workbooks.

Deployment requires the ordinary Composer dependency installation and frontend build (or the existing Docker build). No database migration is needed for this feature.

## Verification

- `tests/Unit/CasingTankPdfExportTest.php`: 18 tests and 166 assertions passed. These cover A resets and continuing letter sequences, consecutive A records, continuation-page resets, partial sequences, interleaved machines, grouping, ordering, six-block pagination, unsupported forms, exact reference labels and material headings, result symbols, PDF page geometry, overlong-value errors, export-permission routing, registered-item availability, and the confirmed CSB6401012 mapping while retaining its own item identity.
- A read-only check of the actual index controller response confirmed identical 40-code lists for the record filter and PDF selector. After the user confirmed the CSB6401012 reference, there are 21 exportable codes, 17 deferred Diaphragm codes, the deferred ten-sample code, and TKB4000100 awaiting a reference mapping. An existing CSB6401012 record also rendered successfully to PDF in memory. No records were changed by these checks.
- PHP static analysis and formatting checks passed for the implementation.
- `npm run check` passed: the existing ESLint regression gate, Vue type checking, production build, and PWA verification. Existing lint warnings and the bundle-size warning remain.
- The actual renderer generated an individual example, a revised two-page monthly example (`A, B, C` followed by `A, B` with the same job number), and an additional labels-without-limits/long-text layout check. The rendered pages were visually inspected. PDF extraction checks verified page count and size, letter placement, symbols, decimal strings, repeated headings, and a blank signing area. Six-block continuation is covered by the automated sequence tests.
- `tests/Feature/WeldingChecksheetPdfExportTest.php` covers database selection by item/month/type, all statuses, soft deletes, permission enforcement, individual downloads, errors, and activity logging. Its local run stops before assertions because MySQL rejects the configured test login with 1045. The existing spreadsheet export regression test hits the same login failure. No test credentials or database guards were changed.
- Physical A4 printing and authenticated downloads against actual records still require user verification after database-backed checks can run.

## Review examples

Local review examples under `output/pdf/casing-tank-individual-export-example.pdf` and `output/pdf/casing-tank-monthly-export-example.pdf` use fictional, in-memory records generated by the application renderer. These generated review files are kept outside version control and contain no production data.
