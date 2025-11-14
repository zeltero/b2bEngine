#!/bin/bash
# Quick setup script for B2B Engine Docker deployment

set -e

echo "================================================"
echo "B2B Engine Docker Setup Script"
echo "================================================"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    echo "Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    echo "Visit: https://docs.docker.com/compose/install/"
    exit 1
fi

echo "✅ Docker is installed: $(docker --version)"
echo "✅ Docker Compose is installed: $(docker compose version)"
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file from template..."
    cp .env.example .env
    echo "✅ .env file created"
    echo ""
    echo "⚠️  IMPORTANT: Please edit .env file and update the following:"
    echo "   - MYSQL_ROOT_PASSWORD"
    echo "   - MYSQL_PASSWORD"
    echo "   - MAGENTO_ADMIN_PASSWORD"
    echo "   - MAGENTO_ADMIN_EMAIL"
    echo "   - MAGENTO_BASE_URL"
    echo ""
    read -p "Press Enter after you've updated .env file..."
else
    echo "✅ .env file already exists"
fi

echo ""

# Generate HTTP Basic Auth password
echo "🔐 Setting up HTTP Basic Authentication..."
echo ""
echo "Enter username for HTTP Basic Auth (default: b2badmin):"
read -r auth_user
auth_user=${auth_user:-b2badmin}

echo "Enter password for HTTP Basic Auth:"
read -s auth_pass

if [ -z "$auth_pass" ]; then
    echo "❌ Password cannot be empty"
    exit 1
fi

echo ""
echo "Generating .htpasswd file..."

if command -v openssl &> /dev/null; then
    # Use openssl to generate password
    echo "$auth_user:$(openssl passwd -apr1 $auth_pass)" > docker/nginx/.htpasswd
    echo "✅ .htpasswd file created using openssl"
elif command -v htpasswd &> /dev/null; then
    # Use htpasswd if available
    htpasswd -cb docker/nginx/.htpasswd "$auth_user" "$auth_pass"
    echo "✅ .htpasswd file created using htpasswd"
else
    echo "❌ Neither openssl nor htpasswd found. Please install one of them."
    exit 1
fi

echo ""
echo "🏗️  Building Docker images..."
docker compose build

echo ""
echo "🚀 Starting Docker containers..."
docker compose up -d

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 10

# Check if containers are running
if docker compose ps | grep -q "Up"; then
    echo "✅ All containers are running"
else
    echo "❌ Some containers failed to start. Check logs with: docker compose logs"
    exit 1
fi

echo ""
echo "================================================"
echo "✅ Docker setup complete!"
echo "================================================"
echo ""
echo "Next steps:"
echo ""
echo "1. Install Magento by running:"
echo "   docker compose exec php-fpm bash"
echo "   Then follow the installation commands in README_DOCKER.md"
echo ""
echo "2. Access your site:"
echo "   Frontend: http://localhost"
echo "   Admin: http://localhost/admin"
echo ""
echo "3. Login credentials:"
echo "   HTTP Auth: $auth_user / [your password]"
echo "   Admin: Check your .env file for MAGENTO_ADMIN_USER and MAGENTO_ADMIN_PASSWORD"
echo ""
echo "For detailed instructions, see README_DOCKER.md"
echo ""
echo "View logs: docker compose logs -f"
echo "Stop containers: docker compose down"
echo ""
