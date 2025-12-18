<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" 
        max-width="1200px" persistent fullscreen>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center bg-primary">
                <span class="text-h5 text-white">
                    <v-icon class="text-white">mdi-point-of-sale</v-icon>
                    {{ isEdit ? 'Edit Sale' : 'New Sale - POS' }}
                </span>
                <v-btn icon="mdi-close" variant="text" class="text-white" @click="close" />
            </v-card-title>
            
            <v-card-text class="pa-4">
                <v-row>
                    <!-- Left Side - Product Search & Cart -->
                    <v-col cols="12" md="8">
                        <!-- Product Search -->
                        <v-card variant="outlined" class="mb-4">
                            <v-card-text>
                                <v-text-field v-model="productSearch" label="Search Product (Name/SKU/Barcode)" 
                                    prepend-inner-icon="mdi-magnify" clearable autofocus
                                    @keyup.enter="searchProducts" @click:clear="searchResults = []" />
                                
                                <!-- Search Results -->
                                <v-list v-if="searchResults.length > 0" class="mt-2" density="compact">
                                    <v-list-item v-for="product in searchResults" :key="product.id"
                                        @click="addToCart(product)">
                                        <template #prepend>
                                            <v-avatar size="40">
                                                <v-img v-if="product.image" :src="product.image" />
                                                <v-icon v-else>mdi-package-variant</v-icon>
                                            </v-avatar>
                                        </template>
                                        <v-list-item-title>{{ product.name }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            SKU: {{ product.sku || 'N/A' }} | Stock: {{ product.stock_quantity || 0 }}
                                        </v-list-item-subtitle>
                                        <template #append>
                                            <v-chip color="primary" size="small">${{ product.sale_price }}</v-chip>
                                        </template>
                                    </v-list-item>
                                </v-list>
                            </v-card-text>
                        </v-card>

                        <!-- Cart Items -->
                        <v-card variant="outlined">
                            <v-card-title class="bg-grey-lighten-4">
                                <v-icon>mdi-cart</v-icon> Cart Items
                            </v-card-title>
                            <v-divider />
                            <v-card-text>
                                <v-table v-if="cartItems.length > 0" density="compact">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th width="100">Qty</th>
                                            <th width="120">Price</th>
                                            <th width="100">Disc.</th>
                                            <th width="100">Tax</th>
                                            <th width="120">Total</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in cartItems" :key="index">
                                            <td>{{ item.product_name }}</td>
                                            <td>
                                                <v-text-field v-model.number="item.quantity" type="number" 
                                                    min="1" density="compact" hide-details
                                                    @update:model-value="updateCartItem(index)" />
                                            </td>
                                            <td>
                                                <v-text-field v-model.number="item.unit_price" type="number" 
                                                    min="0" step="0.01" density="compact" hide-details
                                                    @update:model-value="updateCartItem(index)" />
                                            </td>
                                            <td>
                                                <v-text-field v-model.number="item.discount" type="number" 
                                                    min="0" step="0.01" density="compact" hide-details
                                                    @update:model-value="updateCartItem(index)" />
                                            </td>
                                            <td>
                                                <v-text-field v-model.number="item.tax" type="number" 
                                                    min="0" step="0.01" density="compact" hide-details
                                                    @update:model-value="updateCartItem(index)" />
                                            </td>
                                            <td class="font-weight-bold">${{ item.total.toFixed(2) }}</td>
                                            <td>
                                                <v-btn icon="mdi-delete" size="small" variant="text" 
                                                    color="error" @click="removeFromCart(index)" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                                <v-alert v-else type="info" variant="tonal" class="mt-2">
                                    Cart is empty. Search and add products.
                                </v-alert>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Right Side - Sale Details & Totals -->
                    <v-col cols="12" md="4">
                        <v-card variant="outlined">
                            <v-card-text>
                                <!-- Customer Selection -->
                                <v-autocomplete v-model="form.customer_id" :items="customers" 
                                    item-value="id" item-title="name" label="Customer *" 
                                    clearable :rules="[rules.required]">
                                    <template #prepend>
                                        <v-icon>mdi-account</v-icon>
                                    </template>
                                </v-autocomplete>

                                <!-- Warehouse Selection -->
                                <v-select v-model="form.warehouse_id" :items="warehouses" 
                                    item-value="id" item-title="name" label="Warehouse *" 
                                    :rules="[rules.required]" />

                                <!-- Invoice Date -->
                                <v-text-field v-model="form.invoice_date" type="date" 
                                    label="Invoice Date *" :rules="[rules.required]" />

                                <!-- Due Date -->
                                <v-text-field v-model="form.due_date" type="date" label="Due Date" />

                                <v-divider class="my-4" />

                                <!-- Subtotal -->
                                <v-text-field v-model.number="form.subtotal" label="Subtotal" 
                                    readonly prepend-icon="mdi-calculator" />

                                <!-- Discount -->
                                <v-text-field v-model.number="form.discount_amount" type="number" 
                                    label="Invoice Discount" min="0" step="0.01"
                                    @update:model-value="calculateTotals" />

                                <!-- Tax -->
                                <v-text-field v-model.number="form.tax_amount" type="number" 
                                    label="Additional Tax" min="0" step="0.01"
                                    @update:model-value="calculateTotals" />

                                <!-- Shipping -->
                                <v-text-field v-model.number="form.shipping_cost" type="number" 
                                    label="Shipping Cost" min="0" step="0.01"
                                    @update:model-value="calculateTotals" />

                                <v-divider class="my-4" />

                                <!-- Total Amount -->
                                <v-text-field v-model.number="form.total_amount" label="Total Amount" 
                                    readonly class="text-h6" prepend-icon="mdi-cash" />

                                <!-- Paid Amount -->
                                <v-text-field v-model.number="form.paid_amount" type="number" 
                                    label="Paid Amount" min="0" step="0.01"
                                    @update:model-value="calculateBalance" />

                                <!-- Balance/Due -->
                                <v-text-field v-model.number="form.balance_amount" label="Balance (Due)" 
                                    readonly :class="form.balance_amount > 0 ? 'text-error' : 'text-success'" />

                                <!-- Payment Method -->
                                <v-select v-model="paymentMethod" :items="paymentMethods" 
                                    label="Payment Method" v-if="form.paid_amount > 0" />

                                <!-- Notes -->
                                <v-textarea v-model="form.notes" label="Notes" rows="2" />
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-divider />
            <v-card-actions class="justify-end pa-4">
                <v-btn variant="text" @click="close">Cancel</v-btn>
                <v-btn color="success" :loading="saving" :disabled="cartItems.length === 0"
                    @click="save">
                    <v-icon>mdi-content-save</v-icon> {{ isEdit ? 'Update' : 'Save Sale' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'SaleDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        sale: { type: Object, default: null },
    },
    emits: ['update:modelValue', 'saved'],
    data() {
        return {
            form: this.getEmptyForm(),
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
                if (newVal) {
                    this.form = { ...this.getEmptyForm(), ...newVal };
                    await this.loadSaleItems();
                } else {
                    this.form = this.getEmptyForm();
                    this.cartItems = [];
                }
            },
        },
        modelValue(val) {
            if (val) {
                this.fetchOptions();
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
        async loadSaleItems() {
            if (!this.form.id) return;
            try {
                const { data } = await axios.get(`/api/v1/sales/${this.form.id}`);
                const sale = data.data || data.sale;
                if (sale.items) {
                    this.cartItems = sale.items.map(item => ({
                        product_id: item.product_id,
                        product_name: item.product?.name || 'Unknown',
                        quantity: item.quantity,
                        unit_price: parseFloat(item.unit_price),
                        discount: parseFloat(item.discount || 0),
                        tax: parseFloat(item.tax || 0),
                        total: parseFloat(item.total),
                    }));
                }
            } catch (error) {
                console.error('Failed to load sale items', error);
            }
        },
        async save() {
            if (this.cartItems.length === 0) {
                this.$toast?.error('Please add at least one product');
                return;
            }
            
            if (!this.form.customer_id || !this.form.warehouse_id) {
                this.$toast?.error('Please select customer and warehouse');
                return;
            }

            this.saving = true;
            try {
                const payload = {
                    ...this.form,
                    items: this.cartItems,
                    payment_method: this.paymentMethod,
                };
                
                if (this.isEdit) {
                    await axios.put(`/api/v1/sales/${this.form.id}`, payload);
                    this.$toast?.success('Sale updated successfully');
                } else {
                    await axios.post('/api/v1/sales', payload);
                    this.$toast?.success('Sale created successfully');
                }
                
                this.$emit('saved');
                this.close();
            } catch (error) {
                console.error('Failed to save sale', error);
                this.$toast?.error(error.response?.data?.message || 'Failed to save sale');
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

