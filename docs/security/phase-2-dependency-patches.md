# Phase 2 dependency security notes

This phase patches immediately exploitable dependencies without changing the Laravel major version.

## Removed Composer advisory ignore

The repository previously ignored `PKSA-8qx3-n5y5-vvnd` (`CVE-2025-27515`, Laravel file-validation bypass) without an explanation. The ignore concealed a framework advisory while several upload endpoints accepted spreadsheets. It has been removed. Upload validation in this phase explicitly checks size, MIME type, extension, locally resolved temporary paths, and spreadsheet structure as a compensating control; it is not presented as a framework patch.

Composer insecure-package blocking is enabled. Because Laravel 9 is end-of-life and cannot receive the framework fixes listed below, dependency updates must not add new insecure packages. The remaining framework advisories stay visible in `composer audit` and must not be added to an ignore list.

## Phase 6 framework blockers

Laravel 9.52 is the latest Laravel 9 release, but current advisories require a supported Laravel major and its supported Symfony dependency line. Phase 2 does not cross that major-version boundary. The unresolved Laravel and framework-coupled Symfony advisories are therefore explicit Phase 6 blockers, including:

- Laravel email-rule CRLF injection.
- Laravel temporary signed-URL path confusion.
- Laravel file-validation bypass.
- Symfony HTTP Foundation path parsing and redirect issues.
- Symfony Mailer and Mime header or argument injection issues.
- Symfony Process Windows command-execution hijacking.
- Symfony Routing URL-generation issues.

Until Phase 6, production must remain on PHP 8.2 in Docker, must not use Sendmail transport, and must retain the Phase 2 upload validation and generic browser error handling. New ignores are prohibited.
