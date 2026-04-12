# Orders Domain

## Responsibility

- Own order lifecycle boundaries and ordering rules.
- Define order state transitions and contracts.

## Allowed Dependencies

- `app/Shared/**`
- Internal Orders layers only
- Framework abstractions in outer layers only

## Boundaries

- No cross-domain internal imports.
- Keep domain pure from HTTP, ORM, and provider details.
- Integrations are represented via domain contracts.
