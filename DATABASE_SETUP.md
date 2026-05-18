# Database Configuration for Deployment

## Issue: Database Connection Failed

The deployment script ran successfully but **database migrations failed** because `DB_PASSWORD` is empty in your `.env` file.

## Solution: Configure Database Connection

### Step 1: Check Your Database

First, verify your MySQL/MariaDB server is running and you know the correct credentials:

```bash
# On Windows with MySQL
mysql -u root -p

# If password is empty, just press Enter
# If it works, you're connected! 
# Type 'exit' to disconnect
```

### Step 2: Update `.env` with Database Credentials

Open `.env` in your editor and update the database section:

```bash
# ── Database ─────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_prod
DB_USERNAME=root
DB_PASSWORD=your_actual_password_here    # ← Add your password here!
```

### Step 3: Create Database (if needed)

If the database doesn't exist, create it:

```bash
# Using MySQL command line
mysql -u root -p -e "CREATE DATABASE inventory_prod;"

# Or in MySQL shell
mysql> CREATE DATABASE inventory_prod;
```

### Step 4: Re-run Deployment

Now run the deployment again:

```bash
.\deploy.bat
```

## Quick Reference: Set Database Password in .env

**Before (current - fails):**
```
DB_PASSWORD=
```

**After (working - add your password):**
```
DB_PASSWORD=your_mysql_password
```

## Verify Database Connection

After updating `.env`, test the connection:

```bash
php artisan tinker
>>> DB::connection()->getPdo();
# Should print connection object without error
>>> exit
```

If you see an error, double-check your credentials in `.env`.

## Security Note

For production environments, use a strong, unique password:
- ✓ Good: `MyS3cur3P@ssw0rd!`
- ✗ Bad: `123456` or `password`

Never commit the actual password to git. Only commit `.env.example` or `.env.production` templates.
