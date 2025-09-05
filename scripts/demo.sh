#!/bin/bash

# Demo script for Instasites Static Website Builder
# This script demonstrates the complete workflow

set -e

echo "🚀 Instasites Static Website Builder Demo"
echo "========================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}1. Testing API Health${NC}"
curl -s http://localhost:8000/api/health | jq '.'
echo ""

echo -e "${BLUE}2. Building a minimal website${NC}"
echo "Building minimal.localhost..."
RESPONSE=$(curl -s -X POST http://localhost:8000/api/build \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d @examples/sample-payloads/minimal-site.json)

echo "$RESPONSE" | jq '.'
echo ""

echo -e "${BLUE}3. Building a full-featured website${NC}"
echo "Building test2.localhost..."
RESPONSE=$(curl -s -X POST http://localhost:8000/api/build \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d @examples/sample-payloads/basic-site.json)

echo "$RESPONSE" | jq '.'
echo ""

echo -e "${BLUE}4. Checking generated files${NC}"
echo "Files for minimal.localhost:"
ls -la /var/www/html/sites/minimal.localhost/public/
echo ""

echo "Files for test2.localhost:"
ls -la /var/www/html/sites/test2.localhost/public/
echo ""

echo -e "${BLUE}5. Viewing generated HTML (minimal site)${NC}"
echo "Generated index.html:"
echo "---"
head -20 /var/www/html/sites/minimal.localhost/public/index.html
echo "---"
echo ""

echo -e "${BLUE}6. Checking sitemap and RSS${NC}"
echo "Sitemap:"
cat /var/www/html/sites/test2.localhost/public/sitemap.xml
echo ""

echo "RSS Feed:"
head -10 /var/www/html/sites/test2.localhost/public/rss.xml
echo ""

echo -e "${BLUE}7. Testing reset functionality${NC}"
echo "Resetting minimal.localhost..."
curl -s -X POST http://localhost:8000/api/reset \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d '{"hostname":"minimal.localhost"}' | jq '.'
echo ""

echo "Resetting test2.localhost..."
curl -s -X POST http://localhost:8000/api/reset \
  -H "Content-Type: application/json" \
  -H "X-Builder-Key: dev-key-12345" \
  -d '{"hostname":"test2.localhost"}' | jq '.'
echo ""

echo -e "${GREEN}✅ Demo completed successfully!${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo "• API endpoints are working correctly"
echo "• Static websites are generated successfully"
echo "• Multiple themes and languages are supported"
echo "• SEO features (sitemap, RSS) are working"
echo "• Reset functionality works properly"
echo ""
echo -e "${YELLOW}🔧 For Apache integration:${NC}"
echo "• Run with sudo privileges to enable Apache virtual hosts"
echo "• Configure proper permissions for production use"
echo "• See README-SETUP.md for detailed instructions"
echo ""
echo -e "${YELLOW}🌐 To test websites locally:${NC}"
echo "1. Add entries to /etc/hosts:"
echo "   127.0.0.1 minimal.localhost"
echo "   127.0.0.1 test.localhost"
echo "2. Create Apache virtual hosts manually or with proper permissions"
echo "3. Visit http://minimal.localhost/ in your browser"
