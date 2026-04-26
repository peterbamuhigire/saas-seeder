# Forms

Form primitives live under `src/UI/Form/`.

Rules:

- Every field has a visible label.
- Required fields include `required` and `aria-required="true"`.
- Errors render near the field and summaries use alert/live semantics.
- Standard layouts should not need page-local CSS.
