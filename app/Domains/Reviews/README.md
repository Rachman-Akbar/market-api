# Reviews Domain

## Responsibility

- Own rating and review domain behavior boundaries.
- Define review moderation and integrity contracts.

## Allowed Dependencies

- `app/Shared/**`
- Internal Reviews layers only
- Framework abstractions in outer layers only

## Boundaries

- Keep moderation rules in `Domain`.
- No direct imports from Catalog/Users internals.
- Storage and transport concerns stay outside `Domain`.
