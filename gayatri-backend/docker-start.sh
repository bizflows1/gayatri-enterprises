#!/bin/bash
set -e

echo "==> Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding essential data..."
php artisan db:seed --class=DemoUsersSeeder --force
php artisan db:seed --class=BrandSeeder --force
php artisan db:seed --class=WebsiteTeamMemberSeeder --force
php artisan db:seed --class=CatalogSeeder --force

echo "==> Creating storage link..."
php artisan storage:link || true

echo "==> Starting Apache..."
exec apache2-foreground
