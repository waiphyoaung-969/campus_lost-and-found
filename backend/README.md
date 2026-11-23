# Campus Loop API

Run the backend with PHP 8.5, Composer and the PDO SQLite extension. From this directory, on a fresh checkout:

```sh
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate --no-interaction
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --no-interaction
composer dev
```

Keep your existing local `.env` and application key if setup has already run. The API listens at `http://localhost:8000`. The SPA origin is `http://localhost:5173`. Use `localhost` for both hosts so the browser can share the host-only cookies across ports.

- `GET /sanctum/csrf-cookie` initializes the session and sets the readable `XSRF-TOKEN` cookie.
- `GET /api/v1/me` returns the authenticated campus member. Guests receive 401.
- `POST /api/v1/logout` requires a member session and the URL-decoded `XSRF-TOKEN` in the `X-XSRF-TOKEN` header. It invalidates the session and returns 204.

SPA calls send `Accept: application/json` and include credentials. The database-backed session cookie is HttpOnly and uses SameSite=Lax. The defaults in `.env.example` are for local HTTP development.

Microsoft sign-in is pending. The planned backend OAuth flow requires an approved AIU Microsoft Entra app registration, tenant ID, client ID, client secret, exact callback URI, and confirmed eligibility rules for institution-issued student, faculty and staff accounts. Backend tests use factory members and Laravel sessions.

Run tests with `composer test`. Run formatting with `vendor/bin/pint` (or `php vendor/bin/pint` on Windows).
