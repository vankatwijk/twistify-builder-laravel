#!/bin/bash

# Reset Site Script
# Usage: ./scripts/reset-site.sh <site-name>
# Example: ./scripts/reset-site.sh test

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if site name is provided
if [ -z "$1" ]; then
    echo -e "${RED}❌ Error: Site name is required${NC}"
    echo ""
    echo "Usage: $0 <site-name>"
    echo ""
    echo "Examples:"
    echo "  $0 test      # Resets test.localhost"
    echo "  $0 minimal   # Resets minimal.localhost"
    echo "  $0 mysite    # Resets mysite.localhost"
    echo ""
    exit 1
fi

SITE_NAME="$1"
HOSTNAME="${SITE_NAME}.localhost"

# Check if Laravel server is running
if ! curl -s http://localhost:8000/api/health > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Laravel server is not running${NC}"
    echo "Please start the server with: php artisan serve"
    exit 1
fi

echo -e "${BLUE}🗑️  Resetting website: ${HOSTNAME}${NC}"
echo ""

# Check if site exists before resetting
SITE_DIR="/var/www/html/sites/${HOSTNAME}"
if [ ! -d "$SITE_DIR" ]; then
    echo -e "${YELLOW}⚠️  Warning: Site directory does not exist: ${SITE_DIR}${NC}"
    echo "The site may have already been deleted or never existed."
    echo ""
fi

# Show what will be deleted
echo -e "${BLUE}📁 Current site files:${NC}"
if [ -d "$SITE_DIR" ]; then
    echo "Directory: $SITE_DIR"
    if [ -d "$SITE_DIR/public" ]; then
        echo "Files:"
        ls -la "$SITE_DIR/public/" | head -10
        if [ $(ls -1 "$SITE_DIR/public/" | wc -l) -gt 10 ]; then
            echo "  ... and more files"
        fi
    fi
else
    echo "  No site directory found"
fi

echo ""
echo -e "${BLUE}🔧 Apache virtual host status:${NC}"
php artisan apache:manage list | grep -i "$HOSTNAME" || echo "  No Apache virtual host found for $HOSTNAME"

echo ""
read -p "Are you sure you want to delete $HOSTNAME? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}❌ Reset cancelled${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}📡 Sending reset request...${NC}"

# Make the API request
RESPONSE=$(curl -s -X POST http://localhost:8000/api/reset \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d "{\"hostname\":\"${HOSTNAME}\"}")

# Check if the request was successful
if echo "$RESPONSE" | grep -q '"ok":true'; then
    echo -e "${GREEN}✅ Reset successful!${NC}"
    echo ""
    
    # Pretty print the response if jq is available
    if command -v jq >/dev/null 2>&1; then
        echo "$RESPONSE" | jq '.'
    else
        echo "$RESPONSE"
    fi
    
    echo ""
    echo -e "${BLUE}🧹 Cleanup completed:${NC}"
    
    # Verify deletion
    if [ ! -d "$SITE_DIR" ]; then
        echo "✅ Site directory removed: $SITE_DIR"
    else
        echo "⚠️  Site directory still exists: $SITE_DIR"
    fi
    
    # Check Apache virtual host status
    echo ""
    echo -e "${BLUE}🔧 Apache virtual host status:${NC}"
    if php artisan apache:manage list | grep -i "$HOSTNAME" > /dev/null; then
        echo "⚠️  Apache virtual host still exists for $HOSTNAME"
        echo "You may need to manually disable it:"
        echo "  php artisan apache:manage disable $HOSTNAME"
    else
        echo "✅ Apache virtual host removed for $HOSTNAME"
    fi
    
    echo ""
    echo -e "${BLUE}📝 Additional cleanup (if needed):${NC}"
    echo "1. Remove from /etc/hosts: 127.0.0.1 $HOSTNAME"
    echo "2. Clear browser cache for http://$HOSTNAME/"
    
else
    echo -e "${RED}❌ Reset failed!${NC}"
    echo ""
    if command -v jq >/dev/null 2>&1; then
        echo "$RESPONSE" | jq '.'
    else
        echo "$RESPONSE"
    fi
    
    # Show available sites for reference
    echo ""
    echo -e "${BLUE}📋 Available sites to reset:${NC}"
    if [ -d "/var/www/html/sites" ]; then
        ls -1 /var/www/html/sites/ | grep "\.localhost$" | sed 's/\.localhost$//' | sed 's/^/  /' || echo "  No sites found"
    else
        echo "  Sites directory not found"
    fi
    
    exit 1
fi
