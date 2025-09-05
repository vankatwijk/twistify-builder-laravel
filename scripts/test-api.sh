#!/bin/bash

# Test script for Instasites API
# This script tests the build and reset endpoints with sample data

set -e

# Configuration
API_BASE_URL="http://localhost:8000/api"
API_KEY="dev-key-12345"
EXAMPLES_DIR="$(dirname "$0")/../examples/sample-payloads"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Test health endpoint
test_health() {
    log_info "Testing health endpoint..."
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" "$API_BASE_URL/health")
    http_code=$(echo "$response" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    body=$(echo "$response" | sed -e 's/HTTPSTATUS:.*//g')
    
    if [ "$http_code" -eq 200 ]; then
        log_success "Health check passed"
        echo "Response: $body"
    else
        log_error "Health check failed (HTTP $http_code)"
        echo "Response: $body"
        return 1
    fi
}

# Test build endpoint
test_build() {
    local payload_file="$1"
    local site_name="$2"
    
    if [ ! -f "$payload_file" ]; then
        log_error "Payload file not found: $payload_file"
        return 1
    fi
    
    log_info "Testing build endpoint with $site_name..."
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" \
        -X POST \
        -H "Content-Type: application/json" \
        -H "X-Builder-Key: $API_KEY" \
        -d @"$payload_file" \
        "$API_BASE_URL/build")
    
    http_code=$(echo "$response" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    body=$(echo "$response" | sed -e 's/HTTPSTATUS:.*//g')
    
    if [ "$http_code" -eq 200 ]; then
        log_success "Build successful for $site_name"
        echo "Response: $body"
        
        # Extract hostname from response
        hostname=$(echo "$body" | grep -o '"hostname":"[^"]*"' | cut -d'"' -f4)
        if [ -n "$hostname" ]; then
            log_info "Site available at: http://$hostname/"
            
            # Check if Apache virtual host was created
            apache_created=$(echo "$body" | grep -o '"apache_vhost_created":[^,}]*' | cut -d':' -f2)
            if [ "$apache_created" = "true" ]; then
                log_success "Apache virtual host created successfully"
            else
                log_warning "Apache virtual host creation failed or disabled"
            fi
        fi
    else
        log_error "Build failed for $site_name (HTTP $http_code)"
        echo "Response: $body"
        return 1
    fi
}

# Test reset endpoint
test_reset() {
    local hostname="$1"
    
    log_info "Testing reset endpoint for $hostname..."
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" \
        -X POST \
        -H "Content-Type: application/json" \
        -H "X-Builder-Key: $API_KEY" \
        -d "{\"hostname\":\"$hostname\"}" \
        "$API_BASE_URL/reset")
    
    http_code=$(echo "$response" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    body=$(echo "$response" | sed -e 's/HTTPSTATUS:.*//g')
    
    if [ "$http_code" -eq 200 ]; then
        log_success "Reset successful for $hostname"
        echo "Response: $body"
    else
        log_error "Reset failed for $hostname (HTTP $http_code)"
        echo "Response: $body"
        return 1
    fi
}

# Main test function
run_tests() {
    echo "🧪 Starting Instasites API Tests"
    echo "================================"
    echo ""
    
    # Test 1: Health check
    test_health
    echo ""
    
    # Test 2: Build minimal site
    if [ -f "$EXAMPLES_DIR/minimal-site.json" ]; then
        test_build "$EXAMPLES_DIR/minimal-site.json" "minimal site"
        echo ""
    else
        log_warning "Minimal site payload not found, skipping test"
    fi
    
    # Test 3: Build basic site
    if [ -f "$EXAMPLES_DIR/basic-site.json" ]; then
        test_build "$EXAMPLES_DIR/basic-site.json" "basic site"
        echo ""
    else
        log_warning "Basic site payload not found, skipping test"
    fi
    
    # Test 4: Reset sites
    log_info "Testing reset functionality..."
    test_reset "minimal.localhost"
    test_reset "test.localhost"
    echo ""
    
    log_success "All tests completed!"
    echo ""
    echo "📋 Next steps:"
    echo "   1. Check generated sites in your sites directory"
    echo "   2. Visit http://test.localhost/ or http://minimal.localhost/"
    echo "   3. Check Apache virtual hosts: php artisan apache:manage list"
}

# Check if Laravel server is running
check_server() {
    log_info "Checking if Laravel server is running..."
    
    if curl -s "$API_BASE_URL/health" > /dev/null 2>&1; then
        log_success "Laravel server is running"
        return 0
    else
        log_error "Laravel server is not running or not accessible"
        echo ""
        echo "Please start the Laravel server with:"
        echo "  php artisan serve"
        echo ""
        return 1
    fi
}

# Parse command line arguments
case "${1:-all}" in
    "health")
        test_health
        ;;
    "build")
        if [ -z "$2" ]; then
            log_error "Usage: $0 build <payload-file>"
            exit 1
        fi
        test_build "$2" "custom site"
        ;;
    "reset")
        if [ -z "$2" ]; then
            log_error "Usage: $0 reset <hostname>"
            exit 1
        fi
        test_reset "$2"
        ;;
    "all"|*)
        check_server && run_tests
        ;;
esac
