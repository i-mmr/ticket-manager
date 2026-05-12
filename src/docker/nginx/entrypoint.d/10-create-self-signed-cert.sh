#!/bin/sh
set -eu

# 初回の正式証明書発行前でも Nginx が起動できるよう、仮証明書を作る。
if [ -z "${DOMAIN:-}" ]; then
    echo "DOMAIN is required for production Nginx HTTPS configuration." >&2
    exit 1
fi

CERT_DIR="/etc/letsencrypt/live/${DOMAIN}"

if [ ! -f "${CERT_DIR}/fullchain.pem" ] || [ ! -f "${CERT_DIR}/privkey.pem" ]; then
    mkdir -p "${CERT_DIR}"
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
        -keyout "${CERT_DIR}/privkey.pem" \
        -out "${CERT_DIR}/fullchain.pem" \
        -subj "/CN=${DOMAIN}" >/dev/null 2>&1
fi
