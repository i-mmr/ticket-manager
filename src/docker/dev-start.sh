#!/bin/sh
set -eu

(
    npm install
    npm run dev -- --host 0.0.0.0
) &
node_pid=$!

trap 'kill "$node_pid" 2>/dev/null || true' INT TERM EXIT

php artisan serve --host=0.0.0.0 --port=8000
