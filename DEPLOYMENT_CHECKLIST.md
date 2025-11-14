# Production Deployment Checklist

Use this checklist when deploying B2B Engine to production VPS.

## Pre-Deployment

- [ ] VPS meets minimum requirements (4 CPU, 8GB RAM, 50GB disk)
- [ ] Domain name configured and pointing to VPS IP
- [ ] SSH access to VPS configured
- [ ] Docker and Docker Compose installed
- [ ] Git installed on VPS

## Security Setup

- [ ] Created strong passwords for all services
- [ ] `.env` file configured with production values
- [ ] `MYSQL_ROOT_PASSWORD` changed from default
- [ ] `MYSQL_PASSWORD` changed from default
- [ ] `MAGENTO_ADMIN_PASSWORD` set to strong password
- [ ] HTTP Basic Auth configured in `docker/nginx/.htpasswd`
- [ ] HTTP Basic Auth password changed from default
- [ ] `.env` file added to `.gitignore` (already done)
- [ ] Firewall configured (UFW or iptables)
  - [ ] Port 22 (SSH) open
  - [ ] Port 80 (HTTP) open
  - [ ] Port 443 (HTTPS) open
  - [ ] All other ports blocked

## SSL/HTTPS Configuration

- [ ] Let's Encrypt SSL certificate obtained
- [ ] SSL certificate auto-renewal configured
- [ ] Nginx SSL configuration created
- [ ] HTTP to HTTPS redirect enabled
- [ ] Magento base URLs updated to use HTTPS

## Initial Deployment

- [ ] Repository cloned to VPS
- [ ] `.env` file created and configured
- [ ] HTTP Basic Auth password generated
- [ ] Docker images built successfully
- [ ] Containers started successfully
- [ ] All services healthy (nginx, php-fpm, mysql, elasticsearch, redis)
- [ ] Magento installed successfully
- [ ] B2B module enabled
- [ ] Admin user created
- [ ] Production mode enabled
- [ ] Static content deployed
- [ ] Cache cleared and warmed up
- [ ] Indexers run successfully

## Magento Configuration

- [ ] Store name configured
- [ ] Admin URL customized (security)
- [ ] Two-factor authentication enabled for admin
- [ ] Timezone configured
- [ ] Currency configured
- [ ] Tax settings configured
- [ ] Shipping methods configured
- [ ] Payment methods configured
- [ ] Email settings configured
- [ ] Customer groups created (wholesale tiers)
- [ ] B2B module settings configured

## Performance Optimization

- [ ] Production mode enabled
- [ ] All caches enabled
- [ ] Flat catalog enabled (if needed)
- [ ] Varnish configured (optional)
- [ ] Redis configured for cache and sessions
- [ ] Elasticsearch indexes optimized
- [ ] Image optimization enabled
- [ ] JavaScript/CSS minification enabled
- [ ] JavaScript bundling enabled (optional)

## Backup Configuration

- [ ] Database backup script configured
- [ ] Media files backup configured
- [ ] Automated backup schedule setup (cron)
- [ ] Backup storage location configured
- [ ] Backup retention policy set
- [ ] Test restoration from backup

## Monitoring Setup

- [ ] Health check endpoint configured
- [ ] Log rotation configured
- [ ] Error monitoring setup
- [ ] Uptime monitoring configured
- [ ] Disk space monitoring setup
- [ ] Email alerts configured
- [ ] Performance monitoring setup (optional)

## Testing

- [ ] Frontend loads correctly
- [ ] HTTPS working and redirecting from HTTP
- [ ] HTTP Basic Auth protecting all routes
- [ ] Admin panel accessible
- [ ] Admin login working
- [ ] Product catalog displays correctly
- [ ] Search functionality working
- [ ] Cart functionality working
- [ ] Checkout process working
- [ ] Company registration working
- [ ] Quick order functionality working
- [ ] Email sending working
- [ ] All integrations working (ERP, payment, shipping)

## Documentation

- [ ] Admin credentials documented securely
- [ ] Database credentials documented securely
- [ ] SSH access documented
- [ ] Backup procedures documented
- [ ] Recovery procedures documented
- [ ] Deployment procedures documented
- [ ] Runbook created for common tasks

## Post-Deployment

- [ ] DNS propagation verified
- [ ] SSL certificate expiry date noted (90 days for Let's Encrypt)
- [ ] Monitoring confirmed working
- [ ] Backups confirmed working
- [ ] Performance baseline established
- [ ] Security audit performed
- [ ] Penetration testing performed (optional)
- [ ] Load testing performed (optional)

## Maintenance Schedule

Set up regular maintenance tasks:

- [ ] Daily: Check logs for errors
- [ ] Daily: Verify backups completed
- [ ] Weekly: Security updates check
- [ ] Weekly: Disk space check
- [ ] Monthly: SSL certificate renewal check
- [ ] Monthly: Performance review
- [ ] Quarterly: Full security audit
- [ ] Quarterly: Disaster recovery test

## Emergency Contacts

Document who to contact for:
- [ ] Hosting/VPS issues
- [ ] SSL certificate issues
- [ ] Domain/DNS issues
- [ ] Development team
- [ ] Database administrator
- [ ] Security team

## Rollback Plan

- [ ] Previous version backed up
- [ ] Rollback procedure documented
- [ ] Database rollback tested
- [ ] Downtime communication plan ready

## Compliance

- [ ] GDPR compliance verified (if applicable)
- [ ] Privacy policy updated
- [ ] Terms of service updated
- [ ] Cookie consent implemented
- [ ] Data retention policy configured
- [ ] Customer data export functionality tested
- [ ] Customer data deletion functionality tested

## Notes

Add any deployment-specific notes here:

```
Deployment Date: _____________
Deployed By: _____________
Version: _____________
Domain: _____________
VPS Provider: _____________
VPS IP: _____________
```

## Sign-off

- [ ] Technical lead approval
- [ ] Business owner approval
- [ ] Security approval
- [ ] Operations approval

---

**Remember:** Always test in a staging environment before deploying to production!
