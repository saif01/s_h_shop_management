<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="1400px"
        persistent scrollable>
        <v-card class="pos-dialog">
            <!-- Compact Header -->
            <v-card-title class="pos-header d-flex justify-space-between align-center pa-3">
                <div class="d-flex align-center gap-2">
                    <v-icon color="white">mdi-point-of-sale</v-icon>
                    <span class="text-h6 text-white font-weight-medium">
                        {{ isEdit ? 'Edit Sale' : 'New Sale - POS' }}
                    </span>
                </div>
                <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="close" />
            </v-card-title>

            <v-card-text class="pa-3">
                <v-form ref="formRef" v-model="formValid">
                    <v-row dense class="ma-0">
                        <!-- Left Side - Product Search & Cart -->
                        <v-col cols="12" lg="8" class="pa-2">
                            <!-- Product Search -->
                            <v-card variant="flat" class="mb-3 search-card" elevation="1">
                                <v-card-text class="pa-3">
                                    <v-text-field v-model="productSearch" label="Search Product (Name/SKU/Barcode)"
                                        prepend-inner-icon="mdi-magnify" clearable autofocus variant="outlined"
                                        hide-details @keyup.enter="searchProducts" @click:clear="searchResults = []" />

                                    <!-- Search Results -->
                                    <div v-if="searchResults.length > 0" class="search-results-container">
                                        <v-list class="search-results">
                                            <v-list-item v-for="product in searchResults" :key="product.id"
                                                @click="addToCart(product)" class="search-item">
                                                <template #prepend>
                                                    <v-avatar size="40" color="grey-lighten-4">
                                                        <v-img v-if="product.image" :src="product.image" />
                                                        <v-icon v-else>mdi-package-variant</v-icon>
                                                    </v-avatar>
                                                </template>
                                                <v-list-item-title>{{ product.name }}</v-list-item-title>
                                                <v-list-item-subtitle>
                                                    SKU: {{ product.sku || 'N/A' }} | Stock: {{ product.stock_quantity
                                                        || 0
                                                    }}
                                                </v-list-item-subtitle>
                                                <template #append>
                                                    <v-chip color="primary" size="small" variant="flat">৳{{
                                                        product.sale_price }}</v-chip>
                                                </template>
                                            </v-list-item>
                                        </v-list>
                                    </div>
                                </v-card-text>
                            </v-card>

                            <!-- Compact Cart Items -->
                            <v-card variant="flat" class="cart-card" elevation="1">
                                <v-card-title class="cart-header pa-2">
                                    <v-icon size="18" class="mr-1">mdi-cart</v-icon>
                                    <span class="text-body-2 font-weight-medium">Cart Items ({{ cartItems.length
                                        }})</span>
                                </v-card-title>
                                <v-divider />
                                <v-card-text class="pa-2">
                                    <v-table v-if="cartItems.length > 0" density="compact" class="cart-table">
                                        <thead>
                                            <tr>
                                                <th class="text-caption">Product</th>
                                                <th class="text-caption text-center" style="width: 70px;">Qty</th>
                                                <th class="text-caption text-end" style="width: 90px;">Price</th>
                                                <th class="text-caption text-end" style="width: 80px;">Disc.</th>
                                                <th class="text-caption text-end" style="width: 80px;">Tax</th>
                                                <th class="text-caption text-end" style="width: 100px;">Total</th>
                                                <th class="text-caption" style="width: 40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in cartItems" :key="index" class="cart-row">
                                                <td class="text-body-2">{{ item.product_name }}</td>
                                                <td class="pa-1">
                                                    <v-text-field v-model.number="item.quantity" type="number" min="1"
                                                        density="compact" hide-details variant="outlined"
                                                        class="cart-input"
                                                        @update:model-value="updateCartItem(index)" />
                                                </td>
                                                <td class="pa-1">
                                                    <v-text-field v-model.number="item.unit_price" type="number" min="0"
                                                        step="0.01" density="compact" hide-details variant="outlined"
                                                        class="cart-input"
                                                        @update:model-value="updateCartItem(index)" />
                                                </td>
                                                <td class="pa-1">
                                                    <v-text-field v-model.number="item.discount" type="number" min="0"
                                                        step="0.01" density="compact" hide-details variant="outlined"
                                                        class="cart-input"
                                                        @update:model-value="updateCartItem(index)" />
                                                </td>
                                                <td class="pa-1">
                                                    <v-text-field v-model.number="item.tax" type="number" min="0"
                                                        step="0.01" density="compact" hide-details variant="outlined"
                                                        class="cart-input"
                                                        @update:model-value="updateCartItem(index)" />
                                                </td>
                                                <td class="text-body-2 font-weight-bold text-end">৳{{
                                                    item.total.toFixed(2) }}</td>
                                                <td class="text-center">
                                                    <v-btn icon="mdi-delete" size="x-small" variant="text" color="error"
                                                        @click="removeFromCart(index)" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </v-table>
                                    <v-alert v-else type="info" variant="tonal" density="compact" class="mt-2 mb-0">
                                        <span class="text-caption">Cart is empty. Search and add products.</span>
                                    </v-alert>
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <!-- Right Side - Sale Details & Totals -->
                        <v-col cols="12" lg="4" class="pa-2">
                            <v-card variant="flat" class="details-card" elevation="1">
                                <v-card-text class="pa-2">
                                    <!-- Customer & Warehouse -->
                                    <div class="mb-2">
                                        <v-autocomplete v-model="form.customer_id" :items="customers" item-value="id"
                                            item-title="name" label="Customer *" clearable density="compact"
                                            variant="outlined" hide-details :rules="[rules.required]">
                                            <template #prepend-inner>
                                                <v-icon size="18">mdi-account</v-icon>
                                            </template>
                                        </v-autocomplete>
                                    </div>

                                    <div class="mb-2">
                                        <v-select v-model="form.warehouse_id" :items="warehouses" item-value="id"
                                            item-title="name" label="Warehouse *" density="compact" variant="outlined"
                                            hide-details :rules="[rules.required]">
                                            <template #prepend-inner>
                                                <v-icon size="18">mdi-warehouse</v-icon>
                                            </template>
                                        </v-select>
                                    </div>

                                    <!-- Dates -->
                                    <v-row dense class="ma-0 mb-2">
                                        <v-col cols="6" class="pa-1">
                                            <DatePickerVuetifyInput fieldLabel="Invoice Date" required="true"
                                                variant="outlined" :initialDate="form.invoice_date"
                                                @trigerInputValue="(val) => { form.invoice_date = val; }" />
                                        </v-col>
                                        <v-col cols="6" class="pa-1">
                                            <DatePickerVuetifyInput fieldLabel="Due Date" variant="outlined"
                                                :initialDate="form.due_date"
                                                @trigerInputValue="(val) => { form.due_date = val; }" />
                                        </v-col>
                                    </v-row>

                                    <v-divider class="my-2" />

                                    <!-- Totals Section -->
                                    <div class="totals-section">
                                        <div class="d-flex justify-space-between mb-1">
                                            <span class="text-caption text-grey">Subtotal:</span>
                                            <span class="text-body-2 font-weight-medium">৳{{ form.subtotal.toFixed(2)
                                                }}</span>
                                        </div>

                                        <div class="d-flex justify-space-between mb-1">
                                            <span class="text-caption text-grey">Discount:</span>
                                            <v-text-field v-model.number="form.discount_amount" type="number"
                                                density="compact" variant="outlined" hide-details min="0" step="0.01"
                                                class="compact-input" @update:model-value="calculateTotals" />
                                        </div>

                                        <div class="d-flex justify-space-between mb-1">
                                            <span class="text-caption text-grey">Tax:</span>
                                            <v-text-field v-model.number="form.tax_amount" type="number"
                                                density="compact" variant="outlined" hide-details min="0" step="0.01"
                                                class="compact-input" @update:model-value="calculateTotals" />
                                        </div>

                                        <div class="d-flex justify-space-between mb-2">
                                            <span class="text-caption text-grey">Shipping:</span>
                                            <v-text-field v-model.number="form.shipping_cost" type="number"
                                                density="compact" variant="outlined" hide-details min="0" step="0.01"
                                                class="compact-input" @update:model-value="calculateTotals" />
                                        </div>

                                        <v-divider class="my-2" />

                                        <div class="d-flex justify-space-between mb-2 total-amount">
                                            <span class="text-body-1 font-weight-bold">Total:</span>
                                            <span class="text-h6 font-weight-bold text-primary">৳{{
                                                form.total_amount.toFixed(2) }}</span>
                                        </div>

                                        <div class="mb-2">
                                            <v-text-field v-model.number="form.paid_amount" type="number"
                                                label="Paid Amount" density="compact" variant="outlined" min="0"
                                                step="0.01" prepend-inner-icon="mdi-cash"
                                                @update:model-value="calculateBalance" />
                                        </div>

                                        <div class="mb-2">
                                            <v-text-field v-model.number="form.balance_amount" label="Balance (Due)"
                                                density="compact" variant="outlined" readonly
                                                :color="form.balance_amount > 0 ? 'error' : 'success'"
                                                :class="form.balance_amount > 0 ? 'text-error' : 'text-success'" />
                                        </div>

                                        <div v-if="form.paid_amount > 0" class="mb-2">
                                            <v-select v-model="paymentMethod" :items="paymentMethods"
                                                label="Payment Method" density="compact" variant="outlined"
                                                hide-details />
                                        </div>
                                    </div>

                                    <v-divider class="my-2" />

                                    <!-- Notes -->
                                    <v-textarea v-model="form.notes" label="Notes" rows="2" density="compact"
                                        variant="outlined" hide-details />
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>

            <v-divider />
            <v-card-actions class="pa-2 justify-end">
                <v-btn variant="text" size="small" @click="close">Cancel</v-btn>
                <v-btn color="success" size="small" :loading="saving" :disabled="cartItems.length === 0" @click="save">
                    <v-icon size="18" class="mr-1">mdi-content-save</v-icon>
                    {{ isEdit ? 'Update' : 'Save Sale' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';
import DatePickerVuetifyInput from '@/components/common/DatePickerVuetifyInput.vue';
import adminPaginationMixin from '@/mixins/adminPaginationMixin';

export default {
    name: 'SaleDialog',
    mixins: [adminPaginationMixin],
    components: {
        DatePickerVuetifyInput,
    },
    props: {
        modelValue: { type: Boolean, required: true },
        sale: { type: Object, default: null },
    },
    emits: ['update:modelValue', 'saved'],
    data() {
        return {
            form: this.getEmptyForm(),
            formValid: false,
            cartItems: [],
            productSearch: '',
            searchResults: [],
            customers: [],
            warehouses: [],
            saving: false,
            paymentMethod: 'cash',
            paymentMethods: [
                { title: 'Cash', value: 'cash' },
                { title: 'Card', value: 'card' },
                { title: 'Mobile Banking', value: 'mobile_banking' },
                { title: 'Bank Transfer', value: 'bank_transfer' },
                { title: 'Cheque', value: 'cheque' },
            ],
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
        sale: {
            immediate: true,
            async handler(newVal) {
                if (newVal && newVal.id) {
                    await this.loadSaleForEdit(newVal.id);
                } else {
                    this.form = this.getEmptyForm();
                    this.cartItems = [];
                }
            },
        },
        modelValue(val) {
            if (val) {
                this.fetchOptions();
                if (!this.sale || !this.sale.id) {
                    this.form = this.getEmptyForm();
                    this.cartItems = [];
                }
            } else {
                this.form = this.getEmptyForm();
                this.cartItems = [];
            }
        },
    },
    methods: {
        getEmptyForm() {
            return {
                id: null,
                customer_id: null,
                warehouse_id: null,
                invoice_date: new Date().toISOString().split('T')[0],
                due_date: null,
                subtotal: 0,
                tax_amount: 0,
                discount_amount: 0,
                shipping_cost: 0,
                total_amount: 0,
                paid_amount: 0,
                balance_amount: 0,
                notes: '',
            };
        },
        async fetchOptions() {
            try {
                const [customersRes, warehousesRes] = await Promise.all([
                    axios.get('/api/v1/customers'),
                    axios.get('/api/v1/warehouses'),
                ]);
                this.customers = customersRes.data.data || customersRes.data.customers || [];
                this.warehouses = warehousesRes.data.data || warehousesRes.data.warehouses || [];
            } catch (error) {
                console.error('Failed to load options', error);
            }
        },
        async searchProducts() {
            if (!this.productSearch) return;
            try {
                const { data } = await axios.get('/api/v1/products', {
                    params: { search: this.productSearch, per_page: 10 },
                });
                this.searchResults = data.data || data.products || [];
            } catch (error) {
                console.error('Failed to search products', error);
            }
        },
        addToCart(product) {
            const existingIndex = this.cartItems.findIndex(item => item.product_id === product.id);

            if (existingIndex >= 0) {
                this.cartItems[existingIndex].quantity++;
                this.updateCartItem(existingIndex);
            } else {
                const taxAmount = (product.sale_price * (product.tax_rate || 0)) / 100;
                this.cartItems.push({
                    product_id: product.id,
                    product_name: product.name,
                    quantity: 1,
                    unit_price: parseFloat(product.sale_price),
                    discount: 0,
                    tax: parseFloat(taxAmount.toFixed(2)),
                    total: parseFloat(product.sale_price) + taxAmount,
                });
            }

            this.productSearch = '';
            this.searchResults = [];
            this.calculateTotals();
        },
        updateCartItem(index) {
            const item = this.cartItems[index];
            const subtotal = item.quantity * item.unit_price;
            item.total = subtotal - item.discount + item.tax;
            this.calculateTotals();
        },
        removeFromCart(index) {
            this.cartItems.splice(index, 1);
            this.calculateTotals();
        },
        calculateTotals() {
            const itemsSubtotal = this.cartItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
            const itemsDiscount = this.cartItems.reduce((sum, item) => sum + item.discount, 0);
            const itemsTax = this.cartItems.reduce((sum, item) => sum + item.tax, 0);

            this.form.subtotal = parseFloat(itemsSubtotal.toFixed(2));

            const total = itemsSubtotal
                - itemsDiscount
                - (this.form.discount_amount || 0)
                + itemsTax
                + (this.form.tax_amount || 0)
                + (this.form.shipping_cost || 0);

            this.form.total_amount = parseFloat(total.toFixed(2));
            this.calculateBalance();
        },
        calculateBalance() {
            this.form.balance_amount = parseFloat(
                (this.form.total_amount - (this.form.paid_amount || 0)).toFixed(2)
            );
        },
        async loadSaleForEdit(saleId) {
            if (!saleId) return;

            try {
                const response = await axios.get(`/api/v1/sales/${saleId}`);
                const sale = response.data;

                if (!sale) {
                    this.showError('Sale not found');
                    return;
                }

                console.log('Loading sale for edit:', sale);

                this.form = {
                    id: sale.id,
                    customer_id: sale.customer_id,
                    warehouse_id: sale.warehouse_id,
                    invoice_date: sale.invoice_date ? (sale.invoice_date.includes('T') ? sale.invoice_date.split('T')[0] : sale.invoice_date) : new Date().toISOString().split('T')[0],
                    due_date: sale.due_date ? (sale.due_date.includes('T') ? sale.due_date.split('T')[0] : sale.due_date) : null,
                    subtotal: parseFloat(sale.subtotal || 0),
                    tax_amount: parseFloat(sale.tax_amount || 0),
                    discount_amount: parseFloat(sale.discount_amount || 0),
                    shipping_cost: parseFloat(sale.shipping_cost || 0),
                    total_amount: parseFloat(sale.total_amount || 0),
                    paid_amount: parseFloat(sale.paid_amount || 0),
                    balance_amount: parseFloat(sale.balance_amount || 0),
                    notes: sale.notes || '',
                };

                if (sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                    this.cartItems = sale.items.map(item => {
                        const subtotal = (item.quantity || 0) * (item.unit_price || 0);
                        const discount = parseFloat(item.discount || 0);
                        const tax = parseFloat(item.tax || 0);
                        const total = subtotal - discount + tax;

                        return {
                            product_id: item.product_id,
                            product_name: item.product?.name || 'Unknown',
                            quantity: parseInt(item.quantity, 10),
                            unit_price: parseFloat(item.unit_price || 0),
                            discount: discount,
                            tax: tax,
                            total: total,
                        };
                    });
                } else {
                    this.cartItems = [];
                }

                this.calculateTotals();
            } catch (error) {
                console.error('Failed to load sale for edit:', error);
                console.error('Error response:', error.response?.data);
                this.showError(error.response?.data?.message || 'Failed to load sale details');
            }
        },
        showSuccess(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'success',
                    title: message
                });
            } else if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
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
        async save() {
            if (this.cartItems.length === 0) {
                this.showError('Please add at least one product');
                return;
            }

            if (this.$refs.formRef) {
                const { valid } = await this.$refs.formRef.validate();
                if (!valid) {
                    this.showError('Please fill in all required fields');
                    return;
                }
            }

            if (!this.form.customer_id) {
                this.showError('Please select a customer');
                return;
            }

            if (!this.form.warehouse_id) {
                this.showError('Please select a warehouse');
                return;
            }

            if (!this.form.invoice_date) {
                this.showError('Please select an invoice date');
                return;
            }

            this.saving = true;
            try {
                const payload = {
                    customer_id: Number(this.form.customer_id),
                    warehouse_id: Number(this.form.warehouse_id),
                    invoice_date: this.form.invoice_date,
                    due_date: this.form.due_date || null,
                    paid_amount: parseFloat(this.form.paid_amount) || 0,
                    discount_amount: parseFloat(this.form.discount_amount) || 0,
                    tax_amount: parseFloat(this.form.tax_amount) || 0,
                    shipping_cost: parseFloat(this.form.shipping_cost) || 0,
                    notes: this.form.notes || '',
                    payment_method: this.paymentMethod || 'cash',
                    items: this.cartItems.map(item => ({
                        product_id: Number(item.product_id),
                        quantity: parseInt(item.quantity, 10),
                        unit_price: parseFloat(item.unit_price),
                        discount: parseFloat(item.discount) || 0,
                        tax: parseFloat(item.tax) || 0,
                        notes: item.notes || '',
                    })),
                };

                console.log('Saving sale with payload:', JSON.stringify(payload, null, 2));

                let response;
                if (this.isEdit) {
                    response = await axios.put(`/api/v1/sales/${this.form.id}`, payload);
                    this.showSuccess('Sale updated successfully');
                } else {
                    response = await axios.post('/api/v1/sales', payload);
                    this.showSuccess('Sale created successfully');
                }

                console.log('Sale saved successfully:', response.data);

                this.$emit('saved');
                this.close();
            } catch (error) {
                console.error('Failed to save sale:', error);
                console.error('Error response:', error.response?.data);
                console.error('Error status:', error.response?.status);

                let errorMessage = 'Failed to save sale. Please check the console for details.';

                if (error.response?.data) {
                    if (error.response.data.message) {
                        errorMessage = error.response.data.message;
                    } else if (error.response.data.error) {
                        errorMessage = error.response.data.error;
                    } else if (error.response.data.errors) {
                        const errors = Object.values(error.response.data.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                } else if (error.message) {
                    errorMessage = error.message;
                }

                this.showError(errorMessage);
            } finally {
                this.saving = false;
            }
        },
        close() {
            this.$emit('update:modelValue', false);
            this.form = this.getEmptyForm();
            this.cartItems = [];
            this.searchResults = [];
        },
    },
};
</script>

<style scoped>
.pos-dialog {
    max-height: 95vh;
}

.pos-header {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    position: sticky;
    top: 0;
    z-index: 10;
}

.search-card {
    border-radius: 8px;
}

.cart-card,
.details-card {
    border-radius: 8px;
    height: 100%;
}

.cart-header {
    background-color: rgba(0, 0, 0, 0.02);
    font-size: 0.875rem;
}

.cart-table {
    font-size: 0.875rem;
}

.cart-table th {
    font-weight: 600;
    padding: 8px 4px;
}

.cart-table td {
    padding: 4px;
}

.cart-input :deep(.v-field) {
    font-size: 0.75rem;
}

.cart-input :deep(.v-field__input) {
    padding: 4px 8px;
    min-height: 32px;
}

.cart-row:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.search-results-container {
    margin-top: 8px;
    max-height: 180px;
    overflow-y: auto;
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-radius: 4px;
}

.search-results {
    padding: 0;
}

.search-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-item:hover {
    background-color: rgba(25, 118, 210, 0.08);
}

.totals-section {
    background-color: rgba(0, 0, 0, 0.02);
    padding: 12px;
    border-radius: 6px;
}

.compact-input {
    max-width: 120px;
}

.compact-input :deep(.v-field) {
    font-size: 0.75rem;
}

.compact-input :deep(.v-field__input) {
    padding: 4px 8px;
    min-height: 32px;
}

.total-amount {
    padding: 8px;
    background-color: rgba(25, 118, 210, 0.1);
    border-radius: 4px;
    margin-top: 8px;
}

:deep(.v-text-field--density-compact .v-field) {
    padding-top: 0;
    padding-bottom: 0;
}

:deep(.v-select--density-compact .v-field) {
    padding-top: 0;
    padding-bottom: 0;
}

:deep(.v-autocomplete--density-compact .v-field) {
    padding-top: 0;
    padding-bottom: 0;
}

:deep(.v-textarea--density-compact .v-field) {
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
