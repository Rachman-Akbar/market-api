# Realtime Domain

## Responsibility

- Own realtime event publication boundaries for marketplace capabilities.
- Define event contracts independent from Firebase implementation.

## Allowed Dependencies

- `app/Shared/**`
- Internal Realtime layers only
- Framework abstractions in outer layers only

## Boundaries

- Keep provider-specific SDK usage in `Infrastructure/Firebase`.
- Do not embed auth or business logic into realtime handlers.
- Expose only contracts and application orchestration entry points.
