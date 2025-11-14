# Zeltero B2B Module for Magento

## Overview

This module transforms Magento Open Source into a comprehensive B2B e-commerce platform with features designed for wholesale and business-to-business commerce.

## Features

### Phase 1 - MVP B2B (Implemented)

#### 1. Company Registration with Approval Workflow
- **Location**: `/b2b/company/index`
- **Features**:
  - Self-service company registration form
  - Admin approval workflow (optional auto-approval)
  - Company information management (name, tax ID, contact details)
  - Status tracking (pending, approved, rejected)
- **Admin Management**: 
  - Navigate to: B2B > Companies
  - Approve/reject registrations
  - View company details

#### 2. Wholesale Pricing per Customer Group
- **Configuration**: Stores > Configuration > Zeltero B2B > Pricing Settings
- **Features**:
  - Assign customer groups to approved companies
  - Support for Magento's native customer group pricing
  - Optional retail price display for comparison
- **Setup**:
  1. Create customer groups for different wholesale tiers
  2. Configure tier pricing on products
  3. Assign customer groups to approved companies

#### 3. Quick Order Functionality
- **Location**: `/b2b/quickorder/index`
- **Features**:
  - Fast SKU-based product ordering
  - Multiple item entry (configurable limit)
  - CSV import for bulk orders
  - Direct add to cart functionality
- **CSV Format**:
  ```
  SKU,Quantity
  PRODUCT-001,10
  PRODUCT-002,25
  ```

#### 4. B2B Payment Methods
- **Bank Transfer**: Traditional bank transfer payment
  - Configuration: Stores > Configuration > Sales > Payment Methods > Bank Transfer
  - Displays bank details and payment instructions
  
- **Proforma Invoice**: Invoice-based payment
  - Configuration: Stores > Configuration > Sales > Payment Methods > Proforma Invoice
  - Generates proforma before shipment

#### 5. ERP Export
- **Configuration**: Stores > Configuration > Zeltero B2B > General Settings > ERP Integration
- **Features**:
  - Automatic order export on placement
  - Multiple formats supported: CSV, XML, JSON
  - Configurable export directory
  - Comprehensive order data including items, customer info, and addresses
- **Export Location**: `var/b2b/erp/export/` (configurable)

### Phase 2 - Advanced B2B (Database Ready, Implementation Required)

The following features have database schema prepared but require additional implementation:

#### 1. Company Accounts with Multiple Users
- **Tables**: `zeltero_b2b_company_user`
- **Features to Implement**:
  - User roles (admin, purchaser, user)
  - Permission management
  - User invitation system

#### 2. Credit Limits and Payment Terms
- **Database Fields**: `credit_limit`, `payment_term_days` in `zeltero_b2b_company`
- **Features to Implement**:
  - Credit limit checking at checkout
  - Payment term calculation
  - Outstanding balance tracking

#### 3. Request for Quote (RFQ)
- **Tables**: `zeltero_b2b_rfq`
- **Features to Implement**:
  - RFQ submission form
  - Admin quote management
  - Quote approval workflow
  - Convert quote to order

#### 4. Two-way ERP Integration
- **Current Status**: One-way export implemented
- **Features to Implement**:
  - Import inventory levels from ERP
  - Import invoices and payment status
  - Sync customer balances
  - Real-time stock updates

### Phase 3 - Advanced Features (Planned)

#### 1. Personalized Customer Dashboards
- Purchase history analytics
- Product recommendations
- Spending statistics
- Reorder functionality

#### 2. B2B Customer API
- RESTful API for system-to-system integration
- OAuth authentication
- Order placement via API
- Product catalog access
- Order status queries

#### 3. Headless/PWA Frontend
- Decoupled frontend architecture
- Progressive Web App capabilities
- Enhanced performance
- Modern UI/UX

## Installation

1. Place the module in `app/code/Zeltero/B2B/`

2. Enable the module:
   ```bash
   php bin/magento module:enable Zeltero_B2B
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy
   php bin/magento cache:flush
   ```

3. Configure the module:
   - Navigate to: Stores > Configuration > Zeltero B2B
   - Enable desired features
   - Configure payment methods
   - Set up ERP export if needed

## Configuration

### Company Registration Settings
Path: `Stores > Configuration > Zeltero B2B > General Settings > Company Registration`

- **Enable Company Registration**: Enable/disable the registration feature
- **Auto Approve Companies**: Automatically approve without manual review
- **Default Customer Group**: Customer group for approved companies

### Quick Order Settings
Path: `Stores > Configuration > Zeltero B2B > General Settings > Quick Order`

- **Enable Quick Order**: Enable/disable quick order functionality
- **Allow CSV Import**: Enable CSV file upload
- **Maximum Items per Quick Order**: Limit items per order (default: 100)

### Payment Method Settings
Path: `Stores > Configuration > Zeltero B2B > General Settings > B2B Payment Methods`

- **Enable Bank Transfer**: Enable bank transfer payment
- **Enable Proforma Invoice**: Enable proforma invoice payment

### ERP Integration Settings
Path: `Stores > Configuration > Zeltero B2B > General Settings > ERP Integration`

- **Enable ERP Export**: Automatic order export
- **Export Format**: CSV, XML, or JSON
- **Export Directory Path**: Location for export files

### Wholesale Pricing Settings
Path: `Stores > Configuration > Zeltero B2B > Pricing Settings > Wholesale Pricing`

- **Enable Wholesale Pricing**: Enable B2B pricing features
- **Show Retail Price**: Display retail price for comparison

## Usage

### For Store Administrators

1. **Managing Companies**:
   - Navigate to: B2B > Companies
   - View all registered companies
   - Approve or reject registrations
   - Edit company information
   - Assign customer groups

2. **Managing Quick Orders**:
   - Navigate to: B2B > Quick Orders
   - View customer quick order history
   - Monitor ordering patterns

3. **ERP Exports**:
   - Access exported files in `var/b2b/erp/export/`
   - Configure export format and schedule
   - Import into your ERP system

### For B2B Customers

1. **Company Registration**:
   - Visit: `/b2b/company/index`
   - Fill in company information
   - Wait for admin approval (if not auto-approved)

2. **Quick Ordering**:
   - Log in to your account
   - Visit: `/b2b/quickorder/index`
   - Enter SKUs and quantities
   - Or upload a CSV file
   - Add all items to cart at once

3. **Checkout**:
   - Use B2B-specific payment methods
   - Bank Transfer or Proforma Invoice
   - Follow payment instructions

## Database Schema

### Companies Table (`zeltero_b2b_company`)
- Company information and approval status
- Customer group assignment
- Credit limits and payment terms (Phase 2)

### Company Users Table (`zeltero_b2b_company_user`)
- Multi-user company accounts (Phase 2)
- Role-based permissions

### Quick Order Table (`zeltero_b2b_quick_order`)
- Quick order history
- Draft order storage

### RFQ Table (`zeltero_b2b_rfq`)
- Request for Quote functionality (Phase 2)
- Quote management and approval

## API Endpoints (Frontend)

### Company Registration
- **GET** `/b2b/company/index` - Registration form
- **POST** `/b2b/company/register` - Submit registration

### Quick Order
- **GET** `/b2b/quickorder/index` - Quick order form
- **POST** `/b2b/quickorder/addtocart` - Add items to cart (AJAX)

## Admin Menu

- **B2B** (Main Menu)
  - Companies - Manage company registrations
  - Quick Orders - View quick order history
  - Requests for Quote - RFQ management (Phase 2)

## Permissions

Admin ACL resources:
- `Zeltero_B2B::b2b` - Main B2B menu access
- `Zeltero_B2B::company` - Company management
- `Zeltero_B2B::quick_order` - Quick order management
- `Zeltero_B2B::rfq` - RFQ management
- `Zeltero_B2B::config` - Configuration access

## Development Roadmap

### Completed ✓
- [x] Module structure and configuration
- [x] Company registration with approval
- [x] Quick order with CSV import
- [x] B2B payment methods
- [x] ERP export functionality
- [x] Admin UI for company management
- [x] Customer group integration

### In Progress / Next Steps
- [ ] Complete Phase 2 features implementation
- [ ] RFQ frontend and backend
- [ ] Multi-user company accounts
- [ ] Credit limit enforcement
- [ ] Two-way ERP integration
- [ ] Phase 3 API development
- [ ] PWA frontend option

## Support

For issues, feature requests, or contributions, please refer to the project repository.

## License

This module follows the same license as Magento Open Source (OSL 3.0).

## Version History

- **1.0.0** - Initial release with Phase 1 MVP features
  - Company registration and approval
  - Quick order functionality
  - B2B payment methods
  - ERP export
  - Wholesale pricing support
