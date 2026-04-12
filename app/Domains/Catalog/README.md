# Catalog Domain

## Responsibility

- Own product listing and discovery-related domain concepts.
- Define catalog rules, contracts, and invariants.

## Allowed Dependencies

- `app/Shared/**`
- Internal Catalog layers only
- Framework abstractions in outer layers only

## Boundaries

- Keep business rules in `Domain` only.
- Avoid importing internals from Inventory/Orders or other domains.
- Infrastructure implements domain contracts, not the reverse.
