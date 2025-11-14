# Changelog

All notable changes to the Zeltero B2B module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-11-14

### Added - Phase 1 MVP Features

#### Core Module Structure
- Module registration and configuration
- Database schema for 4 tables (companies, company users, quick orders, RFQ)
- ACL permissions for admin access control
- Dependency injection configuration
- Event observer system

#### Company Registration & Management
- Self-service company registration form at `/b2b/company/index`
- Company information capture (name, tax ID, email, phone, address)
- Admin approval workflow with configurable auto-approval
- Company status management (pending, approved, rejected)
- Admin UI grid for managing companies
- Company approval/rejection actions
- Customer group assignment to companies
- Company model with resource model and collection

#### Quick Order Functionality
- Quick order form at `/b2b/quickorder/index` (requires login)
- SKU-based product ordering
- Multiple item entry with configurable limit
- CSV file import for bulk orders
- Direct add-to-cart functionality via AJAX
- Quick order history tracking
- Admin view for quick order monitoring

#### Wholesale Pricing
- Integration with Magento's customer group system
- Support for tier pricing per customer group
- Configurable retail price display
- Bronze, Silver, Gold wholesale tiers (created via setup patch)

#### B2B Payment Methods
- Bank Transfer payment method
  - Configurable payment instructions
  - Offline payment processing
- Proforma Invoice payment method
  - Invoice-based payment workflow
  - Configurable terms

#### ERP Integration
- Automatic order export on order placement
- Multiple export formats: CSV, XML, JSON
- Configurable export directory
- Comprehensive order data export including:
  - Order details
  - Customer information
  - Line items
  - Billing address
- Event-based export system

#### Admin Interface
- B2B admin menu with sub-sections
- Companies management grid with:
  - Search and filtering
  - Status filter
  - Column sorting
  - Custom actions (approve, edit, delete)
- Quick Orders history view
- RFQ placeholder (Phase 2)
- Configuration interface with settings for:
  - Company registration
  - Quick order
  - Payment methods
  - ERP integration
  - Wholesale pricing

#### Frontend UI
- Professional CSS styling for all B2B pages
- Responsive design for mobile compatibility
- Company registration form with validation
- Quick order form with:
  - Dynamic row addition
  - SKU and quantity fields
  - CSV upload interface
- Customer account navigation integration
- Loading indicators and user feedback

#### Configuration & Setup
- System configuration under Stores > Configuration > Zeltero B2B
- Default configuration values
- Setup data patch for customer groups
- Composer package configuration

#### Documentation
- Comprehensive module README (`app/code/Zeltero/B2B/README.md`)
- Installation and configuration guide (`B2B_DOCUMENTATION.md`)
- Feature documentation with usage examples
- API endpoint reference
- Database schema documentation
- Troubleshooting guide

### Technical Implementation

#### Models & Resources
- Company model with status management methods
- Company resource model and collection
- Payment method models (BankTransfer, Proforma)
- Config source models (ExportFormat, CompanyStatus)
- UI component listing columns

#### Controllers
- Frontend controllers:
  - Company registration form and submission
  - Quick order form and AJAX add-to-cart
- Admin controllers:
  - Company grid, approve, edit, delete
  - Quick order grid
  - RFQ grid (placeholder)

#### Services
- ERP export service with multi-format support
- Order data preparation and formatting

#### Observers
- Order save observer for automatic ERP export

#### Views & Templates
- Admin layouts and UI components
- Frontend layouts with CSS integration
- Company registration template
- Quick order template with JavaScript
- Custom CSS for professional styling

### Database Schema

#### Tables Created
1. **zeltero_b2b_company**
   - Company information storage
   - Approval status tracking
   - Customer group assignment
   - Credit limit fields (Phase 2)
   - Payment terms (Phase 2)

2. **zeltero_b2b_company_user**
   - Multi-user company accounts (Phase 2)
   - Role-based access (Phase 2)
   - User status management

3. **zeltero_b2b_quick_order**
   - Quick order history
   - Draft order storage
   - Items data in JSON format

4. **zeltero_b2b_rfq**
   - Request for Quote system (Phase 2)
   - Quote status tracking
   - Customer and admin notes

### Configuration Paths
- `zeltero_b2b/company/*` - Company registration settings
- `zeltero_b2b/quick_order/*` - Quick order settings
- `zeltero_b2b/payment/*` - B2B payment method settings
- `zeltero_b2b/erp/*` - ERP integration settings
- `zeltero_b2b/wholesale/*` - Wholesale pricing settings

### Routes
- **Frontend:**
  - `b2b/company/index` - Registration form
  - `b2b/company/register` - Submit registration
  - `b2b/quickorder/index` - Quick order form
  - `b2b/quickorder/addtocart` - Add items (AJAX)

- **Admin:**
  - `zeltero_b2b/company/*` - Company management
  - `zeltero_b2b/quickorder/*` - Quick order management
  - `zeltero_b2b/rfq/*` - RFQ management (Phase 2)

### ACL Resources
- `Zeltero_B2B::b2b` - Main menu access
- `Zeltero_B2B::company` - Company management
- `Zeltero_B2B::quick_order` - Quick order access
- `Zeltero_B2B::rfq` - RFQ access
- `Zeltero_B2B::config` - Configuration access

## [Unreleased] - Phase 2 Features (Database Ready)

### Planned
- Multi-user company accounts implementation
- Role-based permissions (admin, purchaser, user)
- Credit limit enforcement at checkout
- Payment terms calculation
- RFQ submission form
- RFQ admin management interface
- Quote approval workflow
- Quote to order conversion
- Two-way ERP integration (inventory, invoices, balances)

## [Future] - Phase 3 Features

### Planned
- Personalized customer dashboards
- Purchase history analytics
- Product recommendations
- Spending statistics
- B2B Customer REST API
- GraphQL API support
- OAuth authentication
- Headless/PWA frontend option
- Advanced reporting and analytics

## Installation

```bash
composer require zeltero/module-b2b
php bin/magento module:enable Zeltero_B2B
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

## Compatibility

- Magento Open Source 2.4.x / Mage-OS
- PHP 8.2, 8.3, 8.4
- MySQL 8.0+ / MariaDB 10.4+
- Elasticsearch 8.x / OpenSearch 2.x

## Contributors

- Zeltero Team

## License

Open Software License (OSL 3.0)
