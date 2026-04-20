# Inventory Domain

## Responsibility

- Own stock availability and quantity state boundaries.
- Define inventory policies and stock consistency contracts.

## Allowed Dependencies

- `app/Shared/**`
- Internal Inventory layers only
- Framework abstractions in outer layers only

## Boundaries

- No direct dependency on Catalog or Orders internals.
- Keep persistence and transport out of `Domain`.
- Expose only inventory contracts and application use cases.
