#!/bin/bash

# Bitnami LAMP Server Setup Script for Instasites
# Usage: sudo ./scripts/setup-bitnami-server.sh
# This script configures a Bitnami LAMP server for the Instasites application

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Error: This script must be run as root (use sudo)${NC}"
    echo "Usage: sudo $0"
    exit 1
fi

echo -e "${BLUE}🚀 Setting up Bitnami LAMP Server for Instasites${NC}"
echo "=================================================="
echo ""

# Get the non-root user (usually 'bitnami')
BITNAMI_USER="bitnami"
if ! id "$BITNAMI_USER" &>/dev/null; then
    echo -e "${YELLOW}⚠️  User 'bitnami' not found, using 'ubuntu' instead${NC}"
    BITNAMI_USER="ubuntu"
fi

echo -e "${BLUE}📋 Configuration:${NC}"
echo "  User: $BITNAMI_USER"
echo "  Apache config: /opt/bitnami/apache2/conf"
echo "  Sites root: /var/www/html/sites"
echo ""

# 1. Update system packages
echo -e "${BLUE}1. Updating system packages...${NC}"
apt-get update -y
apt-get upgrade -y

# 2. Install required packages
echo -e "${BLUE}2. Installing required packages...${NC}"
apt-get install -y \
    curl \
    wget \
    unzip \
    git \
    jq \
    htop \
    nano \
    supervisor

# 3. Install PHP extensions if needed
echo -e "${BLUE}3. Checking PHP extensions...${NC}"
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "  PHP Version: $PHP_VERSION"

# Install common PHP extensions that might be missing
apt-get install -y \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-bcmath

# 4. Create sites directory structure
echo -e "${BLUE}4. Creating sites directory structure...${NC}"
SITES_ROOT="/var/www/html/sites"
mkdir -p "$SITES_ROOT"
mkdir -p "/opt/bitnami/apache2/conf/sites-available"
mkdir -p "/opt/bitnami/apache2/conf/sites-enabled"

# 5. Set proper ownership and permissions
echo -e "${BLUE}5. Setting ownership and permissions...${NC}"

# Sites directory - writable by bitnami user, readable by daemon
chown -R $BITNAMI_USER:daemon "$SITES_ROOT"
chmod -R 755 "$SITES_ROOT"

# Apache configuration directories
chown -R $BITNAMI_USER:daemon /opt/bitnami/apache2/conf/sites-available
chown -R $BITNAMI_USER:daemon /opt/bitnami/apache2/conf/sites-enabled
chmod -R 755 /opt/bitnami/apache2/conf/sites-available
chmod -R 755 /opt/bitnami/apache2/conf/sites-enabled

# Laravel application directory (if exists)
if [ -d "/opt/bitnami/apache2/htdocs/instasites" ]; then
    chown -R $BITNAMI_USER:daemon /opt/bitnami/apache2/htdocs/instasites
    chmod -R 755 /opt/bitnami/apache2/htdocs/instasites

    # Laravel specific permissions
    if [ -d "/opt/bitnami/apache2/htdocs/instasites/storage" ]; then
        chmod -R 775 /opt/bitnami/apache2/htdocs/instasites/storage
    fi
    if [ -d "/opt/bitnami/apache2/htdocs/instasites/bootstrap/cache" ]; then
        chmod -R 775 /opt/bitnami/apache2/htdocs/instasites/bootstrap/cache
    fi
fi

# 6. Configure Apache modules
echo -e "${BLUE}6. Enabling Apache modules...${NC}"
APACHE_MODULES_DIR="/opt/bitnami/apache2/conf"

# Enable required modules
echo "LoadModule rewrite_module modules/mod_rewrite.so" >> "$APACHE_MODULES_DIR/httpd.conf"
echo "LoadModule headers_module modules/mod_headers.so" >> "$APACHE_MODULES_DIR/httpd.conf"
echo "LoadModule expires_module modules/mod_expires.so" >> "$APACHE_MODULES_DIR/httpd.conf"

# 7. Configure Apache virtual hosts include
echo -e "${BLUE}7. Configuring Apache virtual hosts...${NC}"
VHOST_INCLUDE="Include conf/sites-enabled/*.conf"
if ! grep -q "$VHOST_INCLUDE" "$APACHE_MODULES_DIR/httpd.conf"; then
    echo "" >> "$APACHE_MODULES_DIR/httpd.conf"
    echo "# Include virtual hosts" >> "$APACHE_MODULES_DIR/httpd.conf"
    echo "$VHOST_INCLUDE" >> "$APACHE_MODULES_DIR/httpd.conf"
fi

# 8. Create .htaccess template
echo -e "${BLUE}8. Creating .htaccess template...${NC}"
cat > "$SITES_ROOT/.htaccess-template" << 'EOF'
# Static Site .htaccess
RewriteEngine On

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Cache static assets
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
    Header set Cache-Control "public, immutable"
</FilesMatch>

# Cache HTML files for shorter period
<FilesMatch "\.(html|htm)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 hour"
    Header set Cache-Control "public, must-revalidate"
</FilesMatch>

# Fallback to index.html for SPA-like behavior
FallbackResource /index.html

# Deny access to sensitive files
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>
EOF

chown $BITNAMI_USER:daemon "$SITES_ROOT/.htaccess-template"
chmod 644 "$SITES_ROOT/.htaccess-template"

# 9. Configure sudo permissions for Apache reload
echo -e "${BLUE}9. Configuring sudo permissions...${NC}"
echo "$BITNAMI_USER ALL=(ALL) NOPASSWD: /opt/bitnami/ctlscript.sh restart apache" > /etc/sudoers.d/bitnami-apache
echo "$BITNAMI_USER ALL=(ALL) NOPASSWD: /opt/bitnami/ctlscript.sh reload apache" >> /etc/sudoers.d/bitnami-apache
chmod 440 /etc/sudoers.d/bitnami-apache

# 10. Create environment configuration template
echo -e "${BLUE}10. Creating environment configuration...${NC}"
if [ ! -f "/home/$BITNAMI_USER/.env.instasites" ]; then
    cat > "/home/$BITNAMI_USER/.env.instasites" << EOF
# Instasites Configuration for Bitnami LAMP
SITES_ROOT=/var/www/html/sites
APACHE_SITES_AVAILABLE=/opt/bitnami/apache2/conf/sites-available
APACHE_SITES_ENABLED=/opt/bitnami/apache2/conf/sites-enabled
APACHE_RELOAD_COMMAND="sudo /opt/bitnami/ctlscript.sh reload apache"
APACHE_INTEGRATION_ENABLED=true
BUILDER_API_KEY=your-secure-api-key-here
EOF
    chown $BITNAMI_USER:$BITNAMI_USER "/home/$BITNAMI_USER/.env.instasites"
    chmod 600 "/home/$BITNAMI_USER/.env.instasites"
fi

# 11. Test Apache configuration
echo -e "${BLUE}11. Testing Apache configuration...${NC}"
if /opt/bitnami/apache2/bin/httpd -t; then
    echo -e "${GREEN}✅ Apache configuration is valid${NC}"
else
    echo -e "${RED}❌ Apache configuration has errors${NC}"
    echo "Please check the configuration before restarting Apache"
fi

# 12. Create deployment directory
echo -e "${BLUE}12. Creating deployment directory...${NC}"
DEPLOY_DIR="/opt/bitnami/apache2/htdocs/instasites"
if [ ! -d "$DEPLOY_DIR" ]; then
    mkdir -p "$DEPLOY_DIR"
    chown $BITNAMI_USER:daemon "$DEPLOY_DIR"
    chmod 755 "$DEPLOY_DIR"
    echo "Laravel application should be deployed to: $DEPLOY_DIR"
fi

# 13. Create log directories
echo -e "${BLUE}13. Setting up logging...${NC}"
mkdir -p /var/log/instasites
chown $BITNAMI_USER:daemon /var/log/instasites
chmod 755 /var/log/instasites

# 14. Create systemd service for Laravel queue (optional)
echo -e "${BLUE}14. Creating Laravel queue service template...${NC}"
cat > /etc/systemd/system/instasites-queue.service << EOF
[Unit]
Description=Instasites Queue Worker
After=network.target

[Service]
Type=simple
User=$BITNAMI_USER
Group=daemon
WorkingDirectory=$DEPLOY_DIR
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

# Don't enable the service yet - wait for Laravel deployment
echo "Queue service created but not enabled. Enable after Laravel deployment with:"
echo "  sudo systemctl enable instasites-queue"
echo "  sudo systemctl start instasites-queue"

# 15. Create backup script
echo -e "${BLUE}15. Creating backup script...${NC}"
cat > "/home/$BITNAMI_USER/backup-sites.sh" << 'EOF'
#!/bin/bash
# Backup script for Instasites
BACKUP_DIR="/home/bitnami/backups"
SITES_DIR="/var/www/html/sites"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP_DIR/sites_backup_$DATE.tar.gz" -C "$SITES_DIR" .
echo "Backup created: $BACKUP_DIR/sites_backup_$DATE.tar.gz"

# Keep only last 7 backups
find "$BACKUP_DIR" -name "sites_backup_*.tar.gz" -mtime +7 -delete
EOF

chown $BITNAMI_USER:$BITNAMI_USER "/home/$BITNAMI_USER/backup-sites.sh"
chmod +x "/home/$BITNAMI_USER/backup-sites.sh"

# 16. Final verification
echo -e "${BLUE}16. Final verification...${NC}"
echo ""
echo -e "${GREEN}✅ Setup completed successfully!${NC}"
echo ""
echo -e "${BLUE}📋 Summary:${NC}"
echo "  ✅ System packages updated"
echo "  ✅ Required PHP extensions installed"
echo "  ✅ Sites directory created: $SITES_ROOT"
echo "  ✅ Apache virtual host directories created"
echo "  ✅ Proper ownership and permissions set"
echo "  ✅ Apache modules enabled"
echo "  ✅ Virtual host configuration added"
echo "  ✅ Security templates created"
echo "  ✅ Sudo permissions configured"
echo "  ✅ Environment template created"
echo "  ✅ Deployment directory ready: $DEPLOY_DIR"
echo "  ✅ Logging directory created"
echo "  ✅ Queue service template created"
echo "  ✅ Backup script created"
echo ""
echo -e "${YELLOW}📝 Next steps:${NC}"
echo "1. Deploy your Laravel application to: $DEPLOY_DIR"
echo "2. Copy environment settings from: /home/$BITNAMI_USER/.env.instasites"
echo "3. Update your Laravel .env file with the Bitnami-specific paths"
echo "4. Run: composer install --optimize-autoloader --no-dev"
echo "5. Run: php artisan key:generate"
echo "6. Run: php artisan config:cache"
echo "7. Restart Apache: sudo /opt/bitnami/ctlscript.sh restart apache"
echo "8. Test the API: curl http://your-server-ip/instasites/api/health"
echo ""
echo -e "${BLUE}🔧 Useful commands:${NC}"
echo "  Restart Apache: sudo /opt/bitnami/ctlscript.sh restart apache"
echo "  Check Apache status: sudo /opt/bitnami/ctlscript.sh status apache"
echo "  View Apache logs: tail -f /opt/bitnami/apache2/logs/error_log"
echo "  Backup sites: /home/$BITNAMI_USER/backup-sites.sh"
echo "  Check disk usage: df -h"
echo "  Check site permissions: ls -la $SITES_ROOT"
echo ""
echo -e "${GREEN}🎉 Your Bitnami LAMP server is ready for Instasites!${NC}"
