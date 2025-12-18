<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" 
        max-width="900px" persistent>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center bg-primary">
                <span class="text-h5 text-white">
                    <v-icon class="text-white">mdi-file-document</v-icon>
                    <span v-if="saleData">Sale Invoice: {{ saleData.invoice_number }}</span>
                    <span v-else>Loading Sale Details...</span>
                </span>
                <v-btn icon="mdi-close" variant="text" class="text-white" @click="close" />
            </v-card-title>
            
            <v-card-text class="pa-6">
                <div v-if="loading" class="text-center py-8">
                    <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
                    <div class="mt-4">Loading sale details...</div>
                </div>
                <div v-else-if="!saleData" class="text-center py-8">
                    <v-alert type="error" variant="tonal">
                        Failed to load sale details
                    </v-alert>
                </div>
                <div v-else>
                <!-- Header Info -->
                <v-row class="mb-4">
                    <v-col cols="6">
                        <div class="text-subtitle-2">Customer</div>
                        <div class="text-h6">{{ saleData.customer?.name || 'Walk-in' }}</div>
                        <div class="text-caption">{{ saleData.customer?.phone || '' }}</div>
                    </v-col>
                    <v-col cols="6" class="text-right">
                        <div class="text-subtitle-2">Date</div>
                        <div class="text-body-1">{{ formatDate(saleData.invoice_date) }}</div>
                        <v-chip :color="getStatusColor(saleData.status)" size="small" class="mt-2">
                            {{ saleData.status }}
                        </v-chip>
                    </v-col>
                </v-row>

                <v-divider class="my-4" />

                <!-- Items Table -->
                <v-table density="compact">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Tax</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!saleData.items || saleData.items.length === 0">
                            <td colspan="6" class="text-center py-4 text-grey">No items found</td>
                        </tr>
                        <tr v-for="item in (saleData.items || [])" :key="item.id || item.product_id">
                            <td>{{ item.product?.name || 'Unknown' }}</td>
                            <td class="text-right">{{ item.quantity }}</td>
                            <td class="text-right">৳{{ parseFloat(item.unit_price || 0).toFixed(2) }}</td>
                            <td class="text-right">৳{{ parseFloat(item.discount || 0).toFixed(2) }}</td>
                            <td class="text-right">৳{{ parseFloat(item.tax || 0).toFixed(2) }}</td>
                            <td class="text-right font-weight-bold">৳{{ parseFloat(item.total || 0).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </v-table>

                <v-divider class="my-4" />

                <!-- Totals -->
                <v-row>
                    <v-col cols="6" offset="6">
                        <div class="d-flex justify-space-between mb-2">
                            <span>Subtotal:</span>
                            <span>৳{{ parseFloat(saleData.subtotal).toFixed(2) }}</span>
                        </div>
                        <div v-if="saleData.discount_amount > 0" class="d-flex justify-space-between mb-2">
                            <span>Discount:</span>
                            <span class="text-error">-৳{{ parseFloat(saleData.discount_amount).toFixed(2) }}</span>
                        </div>
                        <div v-if="saleData.tax_amount > 0" class="d-flex justify-space-between mb-2">
                            <span>Tax:</span>
                            <span>৳{{ parseFloat(saleData.tax_amount).toFixed(2) }}</span>
                        </div>
                        <div v-if="saleData.shipping_cost > 0" class="d-flex justify-space-between mb-2">
                            <span>Shipping:</span>
                            <span>৳{{ parseFloat(saleData.shipping_cost).toFixed(2) }}</span>
                        </div>
                        <v-divider class="my-2" />
                        <div class="d-flex justify-space-between text-h6 mb-2">
                            <span>Total:</span>
                            <span>৳{{ parseFloat(saleData.total_amount).toFixed(2) }}</span>
                        </div>
                        <div class="d-flex justify-space-between text-success mb-2">
                            <span>Paid:</span>
                            <span>৳{{ parseFloat(saleData.paid_amount).toFixed(2) }}</span>
                        </div>
                        <div class="d-flex justify-space-between font-weight-bold"
                            :class="saleData.balance_amount > 0 ? 'text-error' : 'text-success'">
                            <span>Balance:</span>
                            <span>৳{{ parseFloat(saleData.balance_amount).toFixed(2) }}</span>
                        </div>
                    </v-col>
                </v-row>

                <!-- Notes -->
                <v-alert v-if="saleData.notes" type="info" variant="tonal" class="mt-4">
                    <strong>Notes:</strong> {{ saleData.notes }}
                </v-alert>

                <!-- Warehouse Info -->
                <div class="text-caption mt-4">
                    <strong>Warehouse:</strong> {{ saleData.warehouse?.name || 'N/A' }}
                </div>
                </div>
            </v-card-text>

            <v-divider />
            <v-card-actions class="justify-end pa-4">
                <v-btn color="primary" prepend-icon="mdi-printer" @click="printInvoice">
                    Print Invoice
                </v-btn>
                <v-btn variant="text" @click="close">Close</v-btn>
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
            currentSaleId: null, // Track which sale ID we're loading to prevent duplicates
        };
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newVal) {
                if (newVal && this.sale && this.sale.id) {
                    // Load sale details when dialog opens
                    if (this.currentSaleId !== this.sale.id) {
                        this.loadSaleDetails(this.sale.id);
                    }
                } else if (!newVal) {
                    // Reset data when dialog closes
                    this.saleData = null;
                    this.currentSaleId = null;
                }
            },
        },
        sale: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal.id && this.modelValue) {
                    // Load sale details when sale prop changes and dialog is open
                    if (this.currentSaleId !== newVal.id) {
                        this.loadSaleDetails(newVal.id);
                    }
                }
            },
        },
    },
    methods: {
        async loadSaleDetails(id) {
            if (!id) {
                console.error('No sale ID provided');
                return;
            }
            
            // Prevent duplicate loading
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
            window.print();
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

