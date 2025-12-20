<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSaleSeeder extends Seeder
{
    /**
     * Seed 10 products and 100 sale transactions
     */
    public function run(): void
    {
        // Get admin user for created_by
        $adminUser = User::where('email', 'admin@mail.com')->first() ?? User::first();
        
        if (!$adminUser) {
            $this->command->error('No admin user found. Please run AdminUserSeeder first.');
            return;
        }

        // Get existing categories, units, customers, and warehouses
        $categories = Category::all();
        $units = Unit::all();
        $customers = Customer::all();
        $warehouses = Warehouse::all();

        if ($categories->isEmpty() || $units->isEmpty() || $customers->isEmpty() || $warehouses->isEmpty()) {
            $this->command->error('Required data not found. Please run CurrentProjectSeeder first.');
            return;
        }

        // Seed 10 Products
        $this->command->info('Seeding 10 products...');
        $products = [];
        $productNames = [
            'Laptop Dell Inspiron 15',
            'Samsung Galaxy S23',
            'iPhone 15 Pro',
            'Wireless Mouse Logitech',
            'Mechanical Keyboard RGB',
            'Monitor LG 27 inch',
            'Webcam HD 1080p',
            'USB-C Hub Multiport',
            'SSD 1TB Samsung',
            'RAM 16GB DDR4'
        ];

        for ($i = 0; $i < 10; $i++) {
            $category = $categories->random();
            $unit = $units->random();
            $purchasePrice = rand(500, 5000);
            $salePrice = $purchasePrice * (1 + (rand(10, 30) / 100)); // 10-30% markup
            $sku = 'SKU-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT);

            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $productNames[$i],
                    'barcode' => 'BC' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'order' => $i + 1,
                    'description' => 'Product description for ' . $productNames[$i],
                    'brand' => ['Dell', 'Samsung', 'Apple', 'Logitech', 'Corsair', 'LG', 'Logitech', 'Anker', 'Samsung', 'Corsair'][$i],
                    'image' => null,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => round($salePrice, 2),
                    'tax_rate' => rand(0, 15),
                    'minimum_stock_level' => rand(5, 20),
                    'is_active' => true,
                    'created_by' => $adminUser->id,
                    'updated_by' => $adminUser->id,
                ]
            );

            $products[] = $product;
            $this->command->info("Created/Updated product: {$product->name}");
        }

        // Convert products array to collection
        $productsCollection = collect($products);

        // Seed 100 Sale Transactions
        $this->command->info('Seeding 100 sale transactions...');
        
        // Get the highest invoice number to continue from
        $lastInvoice = Sale::orderBy('id', 'desc')->first();
        $invoiceCounter = $lastInvoice ? (int) str_replace('INV-', '', $lastInvoice->invoice_number) + 1 : 1;

        for ($i = 0; $i < 100; $i++) {
            $customer = $customers->random();
            $warehouse = $warehouses->random();
            
            // Generate unique invoice number
            do {
                $invoiceNumber = 'INV-' . str_pad($invoiceCounter++, 6, '0', STR_PAD_LEFT);
            } while (Sale::where('invoice_number', $invoiceNumber)->exists());
            
            // Random date within last 6 months
            $invoiceDate = Carbon::now()->subMonths(rand(0, 6))->subDays(rand(0, 30));
            $dueDate = $invoiceDate->copy()->addDays(rand(7, 30));
            
            // Random status
            $statuses = ['draft', 'pending', 'partial', 'paid', 'cancelled'];
            $status = $statuses[array_rand($statuses)];
            
            // Select 1-5 random products for this sale
            $saleProducts = $productsCollection->random(rand(1, min(5, count($products))));
            
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;
            $salesItems = [];

            foreach ($saleProducts as $product) {
                $quantity = rand(1, 10);
                $unitPrice = $product->sale_price;
                $discount = rand(0, 20) > 15 ? rand(50, 500) : 0; // 20% chance of discount
                $tax = ($unitPrice * $quantity - $discount) * ($product->tax_rate / 100);
                $total = ($unitPrice * $quantity) - $discount + $tax;

                $subtotal += ($unitPrice * $quantity);
                $discountAmount += $discount;
                $taxAmount += $tax;

                $salesItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => round($tax, 2),
                    'total' => round($total, 2),
                ];
            }

            $shippingCost = rand(0, 20) > 15 ? rand(50, 200) : 0; // 20% chance of shipping
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCost;

            // Calculate paid and balance based on status
            $paidAmount = 0;
            $balanceAmount = $totalAmount;

            if ($status === 'paid') {
                $paidAmount = $totalAmount;
                $balanceAmount = 0;
            } elseif ($status === 'partial') {
                $paidAmount = $totalAmount * (rand(30, 70) / 100);
                $balanceAmount = $totalAmount - $paidAmount;
            } elseif ($status === 'pending') {
                $paidAmount = 0;
                $balanceAmount = $totalAmount;
            } elseif ($status === 'cancelled') {
                $paidAmount = 0;
                $balanceAmount = 0;
            }

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => $status,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'shipping_cost' => $shippingCost,
                'total_amount' => round($totalAmount, 2),
                'paid_amount' => round($paidAmount, 2),
                'balance_amount' => round($balanceAmount, 2),
                'notes' => rand(0, 10) > 7 ? 'Additional notes for invoice ' . $invoiceNumber : null,
                'created_by' => $adminUser->id,
            ]);

            // Create sales items
            foreach ($salesItems as $item) {
                SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'],
                    'tax' => $item['tax'],
                    'total' => $item['total'],
                ]);
            }

            if (($i + 1) % 10 === 0) {
                $this->command->info("Created {$invoiceNumber} - Progress: " . ($i + 1) . "/100");
            }
        }

        $this->command->info('Successfully seeded 10 products and 100 sale transactions!');
    }
}

