import axios from '@/utils/axios.config';

/**
 * Invoice Print Utility
 * Handles printing of sale invoices
 */
export default {
    /**
     * Print invoice for a sale
     * @param {Object} sale - Sale object with at least an id property
     * @param {Function} onError - Error callback function
     */
    async printInvoice(sale, onError) {
        try {
            // Fetch full sale details for printing
            const { data } = await axios.get(`/api/v1/sales/${sale.id}`);
            const saleData = data.data || data.sale || data;

            if (!saleData) {
                if (onError) onError('Sale data not found');
                return;
            }

            // Create print-friendly HTML
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            if (!printWindow) {
                if (onError) onError('Please allow popups to print invoice');
                return;
            }

            // Calculate totals - following ViewSaleDialog.vue logic
            const itemsDiscount = (saleData.items || []).reduce((sum, item) => sum + (parseFloat(item.discount || 0)), 0);
            const itemsTax = (saleData.items || []).reduce((sum, item) => sum + (parseFloat(item.tax || 0)), 0);
            const subtotal = parseFloat(saleData.subtotal || 0);
            const orderDiscount = parseFloat(saleData.discount_amount || 0);
            const orderTax = parseFloat(saleData.tax_amount || 0);
            const shippingCost = parseFloat(saleData.shipping_cost || 0);

            // Backend logic: $discountAmount = $validated['discount_amount'] ?? $discountTotal;
            // Use order discount if > 0, otherwise use items discount
            const discountAmount = (orderDiscount > 0 || (orderDiscount === 0 && itemsDiscount === 0))
                ? orderDiscount
                : itemsDiscount;

            // Calculate total tax - matching ViewSaleDialog.vue logic
            // Prefer explicit order tax when it's different from item tax (backend uses order tax field for totals)
            let calculatedTotalTax;
            if (orderTax > 0 && Math.abs(orderTax - itemsTax) >= 0.01) {
                calculatedTotalTax = orderTax;
            } else {
                // If order tax mirrors item tax, treat it as item tax to avoid mislabeling
                calculatedTotalTax = Math.max(itemsTax, orderTax);
            }

            // Determine if order tax should be shown separately
            const shouldShowOrderTax = orderTax > 0 && !(itemsTax > 0 && Math.abs(orderTax - itemsTax) < 0.01);

            // Use backend's total_amount if available (it's already calculated correctly with tax)
            // Backend formula: $totalAmount = $subtotal - $discountAmount + $taxAmount + $shipping;
            let calculatedTotal;
            if (saleData.total_amount !== undefined && saleData.total_amount !== null) {
                calculatedTotal = parseFloat(saleData.total_amount);
            } else {
                // Fallback: Calculate total using the same formula as backend
                // Use calculatedTotalTax for tax amount
                calculatedTotal = subtotal - discountAmount + calculatedTotalTax + shippingCost;
            }

            // Format date helper
            const formatDate = (dateString) => {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            };

            // Build invoice HTML
            const htmlClose = '</body>\n</html>';
            const invoiceHTML = `<!DOCTYPE html>
<html>
<head>
    <title>Invoice #${saleData.invoice_number}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; color: #000; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: white; }
        .invoice-header { background: #1976d2; color: white; padding: 20px; text-align: center; }
        .invoice-header h1 { font-size: 24px; margin-bottom: 5px; }
        .invoice-header .invoice-number { font-size: 14px; opacity: 0.9; }
        .invoice-section { padding: 20px; border-bottom: 1px solid #ddd; }
        .invoice-row { display: flex; margin-bottom: 20px; }
        .invoice-col { flex: 1; }
        .invoice-col h3 { font-size: 14px; margin-bottom: 10px; color: #666; }
        .invoice-col p { margin: 3px 0; font-size: 13px; }
        .invoice-meta { display: flex; gap: 30px; margin-top: 15px; }
        .invoice-meta-item { flex: 1; }
        .invoice-meta-item label { font-size: 12px; color: #666; display: block; }
        .invoice-meta-item span { font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f5f5f5; padding: 10px; text-align: left; border: 1px solid #ddd; font-size: 12px; font-weight: bold; }
        td { padding: 10px; border: 1px solid #ddd; font-size: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-box { background: #f9f9f9; padding: 15px; border: 2px solid #1976d2; margin-top: 20px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals-row.total { border-top: 2px solid #1976d2; border-bottom: 2px solid #1976d2; padding: 10px 0; margin: 10px 0; background: rgba(25, 118, 210, 0.05); font-weight: bold; }
        .totals-row.balance { border-top: 1px dashed #999; padding-top: 10px; margin-top: 10px; font-weight: bold; }
        .invoice-footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-draft { background: #9e9e9e; color: white; }
        .status-pending { background: #ff9800; color: white; }
        .status-partial { background: #2196f3; color: white; }
        .status-paid { background: #4caf50; color: white; }
        .status-cancelled { background: #f44336; color: white; }
        @media print {
            body { padding: 0; }
            .invoice-container { box-shadow: none; }
            @page { margin: 1.5cm; size: A4; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <div class="invoice-number">#${saleData.invoice_number}</div>
        </div>

        <div class="invoice-section">
            <div class="invoice-row">
                <div class="invoice-col">
                    <h3>From</h3>
                    <p><strong>Your Company Name</strong></p>
                    <p>Company Address, City, State</p>
                    <p>Phone: +1234567890 | Email: info@company.com</p>
                </div>
                <div class="invoice-col">
                    <h3>Bill To</h3>
                    <p><strong>${saleData.customer?.name || 'Walk-in Customer'}</strong></p>
                    ${saleData.customer?.phone ? `<p>Phone: ${saleData.customer.phone}</p>` : ''}
                    ${saleData.customer?.email ? `<p>Email: ${saleData.customer.email}</p>` : ''}
                    ${saleData.customer?.address ? `<p>${saleData.customer.address}</p>` : ''}
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-meta-item">
                    <label>Invoice Date</label>
                    <span>${formatDate(saleData.invoice_date)}</span>
                </div>
                ${saleData.due_date ? `
                <div class="invoice-meta-item">
                    <label>Due Date</label>
                    <span>${formatDate(saleData.due_date)}</span>
                </div>
                ` : ''}
                <div class="invoice-meta-item">
                    <label>Status</label>
                    <span class="status-badge status-${saleData.status || 'pending'}">${(saleData.status || 'pending').toUpperCase()}</span>
                </div>
            </div>
        </div>

        <div class="invoice-section">
            <h3 style="margin-bottom: 15px;">Items</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Product</th>
                        <th class="text-center" style="width: 60px;">Qty</th>
                        <th class="text-right" style="width: 90px;">Price</th>
                        <th class="text-right" style="width: 80px;">Disc.</th>
                        <th class="text-right" style="width: 80px;">Tax</th>
                        <th class="text-right" style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${(saleData.items || []).map((item, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <strong>${item.product?.name || 'Unknown'}</strong>
                            ${item.product?.sku ? `<br><small style="color: #666;">SKU: ${item.product.sku}</small>` : ''}
                        </td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-right">৳${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                        <td class="text-right" style="color: #f44336;">-৳${parseFloat(item.discount || 0).toFixed(2)}</td>
                        <td class="text-right">৳${parseFloat(item.tax || 0).toFixed(2)}</td>
                        <td class="text-right"><strong>৳${parseFloat(item.total || 0).toFixed(2)}</strong></td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>

        <div class="invoice-section">
            <div class="invoice-row">
                <div class="invoice-col">
                    ${saleData.notes ? `
                    <div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                        <h3 style="margin-bottom: 5px; font-size: 13px;">Notes</h3>
                        <p style="font-size: 12px;">${saleData.notes}</p>
                    </div>
                    ` : ''}
                    ${saleData.warehouse ? `
                    <div style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                        <h3 style="margin-bottom: 5px; font-size: 13px;">Warehouse</h3>
                        <p style="font-size: 12px;">${saleData.warehouse.name}</p>
                    </div>
                    ` : ''}
                </div>
                <div class="invoice-col">
                    <div class="totals-box">
                        <div class="totals-row">
                            <span>Subtotal:</span>
                            <span>৳${subtotal.toFixed(2)}</span>
                        </div>
                        ${itemsDiscount > 0 && (!orderDiscount || orderDiscount === 0) ? `
                        <div class="totals-row">
                            <span>Item Discounts:</span>
                            <span style="color: #f44336;">-৳${itemsDiscount.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        ${orderDiscount > 0 ? `
                        <div class="totals-row">
                            <span>Order Discount:</span>
                            <span style="color: #f44336;">-৳${orderDiscount.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        ${itemsTax > 0 ? `
                        <div class="totals-row">
                            <span>Item Tax:</span>
                            <span>৳${itemsTax.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        ${shouldShowOrderTax ? `
                        <div class="totals-row">
                            <span>Order Tax:</span>
                            <span>৳${orderTax.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        ${calculatedTotalTax > 0 ? `
                        <div class="totals-row">
                            <span><strong>Total Tax:</strong></span>
                            <span><strong>৳${calculatedTotalTax.toFixed(2)}</strong></span>
                        </div>
                        ` : ''}
                        ${shippingCost > 0 ? `
                        <div class="totals-row">
                            <span>Shipping:</span>
                            <span>৳${shippingCost.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        <div class="totals-row total">
                            <span>Total:</span>
                            <span style="color: #1976d2;">৳${calculatedTotal.toFixed(2)}</span>
                        </div>
                        <div class="totals-row" style="color: #4caf50;">
                            <span><strong>Paid:</strong></span>
                            <span><strong>৳${parseFloat(saleData.paid_amount || 0).toFixed(2)}</strong></span>
                        </div>
                        <div class="totals-row balance" style="color: ${saleData.balance_amount > 0 ? '#f44336' : '#4caf50'};">
                            <span><strong>Balance:</strong></span>
                            <span><strong>৳${parseFloat(saleData.balance_amount || 0).toFixed(2)}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
` + htmlClose;

            printWindow.document.write(invoiceHTML);
            printWindow.document.close();
        } catch (error) {
            console.error('Failed to print invoice', error);
            if (onError) {
                onError(error.response?.data?.message || 'Failed to print invoice');
            }
        }
    },
};

