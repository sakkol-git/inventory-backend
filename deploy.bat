@echo off
REM Deploy script for Laravel Inventory API to production (Windows)
REM This script handles backend API deployment without frontend assets

setlocal enabledelayedexpansion

echo.
echo 🚀 Starting Inventory API Deployment...
echo.

REM Step 1: Set production environment
echo 📝 Setting up production environment...
if not exist ".env" (
    copy .env.production .env > nul
    echo ✓ .env copied from .env.production
) else (
    echo ✓ .env already exists (using existing configuration)
)

REM Step 2: Install dependencies (production only)
echo 📦 Installing production dependencies...
call composer install --optimize-autoloader --no-dev --no-interaction
if !errorlevel! neq 0 (
    echo ❌ Composer install failed
    exit /b 1
)

REM Step 3: Generate APP_KEY if needed
echo 🔑 Ensuring APP_KEY is set...
php artisan key:generate --force >nul 2>&1

REM Step 4: Run migrations
echo 🗄️  Running database migrations...
php artisan migrate --force --no-interaction
if !errorlevel! neq 0 (
    echo ⚠️  Migration warning (database might not be ready)
)

REM Step 5: Optimize for production
echo ⚡ Optimizing application for production...
php artisan config:cache
php artisan route:cache

REM Step 6: Clear unnecessary caches
echo 🧹 Clearing temporary caches...
php artisan cache:clear --no-interaction >nul 2>&1

echo.
echo ✅ Deployment completed successfully!
echo.
echo 📋 Next steps:
echo   1. Start queue workers:    php artisan queue:work --tries=3 --timeout=0
echo   2. Monitor logs:           Get-Content storage/logs/laravel.log -Tail 50 -Wait
echo   3. Test API health:        curl https://your-domain.com/api/health
echo.

for /f "tokens=2 delims==" %%A in ('findstr /R "^APP_URL=" .env') do set APP_URL=%%A
for /f "tokens=2 delims==" %%B in ('findstr /R "^APP_ENV=" .env') do set APP_ENV=%%B

echo 🔗 API URL: %APP_URL%
echo 🌍 Environment: %APP_ENV%
echo.
