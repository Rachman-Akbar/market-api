# Users Domain

## Responsibility

- Own user profile and account-facing domain behavior.
- Define user aggregate rules independent from transport and storage.

## Allowed Dependencies

- `app/Shared/**`
- Internal Users layers only
- Framework abstractions only in outer layers

## Boundaries

- No direct dependency on other domain internals.
- No HTTP, ORM, or provider logic in `Domain`.
- All persistence goes through domain contracts.
