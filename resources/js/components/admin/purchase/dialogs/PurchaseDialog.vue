<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="1200px"
        scrollable persistent>
        <v-card>
            <v-card-title class="pa-3 d-flex justify-space-between align-center">
                <span class="text-h6">{{ editingPurchase ? 'Edit Purchase Invoice' : 'New Purchase Invoice' }}</span>
            </v-card-title>
            <v-card-text class="pa-0">
                <v-form ref="form" @submit.prevent="handleSave">
                    <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                        <v-tab value="basic">Basic Information</v-tab>
                        <v-tab value="items">Items</v-tab>
                    </v-tabs>

                    <v-window v-model="activeTab">
                        <!-- Basic Information Tab -->
                        <v-window-item value="basic">
                            <div class="pa-3">
                                <v-row dense class="ma-0">
                                    <v-col cols="12" md="6" class="pa-2">
                                        <v-select v-model="localForm.supplier_id" :items="supplierOptions"
                                            item-title="label" item-value="value" label="Supplier" density="compact"
                                            variant="outlined" hide-details="auto" :rules="[rules.required]"
                                            required></v-select>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pa-2">
                                        <v-select v-model="localForm.warehouse_id" :items="validWarehouseOptions"
                                            item-title="label" item-value="value" label="Warehouse" density="compact"
                                            variant="outlined" hide-details="auto" :rules="[rules.required]" required
                                            no-data-text="No warehouses available"></v-select>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pa-2">
                                        <v-select v-model="localForm.grn_id" :items="grnOptions" item-title="label"
                                            item-value="value" label="GRN (Optional)" density="compact"
                                            variant="outlined" hide-details="auto" clearable></v-select>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pa-2">
                                        <DatePickerVuetifyInput field-label="Invoice Date" required="true"
                                            variant="outlined" :initial-date="localForm.invoice_date"
                                            @trigerInputValue="(val) => { localForm.invoice_date = val; }" />
                                        <div class="text-caption text-grey mt-1 ml-2">
                                            <span class="text-error"
                                                style="font-size: 1.1em; font-weight: bold;">*</span>
                                            Select the invoice date
                                        </div>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pa-2">
                                        <DatePickerVuetifyInput field-label="Due Date" variant="outlined"
                                            :initial-date="localForm.due_date"
                                            @trigerInputValue="(val) => { localForm.due_date = val; }" />
                                        <div class="text-caption text-grey mt-1 ml-2">
                                            Optional: Select payment due date
                                        </div>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pa-2">
                                        <v-text-field v-model.number="localForm.shipping_cost" label="Shipping Cost"
                                            type="number" min="0" step="0.01" prefix="৳" density="compact"
                                            variant="outlined" hide-details="auto"
                                            @input="calculateTotals"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" class="pa-2">
                                        <v-textarea v-model="localForm.notes" label="Notes" variant="outlined"
                                            density="compact" rows="2" hide-details="auto"></v-textarea>
                                    </v-col>
                                </v-row>
                            </div>
                        </v-window-item>

                        <!-- Items Tab -->
                        <v-window-item value="items">
                            <div class="pa-3">
                                <div class="d-flex justify-space-between align-center mb-3">
                                    <div>
                                        <span class="text-body-1 font-weight-medium">Invoice Items</span>
                                        <div class="text-caption text-grey mt-1" v-if="localForm.items.length === 0">
                                            Click "Add Item" button to add products to this invoice
                                        </div>
                                    </div>
                                    <v-btn size="small" color="primary" prepend-icon="mdi-plus" density="compact"
                                        @click="addPurchaseItem" variant="flat">
                                        Add Item
                                    </v-btn>
                                </div>
                                <v-table density="compact" class="items-table">
                                    <thead>
                                        <tr>
                                            <th class="text-caption">Product</th>
                                            <th class="text-caption text-center" style="width: 80px;">Qty</th>
                                            <th class="text-caption text-end" style="width: 100px;">Unit Price</th>
                                            <th class="text-caption text-end" style="width: 90px;">Discount</th>
                                            <th class="text-caption text-end" style="width: 90px;">Tax</th>
                                            <th class="text-caption text-end" style="width: 110px;">Total</th>
                                            <th class="text-caption" style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in localForm.items" :key="index">
                                            <td class="pa-1">
                                                <v-select v-model="item.product_id" :items="validProductOptions"
                                                    item-title="label" item-value="value" label="Product"
                                                    placeholder="Select product" density="compact" variant="outlined"
                                                    hide-details="auto"
                                                    :menu-props="{ maxHeight: 400, offset: true, attach: 'body', zIndex: 9999 }"
                                                    @update:model-value="calculateItemTotal(index)"
                                                    :rules="[rules.required]" clearable
                                                    no-data-text="No products available"></v-select>
                                            </td>
                                            <td class="pa-1">
                                                <v-text-field v-model.number="item.quantity" type="number" min="1"
                                                    density="compact" variant="outlined" hide-details
                                                    @input="calculateItemTotal(index)"
                                                    :rules="[rules.required, rules.minValue]"></v-text-field>
                                            </td>
                                            <td class="pa-1">
                                                <v-text-field v-model.number="item.unit_price" type="number" min="0"
                                                    step="0.01" density="compact" variant="outlined" hide-details
                                                    @input="calculateItemTotal(index)"
                                                    :rules="[rules.required]"></v-text-field>
                                            </td>
                                            <td class="pa-1">
                                                <v-text-field v-model.number="item.discount" type="number" min="0"
                                                    step="0.01" density="compact" variant="outlined" hide-details
                                                    @input="calculateItemTotal(index)"></v-text-field>
                                            </td>
                                            <td class="pa-1">
                                                <v-text-field v-model.number="item.tax" type="number" min="0"
                                                    step="0.01" density="compact" variant="outlined" hide-details
                                                    @input="calculateItemTotal(index)"></v-text-field>
                                            </td>
                                            <td class="text-body-2 font-weight-medium text-end">{{
                                                formatCurrency(item.total || 0) }}</td>
                                            <td class="text-center">
                                                <v-btn size="x-small" icon="mdi-delete" variant="text" color="error"
                                                    @click="removePurchaseItem(index)"></v-btn>
                                            </td>
                                        </tr>
                                        <tr v-if="localForm.items.length === 0">
                                            <td colspan="7" class="text-center py-3 text-grey text-caption">No items
                                                added</td>
                                        </tr>
                                        <tr v-if="localForm.items.length > 0" class="summary-row">
                                            <td colspan="5" class="text-right text-body-2 font-weight-bold">Subtotal:
                                            </td>
                                            <td class="text-body-2 font-weight-bold text-end">{{
                                                formatCurrency(calculatedSubtotal) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="localForm.items.length > 0">
                                            <td colspan="5" class="text-right text-caption">Tax:</td>
                                            <td class="text-caption text-end">{{ formatCurrency(calculatedTax) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="localForm.items.length > 0">
                                            <td colspan="5" class="text-right text-caption">Shipping:</td>
                                            <td class="text-caption text-end">{{ formatCurrency(localForm.shipping_cost
                                                || 0) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="localForm.items.length > 0">
                                            <td colspan="5" class="text-right text-caption">Discount:</td>
                                            <td class="text-caption text-end">{{ formatCurrency(calculatedDiscount) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="localForm.items.length > 0" class="total-row">
                                            <td colspan="5" class="text-right text-body-1 font-weight-bold">Total:</td>
                                            <td class="text-body-1 font-weight-bold text-end">{{
                                                formatCurrency(calculatedTotal) }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </div>
                        </v-window-item>
                    </v-window>
                </v-form>
            </v-card-text>
            <v-card-actions class="pa-3">
                <v-spacer></v-spacer>
                <v-btn @click="handleCancel" variant="text" density="compact">Cancel</v-btn>
                <v-btn @click="handleSave" color="primary" :loading="saving" density="compact">
                    {{ editingPurchase ? 'Update' : 'Create' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import DatePickerVuetifyInput from '@/components/common/DatePickerVuetifyInput.vue';

export default {
    name: 'PurchaseDialog',
    components: {
        DatePickerVuetifyInput,
    },
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        editingPurchase: {
            type: Object,
            default: null
        },
        supplierOptions: {
            type: Array,
            default: () => []
        },
        warehouseOptions: {
            type: Array,
            default: () => []
        },
        grnOptions: {
            type: Array,
            default: () => []
        },
        productOptions: {
            type: Array,
            default: () => []
        },
        saving: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue', 'save', 'cancel'],
    data() {
        return {
            activeTab: 'basic',
            localForm: {
                supplier_id: null,
                warehouse_id: null,
                grn_id: null,
                invoice_date: new Date().toISOString().split('T')[0],
                due_date: null,
                shipping_cost: 0,
                notes: '',
                items: []
            },
            rules: {
                required: value => !!value || 'This field is required',
                minValue: value => value > 0 || 'Quantity must be greater than 0'
            }
        };
    },
    computed: {
        calculatedSubtotal() {
            // Subtotal = sum of (quantity × unit_price) without discount
            return this.localForm.items.reduce((sum, item) => {
                return sum + ((item.quantity || 0) * (item.unit_price || 0));
            }, 0);
        },
        calculatedTax() {
            return this.localForm.items.reduce((sum, item) => sum + (item.tax || 0), 0);
        },
        calculatedDiscount() {
            return this.localForm.items.reduce((sum, item) => sum + (item.discount || 0), 0);
        },
        calculatedTotal() {
            // Total = subtotal + tax + shipping - discount (matches backend logic)
            return this.calculatedSubtotal + this.calculatedTax + (this.localForm.shipping_cost || 0) - this.calculatedDiscount;
        },
        validProductOptions() {
            // Ensure productOptions is always an array and properly formatted
            if (!Array.isArray(this.productOptions)) {
                console.warn('ProductOptions is not an array:', this.productOptions);
                return [];
            }

            // Return all options that have both label and value, ensuring values are numbers
            const valid = this.productOptions
                .filter(option => {
                    const hasLabel = option && (option.label || option.title);
                    const hasValue = option && (option.value !== undefined && option.value !== null);
                    return hasLabel && hasValue;
                })
                .map(option => ({
                    ...option,
                    value: Number(option.value)
                }));

            // If editing, add products from purchase items that might not be in the options
            // (e.g., inactive or deleted products)
            if (this.editingPurchase && this.editingPurchase.items) {
                const existingValues = new Set(valid.map(opt => opt.value));

                this.editingPurchase.items.forEach(item => {
                    const productId = item.product_id ? Number(item.product_id) : null;
                    if (productId && !existingValues.has(productId)) {
                        // Product not in options, add it with product name if available
                        const productName = item.product?.name || `Product #${productId}`;
                        const productSku = item.product?.sku || '';
                        valid.push({
                            label: productSku ? `${productName} (${productSku})` : productName,
                            value: productId
                        });
                        existingValues.add(productId); // Prevent duplicates
                    }
                });
            }

            if (valid.length === 0 && this.productOptions.length > 0) {
                console.warn('No valid product options found. ProductOptions:', this.productOptions);
            }
            return valid;
        },
        validWarehouseOptions() {
            // Ensure warehouseOptions is always an array and properly formatted
            if (!Array.isArray(this.warehouseOptions)) {
                console.warn('WarehouseOptions is not an array:', this.warehouseOptions);
                return [];
            }
            if (this.warehouseOptions.length === 0) {
                console.warn('WarehouseOptions is empty');
                return [];
            }
            // Return all options that have both label and value
            const valid = this.warehouseOptions.filter(option => {
                const hasLabel = option && (option.label || option.title);
                const hasValue = option && (option.value !== undefined && option.value !== null);
                return hasLabel && hasValue;
            });
            if (valid.length === 0 && this.warehouseOptions.length > 0) {
                console.warn('No valid warehouse options found. WarehouseOptions:', this.warehouseOptions);
            }
            return valid;
        }
    },
    watch: {
        modelValue(newVal) {
            if (newVal && this.editingPurchase) {
                this.initializeForm(this.editingPurchase);
            } else if (newVal) {
                this.resetForm();
            }
        },
        editingPurchase: {
            handler(newVal) {
                if (newVal && this.modelValue) {
                    this.initializeForm(newVal);
                }
            },
            deep: true
        },
        productOptions: {
            handler(newVal) {
                // Ensure productOptions are reactive
                if (newVal && Array.isArray(newVal) && newVal.length > 0) {
                    console.log('ProductOptions updated:', newVal.length, 'products');
                } else {
                    console.warn('ProductOptions issue:', newVal);
                }
            },
            immediate: true
        },
        warehouseOptions: {
            handler(newVal) {
                // Ensure warehouseOptions are reactive
                if (newVal && Array.isArray(newVal) && newVal.length > 0) {
                    console.log('WarehouseOptions updated:', newVal.length, 'warehouses');
                } else {
                    console.warn('WarehouseOptions issue:', newVal);
                }
            },
            immediate: true
        }
    },
    methods: {
        initializeForm(purchase) {
            this.activeTab = 'basic';
            this.localForm = {
                supplier_id: purchase.supplier_id ? Number(purchase.supplier_id) : null,
                warehouse_id: purchase.warehouse_id ? Number(purchase.warehouse_id) : null,
                grn_id: purchase.grn_id ? Number(purchase.grn_id) : null,
                invoice_date: purchase.invoice_date ? (purchase.invoice_date.includes('T') ? purchase.invoice_date.split('T')[0] : purchase.invoice_date) : new Date().toISOString().split('T')[0],
                due_date: purchase.due_date ? (purchase.due_date.includes('T') ? purchase.due_date.split('T')[0] : purchase.due_date) : null,
                shipping_cost: purchase.shipping_cost || 0,
                notes: purchase.notes || '',
                items: purchase.items?.map(item => ({
                    product_id: item.product_id ? Number(item.product_id) : null,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    discount: item.discount || 0,
                    tax: item.tax || 0,
                    total: item.total || 0
                })) || []
            };
        },
        resetForm() {
            this.activeTab = 'basic';
            this.localForm = {
                supplier_id: null,
                warehouse_id: null,
                grn_id: null,
                invoice_date: new Date().toISOString().split('T')[0],
                due_date: null,
                shipping_cost: 0,
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        calculateItemTotal(index) {
            const item = this.localForm.items[index];
            if (item && item.quantity && item.unit_price) {
                const itemTotal = (item.quantity * item.unit_price) - (item.discount || 0) + (item.tax || 0);
                // Direct assignment works in Vue 3
                item.total = itemTotal;
            } else if (item) {
                item.total = 0;
            }
        },
        calculateTotals() {
            // Trigger recalculation
        },
        addPurchaseItem() {
            // Switch to Items tab if not already there
            if (this.activeTab !== 'items') {
                this.activeTab = 'items';
            }

            // Add new item to the array
            this.localForm.items.push({
                product_id: null,
                quantity: 1,
                unit_price: 0,
                discount: 0,
                tax: 0,
                total: 0
            });
        },
        removePurchaseItem(index) {
            this.localForm.items.splice(index, 1);
        },
        handleSave() {
            if (!this.$refs.form.validate()) {
                return;
            }

            if (this.localForm.items.length === 0) {
                this.$emit('save', { ...this.localForm }, 'Please add at least one item');
                return;
            }

            // Prepare form data with calculated totals to match backend expectations
            const formData = {
                ...this.localForm,
                subtotal: this.calculatedSubtotal,
                tax_amount: this.calculatedTax,
                discount_amount: this.calculatedDiscount,
                shipping_cost: this.localForm.shipping_cost || 0,
                items: this.localForm.items.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    discount: item.discount || 0,
                    tax: item.tax || 0,
                    notes: item.notes || null,
                }))
            };

            this.$emit('save', formData, null);
        },
        handleCancel() {
            this.resetForm();
            this.$emit('cancel');
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '৳0.00';
            return '৳' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }
    }
};
</script>

<style scoped>
.items-table :deep(.v-table__wrapper) {
    max-height: 400px;
    overflow-y: auto;
}

.items-table :deep(th) {
    background-color: rgb(var(--v-theme-surface));
    position: sticky;
    top: 0;
    z-index: 1;
}

.summary-row {
    border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.total-row {
    border-top: 2px solid rgba(var(--v-border-color), var(--v-border-opacity));
    background-color: rgba(var(--v-theme-primary), 0.05);
}
</style>
