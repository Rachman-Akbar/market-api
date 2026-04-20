# Payments Domain

## Responsibility

- Own payment intent and settlement domain boundaries.
- Define payment-related contracts and invariants.

## Allowed Dependencies

- `app/Shared/**`
- Internal Payments layers only
- Framework abstractions in outer layers only

## Boundaries

- No direct gateway SDK logic in `Domain`.
- No imports from Orders or other domain internals.
- External payment providers stay in `Infrastructure`.
