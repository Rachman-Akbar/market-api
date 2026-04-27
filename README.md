# Cart Domain - Laravel DDD

Copy `app/`, `config/`, and optional `database/` into your Laravel project.

## Register service provider

Laravel 11: add this to `bootstrap/providers.php`:

```php
App\Domains\Cart\Infrastructure\Providers\CartServiceProvider::class,
```

Laravel 10 or older: add it to `config/app.php` providers.

## API routes

Protected by `auth:sanctum` by default:

```http
GET    /api/cart
POST   /api/cart/items
PATCH  /api/cart/items/{productId}
DELETE /api/cart/items/{productId}
DELETE /api/cart
```

## Product model mapping

Adjust `config/cart.php` if your Product model or field names differ.

## Recommended MySQL index

Your schema has `active_user_id`. Add this to guarantee one active cart per user:

```sql
ALTER TABLE carts ADD UNIQUE KEY carts_active_user_id_unique (active_user_id);
```

MySQL allows many `NULL` values in unique indexes, so checked-out/abandoned carts can use `active_user_id = NULL`.
