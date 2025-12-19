<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="700px"
        persistent scrollable>
        <v-card class="product-dialog">
            <!-- Compact Header -->
            <v-card-title class="dialog-header d-flex justify-space-between align-center pa-3">
                <span class="text-h6 font-weight-medium">{{ isEdit ? 'Edit Product' : 'Add Product' }}</span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="close" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <v-form ref="formRef" v-model="formValid" lazy-validation>
                    <v-row dense class="ma-0">
                        <v-col cols="12" sm="8" class="pa-2">
                            <v-text-field v-model="form.name" label="Product Name" :rules="[rules.required]"
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="4" class="pa-2 d-flex align-center">
                            <v-switch v-model="form.is_active" inset label="Active" density="compact" hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.sku" label="SKU" density="compact" variant="outlined"
                                hide-details>
                                <template #append-inner>
                                    <v-btn icon="mdi-refresh" size="x-small" variant="text" @click="generateSKU"
                                        :title="'Generate SKU'" />
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.barcode" label="Barcode" density="compact" variant="outlined"
                                hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.brand" label="Brand" density="compact" variant="outlined"
                                hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.image" label="Image URL" density="compact" variant="outlined"
                                hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-select v-model="form.category_id" :items="categoryOptions" item-value="value"
                                item-title="label" label="Category" clearable density="compact" variant="outlined"
                                hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-select v-model="form.unit_id" :items="unitOptions" item-value="value" item-title="label"
                                label="Unit" clearable density="compact" variant="outlined" hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.purchase_price" label="Purchase Price" type="number"
                                min="0" step="0.01" :rules="[rules.required]" density="compact" variant="outlined"
                                hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.sale_price" label="Sale Price" type="number" min="0"
                                step="0.01" :rules="[rules.required]" density="compact" variant="outlined"
                                hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.tax_rate" label="VAT/Tax (%)" type="number" min="0"
                                step="0.01" density="compact" variant="outlined" hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.minimum_stock_level" label="Minimum Stock Alert"
                                type="number" min="0" density="compact" variant="outlined" hide-details />
                        </v-col>
                        <v-col cols="12" class="pa-2">
                            <v-textarea v-model="form.description" label="Description" rows="2" density="compact"
                                variant="outlined" hide-details />
                        </v-col>
                    </v-row>
                </v-form>

                <!-- Stock Information Section -->
                <v-divider class="my-3" />
                <div class="text-subtitle-2 font-weight-medium mb-2">Stock by Warehouse</div>
                <v-card variant="outlined" class="mb-2">
                    <v-card-text class="pa-2">
                        <div v-if="stockByWarehouse && stockByWarehouse.length > 0">
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
                        </div>
                        <div v-else class="text-center text-grey py-2">
                            <span class="text-caption">No stock information available</span>
                        </div>
                    </v-card-text>
                </v-card>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-2 justify-end">
                <v-btn variant="text" size="small" @click="close">Cancel</v-btn>
                <v-btn color="primary" size="small" :loading="saving" @click="save">
                    {{ isEdit ? 'Update' : 'Save' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'ProductDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        product: { type: Object, default: null },
    },
    emits: ['update:modelValue', 'saved'],
    data() {
        return {
            form: this.getEmptyForm(),
            formValid: false,
            saving: false,
            categoryOptions: [],
            unitOptions: [],
            stockByWarehouse: [],
            rules: {
                required: v => !!v || 'Required',
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.form.id;
        },
    },
    watch: {
        product: {
            immediate: true,
            handler(newVal) {
                this.form = newVal ? { ...this.getEmptyForm(), ...newVal } : this.getEmptyForm();
                // Load stock information if product exists
                if (newVal && newVal.stock_by_warehouse) {
                    this.stockByWarehouse = newVal.stock_by_warehouse;
                } else {
                    this.stockByWarehouse = [];
                }
            },
        },
    },
    created() {
        this.fetchOptions();
    },
    methods: {
        getEmptyForm() {
            return {
                id: null,
                name: '',
                sku: '',
                barcode: '',
                brand: '',
                category_id: null,
                unit_id: null,
                description: '',
                image: '',
                purchase_price: 0,
                sale_price: 0,
                tax_rate: 0,
                minimum_stock_level: 0,
                is_active: true,
            };
        },
        async fetchOptions() {
            try {
                const [categoriesRes, unitsRes] = await Promise.all([
                    axios.get('/api/v1/products/categories'),
                    axios.get('/api/v1/products/units'),
                ]);
                this.categoryOptions = categoriesRes.data.categories || [];
                this.unitOptions = unitsRes.data.units || [];
            } catch (error) {
                console.error('Failed to load options', error);
            }
        },
        generateSKU() {
            // Generate SKU based on product name or random
            let sku = '';
            if (this.form.name && this.form.name.trim()) {
                // Use first 3-4 uppercase letters from product name
                const nameParts = this.form.name.trim().toUpperCase().split(/\s+/);
                const prefix = nameParts.map(part => part.substring(0, 2)).join('').substring(0, 4);
                // Add timestamp suffix (last 6 digits)
                const suffix = Date.now().toString().slice(-6);
                sku = `${prefix}-${suffix}`;
            } else {
                // Generate random SKU if no product name
                const prefix = 'PROD';
                const random = Math.random().toString(36).substring(2, 8).toUpperCase();
                sku = `${prefix}-${random}`;
            }
            this.form.sku = sku;
        },
        close() {
            this.$emit('update:modelValue', false);
        },
        async save() {
            const valid = await this.$refs.formRef.validate();
            if (!valid) return;
            this.saving = true;
            try {
                const payload = { ...this.form };
                // Remove stock_by_warehouse from payload as it's read-only
                delete payload.stock_by_warehouse;
                if (this.isEdit) {
                    const response = await axios.put(`/api/v1/products/${this.form.id}`, payload);
                    // Update stock information from response
                    if (response.data && response.data.stock_by_warehouse) {
                        this.stockByWarehouse = response.data.stock_by_warehouse;
                    }
                } else {
                    await axios.post('/api/v1/products', payload);
                }
                this.$emit('saved');
                this.close();
            } catch (error) {
                console.error('Failed to save product', error);
                this.$toast?.error('Failed to save product');
            } finally {
                this.saving = false;
            }
        },
        getStockStatusColor(quantity) {
            if (!this.form.minimum_stock_level) return 'default';
            if (quantity <= 0) return 'error';
            if (quantity <= this.form.minimum_stock_level) return 'warning';
            return 'success';
        },
    },
};
</script>

<style scoped>
.product-dialog {
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

:deep(.v-textarea--density-compact .v-field) {
    padding-top: 0;
    padding-bottom: 0;
}

:deep(.v-switch--density-compact) {
    margin-top: 0;
    margin-bottom: 0;
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
