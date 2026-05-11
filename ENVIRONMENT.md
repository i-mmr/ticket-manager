# Environment Files

This project uses two levels of environment files.

## Docker Compose

Copy the root template for Docker Compose variables:

```bash
cp .env.example .env
```

The root `.env` controls Docker Compose variables:

- Local development: `UID`, `GID`, and `MYSQL_*` for `compose.yaml`.
- Production: `HTTP_PORT` and `PROD_MYSQL_*` for `compose.production.yaml`.

## Laravel App

For local development, copy the development template:

```bash
cp src/.env.development.example src/.env
cd src
php artisan key:generate
```

For production, start from the production template. The production Compose file reads `src/.env.production` as container environment variables:

```bash
cp src/.env.production.example src/.env.production
```

Before deploying, replace every placeholder value:

- `APP_KEY`: generate with `docker compose -f compose.production.yaml run --rm --no-deps app php artisan key:generate --show`, then paste the value into `src/.env.production`.
- `APP_URL`: set to the real HTTPS URL.
- `DB_*`: match the production database service and the `PROD_MYSQL_*` values in the root `.env`.
- `MAIL_*`: set to the real SMTP or mail provider settings.
- `APP_DEBUG`: keep `false`.

Do not commit real `.env` files or production secrets.

## Local Development

Start or rebuild the development environment:

```bash
docker compose up -d --build
```

The development `app` container runs both Laravel and Vite. Usual PHP, Vue, and SCSS edits do not need a container restart.

Useful commands:

```bash
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose exec app npm install
docker compose exec app composer install
```

If you change Dockerfile, Compose, or environment values:

```bash
docker compose up -d --build
```

## Production Deployment

Production uses Nginx in front of PHP-FPM:

- `compose.production.yaml`: publishes the `web` service and keeps `app` internal.
- `src/Dockerfile.production`: builds the Nginx image with the `web` target.
- `src/docker/nginx/default.conf`: Nginx virtual host for Laravel. It serves `public/` and forwards PHP requests to `app:9000`.

Build and start the production containers:

```bash
docker compose -f compose.production.yaml up -d --build
```

Run deployment tasks after the containers are up:

```bash
docker compose -f compose.production.yaml exec app php artisan migrate --force
docker compose -f compose.production.yaml exec app php artisan config:cache
docker compose -f compose.production.yaml exec app php artisan route:cache
docker compose -f compose.production.yaml exec app php artisan view:cache
```

For future releases on the server:

```bash
git pull origin main
docker compose -f compose.production.yaml up -d --build
docker compose -f compose.production.yaml exec app php artisan migrate --force
docker compose -f compose.production.yaml exec app php artisan optimize
```

Production HTTP traffic enters through the `web` service. The `app` service is PHP-FPM only and is not published directly to the host.
