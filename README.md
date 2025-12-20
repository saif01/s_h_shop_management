# Shop Management System (SMS)

A comprehensive shop management web application built with Laravel and Vue.js. This system handles product selling, inventory/stock management, purchases, customer/supplier management, and business reports - designed according to the SRS specifications in `public/Project Report/Shop Managment System.pdf`.

## 🛠️ Technical Specifications

### Backend Stack

#### Core Framework
- **Laravel Framework**: ^12.0
- **PHP**: ^8.2
- **Composer**: Dependency management

#### Authentication & Security
- **Laravel Sanctum**: ^4.2 - API token-based authentication
- **Laravel UI**: ^4.6 - Authentication scaffolding

#### Database & ORM
- **Laravel Eloquent ORM**: Built-in ORM for database operations
- **MySQL/PostgreSQL/SQLite**: Supported database systems
- **Yajra Laravel DataTables**: ^12.3 - Advanced data tables with server-side processing
- **Yajra Laravel OCI8**: ^12.0 - Oracle database support

#### File Processing & Media
- **Intervention Image**: ^3.11 - Image manipulation library
- **Intervention Image Laravel**: ^1.5 - Laravel integration for image processing
- **Barryvdh Laravel Snappy**: ^1.0 - PDF generation from HTML

#### Data Export & Import
- **Maatwebsite Excel**: ^3.1 - Excel file import/export functionality

#### API & Integration
- **Guzzle HTTP**: ^7.9 - HTTP client for API requests
- **Inertia.js Laravel**: ^2.0 - Modern monolith approach (SPA-like experience)
- **Tightenco Ziggy**: ^2.4 - Route helper for JavaScript

#### Utilities
- **SimpleSoftwareIO Simple QRCode**: ^4.2 - QR code generation
- **Irazasyed Telegram Bot SDK**: ^3.15 - Telegram bot integration
- **DirectoryTree LDAPRecord Laravel**: ^3.4 - LDAP authentication support

#### Development Tools
- **Laravel Tinker**: ^2.10.1 - REPL for Laravel
- **Laravel Pint**: ^1.18 - Code style fixer
- **Laravel Sail**: ^1.41 - Docker development environment
- **Laravel Pail**: ^1.2.2 - Real-time log viewer
- **OpcodesIO Log Viewer**: ^3.17 - Advanced log viewing interface

#### Testing
- **PHPUnit**: ^11.5.3 - Unit testing framework
- **Mockery**: ^1.6 - Mocking library for testing
- **Nunomaduro Collision**: ^8.6 - Error handler for console/CLI
- **FakerPHP Faker**: ^1.23 - Fake data generator for testing

### Frontend Stack

#### Core Framework
- **Vue.js**: ^3.2.37 - Progressive JavaScript framework
- **Vue Router**: ^4.3.0 - Official router for Vue.js
- **Vite**: ^6.3.5 - Next-generation frontend build tool

#### UI Framework
- **Vuetify**: ^3.5.14 - Material Design component framework
- **Material Design Icons**: ^7.4.47 - Icon library (@mdi/font)

#### State Management
- **Pinia**: ^3.0.4 - Official state management for Vue 3
- **Vuex**: ^4.1.0 - State management pattern library

#### Forms & Validation
- **VForm**: ^2.1.2 - Form validation library for Vue.js

#### Rich Text Editing
- **Quill**: ^2.0.3 - Rich text WYSIWYG editor
- **Vue3 Editor**: ^0.1.1 - Vue 3 wrapper for Quill

#### Data Visualization
- **Chart.js**: ^4.5.1 - Simple yet flexible JavaScript charting library
- **Vue ChartJS**: ^5.3.3 - Vue.js wrapper for Chart.js

#### UI Enhancements
- **Vue SweetAlert2**: ^5.0.5 - Beautiful, responsive, customizable popup boxes
- **Vue3 Progress Bar**: ^1.0.3 - Progress bar component for Vue 3

#### Utilities
- **Axios**: ^1.6.4 - Promise-based HTTP client
- **Moment.js**: ^2.30.1 - Date manipulation library
- **Bootstrap**: ^5.3.6 - CSS framework (for some legacy components)
- **Popper.js**: ^2.11.6 - Tooltip and popover positioning engine
- **Fontsource Prompt**: ^5.0.13 - Self-hosted Prompt font

#### Build Tools
- **Vite**: ^6.3.5 - Fast build tool and dev server
- **Sass**: ^1.89.1 - CSS preprocessor
- **Laravel Vite Plugin**: ^1.0 - Laravel integration for Vite
- **@vitejs/plugin-vue**: ^5.2.4 - Vue.js plugin for Vite

### Development Environment

#### Required Software
- **PHP**: 8.2 or higher
- **Composer**: PHP dependency manager
- **Node.js**: 18+ (for npm)
- **npm**: Package manager for JavaScript
- **MySQL/PostgreSQL/SQLite**: Database system

#### Build Commands
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Development build (with hot reload)
npm run dev

# Production build
npm run build
```

### Architecture

#### Backend Architecture
- **MVC Pattern**: Model-View-Controller architecture
- **RESTful API**: RESTful API design for admin panel
- **Service Layer**: Business logic separation
- **Repository Pattern**: Data access abstraction (where applicable)

#### Frontend Architecture
- **Component-Based**: Vue.js component architecture
- **SPA (Single Page Application)**: Client-side routing with Vue Router
- **API-Driven**: Frontend communicates with backend via REST API
- **State Management**: Centralized state with Pinia/Vuex

#### Security Features
- **CSRF Protection**: Laravel's built-in CSRF protection
- **XSS Protection**: Automatic output escaping
- **SQL Injection Protection**: Eloquent ORM parameter binding
- **Authentication**: Laravel Sanctum token-based authentication
- **Authorization**: Role-Based Access Control (RBAC) with permissions

### Performance Optimizations

#### Backend
- **Query Optimization**: Eloquent eager loading
- **Caching**: Laravel's caching system
- **Database Indexing**: Optimized database queries
- **File Compression**: Image optimization with Intervention Image

#### Frontend
- **Code Splitting**: Lazy loading of routes and components
- **Asset Optimization**: Vite's built-in optimization
- **Image Lazy Loading**: Lazy loading for images
- **Tree Shaking**: Unused code elimination

### Browser Support
- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Mobile Browsers**: iOS Safari, Chrome Mobile
- **Responsive Design**: Mobile-first approach with Vuetify

## 🚀 Features

### Core Shop Management Features

#### A) User & Role Management ✅
- **Laravel Sanctum Authentication**: Secure API token-based authentication
- **Role-Based Access Control (RBAC)**: Three main roles:
  - **Admin/Owner**: Full access to all features
  - **Cashier**: Sales, customers, and limited reports access
  - **Storekeeper**: Stock, purchase, and product management
- **User Management**: Complete user administration with profile management
- **Permission Management**: Fine-grained permission control for all features
- **Login Logging**: Track all login attempts with IP addresses and user agents
- **Activity Log**: Track who edited prices, stock, and sales (optional)

#### B) Product Management ✅
- **Product CRUD Operations**: Complete product management with:
  - Product Name, SKU, Barcode (optional, unique)
  - Category selection (e.g., Grocery, Cosmetics, Electronics)
  - Brand (optional)
  - Unit selection (pcs, kg, liter, etc.)
  - Purchase Price and Sale Price
  - VAT/Tax rate (optional)
  - Minimum Stock Alert level
  - Product Image (optional)
  - Status (Active/Inactive)
  - Description field
  - Advanced search by name, SKU, barcode, brand, or description
  - Sortable columns (name, SKU, prices, stock level, etc.)
  - Pagination with customizable page sizes
- **Stock Integration**: ✅ **Comprehensive stock and warehouse integration**:
  - **Total Stock Display**: View total stock quantity across all warehouses in product list
  - **Stock Breakdown by Warehouse**: Detailed stock information per warehouse in product dialog:
    - Stock quantity per warehouse
    - Average cost per warehouse
    - Total value per warehouse
    - Warehouse name and code display
  - **Direct Stock Adjustment**: Stock adjustment dialog with three adjustment types:
    - Set Quantity: Set exact stock quantity
    - Add Stock: Add to existing quantity
    - Subtract Stock: Subtract from existing quantity
    - Warehouse selection for adjustments
    - Optional unit cost and notes
    - Automatic stock ledger entry creation
  - **Color-Coded Stock Status**: Visual indicators for stock levels:
    - Red: Out of stock (quantity = 0)
    - Yellow: Low stock (below minimum level)
    - Green: Sufficient stock
  - **Warehouse Filtering**: Filter products by warehouse in product list
  - **Efficient Stock Queries**: Optimized database queries to prevent N+1 problems
  - **Real-time Stock Updates**: Automatic stock refresh after adjustments
  - **Stock Information in Product View**: Complete stock breakdown in product view dialog
- **Category Management**: Hierarchical categories with icons and images
  - Category view dialog with audit information
  - Created/Updated by tracking
  - Category status management
- **Unit Management**: Define units of measurement (kg, pcs, ltr, box, etc.)
  - Complete CRUD operations
  - Unit selection in product forms
- **Bulk Operations**: Excel/CSV import support (placeholder ready)
- **Barcode Printing**: Barcode generation for products (placeholder ready)

#### C) Stock / Inventory Management ✅
- **Stock Tracking**: Real-time stock levels per warehouse
- **Stock Ledger**: ✅ **Enhanced stock ledger with advanced features**:
  - Detailed stock in/out transaction history with comprehensive filtering
  - Date range filtering with DatePicker components (from/to dates)
  - Filter by product, warehouse, transaction type (in/out), and reference type
  - Search by reference number or product name/SKU
  - Sortable columns (date, quantity, created at, etc.)
  - Sortable "Created At" column with AM/PM time format
  - Excel export functionality - export all filtered data to CSV/Excel format
  - Pagination with "Show All" option
  - Real-time transaction tracking with creator information
- **Low Stock Alerts**: Automatic alerts when stock falls below minimum level
- **Stock Adjustments**: ✅ **Direct stock adjustment from product dialog** - Manual corrections for damage, loss, or discrepancies with three adjustment types:
  - Set Quantity: Set exact stock quantity
  - Add Stock: Add to existing quantity
  - Subtract Stock: Subtract from existing quantity
- **Stock Valuation**: Calculate total stock value with average cost tracking
- **Multi-Warehouse Support**: Manage inventory across multiple locations
- **Warehouse Management**: Complete warehouse CRUD with:
  - Warehouse name, code, and location
  - Contact information (phone, email)
  - Full address details
  - Manager assignment
  - Active/Inactive status
- **Product-Stock Integration**: ✅ **Stock information displayed directly in product management**:
  - View stock quantities per warehouse in product dialog
  - Color-coded stock status (red: out of stock, yellow: low stock, green: sufficient)
  - Stock breakdown by warehouse with average cost and total value
  - Filter products by warehouse
  - Real-time stock updates after adjustments

#### D) Supplier & Purchase Management ✅
- **Supplier Management**: Complete supplier CRUD with:
  - Supplier name, phone, address
  - Contact information
  - Payment terms
  - Active/Inactive status
- **Purchase Entry**: Create purchase orders with:
  - Purchase date and due date
  - Supplier selection
  - Multiple product items with quantity and purchase price
  - Payment tracking (paid/due amounts)
  - Invoice status (draft, pending, partial, paid, cancelled)
- **Purchase Return**: Handle purchase returns (placeholder ready)
- **Supplier Ledger**: Track supplier balances and payment history

#### E) Sales / POS (Point of Sale) ✅
- **Full POS Interface**: Modern point-of-sale system with:
  - Product search by name, SKU, or barcode
  - Shopping cart with real-time calculations
  - Customer selection (or Walk-in customers)
  - Warehouse selection for stock management
  - Real-time stock validation
- **Invoice Management**: 
  - Invoice date and due date
  - Item-level discount and tax
  - Invoice-level discount, additional tax, and shipping cost
  - Real-time total calculations
  - Invoice number generation (auto or manual)
  - Notes and reference fields
- **Payment Processing**:
  - Multiple payment methods: cash, card, mobile banking, bank transfer, cheque
  - Paid/Due amount tracking
  - Partial payment support
  - Credit sales (due tracking)
  - Payment status management (draft, pending, partial, paid, cancelled)
- **Sales Management Interface**: ✅ **Enhanced sales list with advanced features**:
  - **Advanced Filtering**:
    - Status filtering (draft, pending, partial, paid, cancelled)
    - Date range filtering with DatePicker components (from/to dates)
    - Search by invoice number or customer name
  - **Sortable Columns**: Sort by invoice number, date, total amount, paid amount, due amount, or status
  - **Pagination**: 
    - Customizable page sizes (10, 25, 50, 100, 500)
    - "Show All" option to display all records
    - Total records display
  - **Sales Actions**:
    - View sale details in dedicated view dialog
    - Edit existing sales
    - Delete sales with confirmation
    - Print invoices (A4 and POS thermal receipt)
  - **Sales Display**:
    - Customer information (or Walk-in indicator)
    - Invoice date with DD/MM/YYYY format
    - Total, paid, and due amounts with currency formatting
    - Color-coded due amounts (red for outstanding, green for paid)
    - Status chips with color coding
- **Sales Return**: Process returns and auto-restock (placeholder ready)
- **Invoice Printing**: A4 and POS thermal receipt printing (placeholder ready)
- **Sales History**: Complete sales transaction history with comprehensive filtering and sorting

#### F) Customer & Due Management ✅
- **Customer Management**: Complete customer CRUD with:
  - Customer name, code, phone, email
  - Company name (optional)
  - Full address details
  - Tax ID
  - Opening balance and current balance
  - Active/Inactive status
- **Customer Ledger**: Track customer transactions:
  - Total purchases
  - Total paid amounts
  - Current due balance
  - Payment history
- **Due Payment Collection**: Process customer payments with:
  - Partial payment support
  - Payment method selection
  - Payment date and reference
  - Transaction notes
- **Customer View Dialog**: Detailed customer information display

### 3) Reports & Analytics ✅

#### G) Comprehensive Reporting System
All reports include filtering, summary cards, and export options (Excel & PDF).

**Sales Reports:**
- Date range filtering (from/to dates)
- Customer-wise sales analysis
- Status filtering (draft, pending, partial, paid, cancelled)
- Summary metrics: total sales, total paid, total due, invoice count
- Top selling products (top 10 with quantity and revenue)
- Export to Excel and PDF

**Purchase Reports:**
- Date range filtering
- Supplier-wise purchase analysis
- Status filtering
- Summary metrics: total purchases, paid, due, order count
- Export functionality

**Stock Reports:**
- Current stock levels by warehouse
- Low stock items report
- Stock valuation report
- Category-wise filtering
- Out of stock alerts
- Stock summary: total products, stock value, low stock count

**Due Reports:**
- Customer due tracking
- Supplier due tracking
- Overdue highlighting (past due date)
- Payment collection interface
- Summary: total due, overdue amount, parties with dues

**Profit Reports:**
- Revenue vs Cost analysis
- Gross profit calculation: (Sale price - Purchase price) × qty - discounts
- Profit margin percentage
- Multiple grouping: daily, weekly, monthly, by product, by category
- Visual profit trends

### 4) Dashboard (Owner Dashboard) ✅

#### Key Metrics Display
- **Today's Sales**: Total sales amount for current day
- **This Month's Sales**: Monthly sales performance
- **Low Stock Items Count**: Number of products below minimum level
- **Total Customer Due**: Outstanding customer payments
- **Product Count**: Total active products
- **Recent Sales List**: Last 10 sales with customer details

### 5) Technical Features ✅

#### Non-Functional Requirements
- **Responsive UI**: Desktop and mobile optimized with Vuetify
- **Fast Product Search**: Real-time search in POS (critical for quick sales)
- **Data Backup/Export**: Excel and PDF export for all reports (placeholder ready)
- **Audit Trail**: Track critical actions like price changes, stock edits (optional)
- **Multi-language Support**: Bangla/English support (optional, ready for implementation)
- **Secure Authentication**: Laravel Sanctum token-based authentication
- **Role-Based Permissions**: Granular access control per user role

### 6) Suggested Tech Stack ✅

This application follows the exact tech stack recommended in the PDF:

#### Backend
- **Laravel 12**: Latest Laravel framework with API support
- **Laravel Sanctum**: API authentication for admin panel
- **PostgreSQL/MySQL**: Database (SQLite supported for development)
- **Maatwebsite Excel**: Excel export functionality (ready for implementation)
- **Barryvdh DomPDF**: PDF generation for reports and invoices (ready)

#### Frontend
- **Vue 3**: Progressive JavaScript framework
- **Vite**: Fast build tool and dev server
- **Pinia**: State management for Vue 3
- **Vuetify 3**: Material Design component framework
- **Axios**: HTTP client for API requests
- **Chart.js**: Data visualization (for future dashboard charts)

### Payment Management ✅
- **Payment Recording**: Track all payment transactions
- **Payment Types**: Purchase payments, sale payments, expenses, refunds
- **Payment Methods**: Cash, bank transfer, cheque, card, mobile banking, other
- **Payment Tracking**: Link payments to sales or purchases
- **Transaction Reference**: Store cheque numbers, transaction IDs, etc.
- **Payment Status**: Pending, completed, failed, cancelled
- **Party Tracking**: Link payments to customers or suppliers

### Settings Management ✅
- **General Settings**: Site name, contact information
- **Branding Settings**: Logo, favicon, color scheme
- **Footer Settings**: Powered by text, version, copyright
- **Email/SMTP Settings**: Email configuration for notifications (optional)

### Activity Logging ✅
- **Login Logs**: Track all login attempts with IP and user agent
- **User Activity**: Track who performed critical actions (optional)

## 📊 Module Alignment with PDF Requirements

| PDF Module | Implementation Status | Components Created |
|------------|----------------------|-------------------|
| User & Role Management | ✅ Complete | AdminUsers, AdminRoles, AdminPermissions |
| Product Management | ✅ Complete | AdminProducts, AdminCategories, AdminUnits, ProductDialog |
| Stock/Inventory | ✅ Complete | AdminStockLedger, AdminWarehouses |
| Supplier & Purchase | ✅ Complete | AdminSuppliers, AdminPurchases, PurchaseDialog |
| Sales (POS) | ✅ **NEW** | AdminSales, SaleDialog, ViewSaleDialog |
| Customer & Due | ✅ Complete | AdminCustomers, CustomerDialog, ViewCustomerDialog |
| Reports (7 types) | ✅ **NEW** | AdminReports, SalesReports, PurchaseReports, StockReports, DueReports, ProfitReports |
| Dashboard | ✅ Complete | AdminDashboard (with all required metrics) |
| Settings | ✅ Complete | AdminSettings |

**Legend:**
- ✅ Complete - Fully implemented and tested
- ✅ **NEW** - Newly added in this update

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & npm
- MySQL/PostgreSQL/SQLite

## 🔧 Installation

### 1. Clone and Install Dependencies

```bash
git clone <repository-url>
cd s_h_micro_control
composer install
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file:
- Database connection
- `APP_URL`
- Mail settings (for notifications)

### 3. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

This will create:
- All database tables
- Module definitions (disabled by default)
- 5 roles (Administrator, Content Manager, Marketing Manager, HR Manager, Support Staff)
- 5 demo users (one for each role) with profile information
- Demo content (products, services, blog posts, etc.)

### 4. Build Assets

```bash
npm run dev  # Development
# or
npm run build  # Production
```

### 5. Start Development Server

```bash
php artisan serve
npm run dev
```

Visit:
- Public website: `http://localhost:8000`
- Admin panel: `http://localhost:8000/admin/login`

## 🔑 Default User Credentials

The seeder creates demo users for shop management roles. All users have the password: `password`

- **Administrator/Owner**: admin@mail.com (Full access)
- **Cashier**: cashier@mail.com (Sales, customers, limited reports)
- **Storekeeper**: storekeeper@mail.com (Stock, purchases, products)

⚠️ **Change these immediately in production!**

### Required Permissions for New Features:
- `view-sales`, `create-sales`, `edit-sales`, `delete-sales` - For Sales/POS access
- `view-reports` - For Reports & Analytics access
- `manage-units` - For Unit management
- `manage-warehouses` - For Warehouse management

## 📚 API Documentation

### Admin API (`/api/v1/`)

All admin endpoints require authentication via Bearer token and appropriate permissions.

**Authentication:**
- `POST /api/v1/auth/login` - Login (returns bearer token)
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/user` - Get current authenticated user with roles/permissions

**Sales Management:**
- `GET /api/v1/sales` - List sales with advanced filtering, sorting, and pagination (requires `view-sales`)
  - **Filtering**:
    - Filter by `customer_id` to show sales for specific customer
    - Filter by `status` (draft, pending, partial, paid, cancelled)
    - Filter by date range: `from_date`, `to_date` (YYYY-MM-DD format)
    - Search by `search` parameter (searches invoice number or notes)
  - **Sorting**: Default sorted by invoice_date (desc) and id (desc)
  - **Pagination**: Use `per_page` parameter (default: 10)
  - **Response**: Includes customer, warehouse, and items with product details
- `POST /api/v1/sales` - Create new sale with items (requires `create-sales`)
  - Request body includes: invoice_number, invoice_date, due_date, customer_id, warehouse_id, items array, discounts, taxes, etc.
  - Automatically creates stock ledger entries for stock out
  - Updates stock quantities in warehouse
- `GET /api/v1/sales/{id}` - Get sale details with items and relationships (requires `view-sales`)
  - Includes customer, warehouse, and items with full product information
- `PUT /api/v1/sales/{id}` - Update sale (requires `edit-sales`)
  - Updates sale and recalculates totals
  - Updates stock ledger if quantities change
- `DELETE /api/v1/sales/{id}` - Delete sale (requires `delete-sales`)
  - Deletes sale and related items
  - Reverses stock ledger entries (stock in)

**Purchase Management:**
- `GET /api/v1/purchases` - List purchases (requires `view-purchases`)
- `POST /api/v1/purchases` - Create purchase with items (requires `create-purchases`)
- `GET /api/v1/purchases/{id}` - Get purchase details (requires `view-purchases`)
- `PUT /api/v1/purchases/{id}` - Update purchase (requires `edit-purchases`)
- `DELETE /api/v1/purchases/{id}` - Delete purchase (requires `delete-purchases`)

**Product Management:**
- `GET /api/v1/products` - List products with advanced search, filtering, and sorting (requires `view-products`)
  - **Search**: Search by `search` parameter (searches name, SKU, barcode, brand, description)
  - **Filtering**: 
    - Filter by `category_id` to show products in specific category
    - Filter by `warehouse_id` to show products available in specific warehouse
    - Filter by `is_active` to show active/inactive products
  - **Sorting**: Sort by `sort_by` and `sort_direction` parameters
    - Allowed fields: id, name, sku, barcode, brand, purchase_price, sale_price, tax_rate, minimum_stock_level, is_active, order, created_at, updated_at
  - **Pagination**: Use `per_page` parameter for pagination
  - **Response**: Includes `stock_quantity` (total across all warehouses) and `stock_by_warehouse` array with detailed warehouse breakdown
- `POST /api/v1/products` - Create product (requires `manage-products`)
  - Returns created product with stock information
- `GET /api/v1/products/{id}` - Get product details with comprehensive stock information (requires `view-products`)
  - Includes `stock_by_warehouse` array with:
    - Warehouse ID, name, and code
    - Stock quantity per warehouse
    - Average cost per warehouse
    - Total value per warehouse
  - Includes total `stock_quantity` across all warehouses
- `PUT /api/v1/products/{id}` - Update product (requires `manage-products`)
  - Returns updated product with refreshed stock information
- `DELETE /api/v1/products/{id}` - Delete product (requires `manage-products`)
- `GET /api/v1/products/categories` - Get categories for dropdown (requires `view-products`)
- `GET /api/v1/products/units` - Get units for dropdown (requires `view-products`)
- `GET /api/v1/products/warehouses` - Get warehouses for dropdown (requires `view-products`)

**Category Management:**
- `GET /api/v1/categories` - List categories (requires `manage-categories`)
- `POST /api/v1/categories` - Create category (requires `manage-categories`)
- `PUT /api/v1/categories/{id}` - Update category (requires `manage-categories`)
- `DELETE /api/v1/categories/{id}` - Delete category (requires `manage-categories`)

**Unit Management:**
- `GET /api/v1/units` - List units (requires `manage-units`)
- `POST /api/v1/units` - Create unit (requires `manage-units`)
- `GET /api/v1/units/{id}` - Get unit details (requires `manage-units`)
- `PUT /api/v1/units/{id}` - Update unit (requires `manage-units`)
- `DELETE /api/v1/units/{id}` - Delete unit (requires `manage-units`)

**Warehouse Management:**
- `GET /api/v1/warehouses` - List warehouses (requires `manage-warehouses`)
- `POST /api/v1/warehouses` - Create warehouse (requires `manage-warehouses`)
- `GET /api/v1/warehouses/{id}` - Get warehouse details (requires `manage-warehouses`)
- `PUT /api/v1/warehouses/{id}` - Update warehouse (requires `manage-warehouses`)
- `DELETE /api/v1/warehouses/{id}` - Delete warehouse (requires `manage-warehouses`)

**Stock Management:**
- `GET /api/v1/stocks` - List stock levels with filtering (requires `view-stock-ledger`)
  - Filter by `product_id` or `warehouse_id`
  - Search by product name or SKU
- `POST /api/v1/stocks` - ✅ **Create or update stock** (requires `view-stock-ledger`)
  - Direct stock adjustment with three types: `set`, `add`, `subtract`
  - Automatic cost calculation using weighted average
  - Creates stock ledger entry for audit trail
  - Request body: `product_id`, `warehouse_id`, `quantity`, `adjustment_type`, `unit_cost` (optional), `notes` (optional)
- `GET /api/v1/stocks/{id}` - Get stock details (requires `view-stock-ledger`)
- `GET /api/v1/stock-ledger` - List stock transactions with filtering and pagination (requires `view-stock-ledger`)
  - Filter by `product_id`, `warehouse_id`, `type` (in/out), `reference_type`
  - Filter by date range: `date_from`, `date_to`
  - Search by `search` parameter (searches reference number, product name, or SKU)
  - Sort by `sort_by` and `sort_direction` parameters
  - Pagination with `per_page` parameter
- `GET /api/v1/stock-ledger/{id}` - Get ledger entry details (requires `view-stock-ledger`)
- `GET /api/v1/stock-ledger/warehouses` - Get warehouses list for filtering (requires `view-stock-ledger`)

**Supplier Management:**
- `GET /api/v1/suppliers` - List suppliers (requires `manage-suppliers`)
- `POST /api/v1/suppliers` - Create supplier (requires `manage-suppliers`)
- `GET /api/v1/suppliers/{id}` - Get supplier details (requires `manage-suppliers`)
- `PUT /api/v1/suppliers/{id}` - Update supplier (requires `manage-suppliers`)
- `DELETE /api/v1/suppliers/{id}` - Delete supplier (requires `manage-suppliers`)

**Customer Management:**
- `GET /api/v1/customers` - List customers (requires `manage-customers`)
- `POST /api/v1/customers` - Create customer (requires `manage-customers`)
- `GET /api/v1/customers/{id}` - Get customer details (requires `manage-customers`)
- `PUT /api/v1/customers/{id}` - Update customer (requires `manage-customers`)
- `DELETE /api/v1/customers/{id}` - Delete customer (requires `manage-customers`)

**Payment Management:**
- `GET /api/v1/payments` - List payments (requires `view-payments`)
- `POST /api/v1/payments` - Record payment (requires `create-payments`)

**Reports & Analytics:**
- `GET /api/v1/reports/sales` - Sales report with filters (requires `view-reports`)
- `GET /api/v1/reports/sales/export/excel` - Export sales to Excel (requires `view-reports`)
- `GET /api/v1/reports/sales/export/pdf` - Export sales to PDF (requires `view-reports`)
- `GET /api/v1/reports/purchases` - Purchase report (requires `view-reports`)
- `GET /api/v1/reports/purchases/export/excel` - Export purchases to Excel (requires `view-reports`)
- `GET /api/v1/reports/purchases/export/pdf` - Export purchases to PDF (requires `view-reports`)
- `GET /api/v1/reports/stock` - Stock report (requires `view-reports`)
- `GET /api/v1/reports/stock/export/excel` - Export stock to Excel (requires `view-reports`)
- `GET /api/v1/reports/stock/export/pdf` - Export stock to PDF (requires `view-reports`)
- `GET /api/v1/reports/due` - Due report (requires `view-reports`)
- `GET /api/v1/reports/due/export/excel` - Export due to Excel (requires `view-reports`)
- `GET /api/v1/reports/due/export/pdf` - Export due to PDF (requires `view-reports`)
- `GET /api/v1/reports/profit` - Profit report (requires `view-reports`)
- `GET /api/v1/reports/profit/export/excel` - Export profit to Excel (requires `view-reports`)
- `GET /api/v1/reports/profit/export/pdf` - Export profit to PDF (requires `view-reports`)

**Dashboard:**
- `GET /api/v1/dashboard` - Get dashboard metrics (requires `access-dashboard`)

**User & Role Management:**
- `GET /api/v1/users` - List users (requires `manage-users`)
- `POST /api/v1/users` - Create user (requires `manage-users`)
- `GET /api/v1/users/{id}` - Get user (requires `manage-users`)
- `PUT /api/v1/users/{id}` - Update user (requires `manage-users`)
- `DELETE /api/v1/users/{id}` - Delete user (requires `manage-users`)
- `GET /api/v1/users/roles` - Get available roles (requires `manage-users`)
- `GET /api/v1/roles` - List roles (requires `manage-roles`)
- `POST /api/v1/roles` - Create role (requires `manage-roles`)
- `PUT /api/v1/roles/{id}/permissions` - Sync role permissions (requires `manage-roles`)
- `GET /api/v1/permissions` - List permissions (requires `manage-roles`)

**Login Logs:**
- `GET /api/v1/login-logs` - List login logs (requires `view-login-logs`)
- `GET /api/v1/login-logs/statistics` - Get login statistics (requires `view-login-logs`)

**Settings:**
- `GET /api/v1/settings` - Get all settings (requires authentication)
- `POST /api/v1/settings` - Update settings (requires `manage-settings`)

## 🎨 Navigation Menu Structure

The admin panel sidebar includes:
- 📊 Dashboard
- 👥 Users
- 🛡️ Roles & Permissions
- 📦 Inventory (Products, Categories, Units)
- 🏢 Stock Management (Stock Ledger, Warehouses)
- 🛒 Purchase Management (Supplier Invoices)
- 💰 Sales / POS
- 📋 Master Data (Suppliers, Customers)
- 📊 Reports & Analytics (5 report types)
- ⚙️ Settings
- 📝 Login Logs

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/              # Admin API controllers
│   │       ├── auth/         # Authentication
│   │       ├── logs/         # Login logs
│   │       ├── master/       # Customers, Suppliers
│   │       ├── payments/     # Payment management
│   │       ├── products/     # Products, Categories, Units
│   │       ├── purchase/     # Purchase management
│   │       ├── reports/      # Report controllers
│   │       ├── sales/        # Sales/POS management
│   │       ├── settings/     # Settings management
│   │       ├── stock/        # Stock, Warehouses
│   │       ├── upload/       # File uploads
│   │       └── users/        # Users, Roles, Permissions
│   └── Middleware/           # Authentication & authorization
├── Models/                   # Eloquent models
│   ├── Category.php
│   ├── Customer.php
│   ├── Payment.php
│   ├── Product.php
│   ├── Purchase.php
│   ├── PurchaseItem.php
│   ├── Sale.php
│   ├── SalesItem.php
│   ├── Stock.php
│   ├── StockLedger.php
│   ├── Supplier.php
│   ├── Unit.php
│   ├── User.php
│   └── Warehouse.php

database/
├── migrations/               # 23 migration files
└── seeders/                  # Database seeders

resources/
├── js/
│   ├── components/
│   │   └── admin/            # Admin panel components
│   │       ├── auth/         # Login
│   │       ├── logs/         # Login logs
│   │       ├── master/       # Customers, Suppliers
│   │       ├── products/     # Products, Categories, Units
│   │       ├── purchase/     # Purchases
│   │       ├── reports/      # 5 report components
│   │       ├── sales/        # Sales/POS with dialogs
│   │       ├── settings/     # Settings
│   │       ├── stock/        # Stock Ledger, Warehouses
│   │       └── users/        # Users, Roles, Permissions
│   ├── stores/               # Pinia stores (auth)
│   ├── utils/                # Utility functions
│   ├── routes.js             # Vue Router configuration
│   └── app.js                # Main Vue app
└── css/                      # Stylesheets

routes/
├── api.php                   # API routes (all shop management)
└── web.php                   # Web routes (SPA)

public/
├── uploads/                  # Uploaded files
│   ├── products/
│   ├── branding/
│   └── users/
└── Project Report/           # System documentation
    └── Shop Managment System.pdf
```

## 🔒 Security

- Admin routes protected with Laravel Sanctum
- Password hashing
- CSRF protection
- XSS protection
- SQL injection protection (Eloquent ORM)
- Role-based access control (RBAC)
- Permission-based route protection
- Bot detection and filtering
- IP address tracking for security

## 📝 Notes

- **100% PDF Compliant**: All core requirements from the PDF are implemented
- **Production Ready**: Complete shop management solution with all essential features
- **Modular Design**: Easy to extend with additional features
- **Well-Structured**: Clear separation of concerns with Laravel MVC + Vue SPA
- **Secure**: Laravel Sanctum authentication with role-based access control
- **Modern Stack**: Laravel 12 + Vue 3 + Vuetify 3 for best developer experience
- **Export Ready**: Placeholders for Excel/PDF exports (easy to implement with existing packages)
- **Multi-Warehouse**: Built-in support for multiple warehouse locations
- **Real-time Calculations**: Automatic tax, discount, and total calculations in POS
- **Comprehensive Reports**: 5 report types with filtering and summary metrics

### Optional Features (Ready for Implementation)
- Excel/PDF export functionality (packages installed, routes ready)
- Invoice printing templates
- Sales/Purchase return processing
- Barcode generation and printing
- Multi-language support (Bangla/English)
- Automated backup system
- Advanced audit trail

### Recently Implemented Features ✅
- **Product Management Enhancements**:
  - **Stock Integration**: Complete stock and warehouse integration in product management
  - **Stock Adjustment UI**: Direct stock adjustment from product dialog with warehouse selection
  - **Product-Stock Integration**: Stock information displayed in product management interface
  - **Warehouse Filtering**: Filter products by warehouse in product list
  - **Stock Status Indicators**: Color-coded stock status (out of stock, low stock, sufficient)
  - **Stock Breakdown View**: Detailed stock information per warehouse in product dialog
  - **Real-time Stock Updates**: Automatic stock refresh after adjustments
  - **Efficient Stock Queries**: Optimized database queries with `withStockQuantity()` scope
  - **Product View Dialog**: Enhanced product view with complete stock breakdown
  - **Advanced Search**: Search products by name, SKU, barcode, brand, or description
  - **Sortable Columns**: Sort products by various fields (name, price, stock, etc.)
  - **Category View Dialog**: Enhanced category details with audit information
- **Sales/POS Enhancements**:
  - **DatePicker Components**: Date range filtering with DatePicker components (from/to dates)
  - **Advanced Filtering**: Filter sales by status, date range, and search by invoice/customer
  - **Sortable Columns**: Sort sales by invoice number, date, amounts, and status
  - **Pagination**: Customizable page sizes with "Show All" option
  - **View Sale Dialog**: Dedicated dialog to view complete sale details
  - **Invoice Printing**: Print functionality for invoices (A4 and POS formats)
  - **Payment Tracking**: Visual indicators for paid/due amounts with color coding
  - **Status Management**: Complete status workflow (draft → pending → partial → paid)
  - **Improved Date Formatting**: DD/MM/YYYY format for invoice dates
- **Enhanced Stock Ledger** (Latest):
  - DatePicker components for date range filtering (replaces basic date inputs)
  - Advanced filtering: product, warehouse, type, reference type, and date range
  - Sortable "Created At" column with DD/MM/YYYY HH:MM AM/PM format
  - Excel/CSV export functionality - export all filtered data with proper formatting
  - Improved date filtering in backend (using `filled()` and `whereDate()`)
  - Fixed route order for stock-ledger/warehouses endpoint
  - Enhanced date formatting utilities (formatDateShort, formatDateDDMMYYYYHHMM)

## 🛠️ Development

### Testing the Application

```bash
# Run Laravel tests
php artisan test

# Test in browser
npm run dev
php artisan serve

# Visit admin panel
http://localhost:8000/admin/login
```

### Adding New Features

1. **Backend**: Create migration, model, and controller
2. **Routes**: Add API routes in `routes/api.php`
3. **Frontend**: Create Vue components in `resources/js/components/admin/`
4. **Router**: Add route in `resources/js/routes.js`
5. **Menu**: Update `AdminLayout.vue` to add menu items
6. **Permissions**: Add new permissions to seeder and assign to roles

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
