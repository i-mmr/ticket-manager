# Environment Files

This project uses two levels of environment files.

## Docker Compose

Copy the root template for Docker Compose variables:

```bash
cp .env.example .env
```

The root `.env` controls local container settings such as `UID`, `GID`, and the MySQL container credentials used by `compose.yaml`.

## Laravel App

For local development, copy the development template:

```bash
cp src/.env.development.example src/.env
cd src
php artisan key:generate
```

`src/.env.example` is kept aligned with `src/.env.development.example` so Laravel tooling still has the conventional example file.

For production, start from the production template:

```bash
cp src/.env.production.example src/.env
```

Before deploying, replace every placeholder value:

- `APP_KEY`: generate on the production server with `php artisan key:generate`.
- `APP_URL`: set to the real HTTPS URL.
- `DB_*`: match the production database service.
- `MAIL_*`: set to the real SMTP or mail provider settings.
- `APP_DEBUG`: keep `false`.

Do not commit real `.env` files or production secrets.
