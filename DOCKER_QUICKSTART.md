# 🐳 Quick Docker Setup Guide

This is a **quick reference** for deploying B2B Engine with Docker. For complete documentation, see [README_DOCKER.md](README_DOCKER.md).

## 🚀 Quick Start (3 steps)

### 1. Install Docker
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt-get install docker-compose-plugin
```

### 2. Run Setup
```bash
./setup-docker.sh
```

### 3. Install Magento
```bash
docker compose exec php-fpm bash
# Then inside container:
php bin/magento setup:install --base-url=http://your-domain.com ...
# (Full command in README_DOCKER.md)
```

## 🔐 Password Protection

The site is protected with HTTP Basic Authentication. Default credentials:
- **Username:** `b2badmin`
- **Password:** Set during setup

To change password:
```bash
make update-password
# OR
echo "username:$(openssl passwd -apr1)" > docker/nginx/.htpasswd
docker compose restart nginx
```

## 📋 Common Commands

Use the included Makefile for easy management:

```bash
make help              # Show all available commands
make up                # Start containers
make down              # Stop containers
make logs              # View logs
make shell             # Open PHP container shell
make cache-flush       # Flush Magento cache
make backup-db         # Backup database
make prod-up           # Start in production mode
```

## 📁 Project Structure

```
b2bEngine/
├── docker/
│   ├── nginx/
│   │   ├── default.conf      # Nginx config with auth
│   │   └── .htpasswd          # Password file
│   ├── php/
│   │   └── php.ini            # PHP settings
│   ├── mysql/
│   │   └── my.cnf             # MySQL config
│   └── docker-entrypoint.sh   # Init script
├── Dockerfile                  # PHP-FPM image
├── docker-compose.yml          # Services config
├── docker-compose.prod.yml     # Production overrides
├── .env.example                # Environment template
├── setup-docker.sh             # Quick setup script
└── Makefile                    # Helper commands
```

## 🌐 Access

After setup:
- **Frontend:** http://your-domain.com
- **Admin:** http://your-domain.com/admin
- **Health Check:** http://your-domain.com/health (no auth)

## 🔒 SSL/HTTPS Setup

```bash
# Install certbot
sudo apt-get install certbot

# Stop nginx temporarily
docker compose stop nginx

# Get certificate
sudo certbot certonly --standalone -d your-domain.com

# Update docker-compose.yml to mount certificates
# Restart with SSL config
docker compose up -d
```

See [README_DOCKER.md](README_DOCKER.md) for detailed SSL setup.

## 🛠️ Services

| Service | Port | Description |
|---------|------|-------------|
| Nginx | 80, 443 | Web server with HTTP auth |
| PHP-FPM | 9000 | PHP processor |
| MySQL | 3306 | Database (internal) |
| Elasticsearch | 9200 | Search engine (internal) |
| Redis | 6379 | Cache & sessions (internal) |

## ⚠️ Important Security Steps

1. ✅ Change all default passwords in `.env`
2. ✅ Update HTTP auth password in `.htpasswd`
3. ✅ Setup SSL/HTTPS with Let's Encrypt
4. ✅ Configure firewall (UFW/iptables)
5. ✅ Enable Magento 2FA for admin
6. ✅ Setup regular backups

## 📖 Documentation

- **Full Docker Guide:** [README_DOCKER.md](README_DOCKER.md)
- **Deployment Checklist:** [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- **B2B Features:** [B2B_DOCUMENTATION.md](B2B_DOCUMENTATION.md)
- **Main README:** [README.md](README.md)

## 🆘 Troubleshooting

**Containers won't start:**
```bash
docker compose logs
docker compose down
docker compose up -d
```

**Permission issues:**
```bash
docker compose exec php-fpm bash
chmod -R 775 var generated pub/static pub/media app/etc
```

**Database connection failed:**
```bash
docker compose logs mysql
# Check .env file has correct database credentials
```

**Reset admin password:**
```bash
docker compose exec php-fpm bash
php bin/magento admin:user:create --admin-user=newadmin ...
```

## 🔄 Production Deployment

For production VPS with resource limits:

```bash
# Use production compose file
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Enable production mode
docker compose exec php-fpm bash
php bin/magento deploy:mode:set production
php bin/magento cache:enable
exit
```

## 📊 Monitoring

```bash
# Check service health
make health-check

# View real-time logs
make logs

# Check container stats
docker stats

# Test HTTP auth
curl -u b2badmin:password http://localhost/health
```

## 🔙 Backup & Restore

```bash
# Backup database
make backup-db

# Restore database
make restore-db FILE=backups/backup-20240101.sql

# Backup media files
tar -czf media-backup.tar.gz pub/media/

# Backup config
cp app/etc/env.php env.php.backup
```

## 📞 Support

- **Issues:** https://github.com/zeltero/b2bEngine/issues
- **Magento Docs:** https://experienceleague.adobe.com/docs/commerce.html
- **Docker Docs:** https://docs.docker.com/

---

**Made with ❤️ for B2B e-commerce**
