# Docker Deployment Guide for B2B Engine

This guide explains how to deploy the B2B Engine Magento platform using Docker on your production VPS with password protection.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Password Protection](#password-protection)
- [Production Deployment](#production-deployment)
- [SSL/HTTPS Setup](#sslhttps-setup)
- [Maintenance](#maintenance)
- [Troubleshooting](#troubleshooting)

## Prerequisites

- Docker Engine 20.10 or newer
- Docker Compose 2.0 or newer
- At least 4GB RAM available
- At least 20GB disk space
- Domain name pointing to your VPS (for production)

### Installing Docker on Ubuntu/Debian

```bash
# Update system
sudo apt-get update
sudo apt-get upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt-get install docker-compose-plugin

# Add your user to docker group (optional, requires logout/login)
sudo usermod -aG docker $USER

# Verify installation
docker --version
docker compose version
```

## Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/zeltero/b2bEngine.git
cd b2bEngine
```

### 2. Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit the .env file with your settings
nano .env
```

**Important:** Change these values in `.env`:
- `MYSQL_ROOT_PASSWORD` - Strong root password for MySQL
- `MYSQL_PASSWORD` - Strong password for Magento database user
- `MAGENTO_ADMIN_PASSWORD` - Admin panel password
- `HTTP_AUTH_PASSWORD` - Password for HTTP basic authentication

### 3. Setup Password Protection

Generate a secure password for HTTP Basic Authentication:

```bash
# Method 1: Using openssl (recommended)
echo "b2badmin:$(openssl passwd -apr1)" > docker/nginx/.htpasswd
# Enter your desired password when prompted

# Method 2: Using htpasswd (if Apache utils installed)
htpasswd -c docker/nginx/.htpasswd b2badmin

# Method 3: Use online generator
# Visit: https://hostingcanada.org/htpasswd-generator/
# Copy the generated line to docker/nginx/.htpasswd
```

### 4. Build and Start Containers

```bash
# Build the Docker images
docker compose build

# Start containers in detached mode
docker compose up -d

# Check container status
docker compose ps
```

### 5. Install Magento

Wait for all services to be ready (about 1-2 minutes), then install Magento:

```bash
# Enter the PHP container
docker compose exec php-fpm bash

# Inside container, run Magento installation
php bin/magento setup:install \
  --base-url=http://your-domain.com \
  --db-host=mysql \
  --db-name=magento_b2b \
  --db-user=magento \
  --db-password=YOUR_DB_PASSWORD \
  --admin-firstname=Admin \
  --admin-lastname=User \
  --admin-email=admin@example.com \
  --admin-user=admin \
  --admin-password=YOUR_ADMIN_PASSWORD \
  --language=en_US \
  --currency=USD \
  --timezone=America/Chicago \
  --use-rewrites=1 \
  --search-engine=elasticsearch8 \
  --elasticsearch-host=elasticsearch \
  --elasticsearch-port=9200 \
  --elasticsearch-index-prefix=magento2 \
  --elasticsearch-timeout=15

# Enable Zeltero B2B module
php bin/magento module:enable Zeltero_B2B
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f en_US
php bin/magento indexer:reindex
php bin/magento cache:flush

# Set production mode
php bin/magento deploy:mode:set production

# Exit container
exit
```

### 6. Access Your Site

- **Frontend:** `http://your-domain.com`
- **Admin Panel:** `http://your-domain.com/admin`

You'll be prompted for HTTP Basic Auth credentials:
- Username: `b2badmin` (or what you configured)
- Password: Your configured password

Then login with Magento admin credentials.

## Configuration

### Environment Variables

Edit `.env` file to customize your deployment:

```env
# Run mode
MAGENTO_RUN_MODE=production

# Database
MYSQL_ROOT_PASSWORD=secure_root_pass
MYSQL_DATABASE=magento_b2b
MYSQL_USER=magento
MYSQL_PASSWORD=secure_db_pass

# Admin
MAGENTO_ADMIN_EMAIL=admin@yourdomain.com
MAGENTO_ADMIN_USER=admin
MAGENTO_ADMIN_PASSWORD=SecureAdminPass123!

# Base URL
MAGENTO_BASE_URL=https://yourdomain.com

# HTTP Auth
HTTP_AUTH_USER=b2badmin
HTTP_AUTH_PASSWORD=your_auth_password
```

### Nginx Configuration

The Nginx configuration includes:
- HTTP Basic Authentication on all routes
- Security headers (X-Frame-Options, X-Content-Type-Options, etc.)
- Optimized caching for static assets
- Health check endpoint at `/health` (no auth required)

To customize, edit: `docker/nginx/default.conf`

### PHP Configuration

PHP settings are optimized for Magento in: `docker/php/php.ini`

Key settings:
- Memory limit: 4G
- Max execution time: 18000s
- Upload max filesize: 64M
- Session saved in Redis

### MySQL Configuration

MySQL is optimized for Magento in: `docker/mysql/my.cnf`

Key settings:
- InnoDB buffer pool: 1G
- Max packet: 256M
- Character set: utf8mb4

## Password Protection

The deployment includes HTTP Basic Authentication for all routes except the health check endpoint.

### Managing Passwords

**Change Password:**

```bash
# Generate new password hash
echo "b2badmin:$(openssl passwd -apr1)" > docker/nginx/.htpasswd

# Restart nginx to apply
docker compose restart nginx
```

**Add Multiple Users:**

```bash
# Add additional users (don't use -c flag after first user)
echo "user2:$(openssl passwd -apr1)" >> docker/nginx/.htpasswd
docker compose restart nginx
```

**Disable Authentication for Specific Routes:**

Edit `docker/nginx/default.conf` and add:

```nginx
location /api {
    auth_basic off;  # Disable auth for API
    # ... rest of location config
}
```

## Production Deployment

For production use with resource limits and optimizations:

```bash
# Use production compose file
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Resource Limits

Production configuration includes:
- PHP-FPM: 2 CPU cores, 4GB RAM
- MySQL: 1 CPU core, 2GB RAM
- Elasticsearch: 1 CPU core, 1GB RAM
- Redis: 0.5 CPU cores, 512MB RAM

### Server Requirements

Recommended VPS specifications:
- **Minimum:** 4 CPU cores, 8GB RAM, 50GB SSD
- **Recommended:** 8 CPU cores, 16GB RAM, 100GB SSD

## SSL/HTTPS Setup

### Using Let's Encrypt (Recommended)

**1. Install Certbot:**

```bash
sudo apt-get install certbot python3-certbot-nginx
```

**2. Stop Docker Nginx temporarily:**

```bash
docker compose stop nginx
```

**3. Generate certificate:**

```bash
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com
```

**4. Create SSL nginx config:**

Create `docker/nginx/ssl.conf`:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Include all other location blocks from default.conf
    # ... (copy content from default.conf)
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

**5. Update docker-compose.yml:**

Add SSL certificate volume to nginx service:

```yaml
nginx:
  volumes:
    - /etc/letsencrypt:/etc/letsencrypt:ro
    - ./docker/nginx/ssl.conf:/etc/nginx/conf.d/ssl.conf:ro
```

**6. Restart containers:**

```bash
docker compose up -d
```

**7. Update Magento base URLs:**

```bash
docker compose exec php-fpm bash
php bin/magento setup:store-config:set --base-url="https://yourdomain.com"
php bin/magento setup:store-config:set --base-url-secure="https://yourdomain.com"
php bin/magento config:set web/secure/use_in_frontend 1
php bin/magento config:set web/secure/use_in_adminhtml 1
php bin/magento cache:flush
exit
```

**8. Setup auto-renewal:**

```bash
# Test renewal
sudo certbot renew --dry-run

# Add to crontab
echo "0 3 * * * certbot renew --quiet && docker compose restart nginx" | sudo crontab -
```

## Maintenance

### Starting/Stopping Services

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# Stop and remove volumes (WARNING: deletes data!)
docker compose down -v

# Restart specific service
docker compose restart nginx
docker compose restart php-fpm
```

### Viewing Logs

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f nginx
docker compose logs -f php-fpm
docker compose logs -f mysql

# Last 100 lines
docker compose logs --tail=100 php-fpm
```

### Accessing Containers

```bash
# PHP-FPM container (for running Magento commands)
docker compose exec php-fpm bash

# MySQL container
docker compose exec mysql mysql -u root -p

# Nginx container
docker compose exec nginx sh
```

### Database Backup

```bash
# Backup
docker compose exec mysql mysqldump -u root -p magento_b2b > backup-$(date +%Y%m%d).sql

# Restore
docker compose exec -T mysql mysql -u root -p magento_b2b < backup-20240101.sql
```

### Magento Commands

```bash
# Always run from inside PHP container
docker compose exec php-fpm bash

# Common commands
php bin/magento cache:flush
php bin/magento cache:clean
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento indexer:reindex
php bin/magento deploy:mode:show
php bin/magento deploy:mode:set production
```

### Updating the Application

```bash
# Pull latest changes
git pull origin main

# Rebuild containers
docker compose build --no-cache

# Restart with new image
docker compose up -d

# Run upgrades
docker compose exec php-fpm bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
exit
```

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker compose logs php-fpm

# Check if ports are already in use
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :443

# Remove old containers and try again
docker compose down
docker compose up -d
```

### Permission Issues

```bash
# Fix file permissions
docker compose exec php-fpm bash
find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
exit
```

### Database Connection Issues

```bash
# Check MySQL is running
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Test connection
docker compose exec mysql mysql -u magento -p magento_b2b
```

### Elasticsearch Issues

```bash
# Check Elasticsearch status
curl http://localhost:9200/_cluster/health?pretty

# Inside container:
docker compose exec php-fpm bash
curl http://elasticsearch:9200/_cluster/health?pretty

# Reindex if needed
php bin/magento indexer:reindex
```

### Static Content Not Loading

```bash
docker compose exec php-fpm bash
php bin/magento setup:static-content:deploy -f en_US
php bin/magento cache:flush
exit
```

### Password Not Working

```bash
# Regenerate .htpasswd
echo "b2badmin:$(openssl passwd -apr1)" > docker/nginx/.htpasswd

# Restart nginx
docker compose restart nginx

# Test access
curl -u b2badmin:yourpassword http://localhost/health
```

### Reset Admin Password

```bash
docker compose exec php-fpm bash
php bin/magento admin:user:create \
  --admin-user=newadmin \
  --admin-password=NewPass123! \
  --admin-email=admin@example.com \
  --admin-firstname=Admin \
  --admin-lastname=User
exit
```

## Performance Optimization

### Enable Caching

```bash
docker compose exec php-fpm bash
php bin/magento cache:enable
php bin/magento cache:flush
exit
```

### Production Mode

```bash
docker compose exec php-fpm bash
php bin/magento deploy:mode:set production
exit
```

### Increase Resources

Edit `docker-compose.prod.yml` to adjust resource limits based on your VPS capacity.

## Security Checklist

- [x] HTTP Basic Authentication enabled
- [ ] SSL/HTTPS configured with Let's Encrypt
- [ ] Changed default passwords in `.env`
- [ ] Updated `.htpasswd` with strong password
- [ ] Firewall configured (UFW/iptables)
- [ ] Regular backups scheduled
- [ ] Magento security patches applied
- [ ] Admin URL customized (Magento setting)
- [ ] Two-factor authentication enabled (Magento setting)

## Support

- **Magento Documentation:** https://experienceleague.adobe.com/docs/commerce.html
- **Docker Documentation:** https://docs.docker.com/
- **Repository Issues:** https://github.com/zeltero/b2bEngine/issues

## License

This project is licensed under the Open Software License (OSL 3.0).
