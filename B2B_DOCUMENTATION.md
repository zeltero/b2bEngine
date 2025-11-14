# B2B Engine - Magento Open Source B2B Platform

## Overview

This repository contains a fully-featured B2B e-commerce platform built on Magento Open Source (Mage-OS). The platform includes comprehensive B2B functionality including company registration, quick ordering, wholesale pricing, B2B payment methods, and ERP integration.

## Architecture

- **Platform**: Magento Open Source (Mage-OS)
- **PHP Version**: 8.2, 8.3, or 8.4
- **Custom Module**: Zeltero_B2B (located in `app/code/Zeltero/B2B/`)

## Features

### Phase 1 - MVP B2B (✅ Implemented)

1. **Company Registration with Approval**
   - Self-service registration form
   - Admin approval workflow
   - Configurable auto-approval
   - Company information management

2. **Wholesale Pricing per Customer Group**
   - Integration with Magento customer groups
   - Tier pricing support
   - Optional retail price display

3. **Quick Order**
   - SKU-based ordering
   - Multiple items entry
   - CSV import for bulk orders
   - Direct cart integration

4. **B2B Payment Methods**
   - Bank Transfer
   - Proforma Invoice
   - Configurable payment instructions

5. **ERP Export**
   - Automatic order export
   - Multiple formats: CSV, XML, JSON
   - Configurable export location

### Phase 2 - Advanced B2B (Database Ready)

- Multi-user company accounts with roles
- Credit limits and payment terms
- Request for Quote (RFQ) system
- Two-way ERP integration

### Phase 3 - Future Enhancements

- Personalized customer dashboards
- B2B Customer API
- Headless/PWA frontend

## Installation

### 🐳 Docker Installation (Recommended for Production)

**Quick Setup with Docker:**
```bash
./setup-docker.sh
```

For complete Docker setup with password protection, see:
- **Quick Guide:** [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md)
- **Full Documentation:** [README_DOCKER.md](README_DOCKER.md)
- **Deployment Checklist:** [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)

### Manual Installation

#### Prerequisites

- PHP 8.2, 8.3, or 8.4
- MySQL 8.0+ or MariaDB 10.4+
- Elasticsearch 8.x or OpenSearch 2.x
- Composer 2.x
- Web server (Apache or Nginx)

#### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/zeltero/b2bEngine.git
   cd b2bEngine
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Create a MySQL database
   - Copy `app/etc/env.php.sample` to `app/etc/env.php`
   - Update database credentials

4. **Install Magento**
   ```bash
   php bin/magento setup:install \
     --base-url=http://your-domain.com \
     --db-host=localhost \
     --db-name=magento \
     --db-user=magento \
     --db-password=password \
     --admin-firstname=Admin \
     --admin-lastname=User \
     --admin-email=admin@example.com \
     --admin-user=admin \
     --admin-password=admin123 \
     --language=en_US \
     --currency=USD \
     --timezone=America/Chicago \
     --use-rewrites=1
   ```

5. **Enable the B2B module**
   ```bash
   php bin/magento module:enable Zeltero_B2B
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy -f
   php bin/magento cache:flush
   ```

6. **Set permissions**
   ```bash
   find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
   find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
   chown -R :www-data .
   chmod u+x bin/magento
   ```

## Configuration

### Admin Panel Access

1. Navigate to: `http://your-domain.com/admin`
2. Login with admin credentials created during installation

### B2B Module Configuration

1. **Navigate to**: Stores > Configuration > Zeltero B2B

2. **Company Registration Settings**:
   - Enable Company Registration: Yes
   - Auto Approve Companies: No (for manual approval) or Yes (automatic)
   - Default Customer Group: Select appropriate group

3. **Quick Order Settings**:
   - Enable Quick Order: Yes
   - Allow CSV Import: Yes
   - Maximum Items: 100

4. **Payment Methods**:
   - Enable Bank Transfer: Yes
   - Enable Proforma Invoice: Yes

5. **ERP Integration**:
   - Enable ERP Export: Yes
   - Export Format: CSV (or XML/JSON)
   - Export Directory Path: b2b/erp/export

6. **Wholesale Pricing**:
   - Enable Wholesale Pricing: Yes
   - Show Retail Price: Yes (to show comparison)

### Customer Groups Setup

1. Navigate to: Customers > Customer Groups
2. Create groups for different wholesale tiers:
   - Bronze Wholesale
   - Silver Wholesale
   - Gold Wholesale
   - etc.

### Product Tier Pricing

1. Edit any product
2. Go to Advanced Pricing
3. Add tier prices for each customer group
4. Example:
   - General (Retail): $100
   - Bronze Wholesale: $85 (15% off)
   - Silver Wholesale: $75 (25% off)
   - Gold Wholesale: $65 (35% off)

## Usage

### For B2B Customers

1. **Register Company**:
   - Visit: `http://your-domain.com/b2b/company/index`
   - Fill in company details
   - Submit for approval

2. **Quick Order**:
   - Login to account
   - Visit: `http://your-domain.com/b2b/quickorder/index`
   - Enter SKUs and quantities OR upload CSV
   - Add to cart

3. **Checkout**:
   - Use B2B payment methods
   - Bank Transfer or Proforma Invoice

### For Administrators

1. **Manage Companies**:
   - Navigate to: B2B > Companies
   - View all registrations
   - Approve/reject companies
   - Assign customer groups

2. **View Quick Orders**:
   - Navigate to: B2B > Quick Orders
   - Monitor ordering activity

3. **ERP Exports**:
   - Files exported to: `var/b2b/erp/export/`
   - Automatic on order placement
   - Import into your ERP system

## API Endpoints

### Frontend Routes

- `/b2b/company/index` - Company registration form
- `/b2b/company/register` - Submit registration (POST)
- `/b2b/quickorder/index` - Quick order form
- `/b2b/quickorder/addtocart` - Add items to cart (POST, AJAX)

### Admin Routes

- `/admin/zeltero_b2b/company/index` - Company management grid
- `/admin/zeltero_b2b/company/approve` - Approve company
- `/admin/zeltero_b2b/quickorder/index` - Quick order history

## Database Schema

### Tables Created

1. **zeltero_b2b_company** - Company information
2. **zeltero_b2b_company_user** - Multi-user accounts (Phase 2)
3. **zeltero_b2b_quick_order** - Quick order history
4. **zeltero_b2b_rfq** - Request for Quote (Phase 2)

## Development

### Module Location

```
app/code/Zeltero/B2B/
```

### Module Documentation

See `app/code/Zeltero/B2B/README.md` for detailed module documentation.

### Running in Development Mode

```bash
php bin/magento deploy:mode:set developer
php bin/magento cache:disable
```

### Debugging

Enable logging:
```bash
php bin/magento setup:config:set --enable-debug-logging=true
```

View logs:
```bash
tail -f var/log/system.log
tail -f var/log/exception.log
```

## Testing

### Manual Testing Checklist

1. **Company Registration**:
   - [ ] Registration form loads at `/b2b/company/index`
   - [ ] Form validation works
   - [ ] Registration submits successfully
   - [ ] Admin can see pending registration
   - [ ] Admin can approve/reject

2. **Quick Order**:
   - [ ] Quick order form loads for logged-in users
   - [ ] Can add multiple items by SKU
   - [ ] CSV import works
   - [ ] Items added to cart correctly

3. **Pricing**:
   - [ ] Retail customers see retail prices
   - [ ] Wholesale customers see discounted prices
   - [ ] Tier pricing applies correctly

4. **Payment Methods**:
   - [ ] Bank Transfer available at checkout
   - [ ] Proforma Invoice available at checkout
   - [ ] Payment instructions display

5. **ERP Export**:
   - [ ] Orders export automatically
   - [ ] Export files created in correct location
   - [ ] Export format is correct

## Troubleshooting

### Module Not Appearing

```bash
php bin/magento module:status
php bin/magento module:enable Zeltero_B2B
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### Database Tables Not Created

```bash
php bin/magento setup:db:status
php bin/magento setup:upgrade
```

### Static Content Issues

```bash
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
```

### Permission Issues

```bash
chmod -R 777 var/ generated/ pub/static/ pub/media/
# Then set proper permissions as shown in installation
```

## Security

- All admin functionality is protected by ACL
- CSRF protection on all forms
- Input validation and sanitization
- SQL injection prevention through ORM
- XSS prevention through output escaping

## Performance

- Database indexes on frequently queried fields
- Collection filtering at database level
- Minimal frontend JavaScript
- Caching-compatible architecture

## Support

- **Module Issues**: See `app/code/Zeltero/B2B/README.md`
- **Magento Documentation**: https://experienceleague.adobe.com/docs/commerce.html
- **Community**: https://community.magento.com/

## License

This project is licensed under the Open Software License (OSL 3.0).

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Version History

- **1.0.0** (Current)
  - Phase 1 MVP B2B features
  - Company registration and approval
  - Quick order with CSV import
  - B2B payment methods
  - ERP export functionality
  - Wholesale pricing support

## Roadmap

- [ ] Complete Phase 2 implementation
- [ ] Add comprehensive unit tests
- [ ] Add integration tests
- [ ] Implement Phase 3 features
- [ ] Add GraphQL API support
- [ ] PWA frontend implementation
- [ ] Advanced reporting and analytics

## Authors

- Zeltero Team

## Acknowledgments

- Built on Magento Open Source / Mage-OS
- Community contributors
- Adobe Commerce documentation
