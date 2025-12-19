<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="900px"
        persistent scrollable>
        <v-card class="invoice-card">
            <!-- Header -->
            <v-card-title class="invoice-header d-flex justify-space-between align-center pa-3">
                <div class="d-flex align-center gap-2">
                    <v-icon color="white" size="24">mdi-file-document-outline</v-icon>
                    <div>
                        <div class="text-h6 text-white font-weight-bold">INVOICE</div>
                        <div v-if="saleData" class="text-caption text-white text-opacity-90">
                            #{{ saleData.invoice_number }}
                        </div>
                    </div>
                </div>
                <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="close" />
            </v-card-title>

            <v-card-text class="pa-0">
                <div v-if="loading" class="text-center py-8">
                    <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                    <div class="mt-3 text-body-2">Loading invoice details...</div>
                </div>
                <div v-else-if="!saleData" class="text-center py-8">
                    <v-alert type="error" variant="tonal" class="ma-3" density="compact">
                        Failed to load invoice details
                    </v-alert>
                </div>
                <div v-else class="invoice-content">
                    <!-- Invoice Header Section -->
                    <div class="invoice-header-section pa-3">
                        <v-row dense>
                            <v-col cols="12" md="6">
                                <div class="invoice-company-info">
                                    <div class="text-subtitle-2 font-weight-bold mb-1">From</div>
                                    <div class="text-body-2 font-weight-medium mb-0">Your Company Name</div>
                                    <div class="text-caption text-grey">Company Address, City, State</div>
                                    <div class="text-caption text-grey">Phone: +1234567890 | Email: info@company.com
                                    </div>
                                </div>
                            </v-col>
                            <v-col cols="12" md="6">
                                <div class="invoice-customer-info">
                                    <div class="text-subtitle-2 font-weight-bold mb-1">Bill To</div>
                                    <div class="text-body-2 font-weight-medium mb-0">
                                        {{ saleData.customer?.name || 'Walk-in Customer' }}
                                    </div>
                                    <div v-if="saleData.customer?.phone" class="text-caption text-grey">
                                        Phone: {{ saleData.customer.phone }}
                                    </div>
                                    <div v-if="saleData.customer?.email" class="text-caption text-grey">
                                        Email: {{ saleData.customer.email }}
                                    </div>
                                    <div v-if="saleData.customer?.address" class="text-caption text-grey">
                                        {{ saleData.customer.address }}
                                    </div>
                                </div>
                            </v-col>
                        </v-row>

                        <v-row dense class="mt-2">
                            <v-col cols="12" md="4">
                                <div class="invoice-meta-item">
                                    <div class="text-caption text-grey mb-0">Invoice Date</div>
                                    <div class="text-body-2 font-weight-medium">{{ formatDate(saleData.invoice_date) }}
                                    </div>
                                </div>
                            </v-col>
                            <v-col cols="12" md="4" v-if="saleData.due_date">
                                <div class="invoice-meta-item">
                                    <div class="text-caption text-grey mb-0">Due Date</div>
                                    <div class="text-body-2 font-weight-medium">{{ formatDate(saleData.due_date) }}
                                    </div>
                                </div>
                            </v-col>
                            <v-col cols="12" md="4">
                                <div class="invoice-meta-item">
                                    <div class="text-caption text-grey mb-0">Status</div>
                                    <v-chip :color="getStatusColor(saleData.status)" size="x-small" variant="flat"
                                        class="font-weight-medium">
                                        {{ saleData.status?.toUpperCase() || 'PENDING' }}
                                    </v-chip>
                                </div>
                            </v-col>
                        </v-row>
                    </div>

                    <!-- Items Table Section -->
                    <div class="invoice-items-section pa-3">
                        <div class="text-subtitle-1 font-weight-bold mb-2">Items</div>
                        <v-table class="invoice-table" density="compact">
                            <thead>
                                <tr class="invoice-table-header">
                                    <th class="text-left" style="width: 30px;">#</th>
                                    <th class="text-left">Product</th>
                                    <th class="text-center" style="width: 60px;">Qty</th>
                                    <th class="text-right" style="width: 90px;">Price</th>
                                    <th class="text-right" style="width: 80px;">Disc.</th>
                                    <th class="text-right" style="width: 80px;">Tax</th>
                                    <th class="text-right" style="width: 100px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!saleData.items || saleData.items.length === 0">
                                    <td colspan="7" class="text-center py-4 text-grey">No items found</td>
                                </tr>
                                <tr v-for="(item, index) in (saleData.items || [])" :key="item.id || item.product_id"
                                    class="invoice-table-row">
                                    <td class="text-grey text-caption">{{ index + 1 }}</td>
                                    <td>
                                        <div class="text-body-2 font-weight-medium">{{ item.product?.name || 'Unknown'
                                        }}</div>
                                        <div v-if="item.product?.sku" class="text-caption text-grey">SKU: {{
                                            item.product.sku }}</div>
                                    </td>
                                    <td class="text-center text-body-2">{{ item.quantity }}</td>
                                    <td class="text-right text-body-2">৳{{ parseFloat(item.unit_price || 0).toFixed(2)
                                    }}</td>
                                    <td class="text-right text-body-2 text-error">-৳{{ parseFloat(item.discount ||
                                        0).toFixed(2) }}</td>
                                    <td class="text-right text-body-2">৳{{ parseFloat(item.tax || 0).toFixed(2) }}</td>
                                    <td class="text-right text-body-2 font-weight-bold">৳{{ parseFloat(item.total ||
                                        0).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </div>

                    <!-- Totals Section -->
                    <div class="invoice-totals-section pa-3">
                        <v-row dense>
                            <v-col cols="12" md="7">
                                <div v-if="saleData.notes" class="invoice-notes">
                                    <div class="text-subtitle-2 font-weight-bold mb-1">Notes</div>
                                    <div class="text-body-2 text-caption">{{ saleData.notes }}</div>
                                </div>
                                <div v-if="saleData.warehouse" class="invoice-warehouse mt-2">
                                    <div class="text-subtitle-2 font-weight-bold mb-1">Warehouse</div>
                                    <div class="text-body-2 text-caption">{{ saleData.warehouse.name }}</div>
                                </div>
                            </v-col>
                            <v-col cols="12" md="5">
                                <div class="invoice-totals-box">
                                    <div class="invoice-totals-row">
                                        <span class="invoice-totals-label text-caption">Subtotal:</span>
                                        <span class="invoice-totals-value text-body-2">৳{{ parseFloat(saleData.subtotal
                                            || 0).toFixed(2) }}</span>
                                    </div>

                                    <div v-if="calculatedItemsDiscount > 0" class="invoice-totals-row">
                                        <span class="invoice-totals-label text-caption">Item Discounts:</span>
                                        <span class="invoice-totals-value text-body-2 text-error">-৳{{
                                            calculatedItemsDiscount.toFixed(2) }}</span>
                                    </div>

                                    <div v-if="saleData.discount_amount > 0" class="invoice-totals-row">
                                        <span class="invoice-totals-label text-caption">Order Discount:</span>
                                        <span class="invoice-totals-value text-body-2 text-error">-৳{{
                                            parseFloat(saleData.discount_amount).toFixed(2) }}</span>
                                    </div>

                                    <div v-if="calculatedItemsTax > 0" class="invoice-totals-row">
                                        <span class="invoice-totals-label text-caption">Item Tax:</span>
                                        <span class="invoice-totals-value text-body-2">৳{{ calculatedItemsTax.toFixed(2)
                                        }}</span>
                                    </div>

                                    <div v-if="saleData.tax_amount > 0" class="invoice-totals-row">
                                        <span class="invoice-totals-label text-caption">Order Tax:</span>
                                        <span class="invoice-totals-value text-body-2">৳{{
                                            parseFloat(saleData.tax_amount).toFixed(2) }}</span>
                                    </div>

                                    <div v-if="calculatedTotalTax > 0"
                                        class="invoice-totals-row invoice-totals-highlight">
                                        <span class="invoice-totals-label text-body-2 font-weight-medium">Total
                                            Tax:</span>
                                        <span class="invoice-totals-value text-body-2 font-weight-medium">৳{{
                                            calculatedTotalTax.toFixed(2) }}</span>
                                    </div>

                                    <div v-if="saleData.shipping_cost > 0" class="invoice-totals-row">
                                        <span class="invoice-totals-label text-caption">Shipping:</span>
                                        <span class="invoice-totals-value text-body-2">৳{{
                                            parseFloat(saleData.shipping_cost).toFixed(2) }}</span>
                                    </div>

                                    <v-divider class="my-2" />

                                    <div class="invoice-totals-row invoice-totals-total">
                                        <span
                                            class="invoice-totals-label text-subtitle-1 font-weight-bold">Total:</span>
                                        <span
                                            class="invoice-totals-value text-subtitle-1 font-weight-bold text-primary">৳{{
                                                calculatedTotalAmount.toFixed(2) }}</span>
                                    </div>

                                    <div class="invoice-totals-row invoice-totals-paid">
                                        <span
                                            class="invoice-totals-label text-body-2 font-weight-medium text-success">Paid:</span>
                                        <span
                                            class="invoice-totals-value text-body-2 font-weight-medium text-success">৳{{
                                                parseFloat(saleData.paid_amount || 0).toFixed(2) }}</span>
                                    </div>

                                    <div class="invoice-totals-row invoice-totals-balance"
                                        :class="saleData.balance_amount > 0 ? 'text-error' : 'text-success'">
                                        <span class="invoice-totals-label text-body-2 font-weight-bold">Balance:</span>
                                        <span class="invoice-totals-value text-body-2 font-weight-bold">৳{{
                                            parseFloat(saleData.balance_amount || 0).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </v-col>
                        </v-row>
                    </div>

                    <!-- Footer -->
                    <div class="invoice-footer pa-2 text-center">
                        <div class="text-caption text-grey">
                            Thank you for your business!
                        </div>
                    </div>
                </div>
            </v-card-text>

            <v-divider />
            <v-card-actions class="justify-end pa-2 bg-grey-lighten-5">
                <v-btn color="primary" prepend-icon="mdi-printer" variant="flat" size="small" @click="printInvoice">
                    Print Invoice
                </v-btn>
                <v-btn variant="text" size="small" @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';
import { formatDate } from '@/utils/formatters';

export default {
    name: 'ViewSaleDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        sale: { type: Object, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            saleData: null,
            loading: false,
            currentSaleId: null,
        };
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newVal) {
                if (newVal && this.sale && this.sale.id) {
                    if (this.currentSaleId !== this.sale.id) {
                        this.loadSaleDetails(this.sale.id);
                    }
                } else if (!newVal) {
                    this.saleData = null;
                    this.currentSaleId = null;
                }
            },
        },
        sale: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal.id && this.modelValue) {
                    if (this.currentSaleId !== newVal.id) {
                        this.loadSaleDetails(newVal.id);
                    }
                }
            },
        },
    },
    computed: {
        calculatedItemsDiscount() {
            if (!this.saleData || !this.saleData.items) return 0;
            return this.saleData.items.reduce((sum, item) => sum + (parseFloat(item.discount || 0)), 0);
        },
        calculatedItemsTax() {
            if (!this.saleData || !this.saleData.items) return 0;
            return this.saleData.items.reduce((sum, item) => sum + (parseFloat(item.tax || 0)), 0);
        },
        calculatedTotalTax() {
            const itemsTax = this.calculatedItemsTax;
            const orderTax = parseFloat(this.saleData?.tax_amount || 0);
            return itemsTax + orderTax;
        },
        calculatedTotalAmount() {
            if (!this.saleData) return 0;

            // Calculate total using the same formula as backend:
            // total = subtotal - discount_amount + tax_amount + shipping_cost
            // But we need to ensure we include ALL taxes (both item taxes and order tax)
            const subtotal = parseFloat(this.saleData.subtotal || 0);
            const discountAmount = parseFloat(this.saleData.discount_amount || 0);
            const shippingCost = parseFloat(this.saleData.shipping_cost || 0);

            // Use calculatedTotalTax which includes both item taxes and order tax
            // This ensures the total always includes all taxes
            const totalTax = this.calculatedTotalTax;

            const total = subtotal - discountAmount + totalTax + shippingCost;
            return parseFloat(total.toFixed(2));
        },
    },
    methods: {
        async loadSaleDetails(id) {
            if (!id) {
                console.error('No sale ID provided');
                return;
            }

            if (this.currentSaleId === id && this.saleData) {
                return;
            }

            this.currentSaleId = id;
            this.loading = true;
            this.saleData = null;

            try {
                const { data } = await axios.get(`/api/v1/sales/${id}`);
                this.saleData = data.data || data.sale || data;

                if (!this.saleData) {
                    this.showError('Sale data not found');
                    this.currentSaleId = null;
                }
            } catch (error) {
                console.error('Failed to load sale details:', error);
                console.error('Error response:', error.response?.data);
                this.showError(error.response?.data?.message || 'Failed to load sale details');
                this.currentSaleId = null;
            } finally {
                this.loading = false;
            }
        },
        formatDate,
        getStatusColor(status) {
            const colors = {
                draft: 'grey',
                pending: 'warning',
                partial: 'info',
                paid: 'success',
                cancelled: 'error',
            };
            return colors[status] || 'grey';
        },
        printInvoice() {
            // Use nextTick to ensure DOM is ready
            this.$nextTick(() => {
                // Try to find dialog element safely
                let dialog = null;
                if (this.$el) {
                    // Check if $el is a DOM element
                    if (this.$el.nodeType === 1) {
                        dialog = this.$el.closest('.v-dialog');
                    } else if (this.$el.querySelector) {
                        // If $el is a component, try to find dialog in it
                        dialog = this.$el.querySelector('.v-dialog');
                    }
                }

                // Also try document querySelector as fallback
                if (!dialog) {
                    dialog = document.querySelector('.v-dialog');
                }

                if (dialog) {
                    dialog.style.position = 'relative';
                    dialog.style.margin = '0';
                }

                // Trigger print
                setTimeout(() => {
                    window.print();
                }, 100);
            });
        },
        close() {
            this.$emit('update:modelValue', false);
            this.saleData = null;
            this.currentSaleId = null;
        },
        showError(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'error',
                    title: message
                });
            } else if (window.Swal) {
                window.Swal.fire({
                    icon: 'error',
                    title: message
                });
            } else {
                alert(message);
            }
        },
    },
};
</script>

<style scoped>
.invoice-card {
    background: white;
}

.invoice-header {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    position: sticky;
    top: 0;
    z-index: 10;
}

.invoice-content {
    background: white;
}

.invoice-header-section {
    background: linear-gradient(to bottom, rgba(25, 118, 210, 0.05), transparent);
    border-bottom: 1px solid rgba(25, 118, 210, 0.1);
}

.invoice-company-info,
.invoice-customer-info {
    background: white;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.invoice-meta-item {
    background: white;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.06);
}

.invoice-items-section {
    background: white;
}

.invoice-table {
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-radius: 6px;
    overflow: hidden;
}

.invoice-table-header {
    background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
}

.invoice-table-header th {
    font-weight: 600;
    color: #424242;
    padding: 10px 8px;
    border-bottom: 1px solid rgba(25, 118, 210, 0.2);
    font-size: 0.75rem;
}

.invoice-table-row {
    transition: background-color 0.2s;
}

.invoice-table-row:hover {
    background-color: rgba(25, 118, 210, 0.04);
}

.invoice-table-row td {
    padding: 8px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.invoice-totals-section {
    background: linear-gradient(to top, rgba(25, 118, 210, 0.05), transparent);
    border-top: 1px solid rgba(25, 118, 210, 0.1);
}

.invoice-totals-box {
    background: white;
    border: 1px solid rgba(25, 118, 210, 0.2);
    border-radius: 6px;
    padding: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.invoice-totals-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
}

.invoice-totals-label {
    color: #616161;
}

.invoice-totals-value {
    font-weight: 500;
    color: #212121;
}

.invoice-totals-highlight {
    background: rgba(25, 118, 210, 0.06);
    padding: 4px 8px;
    margin: 2px -8px;
    border-radius: 3px;
}

.invoice-totals-total {
    padding: 8px 0;
    border-top: 2px solid rgba(25, 118, 210, 0.3);
    border-bottom: 2px solid rgba(25, 118, 210, 0.3);
    margin: 4px -12px;
    padding-left: 12px;
    padding-right: 12px;
    background: rgba(25, 118, 210, 0.05);
}

.invoice-totals-paid {
    margin-top: 4px;
}

.invoice-totals-balance {
    margin-top: 2px;
    padding-top: 4px;
    border-top: 1px dashed rgba(0, 0, 0, 0.2);
}

.invoice-notes,
.invoice-warehouse {
    background: white;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.invoice-footer {
    background: rgba(0, 0, 0, 0.02);
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

/* Print Styles */
@media print {

    /* Hide overlay and dialog backdrop */
    .v-overlay,
    .v-overlay__scrim {
        display: none !important;
        background: transparent !important;
    }

    /* Make dialog full width and remove constraints */
    .v-dialog {
        position: relative !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        height: auto !important;
        top: 0 !important;
        left: 0 !important;
        transform: none !important;
    }

    .v-dialog__container {
        position: static !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Card styling */
    .invoice-card {
        box-shadow: none !important;
        margin: 0 !important;
        border: none !important;
    }

    /* Header styling */
    .invoice-header {
        background: #1976d2 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        page-break-after: avoid;
    }

    /* Hide action buttons and close button */
    .v-card-actions,
    .v-btn,
    button {
        display: none !important;
    }

    /* Content sections */
    .invoice-content {
        page-break-inside: avoid;
    }

    .invoice-header-section {
        page-break-after: avoid;
    }

    .invoice-items-section {
        page-break-inside: avoid;
    }

    .invoice-totals-section {
        page-break-inside: avoid;
    }

    /* Table styling */
    .invoice-table {
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }

    .invoice-table-header {
        background: #f5f5f5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .invoice-table-header th {
        border-bottom: 2px solid #000 !important;
        color: #000 !important;
        font-weight: bold !important;
    }

    .invoice-table-row td {
        border-bottom: 1px solid #ddd !important;
    }

    /* Totals box */
    .invoice-totals-box {
        border: 2px solid #1976d2 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        page-break-inside: avoid;
    }

    /* Company and customer info */
    .invoice-company-info,
    .invoice-customer-info,
    .invoice-meta-item {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }

    /* Footer */
    .invoice-footer {
        page-break-before: avoid;
    }

    /* Ensure proper text colors */
    .text-grey {
        color: #424242 !important;
    }

    /* Remove gradients for better printing */
    .invoice-header-section,
    .invoice-totals-section {
        background: white !important;
    }

    /* Remove hover effects */
    .invoice-table-row:hover {
        background-color: transparent !important;
    }

    /* Page setup */
    @page {
        margin: 1.5cm;
        size: A4;
    }

    /* Avoid page breaks in critical sections */
    .invoice-totals-total,
    .invoice-totals-paid,
    .invoice-totals-balance {
        page-break-inside: avoid;
    }

    /* Ensure chips and badges print properly */
    .v-chip {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* Responsive */
@media (max-width: 960px) {

    .invoice-header-section,
    .invoice-items-section,
    .invoice-totals-section {
        padding: 16px !important;
    }

    .invoice-totals-box {
        margin-top: 16px;
    }
}
</style>
