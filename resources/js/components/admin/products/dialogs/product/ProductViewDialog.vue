<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="800px"
        persistent scrollable>
        <v-card class="product-view-dialog">
            <!-- Header -->
            <v-card-title class="dialog-header d-flex justify-space-between align-center pa-3">
                <span class="text-h6 font-weight-medium">Product Details</span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="close" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <div v-if="loading" class="text-center py-8">
                    <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                    <div class="mt-3 text-body-2">Loading product details...</div>
                </div>
                <div v-else-if="!displayProduct" class="text-center py-8">
                    <v-alert type="error" variant="tonal" class="ma-3" density="compact">
                        Failed to load product details
                    </v-alert>
                </div>
                <div v-else>
                    <!-- Basic Information Section -->
                    <div class="text-subtitle-2 font-weight-medium mb-2">Product Information</div>
                    <v-card variant="outlined" class="mb-2">
                        <v-card-text class="pa-2">
                            <v-row dense class="ma-0">
                                <v-col cols="12" sm="3" class="pa-2" v-if="displayProduct.image">
                                    <div class="text-center">
                                        <v-img :src="displayProduct.image" max-width="120" max-height="120" class="mx-auto"
                                            style="border-radius: 8px;" cover></v-img>
                                    </div>
                                </v-col>
                                <v-col :cols="displayProduct.image ? 12 : 12" :sm="displayProduct.image ? 9 : 12" class="pa-2">
                                    <v-row dense class="ma-0">
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">ID:</span>
                                                <span class="text-body-2 font-weight-medium ml-1">{{ displayProduct.id || '-'
                                                    }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Status:</span>
                                                <v-chip size="x-small" :color="displayProduct.is_active ? 'success' : 'error'"
                                                    variant="tonal" class="ml-1">
                                                    {{ displayProduct.is_active ? 'Active' : 'Inactive' }}
                                                </v-chip>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">SKU:</span>
                                                <span class="text-body-2 ml-1">{{ displayProduct.sku || '-' }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Barcode:</span>
                                                <span class="text-body-2 ml-1">{{ displayProduct.barcode || '-' }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Brand:</span>
                                                <span class="text-body-2 ml-1">{{ displayProduct.brand || '-' }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Category:</span>
                                                <span class="text-body-2 ml-1">{{ displayProduct.category?.name || '-'
                                                    }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Unit:</span>
                                                <span class="text-body-2 ml-1">{{ displayProduct.unit?.name || '-' }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Name:</span>
                                                <span class="text-body-2 font-weight-medium ml-1">{{ displayProduct.name || '-'
                                                    }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" class="pa-2" v-if="displayProduct.description">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Description:</span>
                                                <span class="text-body-2 ml-1">{{ displayProduct.description }}</span>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Pricing & Stock Information Section -->
                    <v-divider class="my-2" />
                    <div class="text-subtitle-2 font-weight-medium mb-2">Pricing & Stock</div>
                    <v-card variant="outlined" class="mb-2">
                        <v-card-text class="pa-2">
                            <v-row dense class="ma-0">
                                <v-col cols="6" sm="3" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Purchase:</span>
                                        <span class="text-body-2 font-weight-medium ml-1">৳{{
                                            parseFloat(displayProduct.purchase_price || 0).toFixed(2) }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="6" sm="3" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Sale:</span>
                                        <span class="text-body-2 font-weight-medium ml-1">৳{{
                                            parseFloat(displayProduct.sale_price || 0).toFixed(2) }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="6" sm="2" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Tax:</span>
                                        <span class="text-body-2 ml-1">{{ parseFloat(displayProduct.tax_rate || 0).toFixed(1)
                                            }}%</span>
                                    </div>
                                </v-col>
                                <v-col cols="6" sm="2" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Profit:</span>
                                        <span class="text-body-2 font-weight-medium text-success ml-1">৳{{
                                            profitPerUnit.toFixed(2) }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="6" sm="2" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Margin:</span>
                                        <span class="text-body-2 font-weight-medium text-success ml-1">{{
                                            profitMargin.toFixed(1) }}%</span>
                                    </div>
                                </v-col>
                                <v-col cols="6" sm="4" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Total Stock:</span>
                                        <v-chip size="x-small" :color="getStockStatusColor(totalStock)" variant="tonal"
                                            class="ml-1 font-weight-medium">
                                            {{ totalStock }}
                                        </v-chip>
                                    </div>
                                </v-col>
                                <v-col cols="6" sm="4" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Min Stock:</span>
                                        <span class="text-body-2 ml-1">{{ displayProduct.minimum_stock_level || 0 }}</span>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Created & Updated Information Section -->
                    <v-divider class="my-2" />
                    <div class="text-subtitle-2 font-weight-medium mb-2">Audit Information</div>
                    <v-card variant="outlined" class="mb-2">
                        <v-card-text class="pa-2">
                            <v-row dense class="ma-0">
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Created By:</span>
                                        <span class="text-body-2 ml-1">{{ displayProduct.created_by?.name || '-' }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Created At:</span>
                                        <span class="text-body-2 ml-1">{{ formatDateShort(displayProduct.created_at) || '-'
                                            }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Updated By:</span>
                                        <span class="text-body-2 ml-1">{{ displayProduct.updated_by?.name || '-' }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Updated At:</span>
                                        <span class="text-body-2 ml-1">{{ formatDateShort(displayProduct.updated_at) || '-'
                                            }}</span>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Stock by Warehouse -->
                    <v-divider class="my-2"
                        v-if="displayProduct.stock_by_warehouse && displayProduct.stock_by_warehouse.length > 0" />
                    <div v-if="displayProduct.stock_by_warehouse && displayProduct.stock_by_warehouse.length > 0"
                        class="text-subtitle-2 font-weight-medium mb-2">Stock by Warehouse</div>
                    <v-card v-if="displayProduct.stock_by_warehouse && displayProduct.stock_by_warehouse.length > 0"
                        variant="outlined" class="mb-2">
                        <v-card-text class="pa-2">
                            <v-table density="compact">
                                <thead>
                                    <tr>
                                        <th>Warehouse</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Avg Cost</th>
                                        <th class="text-end">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="stock in displayProduct.stock_by_warehouse" :key="stock.warehouse_id">
                                        <td>
                                            <span class="font-weight-medium">{{ stock.warehouse_name }}</span>
                                            <span v-if="stock.warehouse_code" class="text-caption text-grey ml-1">
                                                ({{ stock.warehouse_code }})
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <v-chip size="x-small" :color="getStockStatusColor(stock.quantity)"
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
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-2 justify-end">
                <v-btn variant="text" size="small" @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'ProductViewDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        product: { type: Object, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            loading: false,
            productData: null,
        };
    },
    computed: {
        displayProduct() {
            return this.productData || this.product;
        },
        totalStock() {
            if (!this.displayProduct?.stock_by_warehouse || this.displayProduct.stock_by_warehouse.length === 0) {
                return 0;
            }
            return this.displayProduct.stock_by_warehouse.reduce((sum, stock) => sum + (parseFloat(stock.quantity) || 0), 0);
        },
        profitPerUnit() {
            const purchasePrice = parseFloat(this.displayProduct?.purchase_price || 0);
            const salePrice = parseFloat(this.displayProduct?.sale_price || 0);
            return salePrice - purchasePrice;
        },
        profitMargin() {
            const purchasePrice = parseFloat(this.displayProduct?.purchase_price || 0);
            const salePrice = parseFloat(this.displayProduct?.sale_price || 0);
            if (salePrice === 0) return 0;
            return ((salePrice - purchasePrice) / salePrice) * 100;
        },
    },
    watch: {
        modelValue(newVal) {
            if (newVal && this.product && this.product.id) {
                this.loadProductDetails();
            } else {
                this.productData = null;
            }
        },
        product: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal.id) {
                    this.loadProductDetails();
                } else {
                    this.productData = null;
                }
            },
        },
    },
    methods: {
        async loadProductDetails() {
            if (!this.product?.id) return;

            this.loading = true;
            try {
                const response = await axios.get(`/api/v1/products/${this.product.id}`);
                this.productData = response.data;
            } catch (error) {
                console.error('Failed to load product details', error);
                this.$toast?.error('Failed to load product details');
            } finally {
                this.loading = false;
            }
        },
        getStockStatusColor(quantity) {
            if (!this.displayProduct?.minimum_stock_level) return 'default';
            if (quantity <= 0) return 'error';
            if (quantity <= this.displayProduct.minimum_stock_level) return 'warning';
            return 'success';
        },
        formatDateShort(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            const formattedHours = String(hours).padStart(2, '0');

            return `${day}/${month}/${year} ${formattedHours}:${minutes} ${ampm}`;
        },
        close() {
            this.$emit('update:modelValue', false);
        },
    },
};
</script>

<style scoped>
.product-view-dialog {
    max-height: 90vh;
}

.dialog-header {
    background-color: rgba(0, 0, 0, 0.02);
}

.info-item-compact {
    display: flex;
    align-items: center;
    min-height: 32px;
}
</style>
