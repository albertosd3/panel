# 🚨 Laravel Bootstrap Error Fix Guide

## Error yang Anda Alami:
```php
// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->handleRequest(Request::capture());
```

## 🔍 Kemungkinan Penyebab Error:

### 1. **Environment Configuration Issues**
- APP_ENV tidak sesuai untuk production
- APP_URL tidak match dengan domain Forge
- APP_DEBUG masih true di production

### 2. **File Structure Problems**
- Missing atau corrupt bootstrap files
- Wrong file permissions
- Missing vendor directory

### 3. **Server Configuration Issues**
- PHP version compatibility
- Nginx/Apache configuration
- Missing PHP extensions

## 🛠️ SOLUSI STEP-BY-STEP:

### **LANGKAH 1: Upload Diagnostic Tools**
Upload file-file berikut ke root directory Laravel Forge:

1. `bootstrap_fix.php` - Main diagnostic tool
2. `bootstrap_test.php` - Test bootstrap functionality  
3. `clear_cache.php` - Clear and rebuild caches
4. `run_migrations.php` - Run database migrations

### **LANGKAH 2: Fix Environment Configuration**
Ganti di file `.env` Anda:

```env
# UBAH DARI:
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# MENJADI:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### **LANGKAH 3: Run Diagnostic Tools**

#### A. Test Bootstrap
```bash
# Akses via browser:
https://yourdomain.com/bootstrap_test.php
```

#### B. Run Auto-Fix
```bash
# Akses via browser:
https://yourdomain.com/bootstrap_fix.php
```

#### C. Clear Caches
```bash
# Akses via browser:
https://yourdomain.com/clear_cache.php
```

### **LANGKAH 4: SSH Commands (Jika diperlukan)**

```bash
# SSH ke server Forge
ssh forge@your-server-ip

# Masuk ke directory project
cd /home/forge/yourdomain.com

# 1. Fix file permissions
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# 2. Reinstall dependencies
composer install --optimize-autoloader --no-dev

# 3. Clear all caches
php artisan optimize:clear

# 4. Rebuild production caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run migrations
php artisan migrate --force

# 6. Test bootstrap manually
php artisan tinker --execute="echo 'Laravel Version: ' . app()->version()"

# 7. Test web server response
curl -I http://localhost
```

### **LANGKAH 5: Nginx Configuration Check**

```bash
# Check Nginx syntax
sudo nginx -t

# Check site configuration
sudo cat /etc/nginx/sites-available/yourdomain.com

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

## 🔧 EMERGENCY FIX SCRIPT

Jika semua langkah di atas tidak berhasil, jalankan emergency fix:

```bash
#!/bin/bash
# Emergency Laravel Forge Fix

cd /home/forge/yourdomain.com

# Backup current state
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update environment for production
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's#APP_URL=http://localhost#APP_URL=https://yourdomain.com#' .env

# Clear everything
php artisan optimize:clear
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Reinstall and optimize
composer install --optimize-autoloader --no-dev
composer dump-autoload --optimize

# Fix permissions
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache
chown -R forge:forge database

# Generate new key if needed
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test application
php artisan about
```

## 🎯 SPECIFIC ERROR FIXES:

### Error: "Class not found"
```bash
composer dump-autoload --optimize
php artisan clear-compiled
```

### Error: "Permission denied"
```bash
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache
```

### Error: "No application encryption key"
```bash
php artisan key:generate --force
```

### Error: "Database not found"
```bash
# For SQLite
touch database/database.sqlite
chmod 666 database/database.sqlite

# For MySQL
php artisan migrate --force
```

### Error: "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

## 📞 PRIORITY TESTING ORDER:

1. **First:** Access `https://yourdomain.com/bootstrap_test.php`
2. **If fails:** Run emergency fix script via SSH
3. **Then:** Access `https://yourdomain.com/bootstrap_fix.php`
4. **Finally:** Test your panel at `https://yourdomain.com/panel`

## 🔍 DEBUGGING INFO TO COLLECT:

Jika masih error, collect informasi berikut:

1. **Output dari bootstrap_test.php**
2. **Laravel log:** `tail -f storage/logs/laravel.log`
3. **Nginx error log:** `sudo tail -f /var/log/nginx/yourdomain.com-error.log`
4. **PHP-FPM log:** `sudo tail -f /var/log/php8.1-fpm.log`
5. **Server response:** `curl -v https://yourdomain.com`

---

**🚀 Start with uploading the diagnostic tools and running bootstrap_test.php first!**
