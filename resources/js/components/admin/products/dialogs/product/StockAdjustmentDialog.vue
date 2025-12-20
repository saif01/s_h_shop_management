<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="600px"
        persistent scrollable>
        <v-card class="stock-adjustment-dialog">
            <!-- Header -->
            <v-card-title class="dialog-header d-flex justify-space-between align-center pa-3">
                <span class="text-h6 font-weight-medium">Adjust Stock</span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="close" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <!-- Product Info -->
                <v-card variant="outlined" class="mb-3">
                    <v-card-text class="pa-2">
                        <div class="text-subtitle-2 font-weight-medium mb-1">Product Information</div>
                        <div class="text-body-2">
                            <div><strong>Name:</strong> {{ product?.name || '-' }}</div>
                            <div><strong>SKU:</strong> {{ product?.sku || '-' }}</div>
                        </div>
                    </v-card-text>
                </v-card>

                <!-- Current Stock by Warehouse -->
                <div v-if="stockByWarehouse && stockByWarehouse.length > 0" class="mb-3">
                    <div class="text-subtitle-2 font-weight-medium mb-2">Current Stock by Warehouse</div>
                    <v-card variant="outlined" class="mb-3">
                        <v-card-text class="pa-2">
                            <v-table density="compact">
                                <thead>
                                    <tr>
                                        <th>Warehouse</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Avg Cost</th>
                                        <th class="text-end">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="stock in stockByWarehouse" :key="stock.warehouse_id">
                                        <td>
                                            <span class="font-weight-medium">{{ stock.warehouse_name }}</span>
                                            <span v-if="stock.warehouse_code" class="text-caption text-grey ml-1">
                                                ({{ stock.warehouse_code }})
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <v-chip size="small" :color="getStockStatusColor(stock.quantity)"
                                                variant="tonal">
                                                {{ stock.quantity || 0 }}
                                            </v-chip>
                                        </td>
                                        <td class="text-end">৳{{ parseFloat(stock.average_cost || 0).toFixed(2) }}</td>
                                        <td class="text-end">৳{{ parseFloat(stock.total_value || 0).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>
                </div>

                <!-- Stock Adjustment Form -->
                <div class="text-subtitle-2 font-weight-medium mb-2">Stock Adjustment</div>
                <v-form ref="stockFormRef" v-model="stockFormValid" lazy-validation>
                    <v-row dense class="ma-0">
                        <v-col cols="12" class="pa-2">
                            <v-select v-model="stockAdjustment.warehouse_id" :items="warehouseOptions"
                                item-value="value" item-title="label" placeholder="Select warehouse" clearable
                                density="compact" variant="outlined" hide-details="auto"
                                :rules="[rules.required]" hint="Select the warehouse for stock adjustment"
                                persistent-hint>
                                <template #label>
                                    Warehouse <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-select>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-select v-model="stockAdjustment.adjustment_type" :items="adjustmentTypeOptions"
                                item-value="value" item-title="label" placeholder="Select adjustment type"
                                density="compact" variant="outlined" hide-details="auto"
                                :rules="[rules.required]" hint="Set quantity, add stock, or subtract stock"
                                persistent-hint>
                                <template #label>
                                    Type <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-select>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="stockAdjustment.quantity" type="number" min="0"
                                placeholder="Enter quantity (e.g., 100)" density="compact" variant="outlined"
                                hide-details="auto" :rules="[rules.required, rules.positiveNumber]"
                                hint="Enter the quantity to adjust" persistent-hint>
                                <template #label>
                                    Quantity <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="stockAdjustment.unit_cost"
                                label="Unit Cost (Optional)" type="number" min="0" step="0.01"
                                placeholder="Enter unit cost (e.g., 50.00)" density="compact" variant="outlined"
                                hide-details="auto" hint="Optional: Cost per unit for this stock adjustment"
                                persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="stockAdjustment.notes" label="Notes (Optional)"
                                placeholder="Enter notes about this adjustment (optional)" density="compact"
                                variant="outlined" hide-details="auto"
                                hint="Optional: Additional notes or reason for stock adjustment"
                                persistent-hint />
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-2 justify-end">
                <v-btn variant="text" size="small" @click="close">Cancel</v-btn>
                <v-btn color="primary" size="small" :loading="adjustingStock" @click="adjustStock">
                    Adjust Stock
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'StockAdjustmentDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        product: { type: Object, default: null },
    },
    emits: ['update:modelValue', 'saved'],
    data() {
        return {
            stockFormValid: false,
            adjustingStock: false,
            warehouseOptions: [],
            stockByWarehouse: [],
            stockAdjustment: {
                warehouse_id: null,
                quantity: 0,
                unit_cost: null,
                adjustment_type: 'set',
                notes: '',
            },
            adjustmentTypeOptions: [
                { value: 'set', label: 'Set Quantity' },
                { value: 'add', label: 'Add Stock' },
                { value: 'subtract', label: 'Subtract Stock' },
            ],
            rules: {
                required: v => !!v || 'Required',
                positiveNumber: v => (v !== null && v !== undefined && v >= 0) || 'Must be 0 or greater',
            },
        };
    },
    watch: {
        product: {
            immediate: true,
            async handler(newVal) {
                if (newVal && newVal.id) {
                    await this.loadProductStock(newVal.id);
                    this.resetForm();
                } else {
                    this.stockByWarehouse = [];
                    this.resetForm();
                }
            },
        },
        modelValue(newVal) {
            if (newVal && this.product && this.product.id) {
                this.loadProductStock(this.product.id);
            }
        },
    },
    created() {
        this.fetchWarehouses();
    },
    methods: {
        async fetchWarehouses() {
            try {
                const response = await axios.get('/api/v1/products/warehouses');
                this.warehouseOptions = response.data.warehouses || [];
            } catch (error) {
                console.error('Failed to load warehouses', error);
            }
        },
        async loadProductStock(productId) {
            try {
                const response = await axios.get(`/api/v1/products/${productId}`);
                if (response.data && response.data.stock_by_warehouse) {
                    this.stockByWarehouse = response.data.stock_by_warehouse;
                } else {
                    this.stockByWarehouse = [];
                }
            } catch (error) {
                console.error('Failed to load product stock', error);
                this.stockByWarehouse = [];
            }
        },
        getStockStatusColor(quantity) {
            if (!this.product?.minimum_stock_level) return 'default';
            if (quantity <= 0) return 'error';
            if (quantity <= this.product.minimum_stock_level) return 'warning';
            return 'success';
        },
        resetForm() {
            this.stockAdjustment = {
                warehouse_id: null,
                quantity: 0,
                unit_cost: null,
                adjustment_type: 'set',
                notes: '',
            };
            this.$refs.stockFormRef?.resetValidation();
        },
        close() {
            this.resetForm();
            this.$emit('update:modelValue', false);
        },
        async adjustStock() {
            const valid = await this.$refs.stockFormRef?.validate();
            if (!valid) return;

            if (!this.product || !this.product.id) {
                this.$toast?.error('Product is required');
                return;
            }

            if (!this.stockAdjustment.warehouse_id) {
                this.$toast?.error('Please select a warehouse');
                return;
            }

            this.adjustingStock = true;
            try {
                const payload = {
                    product_id: this.product.id,
                    warehouse_id: this.stockAdjustment.warehouse_id,
                    quantity: this.stockAdjustment.quantity,
                    adjustment_type: this.stockAdjustment.adjustment_type,
                    unit_cost: this.stockAdjustment.unit_cost || null,
                    notes: this.stockAdjustment.notes || 'Manual stock adjustment',
                };

                await axios.post('/api/v1/stocks', payload);

                // Reload product stock information
                await this.loadProductStock(this.product.id);

                // Reset form
                this.resetForm();

                this.$toast?.success('Stock adjusted successfully');
                this.$emit('saved'); // Notify parent to refresh list
                this.close();
            } catch (error) {
                console.error('Failed to adjust stock', error);
                const errorMessage = error.response?.data?.message || 'Failed to adjust stock';
                this.$toast?.error(errorMessage);
            } finally {
                this.adjustingStock = false;
            }
        },
    },
};
</script>

<style scoped>
.stock-adjustment-dialog {
    max-height: 90vh;
}

.dialog-header {
    background-color: rgba(0, 0, 0, 0.02);
}

:deep(.v-text-field--density-compact .v-field) {
    padding-top: 0;
    padding-bottom: 0;
}

:deep(.v-select--density-compact .v-field) {
    padding-top: 0;
    padding-bottom: 0;
}

/* Red required field stars */
:deep(.v-label--required .v-label__asterisk) {
    color: rgb(176, 0, 32) !important;
}

:deep(.v-field-label--required .v-label__asterisk) {
    color: rgb(176, 0, 32) !important;
}

:deep(.v-input--required .v-label__asterisk) {
    color: rgb(176, 0, 32) !important;
}
</style>

