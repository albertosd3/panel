# Domain Setup Tutorial - Shortlink Panel

## Overview

This tutorial will guide you through setting up custom domains for your shortlink panel. With custom domains, you can create branded short URLs like `yourbrand.co/abc123` instead of using the default domain.

## Prerequisites

- A domain name you own
- Access to your domain's DNS settings
- Web server with SSL certificate capability
- Laravel shortlink panel installed and running

## Step 1: Domain Registration and DNS Configuration

### 1.1 Point Your Domain to Your Server

Configure your domain's DNS to point to your server where the Laravel application is hosted.

**A Records:**
```
@ → YOUR_SERVER_IP
www → YOUR_SERVER_IP
```

**Or CNAME Records (if using subdomain):**
```
short → your-main-domain.com
```

### 1.2 Wait for DNS Propagation

DNS changes can take 5 minutes to 48 hours to propagate worldwide. You can check DNS propagation using tools like:
- https://whatsmydns.net/
- https://dnschecker.org/

## Step 2: Web Server Configuration

### 2.1 Apache Configuration

Create a new virtual host for your domain:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /path/to/your/laravel/public
    
    <Directory /path/to/your/laravel/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/yourdomain_error.log
    CustomLog ${APACHE_LOG_DIR}/yourdomain_access.log combined
</VirtualHost>
```

### 2.2 Nginx Configuration

Create a new server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/your/laravel/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 2.3 Restart Web Server

**Apache:**
```bash
sudo systemctl restart apache2
```

**Nginx:**
```bash
sudo systemctl restart nginx
```

## Step 3: SSL Certificate Setup (Recommended)

### 3.1 Install Certbot

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx
```

**CentOS/RHEL:**
```bash
sudo yum install certbot python3-certbot-apache  # For Apache
# OR
sudo yum install certbot python3-certbot-nginx   # For Nginx
```

### 3.2 Get SSL Certificate

**For Apache:**
```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

**For Nginx:**
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 3.3 Auto-renewal

Set up automatic renewal:
```bash
sudo crontab -e
```

Add this line:
```
0 12 * * * /usr/bin/certbot renew --quiet
```

## Step 4: Laravel Configuration

### 4.1 Update Environment Variables

Edit your `.env` file:

```env
# Domain Management
SHORTLINK_DEFAULT_DOMAIN=yourdomain.com
SHORTLINK_CUSTOM_DOMAINS=yourdomain.com,another-domain.com
SHORTLINK_FORCE_HTTPS=true
SHORTLINK_ENABLE_WILDCARD_REDIRECT=true
```

### 4.2 Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Step 5: Add Domain to Panel

### 5.1 Access Domain Management

1. Login to your shortlink panel
2. Click the "🌍 Domains" button in the dashboard
3. Click "Add New Domain"

### 5.2 Add Your Domain

1. **Domain:** Enter your domain (e.g., `yourdomain.com`)
2. **Description:** Optional description for this domain
3. Click "Add Domain"

### 5.3 Set as Default (Optional)

If you want this domain to be the default for new shortlinks:
1. Click "Set Default" button next to your domain
2. Confirm the action

### 5.4 Test Domain Configuration

1. Click the "Test" button (🔍) next to your domain
2. The system will check if your domain is properly configured
3. Fix any issues reported by the test

## Step 6: Creating Shortlinks with Custom Domains

### 6.1 Using the Panel

1. Go to the main dashboard
2. In the "Create New Shortlink" form, select your domain from the dropdown
3. Enter the destination URL and optional custom slug
4. Click "✨ Create Shortlink"

### 6.2 Using the API

```bash
curl -X POST https://yourdomain.com/api/create \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "destination": "https://example.com/very-long-url",
    "slug": "custom-slug",
    "domain_id": 1
  }'
```

## Step 7: Multiple Domain Setup

### 7.1 Adding Multiple Domains

You can add multiple domains for different purposes:
- Main brand domain: `yourbrand.co`
- Campaign domains: `promo.yourbrand.co`
- Regional domains: `us.yourbrand.co`, `eu.yourbrand.co`

### 7.2 Domain-specific Configuration

Each domain can have:
- Different SSL certificates
- Different web server configurations
- Different branding/themes (future feature)

## Troubleshooting

### Common Issues

**1. Domain not resolving**
- Check DNS configuration
- Wait for DNS propagation
- Use DNS checker tools

**2. SSL certificate issues**
- Ensure domain points to correct server
- Check Certbot logs: `sudo journalctl -u certbot`
- Try manual certificate generation

**3. Laravel not responding**
- Check web server error logs
- Verify document root path
- Check file permissions
- Ensure .htaccess file exists (Apache)

**4. Shortlinks not working**
- Check Laravel routes
- Verify database connection
- Check application logs: `storage/logs/laravel.log`

### Health Check

The panel includes a health check endpoint at `/health-check`. You can test this manually:

```bash
curl https://yourdomain.com/health-check
```

Should return: `OK`

### Log Files

Check these log files for issues:
- **Laravel:** `storage/logs/laravel.log`
- **Apache:** `/var/log/apache2/error.log`
- **Nginx:** `/var/log/nginx/error.log`
- **SSL:** `/var/log/letsencrypt/letsencrypt.log`

## Security Considerations

### 1. HTTPS Only
Always use HTTPS for production domains to ensure security and build user trust.

### 2. Rate Limiting
Consider implementing rate limiting to prevent abuse:
```env
PANEL_RATE_LIMIT_ENABLED=true
PANEL_RATE_LIMIT_REQUESTS=100
PANEL_RATE_LIMIT_MINUTES=60
```

### 3. Domain Validation
Only add domains you own and control to prevent unauthorized use.

### 4. Regular Updates
Keep your server, PHP, and Laravel framework updated for security.

## Advanced Configuration

### Wildcard Domains

For advanced users, you can set up wildcard domains:

**DNS:**
```
*.yourdomain.com → YOUR_SERVER_IP
```

**Laravel Route (in web.php):**
```php
Route::domain('{subdomain}.yourdomain.com')->group(function () {
    Route::get('/{slug}', [ShortlinkController::class, 'redirect']);
});
```

### CDN Integration

For better performance, consider using a CDN:
1. Set up CloudFlare or similar CDN
2. Point your domain to the CDN
3. Configure CDN to forward requests to your server

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Laravel and web server logs
3. Test each step systematically
4. Ensure all prerequisites are met

For additional help, consult:
- Laravel Documentation: https://laravel.com/docs
- Let's Encrypt Documentation: https://letsencrypt.org/docs/
- Your web server documentation

## Best Practices

1. **Use descriptive domain names** that represent your brand
2. **Set up monitoring** to ensure your domains are always accessible
3. **Regular backups** of your database and configuration
4. **Document your setup** for future reference
5. **Test thoroughly** before using in production
6. **Monitor SSL certificate expiration** dates
7. **Use consistent naming conventions** for subdomains

Remember: A well-configured domain setup enhances your brand presence and user trust in your shortened URLs.
