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
                        <v-col cols="12" class="pa-2">
                            <v-text-field v-model="form.name"
                                placeholder="Enter product name (e.g., Samsung Galaxy S21)" :rules="[rules.required]"
                                density="compact" variant="outlined" hide-details="auto"
                                hint="Enter the full product name as it should appear in the system" persistent-hint>
                                <template #label>
                                    Product Name <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.sku" label="SKU"
                                placeholder="Enter SKU or click generate to auto-generate" density="compact"
                                variant="outlined" hide-details="auto"
                                hint="Stock Keeping Unit - unique identifier for the product" persistent-hint>
                                <template #append-inner>
                                    <v-btn icon="mdi-refresh" size="x-small" variant="text" @click="generateSKU"
                                        :title="'Generate SKU'" />
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.barcode" label="Barcode"
                                placeholder="Enter barcode (e.g., 1234567890123)" density="compact" variant="outlined"
                                hide-details="auto" hint="Optional: Product barcode for scanning" persistent-hint>
                                <template #append-inner>
                                    <v-btn icon="mdi-refresh" size="x-small" variant="text" @click="generateBarcode"
                                        :title="'Generate Barcode'" />
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.brand" label="Brand"
                                placeholder="Enter brand name (e.g., Samsung, Apple)" density="compact"
                                variant="outlined" hide-details="auto"
                                hint="Optional: Product brand or manufacturer name" persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="form.image" label="Image URL"
                                placeholder="Enter image URL or leave empty" density="compact" variant="outlined"
                                hide-details="auto" hint="Optional: Direct URL to product image" persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-select v-model="form.category_id" :items="categoryOptions" item-value="value"
                                item-title="label" label="Category" placeholder="Select a category" clearable
                                density="compact" variant="outlined" hide-details="auto"
                                hint="Optional: Product category for organization" persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-select v-model="form.unit_id" :items="unitOptions" item-value="value" item-title="label"
                                label="Unit" placeholder="Select a unit" clearable density="compact" variant="outlined"
                                hide-details="auto" hint="Optional: Measurement unit (e.g., KG, PCS, LTR)"
                                persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.purchase_price" type="number" min="0" step="0.01"
                                placeholder="Enter purchase price (e.g., 1000.00)" :rules="[rules.required]"
                                density="compact" variant="outlined" hide-details="auto"
                                hint="Cost price at which the product was purchased" persistent-hint>
                                <template #label>
                                    Purchase Price <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.sale_price" type="number" min="0" step="0.01"
                                placeholder="Enter sale price (e.g., 1500.00)" :rules="[rules.required]"
                                density="compact" variant="outlined" hide-details="auto"
                                hint="Selling price of the product to customers" persistent-hint>
                                <template #label>
                                    Sale Price <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.tax_rate" label="VAT/Tax (%)" type="number" min="0"
                                step="0.01" placeholder="Enter tax rate (e.g., 15.00)" density="compact"
                                variant="outlined" hide-details="auto"
                                hint="Optional: VAT or tax percentage applied to the product" persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model.number="form.minimum_stock_level" label="Minimum Stock Alert"
                                type="number" min="0" placeholder="Enter minimum stock level (e.g., 10)"
                                density="compact" variant="outlined" hide-details="auto"
                                hint="Optional: Alert when stock falls below this quantity" persistent-hint />
                        </v-col>
                        <v-col cols="12" class="pa-2">
                            <v-textarea v-model="form.description" label="Description" rows="2"
                                placeholder="Enter product description (optional)" density="compact" variant="outlined"
                                hide-details="auto" hint="Optional: Detailed description of the product"
                                persistent-hint />
                        </v-col>
                        <v-col cols="12" class="pa-2 d-flex align-center">
                            <v-switch v-model="form.is_active" :color="form.is_active ? 'success' : 'error'"
                                label="Active Status" density="compact" hide-details inset class="active-switch" />
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
            async handler(newVal) {
                this.form = newVal ? { ...this.getEmptyForm(), ...newVal } : this.getEmptyForm();
                // Load stock information if product exists
                if (newVal && newVal.id) {
                    await this.loadProductStock(newVal.id);
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
        generateBarcode() {
            // Generate 13-digit EAN-13 barcode (common standard)
            // Format: 12 digits + 1 check digit
            let barcode = '';

            // Generate 12 random digits
            for (let i = 0; i < 12; i++) {
                barcode += Math.floor(Math.random() * 10).toString();
            }

            // Calculate EAN-13 check digit
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                const digit = parseInt(barcode[i]);
                // Odd positions (1-indexed) are multiplied by 1, even by 3
                sum += (i % 2 === 0) ? digit : digit * 3;
            }
            const checkDigit = (10 - (sum % 10)) % 10;
            barcode += checkDigit.toString();

            this.form.barcode = barcode;
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

.active-switch {
    flex: 1;
}

.active-switch :deep(.v-switch__track) {
    height: 24px;
    width: 48px;
}

.active-switch :deep(.v-switch__thumb) {
    width: 20px;
    height: 20px;
}

.active-switch :deep(.v-label) {
    font-weight: 500;
    font-size: 0.875rem;
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

