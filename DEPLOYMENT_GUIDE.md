# Inventory API - Backend Deployment Guide

This is a backend API application with no frontend assets. All deployment configurations have been optimized for this architecture.

## Environment Setup for Deployment

### 1. Production Environment Variables

Use `.env.production` as a template. Update the following for your deployment:

```bash
# Core Settings
APP_NAME="Inventory API"
APP_ENV=production
APP_KEY=base64:YOUR_PRODUCTION_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (REQUIRED - Update with your credentials)
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=inventory_prod
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_db_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your_mail_user
MAIL_PASSWORD=your_mail_password

# JWT Authentication
JWT_SECRET=your-jwt-secret-here

# Redis (Optional - for better performance)
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
```

### 2. Build & Deployment Process

There are two recommended approaches:

#### Option A: Using Deployment Scripts (Recommended)

**On Windows:**
```bash
.\deploy.bat
```

**On Linux/Mac:**
```bash
bash deploy.sh
```

These scripts automatically:
- Set up `.env` from `.env.production`
- Install production dependencies (no dev packages)
- Generate APP_KEY
- Run migrations
- Optimize for production (cache config, routes)

#### Option B: Manual Deployment

**Step 1:** Copy production environment
```bash
cp .env.production .env
```

**Step 2:** Install dependencies with optimization
```bash
composer install --optimize-autoloader --no-dev
```

**Step 3:** Generate APP_KEY (if not already set)
```bash
php artisan key:generate
```

**Step 4:** Run database migrations
```bash
php artisan migrate --force
```

**Step 5:** Optimize for production
```bash
php artisan config:cache
php artisan route:cache
```

**Step 6:** Start queue workers (if using async jobs)
```bash
php artisan queue:work --tries=3 --timeout=0
```

### 3. What's Changed for Backend-Only Deployment

✅ **Vite Build Disabled**: `vite.config.js` configured for backend API only  
✅ **NPM Build Removed**: No frontend asset compilation  
✅ **Composer Scripts Updated**: Separate `setup` and `setup:prod` scripts  
✅ **Deploy Scripts Added**: `deploy.sh` and `deploy.bat` for easy deployment  
✅ **Environment Separated**: `.env.production` for production settings  
✅ **View Cache Removed**: Backend API has no views to cache

### 4. Key Environment Variables for Deployment

| Variable | Purpose | Development | Production |
|----------|---------|-------------|-----------|
| APP_ENV | Application environment | local | production |
| APP_DEBUG | Debug mode | true | false |
| LOG_LEVEL | Logging level | debug | info |
| CACHE_STORE | Cache driver | database | redis (recommended) |
| SESSION_DRIVER | Session driver | database | redis (recommended) |
| QUEUE_CONNECTION | Job queue driver | database | redis (recommended) |
| DB_PASSWORD | Database password | empty | ⚠️ **REQUIRED** |

### 5. Database Migrations

Migrations are automatically run during deployment. To manually manage:

```bash
# Run pending migrations
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback --force

# Reset database (careful!)
php artisan migrate:reset --force
```

### 6. Caching and Optimization

The `composer run deploy` command caches:
- ✅ Configuration
- ✅ Routes

To manually cache:

```bash
php artisan config:cache
php artisan route:cache
php artisan cache:clear
```

### 7. Security Recommendations

- [ ] Set `APP_DEBUG=false` in production
- [ ] Generate and store `JWT_SECRET` securely
- [ ] Use environment-specific database credentials
- [ ] **Set a strong `DB_PASSWORD`** (required - currently empty!)
- [ ] Keep `APP_KEY` safe and never commit sensitive keys
- [ ] Use HTTPS for `APP_URL`
- [ ] Configure CORS properly for frontend consumers
- [ ] Use Redis for cache, sessions, and queues

### 8. Deployment Checklist

- [ ] `.env.production` configured with production values
- [ ] `DB_PASSWORD` is set (not empty!) ⚠️ **CRITICAL**
- [ ] `APP_KEY` is unique and stored securely
- [ ] `JWT_SECRET` generated and stored securely
- [ ] Mail configuration for production email service
- [ ] Database credentials verified and tested
- [ ] Redis configured (if using for cache/sessions/queues)
- [ ] Migrations run successfully
- [ ] Cache commands executed (config:cache, route:cache)
- [ ] Queue workers started in background (if using async jobs)
- [ ] Log files and storage paths have correct permissions
- [ ] API tested with health check endpoint

### 9. Troubleshooting Deployment Errors

**Error: "Could not resolve entry module index.html"**
- ✓ Fixed: `vite.config.js` configured for backend API

**Error: "The resources/views directory does not exist"**
- ✓ Fixed: Removed `view:cache` from deploy script

**Error: "Access denied for user 'root'@'localhost' (using password: NO)"**
- Solution: Ensure `DB_PASSWORD` is set in `.env` file

**Error: "Could not delete vendor/composer..."** (Windows)
- Solution: Close file explorer, turn off antivirus temporarily, or use Git Bash instead of PowerShell

**Error: Database connection fails**
- Solution: Verify credentials in `.env`, ensure database server is running, check network connectivity

### 10. Verification

After deployment, verify the API is working:

```bash
# Check API health endpoint
curl https://your-domain.com/api/health

# Monitor logs for errors
tail -f storage/logs/laravel.log

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Rollback on Issues

If deployment fails:

1. Check logs: `tail -f storage/logs/laravel.log`
2. Verify database connection: `php artisan tinker`
3. Clear caches: `php artisan cache:clear && php artisan config:clear`
4. Review `.env` settings
5. Rerun migrations if needed: `php artisan migrate:rollback` then `php artisan migrate --force`
