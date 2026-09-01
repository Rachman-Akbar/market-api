# market-api

Backend Laravel 13 (PHP 8.3) marketplace (Ziip / MarketKu) dengan arsitektur Domain-Driven Design (DDD).

- 📄 **Dokumentasi resmi terbaru:** lihat [`docs-baru/`](docs-baru/README.md)
- 🗄️ **Dokumentasi lama (arsip):** [`docs-lama/`](docs-lama/)
- **Test:** `php artisan test` → 53 passed / 175 assertions (hijau)
- **Setup cepat:** `composer install` → `cp .env.example .env` → `php artisan key:generate` → `php artisan migrate:fresh --seed` → `php artisan serve`

API prefix: `/api/v1/`. Auth: Laravel Sanctum.
