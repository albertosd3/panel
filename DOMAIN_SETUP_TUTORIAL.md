# Domain Setup (Default Domain Only)

Updated: 2025-08-12

The panel now always uses a single default domain for all shortlinks. The previous domain management UI has been removed. Use the steps below to set or change the default domain.

## 1) Point DNS to your server
- A/AAAA records for your root or subdomain to your server IP
- Optional: enable HTTPS via your web server/Cloudflare

## 2) Create or update the default domain record
The application reads the default active domain from the `domains` table via `Domain::getDefault()`.

Option A — via SQL:
```sql
-- Create table records if not present (migrations already exist)
INSERT INTO domains (domain, is_active, is_default, force_https, created_at, updated_at)
VALUES ('yourdomain.com', 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT(domain) DO UPDATE SET is_active=EXCLUDED.is_active, is_default=EXCLUDED.is_default, force_https=EXCLUDED.force_https, updated_at=CURRENT_TIMESTAMP;

-- Ensure only one default
iUPDATE domains SET is_default = 0 WHERE domain <> 'yourdomain.com';
```

Option B — via Artisan Tinker:
```bash
php artisan tinker
```
```php
use App\Models\Domain;
$all = Domain::all();
// Create or update your domain
$dom = Domain::firstOrCreate(['domain' => 'yourdomain.com'], [
  'is_active' => true,
  'is_default' => true,
  'force_https' => true,
]);
// Make it the only default
db()->table('domains')->update(['is_default' => false]);
$dom->is_default = true; $dom->is_active = true; $dom->save();
exit;
```

Notes:
- If no active default is found, the app falls back to the first active domain.
- If no domain row exists, it falls back to `APP_URL`.

## 3) Clear caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 4) Verify
- Create a shortlink in the panel
- Click the slug; it should open `https://yourdomain.com/{slug}` (depending on force_https)

## Reference
- URL building is handled by `Shortlink@getFullUrlAttribute()` which uses `Domain::getDefault()`.
- Redirect handling is unchanged; only the displayed/constructed short URL uses the default domain.
