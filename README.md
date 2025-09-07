# Instasites Static Website Builder

A Laravel-based API for generating static websites with automatic Apache virtual host configuration.

## 📋 COMPLETE PROJECT FILE STRUCTURE & EXPLANATION

### 🏗️ CORE ARCHITECTURE

#### 1. 🚪 Entry Points
- **`public/index.php`** - Main entry point for all HTTP requests
- **`bootstrap/app.php`** - Laravel application bootstrap configuration
- **`bootstrap/providers.php`** - Service provider registration
- **`artisan`** - Command-line interface

#### 2. ⚙️ Configuration Files
- **`config/instasites.php`** - Main Instasites configuration (sites root, Apache paths, API keys)
- **`config/app.php`** - Laravel application configuration
- **`.env`** - Environment variables (API keys, database, Apache paths)

#### 3. 🛣️ Routes & Controllers
- **`routes/api.php`** - API endpoints (`/build`, `/reset`, `/health`)
- **`routes/web.php`** - Web routes (homepage)
- **`app/Http/Controllers/Instasites/BuildController.php`** - Main API controller

### 🔧 BUSINESS LOGIC

#### 4. 🏭 Core Services
- **`app/Services/Instasites/SiteBuilderService.php`** - Main site generation engine
- **`app/Services/Instasites/ApacheVirtualHostService.php`** - Apache virtual host management
- **`app/Providers/InstaSitesServiceProvider.php`** - Service provider for dependency injection

#### 5. 🎨 Theme System
```
resources/views/instasites/themes/
├── classic/
│   ├── layouts/base.blade.php     # Main layout
│   ├── page.blade.php             # Page template
│   ├── post.blade.php             # Blog post template
│   └── assets/style.css           # Theme CSS
├── cyberchat/
│   ├── layouts/base.blade.php     # Cyberpunk theme
│   ├── assets/style.css
│   └── partials/
├── general/
│   ├── layouts/base.blade.php     # Landing page theme
│   ├── assets/style.css
│   └── partials/hero.blade.php    # Hero section
```

### 📄 SAMPLE DATA & EXAMPLES

#### 6. 📋 Sample Payloads
- **`examples/sample-payloads/minimal-site.json`** - Simple 1-page site
- **`examples/sample-payloads/basic-site.json`** - Full site with multiple pages, posts, multi-language

#### 7. 🎯 Generated Output Structure
```
/var/www/html/sites/
├── example.localhost/
│   ├── public/                    # Static website files
│   │   ├── index.html            # Homepage
│   │   ├── about/index.html      # About page
│   │   ├── blog/post/index.html  # Blog posts
│   │   ├── assets/classic/       # Theme assets
│   │   ├── sitemap.xml           # SEO sitemap
│   │   └── rss.xml               # RSS feed
│   └── manifest.json             # Build metadata
```

### 🛠️ AUTOMATION SCRIPTS

#### 8. 🚀 Management Scripts
- **`scripts/build-site.sh`** - Build websites with dynamic payloads
- **`scripts/reset-site.sh`** - Delete websites with confirmation
- **`scripts/demo.sh`** - Full system demonstration
- **`scripts/test-api.sh`** - API testing suite
- **`scripts/setup-local-apache.sh`** - Local development setup
- **`scripts/setup-bitnami-server.sh`** - Production server setup

#### 9. 🔧 Apache Management
- **`app/Console/Commands/ApacheManageCommand.php`** - CLI for Apache operations
- **Virtual host templates** - Auto-generated Apache configurations

## 📊 HOW EVERYTHING WORKS TOGETHER

### 🔄 Request Flow:
1. **API Request** → `routes/api.php` → `BuildController`
2. **Validation** → JSON payload validated against schema
3. **Site Building** → `SiteBuilderService` processes the request
4. **Theme Rendering** → Blade templates generate HTML
5. **Asset Copying** → CSS/JS copied to site directory
6. **Apache Config** → Virtual host created automatically
7. **Response** → JSON with build status and metadata

### 🎨 Theme System:
1. **Theme Selection** → From JSON payload (`blueprint.theme.name`)
2. **Template Loading** → `resources/views/instasites/themes/{theme}/`
3. **Variable Injection** → Site data passed to Blade templates
4. **HTML Generation** → `view()->render()` creates static HTML
5. **Asset Processing** → CSS variables injected, files copied

### 🌐 Multi-Language Support:
1. **Locale Detection** → From `locales` array in payload
2. **Content Organization** → Pages/posts grouped by locale
3. **URL Structure** → `/en/page/` vs `/es/page/`
4. **Fallback Logic** → Missing translations use default locale

## ⚡ KEY FEATURES

### 🔐 Security:
- API key authentication (`X-Builder-Key` header)
- Input validation and sanitization
- Secure file permissions

### 🚀 Performance:
- Static HTML generation (no database queries)
- Optimized CSS with caching headers
- Compressed assets

### 🔧 Production Ready:
- Apache virtual host automation
- Backup scripts with retention
- Queue worker services
- Comprehensive logging

### 📱 SEO Optimized:
- Automatic sitemap.xml generation
- RSS feeds for blog content
- Meta tags and canonical URLs
- Mobile-responsive themes

## 🚀 QUICK START

### 1. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Install dependencies
composer install
```

### 2. Configure Apache (Local Development)

```bash
./scripts/setup-local-apache.sh
```

### 3. Start the Laravel Server

```bash
php artisan serve
```

### 4. Build Your First Site

```bash
# Simple build
./scripts/build-site.sh mysite

# Or use API directly
curl -X POST http://localhost:8000/api/build \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d @examples/sample-payloads/minimal-site.json
```

## 📡 API ENDPOINTS

### Health Check
```
GET /api/health
```

### Build Website
```
POST /api/build
Headers:
  Content-Type: application/json
  X-Builder-Key: your-api-key

Body: JSON payload (see examples/sample-payloads/)
```

### Reset Website
```
POST /api/reset
Headers:
  Content-Type: application/json
  X-Builder-Key: your-api-key

Body: {"hostname": "example.localhost"}
```

## ⚙️ CONFIGURATION

### Environment Variables

```env
# API Security
BUILDER_API_KEY=dev-key-12345

# Sites Configuration
SITES_ROOT=/var/www/html/sites

# Apache Configuration
APACHE_SITES_AVAILABLE=/etc/apache2/sites-available
APACHE_SITES_ENABLED=/etc/apache2/sites-enabled
APACHE_RELOAD_COMMAND="sudo systemctl reload apache2"
APACHE_INTEGRATION_ENABLED=true
```

## 📋 SAMPLE PAYLOADS

### Minimal Site
```json
{
  "blueprint": {
    "site_name": "My Site",
    "primary_domain": "example.localhost",
    "theme": {"name": "classic"}
  },
  "pages": [
    {
      "slug": "home",
      "title": "Welcome",
      "html": "<h1>Hello World</h1>"
    }
  ]
}
```

### Multi-Language Site
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
    { "slug":"home", "locale":"fr", "html":"<h1>Bonjour FR</h1>" },
    { "slug":"about", "locale":"en", "html":"<p>About page</p>" }
  ],
  "posts": []
}
```

### Cyberchat Theme (Neon Style)
```json
{
  "blueprint": {
    "site_name": "Cyber",
    "primary_domain": "cyber.localhost",
    "default_locale": "en",
    "theme": {
      "name": "cyberchat",
      "logoText": "Cyber",
      "primaryColor":"#22d3ee",
      "accentColor":"#a855f7"
    }
  },
  "pages": [
    { "slug":"home", "locale":"en", "html":"<h1>Welcome to Cyberchat</h1><p>Neon vibes.</p>" }
  ]
}
```

### General Theme (Landing Page)
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

## 🛠️ MANAGEMENT SCRIPTS

### Build a Website
```bash
# Build with auto-generated content
./scripts/build-site.sh mycompany

# This creates mycompany.localhost with:
# - Professional homepage
# - About page
# - Classic theme
# - SEO optimization
```

### Reset a Website
```bash
# Delete a website with confirmation
./scripts/reset-site.sh mycompany

# This removes:
# - All generated files
# - Apache virtual host
# - Site directory
```

### Demo & Testing
```bash
# Run full demonstration
./scripts/demo.sh

# Test API endpoints
./scripts/test-api.sh

# Setup local Apache
./scripts/setup-local-apache.sh

# Setup production server (Bitnami)
sudo ./scripts/setup-bitnami-server.sh
```

## 🔧 APACHE MANAGEMENT

### Command Line Interface
```bash
# List managed virtual hosts
php artisan apache:manage list

# Enable a site
php artisan apache:manage enable example.localhost

# Disable a site
php artisan apache:manage disable example.localhost

# Reload Apache configuration
php artisan apache:manage reload

# Test Apache configuration
php artisan apache:manage test-config
```

## 🎨 AVAILABLE THEMES

### Classic Theme
- **Style**: Clean, professional design
- **Use Case**: Business websites, portfolios
- **Features**: Bootstrap-based, responsive navigation

### Cyberchat Theme
- **Style**: Modern, tech-focused with neon accents
- **Use Case**: Gaming, tech startups, crypto projects
- **Features**: Dark theme, gradient backgrounds, futuristic design

### General Theme
- **Style**: Flexible landing page design
- **Use Case**: Marketing sites, product launches
- **Features**: Hero sections, feature lists, call-to-action buttons

## 🚀 PRODUCTION DEPLOYMENT

### For Bitnami LAMP (AWS Lightsail)
```bash
# Run the setup script
sudo ./scripts/setup-bitnami-server.sh

# Deploy your Laravel app to:
# /opt/bitnami/apache2/htdocs/instasites/

# Sites will be generated in:
# /var/www/html/sites/
```

### For Ubuntu/Nginx
```bash
# Follow the deployment guide in the original README
# Configure Nginx to serve static sites by Host header
# Mount Laravel API at /api/*
```

## 🔍 TROUBLESHOOTING

### Common Issues

**Build returns 401**
- Missing `X-Builder-Key` header
- API key mismatch in `.env`

**Generated HTML has no content**
- Ensure payload validation includes `html` fields
- Check Blade template rendering

**CSS missing**
- Verify theme assets exist in `resources/instasites/themes/{theme}/assets/`
- Check file permissions

**Apache virtual host issues**
- Verify proper permissions on Apache directories
- Check sudo permissions for Apache reload

### Logs
```bash
# Laravel application logs
tail -f storage/logs/laravel.log

# Apache error logs (if using Apache)
sudo tail -f /var/log/apache2/error.log

# Check generated site files
ls -la /var/www/html/sites/example.localhost/public/
```

## 📈 FEATURES OVERVIEW

This architecture allows you to send a JSON payload to the API and get a complete, production-ready static website with Apache configuration automatically handled!

### ✅ What You Get:
- **Static HTML generation** from JSON payloads
- **Multi-language support** with proper URL structure
- **SEO optimization** (sitemaps, meta tags, RSS feeds)
- **Apache virtual host automation**
- **Multiple professional themes**
- **Mobile-responsive design**
- **Security headers and caching**
- **Comprehensive management tools**

### 🎯 Perfect For:
- **SaaS platforms** generating customer websites
- **Marketing agencies** creating client sites
- **E-commerce platforms** with store builders
- **Content management systems**
- **Multi-tenant applications**

---

## 📚 ADDITIONAL RESOURCES

### Documentation Files
- **`README-SETUP.md`** - Detailed setup instructions
- **`examples/sample-payloads/`** - Complete JSON examples
- **`scripts/`** - All automation scripts

### Support & Maintenance
- Check logs: `storage/logs/laravel.log`
- Run tests: `./scripts/test-api.sh`
- Verify Apache: `php artisan apache:manage test-config`

---

**🎉 Your Instasites system is ready to generate beautiful static websites from JSON payloads!**
