# Docker Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Production VPS                            │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │                    Docker Network (b2b-network)             │ │
│  │                                                              │ │
│  │  ┌──────────────┐                                          │ │
│  │  │   Nginx      │  Port 80/443                             │ │
│  │  │   (Alpine)   │  ← HTTP Basic Auth (Password Protection) │ │
│  │  │              │  ← SSL/HTTPS Ready                       │ │
│  │  └──────┬───────┘                                          │ │
│  │         │                                                    │ │
│  │         ├─ /health (no auth)                               │ │
│  │         ├─ /* (with auth) → PHP-FPM                        │ │
│  │         │                                                    │ │
│  │  ┌──────▼───────┐                                          │ │
│  │  │   PHP-FPM    │  Port 9000                               │ │
│  │  │   (PHP 8.3)  │  ← Magento B2B Engine                    │ │
│  │  │              │  ← Zeltero_B2B Module                    │ │
│  │  └──────┬───────┘                                          │ │
│  │         │                                                    │ │
│  │    ┌────┼────────┬──────────┐                              │ │
│  │    │    │        │          │                              │ │
│  │  ┌─▼────▼─┐  ┌──▼─────┐  ┌─▼────────┐                    │ │
│  │  │ MySQL  │  │ Elastic│  │  Redis   │                     │ │
│  │  │ 8.0    │  │search  │  │  7       │                     │ │
│  │  │        │  │ 8.11   │  │          │                     │ │
│  │  └────────┘  └────────┘  └──────────┘                     │ │
│  │  Port 3306   Port 9200   Port 6379                         │ │
│  │  (internal)  (internal)   (internal)                       │ │
│  │                                                              │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                   │
│  Persistent Volumes:                                             │
│  ├─ mysql-data                                                   │
│  ├─ elasticsearch-data                                           │
│  └─ redis-data                                                   │
│                                                                   │
└───────────────────────────────────────────────────────────────┘
```

## Component Details

### Nginx (nginx:alpine)
- **Role:** Web server and reverse proxy
- **Ports:** 80 (HTTP), 443 (HTTPS)
- **Features:**
  - HTTP Basic Authentication on all routes
  - Security headers (X-Frame-Options, X-Content-Type-Options, etc.)
  - Static content caching
  - Health check endpoint (/health) without auth
  - SSL/TLS ready
- **Config:** `docker/nginx/default.conf`
- **Auth:** `docker/nginx/.htpasswd`

### PHP-FPM (Custom - PHP 8.3)
- **Role:** PHP processor for Magento
- **Port:** 9000 (internal)
- **Extensions:** bcmath, ctype, curl, dom, gd, intl, mbstring, mysqli, pdo_mysql, soap, sodium, xsl, zip, redis, opcache
- **Memory:** 4GB
- **Features:**
  - OPcache enabled for production
  - Redis session storage
  - Optimized for Magento 2
- **Config:** `docker/php/php.ini`
- **Build:** `Dockerfile`

### MySQL (mysql:8.0)
- **Role:** Database server
- **Port:** 3306 (internal only)
- **Storage:** Persistent volume `mysql-data`
- **Optimizations:**
  - InnoDB buffer pool: 1GB
  - UTF8MB4 character set
  - Optimized for Magento workload
- **Config:** `docker/mysql/my.cnf`

### Elasticsearch (8.11.0)
- **Role:** Search engine and catalog indexing
- **Port:** 9200 (internal only)
- **Storage:** Persistent volume `elasticsearch-data`
- **Memory:** 512MB heap
- **Mode:** Single-node cluster
- **Security:** Disabled for internal use

### Redis (redis:7-alpine)
- **Role:** Cache and session storage
- **Port:** 6379 (internal only)
- **Storage:** Persistent volume `redis-data`
- **Persistence:** AOF (Append Only File) enabled
- **Databases:**
  - DB 0: Sessions
  - DB 1: Cache

## Network Flow

```
Internet → Port 80/443 → Nginx (HTTP Auth) → PHP-FPM → [MySQL, Elasticsearch, Redis]
                ↓
         Health Check (/health)
         No Auth Required
```

## Resource Limits (Production)

| Service       | CPU Limit | CPU Reserved | Memory Limit | Memory Reserved |
|---------------|-----------|--------------|--------------|-----------------|
| PHP-FPM       | 2 cores   | 1 core       | 4GB          | 2GB             |
| MySQL         | 1 core    | 0.5 cores    | 2GB          | 1GB             |
| Elasticsearch | 1 core    | 0.5 cores    | 1GB          | 512MB           |
| Redis         | 0.5 cores | 0.25 cores   | 512MB        | 256MB           |
| **Total**     | **4.5**   | **2.25**     | **7.5GB**    | **3.75GB**      |

**Recommended VPS:** 8 CPU cores, 16GB RAM, 100GB SSD

## Security Layers

```
┌─────────────────────────────────────┐
│ 1. Firewall (UFW/iptables)         │
│    Only 22, 80, 443 open            │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│ 2. SSL/TLS (HTTPS)                  │
│    Let's Encrypt certificates       │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│ 3. HTTP Basic Authentication        │
│    Username + Password required     │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│ 4. Magento Admin Authentication     │
│    Separate admin credentials       │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│ 5. Magento 2FA (optional)           │
│    Two-factor authentication        │
└─────────────────────────────────────┘
```

## Data Persistence

All critical data is stored in named Docker volumes:

```
/var/lib/docker/volumes/
├── b2bengine_mysql-data/
│   └── Database tables and data
├── b2bengine_elasticsearch-data/
│   └── Search indexes
└── b2bengine_redis-data/
    └── Cache and session data
```

Application data (media files, generated files):
```
/var/www/html/
├── pub/media/          (Product images, uploads)
├── pub/static/         (Generated static content)
├── var/                (Logs, cache)
├── generated/          (Generated code)
└── app/etc/env.php     (Configuration)
```

## Backup Strategy

1. **Database:** Daily backup via cron
   ```bash
   make backup-db
   ```

2. **Media Files:** Weekly backup
   ```bash
   tar -czf media-backup.tar.gz pub/media/
   ```

3. **Configuration:** On change
   ```bash
   cp app/etc/env.php ~/backups/
   ```

## Monitoring Points

- **Health Check:** `http://your-domain.com/health`
- **Container Status:** `docker compose ps`
- **Logs:** `docker compose logs -f`
- **Resources:** `docker stats`
- **Elasticsearch:** `curl localhost:9200/_cluster/health`
- **MySQL:** `docker compose exec mysql mysqladmin ping`
- **Redis:** `docker compose exec redis redis-cli ping`

## Scaling Considerations

For high-traffic scenarios, consider:

1. **Horizontal Scaling:**
   - Multiple PHP-FPM containers with load balancer
   - Read replicas for MySQL
   - Elasticsearch cluster

2. **Vertical Scaling:**
   - Increase resource limits in docker-compose.prod.yml
   - More CPU/RAM for VPS

3. **Caching:**
   - Add Varnish container in front of Nginx
   - Enable Magento Full Page Cache
   - CDN for static assets

4. **Separation:**
   - Dedicated database server
   - Dedicated Elasticsearch server
   - Separate Redis for cache and sessions
