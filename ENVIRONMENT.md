# 環境構築手順

このプロジェクトでは、開発環境と本番環境で使う Docker Compose と環境変数ファイルを分けています。

## ファイルの役割

開発環境用
- `compose.yaml`:
    Laravel と Vite を `app` コンテナで起動します。
- `.env.example`:
    開発用 Docker Compose 変数のテンプレートです。
- `src/.env.development.example`:
    開発用 Laravel 環境変数のテンプレートです。

本番環境用
- `compose.production.yaml`:
    本番環境用。Nginx の `web` と PHP-FPM の `app` を分けます。
- `.env.production.example`:
    本番用 Docker Compose 変数のテンプレートです。
- `src/.env.production.example`:
    本番用 Laravel 環境変数のテンプレートです。

## 開発環境

開発環境では、ルートの `.env` と Laravel 用の `src/.env` を作成します。

```bash
cp .env.example .env
cp src/.env.development.example src/.env
```

初回のみ Laravel の `APP_KEY` を生成します。

```bash
docker compose build app
docker compose run --rm --no-deps app php artisan key:generate
```

開発環境を起動します。

```bash
docker compose up -d --build
```

開発環境の `app` コンテナでは Laravel と Vite が同時に起動します。通常の PHP、Vue、SCSS の修正であれば、コンテナ再起動は不要です。

よく使うコマンドです。

```bash
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose exec app npm install
docker compose exec app composer install
```

開発環境のメール送信も Resend を使います。`src/.env` の `RESEND_API_KEY` と `MAIL_FROM_ADDRESS` を確認してください。

Dockerfile、Compose、環境変数を変更した場合は再ビルドします。

```bash
docker compose up -d --build
```

## 本番環境

本番環境では、開発用の `.env.example` は使いません。VPS 上では本番用の `.env.production` と `src/.env.production` を作成します。

```bash
cp .env.production.example .env.production
cp src/.env.production.example src/.env.production
```

`.env.production` では Docker Compose と MySQL コンテナ用の値を設定します。

```env
HTTP_PORT=80
MYSQL_DATABASE=ticket_manager
MYSQL_USER=ticket_manager
MYSQL_PASSWORD=change-me-strong-db-password
MYSQL_ROOT_PASSWORD=change-me-strong-root-password
```

`src/.env.production` では Laravel アプリ用の値を設定します。最低限、次を本番値に変更してください。

- `APP_URL`: 本番の URL
- `APP_KEY`: 本番用のアプリケーションキー
- `MAIL_MAILER`: `resend`
- `RESEND_API_KEY`: Resend の API キー
- `MAIL_FROM_ADDRESS`: Resend で送信許可済みの From アドレス。本番では Resend で認証済みの独自ドメインを使ってください。
- `APP_DEBUG`: 必ず `false`

`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD` は `src/.env.production` に直接書かず、`compose.production.yaml` が `.env.production` の `MYSQL_*` から Laravel コンテナへ渡します。`DB_HOST=db` はそのままで問題ありません。

`APP_KEY` は次のコマンドで生成し、表示された値を `src/.env.production` の `APP_KEY=` に貼り付けます。

```bash
docker compose --env-file .env.production -f compose.production.yaml build app
docker compose --env-file .env.production -f compose.production.yaml run --rm --no-deps app php artisan key:generate --show
```

本番環境を起動します。

```bash
docker compose --env-file .env.production -f compose.production.yaml up -d --build
```

起動後、マイグレーションと Laravel の最適化を実行します。

```bash
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan migrate --force
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan optimize
```

必要に応じて個別にキャッシュを作り直す場合は、次を使います。

```bash
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan config:cache
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan route:cache
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan view:cache
```

## 本番反映の流れ

ローカルで修正した内容を GitHub に push します。

```bash
git status
git add .
git commit -m "変更内容を書く"
git push origin main
```

VPS 側で最新コードを取得して反映します。

```bash
cd /path/to/ticket-manager
git pull origin main
docker compose --env-file .env.production -f compose.production.yaml up -d --build
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan migrate --force
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan optimize
```

## 本番構成の概要

本番環境では Nginx と PHP-FPM を分けています。

- `web`: Nginx。HTTP リクエストを受けます。
- `app`: Laravel の PHP-FPM。外部には直接公開しません。
- `db`: MySQL。外部には公開しません。
- `redis`: セッション、キュー、キャッシュ用です。

Nginx 設定は `src/docker/nginx/default.conf` にあります。`public/` を公開ディレクトリにし、PHP の処理は `app:9000` に渡します。

## 注意点

- 本番コマンドには必ず `--env-file .env.production` を付けてください。
- 本番では `cp .env.example .env` は使いません。
- 本番の DB パスワードや `APP_KEY` は Git にコミットしないでください。
- HTTPS 化はこの Docker 構成にはまだ含めていません。必要に応じて VPS 側で Caddy、Nginx リバースプロキシ、またはロードバランサを設定してください。
