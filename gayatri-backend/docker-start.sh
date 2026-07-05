#!/bin/bash
set -e

# Strip hidden CR characters that Render dashboard sometimes injects into env vars
export APP_URL=$(echo "${APP_URL}" | tr -d '\r')
export FRONTEND_URLS=$(echo "${FRONTEND_URLS}" | tr -d '\r')
export SANCTUM_STATEFUL_DOMAINS=$(echo "${SANCTUM_STATEFUL_DOMAINS}" | tr -d '\r')
export DB_HOST=$(echo "${DB_HOST}" | tr -d '\r')

echo "==> Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting Apache (background)..."
apache2-foreground &
APACHE_PID=$!

# Give Apache a moment to bind the port
sleep 3

echo "==> Running migrations..."
php artisan migrate --force || echo "Migration failed — check DB vars"

echo "==> Seeding essential data..."
php artisan db:seed --class=DemoUsersSeeder --force || true
php artisan db:seed --class=BrandSeeder --force || true
php artisan db:seed --class=WebsiteTeamMemberSeeder --force || true
php artisan db:seed --class=CatalogSeeder --force || true

echo "==> Creating storage link..."
php artisan storage:link || true

echo "==> All done. Keeping Apache alive..."
wait $APACHE_PID
