#!/bin/bash
# Deploy script for Laravel Inventory API to production
# This script handles backend API deployment without frontend assets

set -e

echo "🚀 Starting Inventory API Deployment..."

# Step 1: Set production environment
echo "📝 Setting up production environment..."
if [ ! -f ".env" ]; then
    cp .env.production .env
    echo "✓ .env copied from .env.production"
else
    echo "✓ .env already exists (using existing configuration)"
fi

# Step 2: Install dependencies (production only)
echo "📦 Installing production dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Step 3: Generate APP_KEY if needed
echo "🔑 Ensuring APP_KEY is set..."
php artisan key:generate --force 2>/dev/null || true

# Step 4: Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force --no-interaction

# Step 5: Optimize for production
echo "⚡ Optimizing application for production..."
php artisan config:cache
php artisan route:cache

# Step 6: Clear unnecessary caches
echo "🧹 Clearing temporary caches..."
php artisan cache:clear --no-interaction 2>/dev/null || true

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📋 Next steps:"
echo "  1. Start queue workers:    php artisan queue:work --tries=3 --timeout=0"
echo "  2. Monitor logs:           tail -f storage/logs/laravel.log"
echo "  3. Test API health:        curl https://your-domain.com/api/health"
echo ""
echo "🔗 API URL: $(grep APP_URL .env | cut -d '=' -f 2)"
echo "🌍 Environment: $(grep APP_ENV .env | cut -d '=' -f 2)"
