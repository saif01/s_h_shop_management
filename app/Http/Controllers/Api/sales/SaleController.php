<?php

namespace App\Http\Controllers\Api\sales;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Stock;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'warehouse', 'items.product'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $sales = $query->paginate($request->get('per_page', 10));

        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $rules = [
            'invoice_number' => 'nullable|string|max:255|unique:sales,invoice_number',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ];

        // Only validate status if it's present in the request and not null/empty
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $rules['status'] = 'required|string|in:draft,pending,partial,paid,cancelled';
        }

        $validated = $request->validate($rules);

        $invoiceNumber = $validated['invoice_number'] ?? Str::upper('INV-' . now()->format('YmdHis') . '-' . Str::random(4));
        $paidAmount = $validated['paid_amount'] ?? 0;
        $shipping = $validated['shipping_cost'] ?? 0;

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($validated['items'] as $item) {
            $lineSubtotal = $item['unit_price'] * $item['quantity'];
            $lineDiscount = $item['discount'] ?? 0;
            $lineTax = $item['tax'] ?? 0;

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;
        }

        $discountAmount = $validated['discount_amount'] ?? $discountTotal;
        $taxAmount = $validated['tax_amount'] ?? $taxTotal;
        $totalAmount = $subtotal - $discountAmount + $taxAmount + $shipping;
        $paidAmount = min($paidAmount, $totalAmount);
        $balance = max($totalAmount - $paidAmount, 0);
        
        // Use provided status or calculate it automatically
        if (isset($validated['status']) && $validated['status'] !== null && $validated['status'] !== '') {
            $status = (string) $validated['status'];
        } else {
            $status = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
        }
        // Ensure status is always a valid string
        $status = (string) $status;
        $isDraft = $status === 'draft';

        $sale = DB::transaction(function () use ($validated, $invoiceNumber, $subtotal, $discountAmount, $taxAmount, $shipping, $totalAmount, $paidAmount, $balance, $status, $isDraft) {
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => $status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shipping,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balance,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                $stock = null;
                
                // Check stock availability and lock for update (skip for draft sales)
                if (!$isDraft) {
                    $stock = Stock::where('product_id', $itemData['product_id'])
                        ->where('warehouse_id', $validated['warehouse_id'])
                        ->lockForUpdate()
                        ->first();

                    $availableQty = $stock?->quantity ?? 0;
                    if ($availableQty < $itemData['quantity']) {
                        abort(422, "Insufficient stock for {$product->name}");
                    }
                }

                $lineSubtotal = $itemData['unit_price'] * $itemData['quantity'];
                $lineDiscount = $itemData['discount'] ?? 0;
                $lineTax = $itemData['tax'] ?? 0;
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $saleItem = SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Skip stock deduction and ledger for draft sales
                if (!$isDraft && $stock) {
                    $oldQty = $stock->quantity;
                    $newQty = $oldQty - $saleItem->quantity;
                    $newQty = max($newQty, 0);
                    $stock->update([
                        'quantity' => $newQty,
                        'total_value' => $newQty * $stock->average_cost,
                    ]);
                    $balanceAfter = $newQty;
                    $valueBefore = $oldQty * $stock->average_cost;
                    $valueAfter = $newQty * $stock->average_cost;
                    $unitCost = $stock->average_cost;

                    StockLedger::create([
                        'product_id' => $saleItem->product_id,
                        'warehouse_id' => $validated['warehouse_id'],
                        'type' => 'out',
                        'reference_type' => 'sales',
                        'reference_id' => $sale->id,
                        'reference_number' => $invoiceNumber,
                        'quantity' => $saleItem->quantity,
                        'unit_cost' => $unitCost,
                        'weighted_avg_cost' => $unitCost,
                        'total_cost' => $saleItem->quantity * $unitCost,
                        'balance_after' => $balanceAfter,
                        'value_before' => $valueBefore,
                        'value_after' => $valueAfter,
                        'created_by' => auth()->id(),
                        'transaction_date' => $validated['invoice_date'],
                    ]);
                }
            }

            // Skip payment creation for draft sales
            if (!$isDraft && $paidAmount > 0) {
                Payment::create([
                    'payment_number' => Str::upper('REC-' . now()->format('YmdHis') . '-' . Str::random(4)),
                    'payment_type' => 'sale',
                    'party_type' => 'customer',
                    'party_id' => $validated['customer_id'],
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'reference_number' => $invoiceNumber,
                    'payment_date' => $validated['invoice_date'],
                    'amount' => $paidAmount,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                ]);
            }

            return $sale;
        });

        $sale->load(['customer', 'warehouse', 'items.product']);

        return response()->json($sale, 201);
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'warehouse', 'items.product']);
        return response()->json($sale);
    }

    public function update(Request $request, Sale $sale)
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ];

        // Only validate status if it's present in the request and not null/empty
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $rules['status'] = 'required|string|in:draft,pending,partial,paid,cancelled';
        }

        $validated = $request->validate($rules);

        $paidAmount = $validated['paid_amount'] ?? 0;
        $shipping = $validated['shipping_cost'] ?? 0;

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($validated['items'] as $item) {
            $lineSubtotal = $item['unit_price'] * $item['quantity'];
            $lineDiscount = $item['discount'] ?? 0;
            $lineTax = $item['tax'] ?? 0;

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;
        }

        $discountAmount = $validated['discount_amount'] ?? $discountTotal;
        $taxAmount = $validated['tax_amount'] ?? $taxTotal;
        $totalAmount = $subtotal - $discountAmount + $taxAmount + $shipping;
        $paidAmount = min($paidAmount, $totalAmount);
        $balance = max($totalAmount - $paidAmount, 0);
        
        // Use provided status, otherwise keep existing status, or calculate it automatically
        if (isset($validated['status']) && $validated['status'] !== null && $validated['status'] !== '') {
            $status = (string) $validated['status'];
        } elseif (!empty($sale->status)) {
            $status = (string) $sale->status;
        } else {
            $status = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
        }
        // Ensure status is always a valid string
        $status = (string) $status;
        $isDraft = $status === 'draft';

        $sale = DB::transaction(function () use ($sale, $validated, $subtotal, $discountAmount, $taxAmount, $shipping, $totalAmount, $paidAmount, $balance, $status, $isDraft) {
            // Update sale
            $sale->update([
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => $status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shipping,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balance,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing items
            $sale->items()->delete();

            // Create new items
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                $stock = null;
                
                // Check stock availability and lock for update (skip for draft sales)
                if (!$isDraft) {
                    $stock = Stock::where('product_id', $itemData['product_id'])
                        ->where('warehouse_id', $validated['warehouse_id'])
                        ->lockForUpdate()
                        ->first();

                    $availableQty = $stock?->quantity ?? 0;
                    if ($availableQty < $itemData['quantity']) {
                        abort(422, "Insufficient stock for {$product->name}");
                    }
                }

                $lineSubtotal = $itemData['unit_price'] * $itemData['quantity'];
                $lineDiscount = $itemData['discount'] ?? 0;
                $lineTax = $itemData['tax'] ?? 0;
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $saleItem = SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Skip stock deduction and ledger for draft sales
                if (!$isDraft && $stock) {
                    $oldQty = $stock->quantity;
                    $newQty = $oldQty - $saleItem->quantity;
                    $newQty = max($newQty, 0);
                    $stock->update([
                        'quantity' => $newQty,
                        'total_value' => $newQty * $stock->average_cost,
                    ]);
                    $balanceAfter = $newQty;
                    $valueBefore = $oldQty * $stock->average_cost;
                    $valueAfter = $newQty * $stock->average_cost;
                    $unitCost = $stock->average_cost;

                    StockLedger::create([
                        'product_id' => $saleItem->product_id,
                        'warehouse_id' => $validated['warehouse_id'],
                        'type' => 'out',
                        'reference_type' => 'sales',
                        'reference_id' => $sale->id,
                        'reference_number' => $sale->invoice_number,
                        'quantity' => $saleItem->quantity,
                        'unit_cost' => $unitCost,
                        'weighted_avg_cost' => $unitCost,
                        'total_cost' => $saleItem->quantity * $unitCost,
                        'balance_after' => $balanceAfter,
                        'value_before' => $valueBefore,
                        'value_after' => $valueAfter,
                        'created_by' => auth()->id(),
                        'transaction_date' => $validated['invoice_date'],
                    ]);
                }
            }

            // Skip payment creation/update for draft sales
            if (!$isDraft && $paidAmount > 0) {
                $payment = Payment::where('reference_type', 'sale')
                    ->where('reference_id', $sale->id)
                    ->first();

                if ($payment) {
                    $payment->update([
                        'amount' => $paidAmount,
                        'payment_method' => $validated['payment_method'] ?? 'cash',
                        'payment_date' => $validated['invoice_date'],
                    ]);
                } else {
                    Payment::create([
                        'payment_number' => Str::upper('REC-' . now()->format('YmdHis') . '-' . Str::random(4)),
                        'payment_type' => 'sale',
                        'party_type' => 'customer',
                        'party_id' => $validated['customer_id'],
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'reference_number' => $sale->invoice_number,
                        'payment_date' => $validated['invoice_date'],
                        'amount' => $paidAmount,
                        'payment_method' => $validated['payment_method'] ?? 'cash',
                        'status' => 'completed',
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            return $sale->fresh();
        });

        $sale->load(['customer', 'warehouse', 'items.product']);

        return response()->json($sale);
    }

    public function destroy(Sale $sale)
    {
        if (!in_array($sale->status, ['draft', 'pending'])) {
            return response()->json(['message' => 'Only draft/pending sales can be deleted'], 422);
        }

        $sale->delete();

        return response()->json(['message' => 'Sale deleted']);
    }
}

