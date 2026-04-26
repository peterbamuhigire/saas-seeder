# UI Shell Contract

Authenticated pages must provide:

- exactly one `<main id="main-body" tabindex="-1">` landmark,
- a skip link targeting `#main-body`,
- shared head/footer assets,
- page header slot,
- tenant context slot where tenant state matters,
- module and permission-aware navigation,
- documented empty/loading/error/success states.

New pages should consume primitives from `src/UI/` before adding bespoke markup.
