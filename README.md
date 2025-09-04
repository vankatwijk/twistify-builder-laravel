# Instasites (Laravel) — Ubuntu/Nginx Deployment

This guide deploys the Laravel **builder API** and serves generated **static sites** by Host header.

- OS: Ubuntu 22.04 LTS or 24.04 LTS
- Web: Nginx
- PHP: PHP 8.3 + FPM
- App path: `/var/www/instasites/twistify-builder-laravel`
- Sites root: `/var/www/instasites/sites/<hostname>/public`

---

## 0) Prereqs

- A clean Ubuntu server (22.04/24.04).
- SSH access with sudo.
- Open firewall for **HTTP (80)** and (optional) **HTTPS (443)**:
  ```bash
  sudo ufw allow OpenSSH
  sudo ufw allow http
  sudo ufw allow https
  sudo ufw enable
  sudo ufw status
  ```

---

## 1) Install system packages

```bash
sudo apt update
sudo apt install -y software-properties-common curl git unzip nginx

# PHP 8.3 (24.04 ships 8.3; for 22.04 add the PPA):
sudo add-apt-repository ppa:ondrej/php -y || true
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-xml php8.3-mbstring php8.3-zip php8.3-curl php8.3-gd

# Composer (global)
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

Enable and check services:
```bash
sudo systemctl enable --now php8.3-fpm
sudo systemctl enable --now nginx
sudo systemctl status php8.3-fpm --no-pager
sudo systemctl status nginx --no-pager
```

---

## 2) App code & environment

```bash
# Directory layout
sudo mkdir -p /var/www/instasites
sudo chown -R $USER:$USER /var/www/instasites
cd /var/www/instasites

# Bring your code
git clone https://YOUR_REPO_URL twistify-builder-laravel
cd twistify-builder-laravel

# PHP deps
composer install --no-dev --optimize-autoloader

# Environment
cp .env.example .env 2>/dev/null || true
php artisan key:generate
```

Set the **.env** (minimal):
```bash
cat >> .env <<'EOF'
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_SERVER_IP

# Instasites
BUILDER_API_KEY=dev-key
SITES_ROOT=/var/www/instasites/sites
MAX_CONCURRENCY=6
EOF
```

Create the sites root and fix permissions for the web user:
```bash
sudo mkdir -p /var/www/instasites/sites
sudo chown -R www-data:www-data /var/www/instasites/sites
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 3) Nginx vhost (Host-based static + Laravel at /api)

Create `/etc/nginx/sites-available/instasites.conf`:

```nginx
server {
  listen 80 default_server;
  server_name _;

  # 1) Serve generated static sites by Host header
  root /var/www/instasites/sites/$host/public;
  index index.html;
  client_max_body_size 25m;

  location / {
    try_files $uri $uri/ /index.html =404;
  }

  # 2) Mount Laravel (builder) at /api/*
  # Route all /api/* requests to the Laravel public/
  location /api/ {
    root /var/www/instasites/twistify-builder-laravel/public;
    try_files $uri /index.php?$query_string;
  }

  # FastCGI for *only* /api/*.php (keeps PHP disabled elsewhere)
  location ~ ^/api/.+\.php$ {
    root /var/www/instasites/twistify-builder-laravel/public;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  }

  # Deny PHP execution anywhere else (static tree safety)
  location ~ \.php$ { return 404; }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/instasites.conf /etc/nginx/sites-enabled/instasites.conf
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4) Health check route (optional but recommended)

Add to `routes/api.php`:

```php
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'app' => config('app.name', 'laravel'),
        'env' => app()->environment(),
        'time' => now()->toIso8601String(),
    ]);
});

// Protected builder routes
Route::middleware('builder.key')->group(function () {
    Route::post('/build', [\App\Http\Controllers\Instasites\BuildController::class, 'build']);
    Route::post('/reset', [\App\Http\Controllers\Instasites\BuildController::class, 'reset']);
});
```

Clear caches (if available):
```bash
php artisan clear || true
rm -rf storage/framework/views/*
```

Test:
```bash
curl -i http://YOUR_SERVER_IP/api/health
```
Expect `HTTP/1.1 200` with JSON.

---

## 5) First build & preview

Create a sample payload:
```bash
cat > /tmp/payload.json <<'JSON'
{
  "blueprint": {
    "site_name": "Demo",
    "primary_domain": "demo.localhost",
    "default_locale": "en",
    "theme": { "name": "classic", "logoText": "Demo" }
  },
  "locales": ["en","fr"],
  "pages": [
    { "slug":"home", "locale":"en", "html":"<h1>Hello EN</h1>" },
    { "slug":"home", "locale":"fr", "html":"<h1>Bonjour FR</h1>" },
    { "slug":"about", "locale":"en", "html":"<p>About page</p>" }
  ],
  "posts": []
}
JSON
```

Build:
```bash
curl -X POST http://YOUR_SERVER_IP/api/build   -H 'Content-Type: application/json'   -H 'X-Builder-Key: dev-key'   -d @/tmp/payload.json
```

Check output:
```
ls -R /var/www/instasites/sites/demo.localhost/public
```

Preview without DNS:

- From your laptop, add to **/etc/hosts**:
  ```
  YOUR_SERVER_IP demo.localhost
  ```
- Open `http://demo.localhost/` (EN), `http://demo.localhost/fr/` (FR)

**cURL preview** (no hosts edit needed):
```bash
curl -H 'Host: demo.localhost' http://YOUR_SERVER_IP/ | head
```

---

## 6) Enable HTTPS (optional)

If you have real domains pointing to this server, install Certbot:

```bash
sudo snap install --classic certbot
sudo ln -s /snap/bin/certbot /usr/bin/certbot
# Example for a specific domain:
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

For many different customer hosts you’ll typically terminate TLS at a CDN (Cloudflare) and keep origin HTTP. If you must issue many certs here, create separate `server_name` blocks per domain and run `certbot` on each.

---

## 7) Useful maintenance

**Service status**
```bash
systemctl status nginx --no-pager
systemctl status php8.3-fpm --no-pager
```

**Logs**
```bash
sudo tail -n 200 /var/log/nginx/error.log
sudo tail -n 200 /var/log/nginx/access.log
sudo tail -n 200 /var/www/instasites/twistify-builder-laravel/storage/logs/laravel.log
```

**Permissions reset**
```bash
sudo chown -R www-data:www-data /var/www/instasites/sites
sudo chown -R www-data:www-data /var/www/instasites/twistify-builder-laravel/storage                                    /var/www/instasites/twistify-builder-laravel/bootstrap/cache
```

---

## 8) Common gotchas

- **`/api/health` is 404**  
  Your Nginx site isn’t loaded or PHP-FPM isn’t wired. Run `nginx -t`, `systemctl reload nginx`. Confirm the block above is the *only* default server on :80.

- **Build returns 401**  
  Missing `X-Builder-Key` or mismatch with `BUILDER_API_KEY` in `.env`.

- **Generated HTML has no content**  
  Ensure your `validatePayload()` includes:
  ```php
  'pages.*.html' => 'nullable|string',
  'posts.*.html' => 'nullable|string',
  ```
  or use your manual validator. Otherwise Laravel strips `html`/`meta_*`.

- **CSS missing**  
  Your service copies from:
  `resources/instasites/themes/<theme>/assets/` →  
  `/assets/<theme>/` inside each generated site. Ensure `style.css` exists there.

- **403/permission denied on write**  
  The `sites` root must be writable by **www-data**.

- **Raw IP shows nothing**  
  This setup serves static sites based on the **Host**. Add a hosts entry (see §5) or point a real domain at the server. If you want the IP to show Laravel, add a second server block with `root /var/www/instasites/twistify-builder-laravel/public;` and `server_name YOUR_SERVER_IP;` before the default block.

---

## 9) Example: IP hits Laravel (optional)

Create `/etc/nginx/sites-available/instasites-builder.conf`:
```nginx
server {
  listen 80;
  server_name YOUR_SERVER_IP;

  root /var/www/instasites/twistify-builder-laravel/public;
  index index.php;

  location / {
    try_files $uri /index.php?$query_string;
  }
  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  }
}
```

Enable it **before** the default:
```bash
sudo ln -s /etc/nginx/sites-available/instasites-builder.conf /etc/nginx/sites-enabled/instasites-builder.conf
sudo nginx -t && sudo systemctl reload nginx
```

Now `http://YOUR_SERVER_IP/` loads Laravel’s welcome; any other Host still serves the static site tree.

---

## 10) API quick reference

- **POST `/api/build`**  
  Headers: `Content-Type: application/json`, `X-Builder-Key: <key>`  
  Body: `{ blueprint, locales, pages[], posts[], reset? }`

- **POST `/api/reset`**  
  Body: `{ "hostname": "<host>" }` — deletes `/sites/<host>/`

- **GET `/api/health`** → `{ ok: true, time: ... }`

---

## Appendix A — Sample payloads

### A1. Classic (minimal)
```json
{
  "blueprint": {
    "site_name": "Demo",
    "primary_domain": "demo.localhost",
    "default_locale": "en",
    "theme": { "name": "classic", "logoText": "Demo" }
  },
  "locales": ["en","fr"],
  "pages": [
    { "slug":"home", "locale":"en", "html":"<h1>Hello EN</h1>" },
    { "slug":"home", "locale":"fr", "html":"<h1>Bonjour FR</h1>" }
  ]
}
```

### A2. Cyberchat (neon style)
```json
{
  "blueprint": {
    "site_name": "Cyber",
    "primary_domain": "cyber.localhost",
    "default_locale": "en",
    "theme": { "name": "cyberchat", "logoText": "Cyber", "primaryColor":"#22d3ee", "accentColor":"#a855f7" }
  },
  "pages": [
    { "slug":"home", "locale":"en", "html":"<h1>Welcome to Cyberchat</h1><p>Neon vibes.</p>" }
  ]
}
```

### A3. General (promo landing)
```json
{
  "blueprint": {
    "site_name": "Your Brand",
    "primary_domain": "brand.localhost",
    "default_locale": "en",
    "theme": {
      "name": "general",
      "logoText": "Your Brand",
      "primaryColor": "#ff3434",
      "accentColor": "#22d3ee",
      "cta": { "text": "Join Now", "href": "/signup/" },
      "hero": {
        "title": "Claim your welcome offer",
        "subtitle": "Fast payouts • 24/7 support • 4000+ games",
        "cta": { "text": "Start Playing", "href": "/signup/" },
        "badges": ["5-min payouts", "Crypto & FIAT", "Weekly promos"]
      },
      "features": ["Fast withdrawals", "Huge game library", "Tournaments", "24/7 support"]
    }
  },
  "pages": [
    { "slug":"home", "locale":"en", "html":"<h1>Home</h1><p>Landing content...</p>" }
  ]
}
```