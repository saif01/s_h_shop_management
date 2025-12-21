# Shop Management System - User Guide

A simple, step-by-step guide for using the Shop Management System (SMS) in your daily business operations.

---

## 📋 Table of Contents

1. [System Overview](#1-system-overview)
2. [Getting Started](#2-getting-started)
3. [Daily Operations](#3-daily-operations)
4. [Administrative Tasks](#4-administrative-tasks)
5. [Reports & Analytics](#5-reports--analytics)
6. [Troubleshooting](#6-troubleshooting)
7. [Quick Reference](#7-quick-reference)

---

## 1. System Overview

### 1.1 What is This System?

The Shop Management System helps you manage your entire business in one place:

- **Inventory Management** - Keep track of all your products and stock levels
- **Sales/POS** - Process customer sales quickly and efficiently
- **Purchase Management** - Record purchases from suppliers
- **Customer & Supplier Management** - Maintain contact information and payment records
- **Financial Tracking** - Monitor money owed by customers and to suppliers
- **Reports & Analytics** - Get insights about your business performance

### 1.2 User Roles

The system has three main user types:

1. **Administrator/Owner** - Can access everything in the system
2. **Cashier** - Can process sales, manage customers, and view reports
3. **Storekeeper** - Can manage products, stock, and purchases

---

## 2. Getting Started

### 2.1 Initial Setup for New Shop

When you first start using the system, follow these steps in order:

#### Step 1: Configure Settings
1. Click on **Settings** (⚙️ icon in the sidebar)
2. Enter your shop name and contact information
3. Upload your shop logo (optional)
4. Save the settings

#### Step 2: Create Warehouses
1. Go to **Stock Management → Warehouses**
2. Click **Add Warehouse**
3. Enter:
   - Warehouse name (e.g., "Main Warehouse")
   - Warehouse code (e.g., "WH-001")
   - Address and contact details
   - Select a manager from the user list
4. Click **Save**

#### Step 3: Create Product Categories
1. Go to **Inventory → Categories**
2. Click **Add Category**
3. Enter category name (e.g., "Grocery", "Electronics", "Clothing")
4. Add a description and image if you want (optional)
5. Click **Save**

#### Step 4: Create Units of Measurement
1. Go to **Inventory → Units**
2. Click **Add Unit**
3. Enter unit name and code:
   - Examples: Piece (PCS), Kilogram (KG), Liter (LTR), Box (BOX), Carton (CARTON)
4. Click **Save**

#### Step 5: Add Products
1. Go to **Inventory → Products**
2. Click **Add Product**
3. Fill in the product information:
   - Product Name
   - SKU (Stock Keeping Unit - optional)
   - Barcode (optional)
   - Select Category and Unit
   - Enter Purchase Price (what you pay)
   - Enter Sale Price (what you charge)
   - Set Minimum Stock Level (system will alert you when stock is low)
   - Upload product image (optional)
4. Click **Save**
5. **Important**: After creating the product, click **Stock Adjustment** to add your opening stock

#### Step 6: Add Suppliers
1. Go to **Master Data → Suppliers**
2. Click **Add Supplier**
3. Enter supplier details:
   - Name and code
   - Contact information (phone, email)
   - Address
4. Click **Save**

#### Step 7: Add Customers
1. Go to **Master Data → Customers**
2. Click **Add Customer**
3. Enter customer details:
   - Name and code
   - Contact information (phone, email)
   - Address
   - Opening balance (if customer already owes you money)
4. Click **Save**

---

## 3. Daily Operations

### 3.1 Making a Purchase from Supplier

When you receive stock from a supplier:

**Step 1: Create Purchase Invoice**
1. Go to **Purchase Management → Supplier Invoices**
2. Click **Add Purchase**
3. Fill in **Basic Information**:
   - Select the Supplier
   - Select the Warehouse where stock will be stored
   - Set the Invoice Date
   - Set Due Date (when payment is due - optional)
   - Add Shipping Cost if any
   - Add any notes
4. Go to **Items Tab**:
   - Click **Add Item**
   - Select the Product
   - Enter Quantity received
   - Enter Unit Price (what you paid per unit)
   - Add Discount if any
   - Add Tax if any
   - Repeat for all products received
5. Review the totals (calculated automatically)
6. Click **Save as Draft** (if you want to review later) or **Create Purchase** (to update stock immediately)

**Step 2: Receive Stock (If you saved as Draft)**
1. Find the draft purchase in the list
2. Click **Receive Stock** button
3. The system will automatically:
   - Update stock quantities in the warehouse
   - Record the transaction in stock ledger
   - Update purchase status

**Step 3: Record Payment**
1. Click **Record Payment** on the purchase
2. Enter:
   - Payment Amount
   - Payment Method (Cash, Bank Transfer, Cheque, etc.)
   - Payment Date
   - Reference number (cheque number, transaction ID, etc.)
3. Click **Save**
4. The purchase status will update automatically:
   - **Partial** if you paid some amount
   - **Paid** if you paid the full amount

**Understanding Purchase Status:**
- **Draft** - Created but stock not received yet (you can edit or delete)
- **Pending** - Stock received but no payment made
- **Partial** - Stock received, partial payment made
- **Paid** - Fully paid
- **Cancelled** - Purchase was cancelled

### 3.2 Making a Sale to Customer

**Step 1: Create Sale Invoice**
1. Go to **Sales / POS**
2. Click **Add Sale**
3. **Add Products**:
   - Search for products by typing the name, SKU, or scanning barcode
   - Click on the product to add it to cart
   - Enter the quantity
   - The system will check if stock is available
   - Repeat for all items
4. **Select Customer**:
   - Choose a customer from the list (or leave blank for Walk-in customer)
   - You can also search for or create a new customer
5. **Select Warehouse**:
   - Choose the warehouse where stock will be taken from
6. **Invoice Details**:
   - Invoice Date (defaults to today)
   - Due Date (if customer will pay later)
   - Add discount if any
   - Add shipping cost if any
   - Add notes
7. **Payment Information**:
   - Enter how much the customer paid
   - Select Payment Method
   - Remaining amount will be shown as Due
8. Review all totals (calculated automatically)
9. Click **Save Sale**

**What Happens Automatically:**
- Stock is reduced in the selected warehouse
- Transaction is recorded in stock ledger
- Customer balance is updated (if it's a credit sale)
- Sale record is created

**Step 2: View or Edit Sale**
1. Go to **Sales / POS** list
2. Find the sale you want to view
3. Click **View** to see all details
4. Click **Edit** to make changes (only if the sale status allows)

**Step 3: Collect Payment (For Credit Sales)**
1. Go to **Reports → Due Reports**
2. Filter by customer name
3. Click on the customer's due amount
4. Click **Record Payment**
5. Enter payment details and save

**Understanding Sale Status:**
- **Draft** - Created but not finalized
- **Pending** - Created, no payment received
- **Partial** - Some payment received
- **Paid** - Fully paid
- **Cancelled** - Sale was cancelled

### 3.3 Adjusting Stock

Sometimes you need to correct stock levels (e.g., found extra items, damaged items removed):

**Step 1: Access Stock Adjustment**
1. Go to **Inventory → Products**
2. Find the product
3. Click **Stock Adjustment** button
   - OR go to **Stock Management → Stock Ledger** and use stock adjustment there

**Step 2: Make the Adjustment**
1. Select the **Warehouse**
2. Choose **Adjustment Type**:
   - **Set Quantity** - Set exact stock (e.g., set to 100 pieces)
   - **Add Stock** - Add to existing (e.g., add 50 pieces)
   - **Subtract Stock** - Remove from existing (e.g., remove 20 pieces)
3. Enter the **Quantity**
4. Enter **Unit Cost** (optional - helps with cost calculations)
5. Add **Notes** explaining why (e.g., "Damaged items removed", "Stock found during audit")
6. Click **Save**

**What Happens:**
- Stock quantity is updated
- Transaction is recorded in stock ledger
- Cost is recalculated (if you entered cost)

### 3.4 Viewing Stock Levels

You can check stock in three ways:

**Option 1: View Stock Per Product**
1. Go to **Inventory → Products**
2. Click on any product
3. See **Stock by Warehouse** section showing:
   - Quantity in each warehouse
   - Average cost per warehouse
   - Total value per warehouse

**Option 2: View Stock Ledger**
1. Go to **Stock Management → Stock Ledger**
2. Apply filters:
   - Date range
   - Product name
   - Warehouse
   - Type (Stock In or Stock Out)
3. View all stock movements
4. Export to Excel if needed

**Option 3: View Stock Report**
1. Go to **Reports → Stock Reports**
2. Select report type:
   - Current Stock
   - Stock Ledger
   - Low Stock
3. Apply filters (warehouse, category)
4. View summary and detailed data
5. Export to PDF or Excel

### 3.5 Collecting Customer Payments

You can record customer payments in three ways:

**Method 1: From Due Reports**
1. Go to **Reports → Due Reports**
2. Filter by Customer (or view all)
3. See all customer due amounts
4. Click on a customer
5. Click **Record Payment**
6. Enter payment details and save

**Method 2: From Customer Profile**
1. Go to **Master Data → Customers**
2. Find the customer
3. View customer details
4. See current balance
5. Click **Record Payment**

**Method 3: From Sales List**
1. Go to **Sales / POS**
2. Find the sale with due amount
3. Click **Record Payment**
4. Enter payment details
5. Save

---

## 4. Administrative Tasks

### 4.1 Managing Users

**Create New User:**
1. Go to **Users** (👥 icon in sidebar)
2. Click **Add User**
3. Fill in:
   - Name and Email
   - Password
   - Role (Administrator, Cashier, or Storekeeper)
   - Status (Active or Inactive)
4. Click **Save**

**Assign Permissions:**
1. Go to **Roles & Permissions → Roles**
2. Select the role you want to modify
3. Click **Permissions** button
4. Check or uncheck permissions as needed
5. Click **Save**

**Common Roles:**
- **Cashier** - Can access dashboard, create sales, manage customers, record payments, view reports
- **Storekeeper** - Can manage products, stock, purchases, suppliers, and view reports

### 4.2 Managing Warehouses

**Add New Warehouse:**
1. Go to **Stock Management → Warehouses**
2. Click **Add Warehouse**
3. Enter:
   - Name (e.g., "Branch Warehouse")
   - Code (e.g., "BR-WH-001")
   - Address details
   - Contact information
   - Manager (select from users)
   - Status (Active or Inactive)
4. Click **Save**

### 4.3 Managing Categories

**Add Category:**
1. Go to **Inventory → Categories**
2. Click **Add Category**
3. Enter:
   - Category Name
   - Description (optional)
   - Image (optional)
   - Status (Active or Inactive)
4. Click **Save**

### 4.4 System Settings

**Update Settings:**
1. Go to **Settings** (⚙️ icon)
2. Update:
   - **General**: Shop name, contact information
   - **Branding**: Logo, favicon
   - **Footer**: Copyright text
   - **Email**: Email configuration (optional)
3. Click **Save**

---

## 5. Reports & Analytics

### 5.1 Dashboard Overview

The dashboard shows you everything you need to know about your business at a glance:

**AI-Powered Business Insights** (Top Section):
- **Sales Forecast** - Predicts your expected sales for next week or month
- **Anomaly Detection** - Alerts you to:
  - Sudden sales drops
  - Low stock situations
  - High customer dues
  - Negative profit warnings
- **Performance Score** - Overall business health score (0-100) with color indicators:
  - Green (85-100): Excellent
  - Blue (70-84): Very Good
  - Yellow (50-69): Good
  - Red (0-49): Needs Improvement
- **Smart Recommendations** - Actionable advice like:
  - Boost sales performance
  - Restock inventory items
  - Optimize profit margins
  - Collect outstanding payments
- **Trend Insights** - Shows sales growth, profit margins, and customer activity

**Key Metrics:**
- Today's Sales (with growth percentage)
- Month Sales (with growth percentage)
- Profit (Revenue minus Cost)
- Customer Due (money customers owe you)
- Purchases and Supplier Due
- Product Count and Low Stock Items

**Visualizations:**
- Sales Trend Chart (view 7 days or 30 days)
- Top 5 Products by Sales
- Top 5 Customers by Sales

**Alerts:**
- Low Stock Items list
- Recent Sales transactions

### 5.2 Sales Reports

**Generate Sales Report:**
1. Go to **Reports → Sales Reports**
2. Set filters:
   - **Date Range**: Select From Date and To Date
   - **Customer**: Filter by specific customer (optional)
   - **Status**: Filter by sale status (optional)
3. Click **Generate Report**
4. View:
   - Summary cards (Total Sales, Paid, Due, Number of Invoices)
   - Detailed sales table
   - Top selling products
5. **Export**: Click "Export PDF" to download as PDF file

### 5.3 Purchase Reports

**Generate Purchase Report:**
1. Go to **Reports → Purchase Reports**
2. Set filters:
   - **Date Range**: Select dates
   - **Supplier**: Filter by supplier (optional)
   - **Status**: Filter by purchase status (optional)
3. Click **Generate Report**
4. View summary and detailed data
5. Export to PDF or Excel

### 5.4 Stock Reports

**Generate Stock Report:**
1. Go to **Reports → Stock Reports**
2. Select **Report Type**:
   - Current Stock
   - Stock Ledger
   - Low Stock
3. Set filters:
   - **Warehouse**: Filter by warehouse (optional)
   - **Category**: Filter by category (optional)
   - **Low Stock Only**: Check to show only low stock items
4. Click **Generate Report**
5. View summary and detailed data
6. Export to PDF or Excel

### 5.5 Due Reports

**Generate Due Report:**
1. Go to **Reports → Due Reports**
2. Select **Party Type**:
   - Customer Due (money customers owe you)
   - Supplier Due (money you owe suppliers)
3. Set filters:
   - **Party**: Filter by specific customer/supplier (optional)
   - **Overdue Only**: Check to show only overdue amounts
4. Click **Generate Report**
5. View summary and due details
6. Export to PDF or Excel

### 5.6 Profit Reports

**Generate Profit Report:**
1. Go to **Reports → Profit Reports**
2. Set filters:
   - **Date Range**: Select dates
   - **Category**: Filter by category (optional)
   - **Group By**: Choose how to group data
     - Daily
     - Weekly
     - Monthly
     - By Product
     - By Category
3. Click **Generate Report**
4. View:
   - Summary (Revenue, Cost, Profit, Profit Margin)
   - Detailed profit data
5. Export to PDF or Excel

---

## 6. Troubleshooting

### 6.1 Regular Maintenance Tasks

**Daily:**
- Check dashboard for low stock alerts
- Review recent sales and purchases
- Monitor customer and supplier dues

**Weekly:**
- Review stock levels
- Generate sales and profit reports
- Check for any issues

**Monthly:**
- Generate all monthly reports (Sales, Purchase, Profit, Stock)
- Review and update product prices if needed
- Review user access and permissions

### 6.2 Common Issues & Solutions

**Issue: Stock Not Updating After Purchase**

**Solution:**
1. Check if purchase status is "Draft"
2. If it's Draft, click "Receive Stock" button
3. If not Draft, check the Stock Ledger to see if entries were created
4. Make sure you selected the correct warehouse

**Issue: Can't Add Product to Sale**

**Solution:**
1. Check if the product has stock in the selected warehouse
2. Make sure the product is Active (not Inactive)
3. Check that you selected the correct warehouse in the sale form
4. Review Stock Ledger to see recent stock movements

**Issue: Dashboard Not Loading**

**Solution:**
1. Refresh the page (press F5 or click refresh button)
2. Make sure you're logged in
3. Try logging out and logging back in
4. Contact your system administrator if problem persists

**Issue: Permission Denied Error**

**Solution:**
1. Check your user role - you may not have permission for that action
2. Logout and login again
3. Contact administrator to check your permissions

**Issue: PDF Export Not Working**

**Solution:**
1. Make sure you have a stable internet connection
2. Try again after a few moments
3. Check if your browser allows downloads
4. Contact system administrator if problem continues

---

## 7. Quick Reference

### 7.1 Keyboard Shortcuts

**In Sales/POS:**
- Start typing product name/SKU to search
- Click product or press Enter to add to cart
- Type number and press Enter for quick quantity entry

### 7.2 Understanding Statuses

**Purchase Status:**
- **Draft** - Created but stock not received (you can edit or delete)
- **Pending** - Stock received, no payment made
- **Partial** - Stock received, some payment made
- **Paid** - Fully paid
- **Cancelled** - Purchase was cancelled

**Sale Status:**
- **Draft** - Created but not finalized
- **Pending** - Created, no payment received
- **Partial** - Some payment received
- **Paid** - Fully paid
- **Cancelled** - Sale was cancelled

**Stock Status Indicators:**
- **Red** - Out of stock (quantity is 0)
- **Yellow** - Low stock (below minimum level)
- **Green** - Sufficient stock

### 7.3 Currency and Date Formats

**Currency:**
- All money amounts use BDT (Bangladeshi Taka)
- Symbol: ৳
- Format: ৳12,345.67 (with commas and 2 decimal places)

**Date Formats:**
- Display: DD/MM/YYYY (e.g., 25/12/2024)
- With Time: DD/MM/YYYY HH:MM AM/PM (e.g., 25/12/2024 02:30 PM)

### 7.4 Workflow Diagrams

**Purchase to Stock Flow:**
```
1. Create Purchase (Draft)
   ↓
2. Add Items (Product, Quantity, Price)
   ↓
3. Save as Draft OR Create Purchase
   ↓
4. If Draft: Click "Receive Stock"
   OR
   If Created: Stock auto-updated
   ↓
5. Stock Updated in Warehouse
   ↓
6. (Optional) Record Payment
   ↓
7. Purchase Status Updated
```

**Sales Flow:**
```
1. Create Sale / Use POS
   ↓
2. Select Warehouse
   ↓
3. Add Products to Cart
   (System checks stock)
   ↓
4. Select Customer (or Walk-in)
   ↓
5. Enter Payment Details
   ↓
6. Save Sale
   ↓
7. Stock Reduced in Warehouse
   ↓
8. Customer Balance Updated (if credit sale)
   ↓
9. Sale Status Set
```

**Stock Adjustment Flow:**
```
1. Go to Product or Stock Ledger
   ↓
2. Click "Stock Adjustment"
   ↓
3. Select Warehouse
   ↓
4. Choose Adjustment Type
   (Set Quantity / Add Stock / Subtract Stock)
   ↓
5. Enter Quantity & Optional Cost
   ↓
6. Add Notes
   ↓
7. Save
   ↓
8. Stock Updated
```

### 7.5 Best Practices

**Data Entry:**
1. Always verify stock before creating sales
2. Use correct warehouse when creating purchases/sales
3. Enter accurate prices for correct profit calculations
4. Set minimum stock levels for all products
5. Record payments promptly for accurate due tracking

**Stock Management:**
1. Do regular stock audits to ensure accuracy
2. Use stock adjustments for corrections (don't edit directly)
3. Check low stock alerts daily
4. Review stock ledger regularly for any discrepancies

**User Management:**
1. Assign appropriate roles based on job functions
2. Review permissions regularly
3. Deactivate unused accounts
4. Use strong passwords

**Reporting:**
1. Generate reports regularly for business insights
2. Export PDFs for record keeping
3. Compare reports (month-over-month, year-over-year)
4. Use filters effectively for specific analysis

---

## 8. Daily Checklists

### Morning Checklist

- [ ] Check dashboard for low stock alerts
- [ ] Review yesterday's sales
- [ ] Check customer dues
- [ ] Verify system is working properly

### End of Day Checklist

- [ ] Complete all sales entries
- [ ] Record all payments received
- [ ] Update stock if any adjustments needed
- [ ] Generate daily sales report
- [ ] Review tomorrow's purchase orders

### Weekly Checklist

- [ ] Generate weekly reports (Sales, Purchase, Profit)
- [ ] Review stock levels and reorder if needed
- [ ] Check customer/supplier payment status
- [ ] Review and update product prices if needed

### Monthly Checklist

- [ ] Generate all monthly reports
- [ ] Review profit margins
- [ ] Check slow-moving products
- [ ] Review user access and permissions
- [ ] Full system backup (contact administrator)

---

**End of User Guide**

*This guide helps you use the Shop Management System effectively. If you need help, contact your system administrator.*
