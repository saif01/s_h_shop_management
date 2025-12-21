<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurrentProjectSeeder extends Seeder
{
    /**
     * Seed a minimal shop setup: units, categories, warehouse, sample products, stock, suppliers/customers, one sale.
     */
    public function run(): void
    {
        // Get admin user for created_by and updated_by
        $adminUser = User::where('email', 'admin@mail.com')->first() ?? User::first();

        // Units
        $units = [
            ['name' => 'Piece', 'code' => 'PCS'],
            ['name' => 'Kilogram', 'code' => 'KG'],
            ['name' => 'Liter', 'code' => 'LTR'],
        ];
        foreach ($units as $unit) {
            Unit::updateOrCreate(['code' => $unit['code']], $unit);
        }

        // Categories
        $categories = [
            ['name' => 'Grocery', 'slug' => 'grocery', 'order' => 1],
            ['name' => 'Cosmetics', 'slug' => 'cosmetics', 'order' => 2],
            ['name' => 'Electronics', 'slug' => 'electronics', 'order' => 3],
        ];
        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'is_active' => true,
                    'created_by' => $adminUser?->id,
                    'updated_by' => $adminUser?->id,
                ])
            );
        }

        // Warehouses
        $warehousesData = [
            [
                'code' => 'WH-001',
                'name' => 'Main Warehouse',
                'address' => '123 Main Street, Business District',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'postal_code' => '1200',
                'phone' => '01700001001',
                'email' => 'warehouse1@sms.com',
                'is_active' => true,
            ],
            [
                'code' => 'WH-002',
                'name' => 'Secondary Warehouse',
                'address' => '456 Industrial Area, Port Zone',
                'city' => 'Chittagong',
                'state' => 'Chittagong',
                'country' => 'Bangladesh',
                'postal_code' => '4000',
                'phone' => '01700001002',
                'email' => 'warehouse2@sms.com',
                'is_active' => true,
            ],
            [
                'code' => 'WH-003',
                'name' => 'Distribution Center',
                'address' => '789 Commerce Road, Export Zone',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'postal_code' => '1212',
                'phone' => '01700001003',
                'email' => 'warehouse3@sms.com',
                'is_active' => true,
            ],
        ];

        $warehouses = [];
        foreach ($warehousesData as $warehouseData) {
            $warehouses[] = Warehouse::firstOrCreate(
                ['code' => $warehouseData['code']],
                $warehouseData
            );
        }
        $warehouse = $warehouses[0]; // Use first warehouse for sample stock and sale

        // Suppliers
        $suppliersData = [
            [
                'code' => 'SUP-001',
                'name' => 'ABC Trading Company',
                'company_name' => 'ABC Trading Company Ltd.',
                'email' => 'contact@abctrading.com',
                'phone' => '01700000001',
                'mobile' => '01700000001',
                'address' => '123 Business Street, Motijheel',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'postal_code' => '1200',
                'tax_id' => 'TAX-001',
                'opening_balance' => 0,
                'current_balance' => 50000.00,
                'notes' => 'Primary supplier for electronics and gadgets',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'XYZ Wholesale Mart',
                'company_name' => 'XYZ Wholesale Mart Pvt. Ltd.',
                'email' => 'info@xyzmart.com',
                'phone' => '01700000002',
                'mobile' => '01700000002',
                'address' => '456 Market Road, Gulshan',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'postal_code' => '1212',
                'tax_id' => 'TAX-002',
                'opening_balance' => 0,
                'current_balance' => 25000.00,
                'notes' => 'Bulk supplier for grocery items',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'Global Imports Ltd',
                'company_name' => 'Global Imports Limited',
                'email' => 'sales@globalimports.com',
                'phone' => '01700000003',
                'mobile' => '01700000003',
                'address' => '789 Import Zone, Chittagong',
                'city' => 'Chittagong',
                'state' => 'Chittagong',
                'country' => 'Bangladesh',
                'postal_code' => '4000',
                'tax_id' => 'TAX-003',
                'opening_balance' => 0,
                'current_balance' => 75000.00,
                'notes' => 'International goods supplier',
                'is_active' => true,
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $supplierData) {
            $suppliers[] = Supplier::firstOrCreate(
                ['code' => $supplierData['code']],
                array_merge($supplierData, [
                    'created_by' => $adminUser?->id,
                ])
            );
        }
        $supplier = $suppliers[0]; // Use first supplier for sample sale

        // Customers
        $customersData = [
            [
                'name' => 'Walk-in Customer',
                'code' => 'CUS-001',
                'company_name' => null,
                'email' => null,
                'phone' => '01800000001',
                'mobile' => '01800000001',
                'address' => 'N/A',
                'city' => null,
                'state' => null,
                'country' => null,
                'postal_code' => null,
                'tax_id' => null,
                'opening_balance' => 0,
                'current_balance' => 0,
                'notes' => 'Default walk-in customer',
                'is_active' => true,
            ],
            [
                'name' => 'Ahmed Rahman',
                'code' => 'CUS-002',
                'company_name' => 'Rahman Enterprises',
                'email' => 'ahmed.rahman@email.com',
                'phone' => '01800000002',
                'mobile' => '01800000002',
                'address' => '12 Residential Area, Dhanmondi',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'postal_code' => '1205',
                'tax_id' => null,
                'opening_balance' => 0,
                'current_balance' => 15000.00,
                'notes' => 'Regular customer with credit account',
                'is_active' => true,
            ],
            [
                'name' => 'Fatima Begum',
                'code' => 'CUS-003',
                'company_name' => 'Fatima Retail Store',
                'email' => 'fatima@retailstore.com',
                'phone' => '01800000003',
                'mobile' => '01800000003',
                'address' => '45 Shop Road, Uttara',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'postal_code' => '1230',
                'tax_id' => 'CUS-TAX-003',
                'opening_balance' => 0,
                'current_balance' => 8000.00,
                'notes' => 'Retail store owner, regular bulk orders',
                'is_active' => true,
            ],
        ];

        $customers = [];
        foreach ($customersData as $customerData) {
            $customers[] = Customer::firstOrCreate(
                ['code' => $customerData['code']],
                array_merge($customerData, [
                    'created_by' => $adminUser?->id,
                ])
            );
        }
        $customer = $customers[0]; // Use first customer for sample sale

        // Products + stock
        $products = [
            [
                'name' => 'Rice 1kg',
                'sku' => 'RICE-1KG',
                'barcode' => '1000001',
                'category_id' => Category::where('slug', 'grocery')->first()->id ?? null,
                'unit_id' => Unit::where('code', 'KG')->first()->id ?? null,
                'order' => 1,
                'purchase_price' => 45,
                'sale_price' => 55,
                'minimum_stock_level' => 5,
            ],
            [
                'name' => 'Shampoo 200ml',
                'sku' => 'SHP-200',
                'barcode' => '1000002',
                'category_id' => Category::where('slug', 'cosmetics')->first()->id ?? null,
                'unit_id' => Unit::where('code', 'LTR')->first()->id ?? null,
                'order' => 2,
                'purchase_price' => 120,
                'sale_price' => 150,
                'minimum_stock_level' => 3,
            ],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, [
                    'is_active' => true,
                    'created_by' => $adminUser?->id,
                    'updated_by' => $adminUser?->id,
                ])
            );

            Stock::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 20, 'average_cost' => $product->purchase_price, 'total_value' => 20 * $product->purchase_price]
            );
        }

        // Sample sale with due tracking
        $product = Product::where('sku', 'RICE-1KG')->first();
        if ($product) {
            $sale = Sale::firstOrCreate(
                ['invoice_number' => 'INV-0001'],
                [
                    'customer_id' => $customer->id,
                    'warehouse_id' => $warehouse->id,
                    'invoice_date' => Carbon::today(),
                    'status' => 'paid',
                    'subtotal' => $product->sale_price * 2,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'shipping_cost' => 0,
                    'total_amount' => $product->sale_price * 2,
                    'paid_amount' => $product->sale_price * 2,
                    'balance_amount' => 0,
                    'created_by' => $adminUser?->id ?? 1,
                ]
            );

            SalesItem::firstOrCreate(
                ['sale_id' => $sale->id, 'product_id' => $product->id],
                [
                    'quantity' => 2,
                    'unit_price' => $product->sale_price,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $product->sale_price * 2,
                ]
            );
        }
    }
}

