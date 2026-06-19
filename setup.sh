#!/bin/bash
# =============================================================================
# Qat ERP - Local Setup Script
# =============================================================================
# This script sets up the Qat ERP project for local development.
# Run it on a fresh clone of the repository.
# =============================================================================

set -e

echo "=================================================="
echo "  Qat ERP - Local Development Setup"
echo "=================================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check PHP version
echo -e "${YELLOW}[1/7] Checking PHP version...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.3+${NC}"
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_VERSION_ID;")
if [ "$PHP_VERSION" -lt 80300 ]; then
    echo -e "${RED}PHP 8.3+ required. Current version: $(php -v | head -1)${NC}"
    exit 1
fi
echo -e "${GREEN}PHP $(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;") found.${NC}"

# Check Composer
echo -e "${YELLOW}[2/7] Checking Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed.${NC}"
    exit 1
fi
echo -e "${GREEN}Composer $(composer --version | awk '{print $3}') found.${NC}"

# Check Node.js
echo -e "${YELLOW}[3/7] Checking Node.js...${NC}"
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js is not installed. Please install Node.js 18+${NC}"
    exit 1
fi
NODE_VERSION=$(node -v | sed 's/v//' | awk -F. '{print $1}')
if [ "$NODE_VERSION" -lt 18 ]; then
    echo -e "${RED}Node.js 18+ required. Current version: $(node -v)${NC}"
    exit 1
fi
echo -e "${GREEN}Node.js $(node -v) found.${NC}"

# Install Composer dependencies
echo -e "${YELLOW}[4/7] Installing Composer dependencies...${NC}"
composer install --optimize-autoloader --no-interaction
echo -e "${GREEN}Composer dependencies installed.${NC}"

# Setup environment
echo -e "${YELLOW}[5/7] Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
    echo -e "${GREEN}.env file created.${NC}"
else
    echo -e "${GREEN}.env file already exists, skipping.${NC}"
fi

# Install NPM dependencies and build
echo -e "${YELLOW}[6/7] Building frontend assets...${NC}"
if [ -f package.json ]; then
    npm install --no-audit --no-fund
    npm run build
    echo -e "${GREEN}Frontend assets built.${NC}"
else
    echo -e "${YELLOW}No package.json found, skipping frontend build.${NC}"
fi

# Setup database
echo -e "${YELLOW}[7/7] Setting up database...${NC}"
if grep -q "DB_CONNECTION=mysql" .env 2>/dev/null; then
    echo -e "${YELLOW}MySQL configured. Running migrations...${NC}"
    php artisan migrate --graceful --no-interaction
    php artisan db:seed --no-interaction
elif grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null; then
    echo -e "${YELLOW}SQLite configured. Creating database...${NC}"
    touch database/database.sqlite
    php artisan migrate --graceful --no-interaction
    php artisan db:seed --no-interaction
fi
echo -e "${GREEN}Database setup complete.${NC}"

echo ""
echo -e "${GREEN}=================================================="
echo "  Setup Complete!"
echo "=================================================="
echo ""
echo "  Next steps:"
echo "    1. Configure database in .env if needed"
echo "    2. Run: php artisan serve"
echo "    3. Visit: http://localhost:8000"
echo "    4. Login: admin / password"
echo ""
echo "  For Android builds:"
echo "    php artisan native:install android"
echo "    php artisan native:run android"
echo ""
echo -e "${NC}"
