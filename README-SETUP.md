# Instasites Static Website Builder - Setup Guide

This Laravel application provides an API for building static websites with automatic Apache virtual host configuration.

## Features

- ✅ **Build API**: Create static websites from JSON payloads
- ✅ **Reset API**: Remove existing websites
- ✅ **Multi-language support**: Generate sites in multiple locales
- ✅ **Theme system**: Multiple built-in themes (classic, cyberchat, general)
- ✅ **Apache integration**: Automatic virtual host creation
- ✅ **SEO optimized**: Automatic sitemap and RSS feed generation

## Quick Start

### 1. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Install dependencies (if not already done)
composer install
```

### 2. Configure Apache (Local Development)

Run the setup script to configure Apache for local development:

```bash
./scripts/setup-local-apache.sh
```

This script will:
- Create the sites directory structure
- Enable required Apache modules
- Configure permissions
- Update your .env file

### 3. Start the Laravel Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

### 4. Test the System

Run the comprehensive test suite:

```bash
./scripts/test-api.sh
```

Or test individual components:

```bash
# Test health endpoint
curl http://localhost:8000/api/health

# Test build endpoint
curl -X POST http://localhost:8000/api/build \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d @examples/sample-payloads/minimal-site.json
```

## API Endpoints

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

## Configuration

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

### Apache Permissions (Production)

For production use, you'll need to configure proper permissions:

```bash
# Add www-data user to apache group
sudo usermod -a -G www-data apache

# Set proper permissions on Apache directories
sudo chown -R www-data:www-data /etc/apache2/sites-available
sudo chown -R www-data:www-data /etc/apache2/sites-enabled

# Allow www-data to reload Apache
echo "www-data ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload apache2" | sudo tee /etc/sudoers.d/apache-reload
```

## Sample Payloads

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

### Full Site
See `examples/sample-payloads/basic-site.json` for a complete example with:
- Multiple pages and posts
- Multi-language support
- SEO metadata
- Navigation configuration

## Apache Management

Use the built-in Apache management commands:

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

## Directory Structure

```
/var/www/html/sites/
├── example.localhost/
│   ├── public/           # Generated static files
│   │   ├── index.html
│   │   ├── about/
│   │   ├── blog/
│   │   └── assets/
│   └── manifest.json     # Build metadata
```

## Themes

Available themes:
- **classic**: Clean, professional design
- **cyberchat**: Modern, tech-focused design  
- **general**: Flexible, general-purpose design

Theme assets are automatically copied and customized based on your configuration.

## Troubleshooting

### Apache Virtual Host Issues

1. **Permission Denied**: Ensure proper permissions on Apache directories
2. **Apache Not Reloading**: Check sudo permissions for www-data user
3. **Sites Not Accessible**: Verify Apache is running and sites are enabled

### Build Issues

1. **Theme Not Found**: Ensure theme exists in `resources/instasites/themes/`
2. **Invalid JSON**: Validate your payload against the schema
3. **Missing Required Fields**: Check that `blueprint.site_name` and `blueprint.primary_domain` are provided

### Logs

Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Configure proper Apache permissions
3. Use a process manager like Supervisor for the Laravel queue
4. Set up proper SSL certificates
5. Configure firewall rules
6. Use a reverse proxy if needed

## Security Notes

- The API key (`BUILDER_API_KEY`) should be kept secure
- Apache integration requires elevated permissions - use carefully
- Generated sites are publicly accessible once Apache virtual hosts are created
- Consider rate limiting for production use

## Support

For issues or questions:
1. Check the logs: `storage/logs/laravel.log`
2. Run the test suite: `./scripts/test-api.sh`
3. Verify Apache configuration: `php artisan apache:manage test-config`
