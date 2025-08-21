# 🔧 Laravel Forge Deployment Troubleshooting Guide

## Masalah: Shortlink Creation Gagal di Forge

Anda melaporkan bahwa shortlink creation masih error setelah deployment ke Laravel Forge. Berikut langkah-langkah komprehensif untuk mendiagnosis dan memperbaiki masalah:

## 1. 📋 Upload dan Jalankan File Diagnostik

Upload file-file berikut ke root directory Laravel Forge Anda:

### File yang Sudah Dibuat:
- `forge_diagnostics.php` - Diagnostic tool lengkap
- `forge_autofix.php` - Auto-fix script  
- `test_api_direct.php` - Test API backend langsung
- `frontend_debug.html` - Debug tool untuk frontend JavaScript
- `health_check.php` - Enhanced health check

### Jalankan Diagnosis:
1. **Akses:** `https://yourdomain.com/forge_diagnostics.php`
2. **Test semua komponen** dan catat error yang muncul
3. **Jalankan auto-fix:** `https://yourdomain.com/forge_autofix.php`

## 2. 🔍 Langkah-Langkah Debugging Sistematis

### A. Cek Backend API Health
```bash
# Akses health check
curl https://yourdomain.com/health_check.php

# Test API langsung tanpa frontend
curl https://yourdomain.com/test_api_direct.php
```

### B. Cek Database & Migrations
```bash
# SSH ke server Forge
ssh forge@your-server-ip

# Masuk ke directory project
cd /home/forge/yourdomain.com

# Cek status migrations
php artisan migrate:status

# Run migrations jika diperlukan
php artisan migrate --force

# Cek database tables
php artisan tinker
>>> \App\Models\Shortlink::count()
>>> \App\Models\PanelSetting::count()
```

### C. Clear & Rebuild Caches
```bash
# Clear semua cache
php artisan optimize:clear

# Rebuild production caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache
```

### D. Cek Environment Variables
```bash
# Cek .env file
cat .env | grep -E "(APP_|DB_|STOPBOT_)"

# Verify APP_KEY exists
php artisan key:generate --show
```

## 3. 🌐 Frontend JavaScript Issues

### A. Test dengan Frontend Debug Tool
1. **Akses:** `https://yourdomain.com/frontend_debug.html`
2. **Jalankan semua tests:**
   - Environment check
   - CSRF token test
   - API endpoints test
   - Shortlink creation test

### B. Browser Console Check
1. **Buka browser DevTools** (F12)
2. **Pergi ke Console tab**
3. **Akses panel:** `https://yourdomain.com/panel`
4. **Coba buat shortlink** dan lihat error messages
5. **Cek Network tab** untuk failed requests

### C. Common Frontend Issues & Fixes

#### Issue 1: Mixed Content (HTTP/HTTPS)
```javascript
// Jika ada error mixed content, pastikan semua API calls menggunakan HTTPS
// Check di browser console:
console.log('Current protocol:', window.location.protocol);
console.log('Current host:', window.location.host);
```

#### Issue 2: CSRF Token Problems
```javascript
// Cek CSRF token di browser console:
const token = document.querySelector('meta[name="csrf-token"]');
console.log('CSRF Token:', token ? token.getAttribute('content') : 'NOT FOUND');
```

#### Issue 3: API Endpoint Not Found
```javascript
// Test API endpoints manually:
fetch('/api/list', {
    headers: { 'Accept': 'application/json' }
}).then(r => r.json()).then(console.log);
```

## 4. 🔧 Common Fixes untuk Forge

### A. Environment Setup
```bash
# Pastikan environment production
echo "APP_ENV=production" >> .env
echo "APP_DEBUG=false" >> .env

# Set proper APP_URL
echo "APP_URL=https://yourdomain.com" >> .env
```

### B. Database Setup
```bash
# Jika menggunakan MySQL di Forge
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=your_password

# Atau tetap SQLite
DB_CONNECTION=sqlite
# Pastikan file database.sqlite ada dan writable
touch database/database.sqlite
chmod 666 database/database.sqlite
```

### C. Web Server Configuration
Pastikan Nginx configuration benar untuk Laravel:

```nginx
# /etc/nginx/sites-available/yourdomain.com
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Untuk API routes
location /api {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### D. PHP Configuration
```bash
# Cek PHP version
php -v

# Pastikan extensions required ada
php -m | grep -E "(pdo|sqlite|json|curl|mbstring|openssl)"
```

## 5. 🐛 Debugging Steps Berdasarkan Error Type

### A. Jika "Loading shortlinks..." Terus Muncul
**Diagnosis:**
- API `/api/list` gagal
- JavaScript error
- CORS/Mixed content issue

**Fix:**
1. Test `/api/list` langsung di browser
2. Cek browser console untuk JavaScript errors
3. Verify CSRF token
4. Check HTTPS configuration

### B. Jika Shortlink Creation Button Tidak Respond
**Diagnosis:**
- JavaScript event listener tidak terpasang
- Form validation error
- API endpoint tidak accessible

**Fix:**
1. Cek browser console saat click button
2. Test API `/api/create` dengan POST request
3. Verify CSRF token in request headers

### C. Jika Error 500 pada API Calls
**Diagnosis:**
- Server error (cek logs)
- Database connection issue
- Missing dependencies

**Fix:**
1. Cek Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify database connection
3. Run `composer install --optimize-autoloader --no-dev`

### D. Jika Error 404 pada Routes
**Diagnosis:**
- Route cache issue
- Nginx configuration
- File permissions

**Fix:**
1. Clear route cache: `php artisan route:clear`
2. Rebuild route cache: `php artisan route:cache`
3. Check Nginx configuration

## 6. 📝 Step-by-Step Debugging Protocol

### Langkah 1: Backend Health Check
```bash
# SSH ke server
ssh forge@your-server

# Test Laravel bootstrap
cd /home/forge/yourdomain.com
php artisan --version

# Test database
php artisan tinker
>>> DB::connection()->getPdo()
>>> \App\Models\Shortlink::count()
```

### Langkah 2: API Endpoint Test
```bash
# Test dari server langsung
curl -X GET "http://localhost/api/list" \
  -H "Accept: application/json"

curl -X POST "http://localhost/api/create" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"is_rotator":false,"destination":"https://google.com"}'
```

### Langkah 3: Frontend Debug
1. Access `https://yourdomain.com/frontend_debug.html`
2. Run all tests systematically
3. Note any failures and error messages

### Langkah 4: Log Analysis
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx logs
sudo tail -f /var/log/nginx/yourdomain.com-error.log
sudo tail -f /var/log/nginx/yourdomain.com-access.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm.log
```

## 7. 🚀 Emergency Fix Script

Jika semua langkah di atas tidak berhasil, jalankan emergency fix:

```bash
#!/bin/bash
# Emergency fix script untuk Laravel Forge

cd /home/forge/yourdomain.com

# Backup current state
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Clear everything
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Reinstall dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# Test health
php artisan tinker --execute="echo 'Database: ' . \App\Models\Shortlink::count() . ' shortlinks'"

echo "Emergency fix complete. Test your application now."
```

## 8. 📞 If All Else Fails

Jika setelah semua langkah di atas masalah masih persist:

1. **Share diagnostic results** dari tools yang sudah dibuat
2. **Provide error logs** dari Laravel dan Nginx
3. **Browser console screenshots** dari Developer Tools
4. **Network tab recordings** showing failed requests

### Quick Checklist:
- [ ] `forge_diagnostics.php` - semua tests pass?
- [ ] `test_api_direct.php` - backend API working?
- [ ] `frontend_debug.html` - JavaScript tests pass?
- [ ] Browser console - no JavaScript errors?
- [ ] Network tab - API calls returning 200?
- [ ] Laravel logs - no PHP errors?

## 9. 🔒 Security Reminder

**PENTING:** Setelah debugging selesai, hapus file-file diagnostic:
```bash
rm forge_diagnostics.php
rm forge_autofix.php  
rm test_api_direct.php
rm frontend_debug.html
```

---

**Mulai dengan menjalankan `forge_diagnostics.php` dan `frontend_debug.html` untuk mendapatkan gambaran lengkap masalah yang terjadi.**
