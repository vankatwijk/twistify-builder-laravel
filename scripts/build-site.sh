#!/bin/bash

# Build Site Script
# Usage: ./scripts/build-site.sh <site-name>
# Example: ./scripts/build-site.sh test
# Example: ./scripts/build-site.sh mycompany

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
    echo "  $0 test        # Creates test.localhost"
    echo "  $0 minimal     # Creates minimal.localhost"
    echo "  $0 mycompany   # Creates mycompany.localhost"
    echo ""
    exit 1
fi

SITE_NAME="$1"
HOSTNAME="${SITE_NAME}.localhost"

# Generate site title (capitalize first letter)
SITE_TITLE="$(echo ${SITE_NAME^} | sed 's/[_-]/ /g') Site"

# Check if Laravel server is running
if ! curl -s http://localhost:8000/api/health > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Laravel server is not running${NC}"
    echo "Please start the server with: php artisan serve"
    exit 1
fi

echo -e "${BLUE}🚀 Building website: ${SITE_NAME}${NC}"
echo -e "${BLUE}🌐 Hostname: ${HOSTNAME}${NC}"
echo -e "${BLUE}📝 Title: ${SITE_TITLE}${NC}"
echo ""

# Create JSON payload directly in the script
PAYLOAD=$(cat <<EOF
{
  "blueprint": {
    "site_name": "${SITE_TITLE}",
    "primary_domain": "${HOSTNAME}",
    "theme": {
      "name": "classic"
    }
  },
  "pages": [
    {
      "slug": "home",
      "title": "Welcome to ${SITE_TITLE}",
      "html": "<h1>Welcome to ${SITE_TITLE}</h1><p>This is the homepage of your new website. You can customize this content to match your needs.</p><p>Features:</p><ul><li>Fast static site generation</li><li>SEO optimized</li><li>Mobile responsive</li></ul>",
      "meta_title": "Welcome to ${SITE_TITLE}",
      "meta_description": "Welcome to ${SITE_TITLE} - a modern, fast, and SEO-optimized website."
    },
    {
      "slug": "about",
      "title": "About Us",
      "html": "<h1>About ${SITE_TITLE}</h1><p>Learn more about our company and what we do.</p><p>We are dedicated to providing excellent service and innovative solutions.</p><h2>Our Mission</h2><p>To deliver high-quality websites that help businesses grow and succeed online.</p>",
      "meta_title": "About Us - ${SITE_TITLE}",
      "meta_description": "Learn more about ${SITE_TITLE} and our mission to help businesses succeed online."
    }
  ]
}
EOF
)

echo -e "${BLUE}📡 Sending build request...${NC}"

# Make the API request
RESPONSE=$(curl -s -X POST http://localhost:8000/api/build \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d "$PAYLOAD")

# Check if the request was successful
if echo "$RESPONSE" | grep -q '"ok":true'; then
    echo -e "${GREEN}✅ Build successful!${NC}"
    echo ""

    # Pretty print the response if jq is available
    if command -v jq >/dev/null 2>&1; then
        echo "$RESPONSE" | jq '.'
    else
        echo "$RESPONSE"
    fi

    echo ""
    echo -e "${BLUE}📁 Generated files:${NC}"
    if [ -d "/var/www/html/sites/${HOSTNAME}/public" ]; then
        ls -la "/var/www/html/sites/${HOSTNAME}/public/"
    else
        echo "  Site directory not found (check SITES_ROOT configuration)"
    fi

    echo ""
    echo -e "${BLUE}🌐 Next steps:${NC}"
    echo "1. Add to /etc/hosts: 127.0.0.1 ${HOSTNAME}"
    echo "2. Create Apache virtual host (if not auto-created)"
    echo "3. Visit: http://${HOSTNAME}/"
    echo "   - Homepage: http://${HOSTNAME}/"
    echo "   - About page: http://${HOSTNAME}/about/"
    echo ""
else
    echo -e "${RED}❌ Build failed!${NC}"
    echo ""
    if command -v jq >/dev/null 2>&1; then
        echo "$RESPONSE" | jq '.'
    else
        echo "$RESPONSE"
    fi
    exit 1
fi
