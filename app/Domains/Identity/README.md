# Identity Domain

## Responsibility

- Own identity lifecycle boundaries (identity context only).
- Define identity-related rules and contracts without auth provider implementation.

## Allowed Dependencies

- `app/Shared/**`
- Internal Identity layers only
- Laravel framework abstractions in outer layers only

## Boundaries

- Do not contain transport, persistence, or Firebase/Auth implementation in `Domain`.
- Do not import internals from other domain modules.
- Expose only domain contracts and application entry points.
