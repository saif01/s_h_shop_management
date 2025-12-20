<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Products</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add Product
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="categoryFilter" :items="categoryOptions" item-value="value"
                            item-title="label" label="Category" clearable variant="outlined" density="compact"
                            @update:model-value="loadProducts" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status" clearable
                            variant="outlined" density="compact" @update:model-value="loadProducts" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="search" label="Search by name, SKU, barcode"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadProducts" />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Products List -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Products</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('name')">
                                <div class="sortable-header">
                                    <span>Name</span>
                                    <v-icon v-if="sortBy === 'name'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('sku')">
                                <div class="sortable-header">
                                    <span>SKU</span>
                                    <v-icon v-if="sortBy === 'sku'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Category</th>
                            <th class="text-end sortable" @click="onSort('order')">
                                <div class="sortable-header justify-end">
                                    <span>Order</span>
                                    <v-icon v-if="sortBy === 'order'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end sortable" @click="onSort('purchase_price')">
                                <div class="sortable-header justify-end">
                                    <span>Purchase</span>
                                    <v-icon v-if="sortBy === 'purchase_price'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end sortable" @click="onSort('sale_price')">
                                <div class="sortable-header justify-end">
                                    <span>Sale</span>
                                    <v-icon v-if="sortBy === 'sale_price'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end">Total Stock</th>
                            <th class="text-end sortable" @click="onSort('minimum_stock_level')">
                                <div class="sortable-header justify-end">
                                    <span>Min Stock</span>
                                    <v-icon v-if="sortBy === 'minimum_stock_level'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('is_active')">
                                <div class="sortable-header">
                                    <span>Status</span>
                                    <v-icon v-if="sortBy === 'is_active'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="60"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="60" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="50"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Product Data -->
                        <template v-else>
                            <tr v-for="product in products" :key="product.id">
                                <td>
                                    <span class="font-weight-medium">{{ product.name }}</span>
                                </td>
                                <td>{{ product.sku || '-' }}</td>
                                <td>{{ product.category?.name || '-' }}</td>
                                <td class="text-end">{{ product.order || 0 }}</td>
                                <td class="text-end">৳{{ parseFloat(product.purchase_price || 0).toFixed(2) }}</td>
                                <td class="text-end">৳{{ parseFloat(product.sale_price || 0).toFixed(2) }}</td>
                                <td class="text-end">
                                    <v-chip size="small" :color="getStockStatusColor(product)" variant="tonal">
                                        {{ product.stock_quantity || 0 }}
                                    </v-chip>
                                    <v-tooltip
                                        v-if="product.stock_by_warehouse && product.stock_by_warehouse.length > 0"
                                        activator="parent" location="top">
                                        <div class="text-caption">
                                            <div v-for="stock in product.stock_by_warehouse" :key="stock.warehouse_id"
                                                class="py-1">
                                                <strong>{{ stock.warehouse_name }}:</strong> {{ stock.quantity || 0 }}
                                            </div>
                                        </div>
                                    </v-tooltip>
                                </td>
                                <td class="text-end">{{ product.minimum_stock_level || 0 }}</td>
                                <td>
                                    <v-chip size="small" :color="product.is_active ? 'success' : 'error'"
                                        variant="tonal">
                                        {{ product.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn icon="mdi-eye" variant="text" size="small" color="info"
                                        @click="openViewDialog(product)" :title="'View Product'" />
                                    <v-btn icon="mdi-pencil" variant="text" size="small" @click="openDialog(product)" />
                                    <v-btn icon="mdi-package-variant" variant="text" size="small" color="primary"
                                        @click="openStockDialog(product)" :title="'Adjust Stock'" />
                                    <v-btn icon="mdi-delete" variant="text" size="small" color="error"
                                        @click="deleteProduct(product)" />
                                </td>
                            </tr>
                            <tr v-if="products.length === 0">
                                <td colspan="10" class="text-center py-4">No products found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="products.length > 0 && pagination.total > 0">
                            <span v-if="perPage === 'all'">
                                Showing <strong>all {{ pagination.total.toLocaleString() }}</strong> records
                            </span>
                            <span v-else>
                                Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                                <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                                <strong>{{ pagination.total.toLocaleString() }}</strong> records
                            </span>
                        </span>
                        <span v-else>No records found</span>
                    </div>

                    <!-- Right: Items Per Page and Pagination -->
                    <PaginationControls v-model="currentPage" :pagination="pagination" :per-page-value="perPage"
                        :per-page-options="perPageOptions" @update:per-page="onPerPageUpdate"
                        @page-change="onPageChange" />
                </div>
            </v-card-text>
        </v-card>

        <product-dialog v-model="dialog" :product="editingProduct" @saved="loadProducts" />
        <product-view-dialog v-model="viewDialog" :product="viewingProduct" />
        <stock-adjustment-dialog v-model="stockDialog" :product="selectedProduct" @saved="loadProducts" />
    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import ProductDialog from './dialogs/product/ProductDialog.vue';
import ProductViewDialog from './dialogs/product/ProductViewDialog.vue';
import StockAdjustmentDialog from './dialogs/product/StockAdjustmentDialog.vue';
import PaginationControls from '../../common/PaginationControls.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';

export default {
    name: 'AdminProducts',
    components: {
        ProductDialog,
        ProductViewDialog,
        StockAdjustmentDialog,
        PaginationControls
    },
    mixins: [commonMixin],
    data() {
        return {
            products: [],
            categoryOptions: [],
            categoryFilter: null,
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            activeFilter: null,
            dialog: false,
            editingProduct: null,
            viewDialog: false,
            viewingProduct: null,
            stockDialog: false,
            selectedProduct: null,
            // Sorting state - default to order ASC
            sortBy: 'order',
            sortDirection: 'asc',
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    async mounted() {
        await this.loadOptions();
        await this.loadProducts();
    },
    methods: {
        async loadOptions() {
            try {
                const response = await this.$axios.get('/api/v1/products/categories', {
                    headers: this.getAuthHeaders()
                });
                this.categoryOptions = response.data.categories || [];
            } catch (error) {
                console.error('Failed to load categories', error);
            }
        },
        async loadProducts() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                // Handle "Show All" option
                if (this.perPage === 'all') {
                    params.per_page = 999999; // Very large number to get all records
                }

                if (this.search) {
                    params.search = this.search;
                }
                if (this.categoryFilter) {
                    params.category_id = this.categoryFilter;
                }
                if (this.activeFilter !== null) {
                    params.is_active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/products', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.products = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load products');
            } finally {
                this.loading = false;
            }
        },
        onSort(field) {
            this.handleSort(field);
            this.loadProducts();
        },
        openDialog(product) {
            this.editingProduct = product;
            this.dialog = true;
        },
        openViewDialog(product) {
            this.viewingProduct = product;
            this.viewDialog = true;
        },
        openStockDialog(product) {
            this.selectedProduct = product;
            this.stockDialog = true;
        },
        async deleteProduct(product) {
            if (!confirm(`Are you sure you want to delete ${product.name}?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/products/${product.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Product deleted successfully');
                await this.loadProducts();
            } catch (error) {
                this.handleApiError(error, 'Error deleting product');
            }
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadProducts();
        },
        getStockStatusColor(product) {
            const stockQty = product.stock_quantity || 0;
            const minStock = product.minimum_stock_level || 0;
            if (stockQty <= 0) return 'error';
            if (minStock > 0 && stockQty <= minStock) return 'warning';
            return 'success';
        },
        buildPaginationParams(additionalParams = {}) {
            return paginationUtils.buildPaginationParams(
                this.currentPage,
                this.perPage,
                additionalParams,
                this.sortBy,
                this.sortDirection
            );
        },
        updatePagination(responseData) {
            paginationUtils.updatePagination(this, responseData);
        },
        resetPagination() {
            paginationUtils.resetPagination(this);
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadProducts();
        },
    },
};
</script>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-title {
    margin: 0;
}

.sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
    position: relative;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.04);
}

/* Ensure icons are visible on table header */
:deep(.v-table thead th) {
    background-color: rgba(var(--v-theme-surface), 1);
}

:deep(.v-table thead th.sortable) {
    background-color: rgba(var(--v-theme-surface), 1);
}

:deep(.v-table thead th.sortable:hover) {
    background-color: rgba(0, 0, 0, 0.04);
}

.sortable-header {
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: flex-start;
    width: 100%;
}

.sortable-header.justify-end {
    justify-content: flex-end;
}

.sort-icon {
    flex-shrink: 0;
    transition: opacity 0.2s, color 0.2s, background-color 0.2s;
    display: inline-flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    font-size: 18px !important;
    width: 18px !important;
    height: 18px !important;
    line-height: 1 !important;
    background-color: white;
    border-radius: 4px;
    padding: 2px;
}

.sort-icon.active {
    opacity: 1 !important;
    color: rgb(var(--v-theme-primary)) !important;
    visibility: visible !important;
    background-color: white !important;
}

.sort-icon.active :deep(svg),
.sort-icon.active :deep(path) {
    fill: currentColor !important;
    color: rgb(var(--v-theme-primary)) !important;
    opacity: 1 !important;
}

.sort-icon.inactive {
    opacity: 0.7 !important;
    color: #424242 !important;
    visibility: visible !important;
    background-color: white !important;
}

.sort-icon.inactive :deep(svg),
.sort-icon.inactive :deep(path) {
    fill: #424242 !important;
    color: #424242 !important;
    opacity: 0.7 !important;
}

.sortable:hover .sort-icon.inactive {
    opacity: 1 !important;
    color: #212121 !important;
    background-color: white !important;
}

.sortable:hover .sort-icon.inactive :deep(svg),
.sortable:hover .sort-icon.inactive :deep(path) {
    fill: #212121 !important;
    color: #212121 !important;
    opacity: 1 !important;
}
</style>

<style>
/* Non-scoped styles for add button - ensures it works across all scenarios */
.page-header .add-button {
    visibility: visible !important;
    opacity: 1 !important;
    display: inline-flex !important;
}

.page-header .add-button .v-btn__content {
    visibility: visible !important;
    opacity: 1 !important;
    display: inline-flex !important;
    align-items: center !important;
}

.page-header .add-button .v-btn__prepend {
    visibility: visible !important;
    opacity: 1 !important;
    display: inline-flex !important;
    margin-inline-end: 8px !important;
}

.page-header .add-button .v-icon {
    visibility: visible !important;
    opacity: 1 !important;
    display: inline-flex !important;
    font-size: 20px !important;
}
</style>
