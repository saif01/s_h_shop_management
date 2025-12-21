<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="900"
        scrollable>
        <v-card v-if="returnItem">
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Purchase Return Details</span>
                <v-chip :color="getStatusColor(returnItem.status)" size="small">
                    {{ returnItem.status }}
                </v-chip>
            </v-card-title>
            <v-card-text class="pa-0">
                <div class="pa-6">
                    <!-- Header Information -->
                    <v-row class="mb-4">
                        <v-col cols="12" md="6">
                            <div class="text-caption text-grey mb-1">Return Number</div>
                            <div class="text-h6 font-weight-bold">{{ returnItem.return_number }}</div>
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="text-caption text-grey mb-1">Return Date</div>
                            <div class="text-body-1">{{ formatDate(returnItem.return_date) }}</div>
                        </v-col>
                    </v-row>

                    <!-- Purchase Invoice and Supplier -->
                    <v-row class="mb-4">
                        <v-col cols="12" md="6">
                            <div class="text-caption text-grey mb-1">Purchase Invoice</div>
                            <div class="text-body-1 font-weight-medium">{{ returnItem.purchase?.invoice_number || 'N/A'
                            }}</div>
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="text-caption text-grey mb-1">Supplier</div>
                            <div class="text-body-1 font-weight-medium">{{ returnItem.supplier?.name || 'N/A' }}</div>
                        </v-col>
                    </v-row>

                    <!-- Reason -->
                    <v-row class="mb-4" v-if="returnItem.reason">
                        <v-col cols="12" md="6">
                            <div class="text-caption text-grey mb-1">Reason</div>
                            <div class="text-body-1">{{ returnItem.reason }}</div>
                        </v-col>
                    </v-row>

                    <!-- Notes -->
                    <v-row class="mb-4" v-if="returnItem.notes">
                        <v-col cols="12">
                            <div class="text-caption text-grey mb-1">Notes</div>
                            <div class="text-body-1">{{ returnItem.notes }}</div>
                        </v-col>
                    </v-row>

                    <!-- Return Items -->
                    <div class="mt-4">
                        <h3 class="text-h6 mb-3">Return Items</h3>
                        <v-table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in returnItem.items" :key="index">
                                    <td>{{ item.product?.name || 'N/A' }}</td>
                                    <td>{{ item.product?.sku || 'N/A' }}</td>
                                    <td>{{ item.quantity }}</td>
                                    <td>{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="font-weight-medium">{{ formatCurrency(item.total || 0) }}</td>
                                </tr>
                                <tr v-if="!returnItem.items || returnItem.items.length === 0">
                                    <td colspan="5" class="text-center py-4 text-grey">No items found</td>
                                </tr>
                            </tbody>
                        </v-table>

                        <!-- Summary -->
                        <v-row class="mt-4">
                            <v-col cols="12" md="6" offset-md="6">
                                <v-card variant="outlined">
                                    <v-card-text>
                                        <div class="d-flex justify-space-between">
                                            <span class="text-h6 font-weight-bold">Total:</span>
                                            <span class="text-h6 font-weight-bold">{{
                                                formatCurrency(returnItem.total_amount || 0) }}</span>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </div>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="$emit('update:modelValue', false)" variant="text">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { formatCurrency, formatDateDDMMYYYY } from '@/utils/formatters';

export default {
    name: 'ViewPurchaseReturnDialog',
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        returnItem: {
            type: Object,
            default: null
        }
    },
    emits: ['update:modelValue'],
    methods: {
        formatCurrency,
        formatDate: formatDateDDMMYYYY,
        getStatusColor(status) {
            const colors = {
                'draft': 'warning',
                'approved': 'info',
                'completed': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        }
    }
};
</script>
