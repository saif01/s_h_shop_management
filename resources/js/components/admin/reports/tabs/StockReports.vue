<template>
    <div>
        <!-- Filters -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.warehouse_id" :items="warehouses" item-value="id" item-title="name"
                            label="Warehouse" variant="outlined" density="compact" clearable
                            @update:model-value="onFilterChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.category_id" :items="categories" item-value="id" item-title="name"
                            label="Category" variant="outlined" density="compact" clearable
                            @update:model-value="onFilterChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.report_type" :items="reportTypes" label="Report Type"
                            variant="outlined" density="compact" @update:model-value="onFilterChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-switch v-model="filters.low_stock_only" label="Low Stock Only" density="compact"
                            @update:model-value="onFilterChange" />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Summary Cards -->
        <v-row v-if="summary" class="mb-4" dense>
            <v-col cols="12" sm="6" md="3">
                <v-card color="primary" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Total Products</div>
                        <div class="text-h6 font-weight-bold">{{ summary.total_products || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card color="success" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Total Stock Value</div>
                        <div class="text-h6 font-weight-bold">{{ formatCurrency(summary.total_stock_value) }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card color="warning" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Low Stock Items</div>
                        <div class="text-h6 font-weight-bold">{{ summary.low_stock_count || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card color="error" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Out of Stock</div>
                        <div class="text-h6 font-weight-bold">{{ summary.out_of_stock_count || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Report Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Stock Report</span>
                <div class="d-flex align-center gap-2">
                    <v-btn color="success" size="small" prepend-icon="mdi-file-excel" @click="exportExcel"
                        :disabled="loading || !reportData.length" :loading="exporting">
                        Export Excel
                    </v-btn>
                    <v-btn color="error" size="small" prepend-icon="mdi-file-pdf-box" @click="exportPDF"
                        :disabled="loading || !reportData.length" :loading="exporting">
                        Export PDF
                    </v-btn>
                    <span class="text-caption text-grey">
                        Total Records: <strong>{{ (pagination.total || 0).toLocaleString() }}</strong>
                        <span v-if="reportData.length > 0">
                            | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage,
                                pagination.total).toLocaleString() }} of {{ pagination.total.toLocaleString() }}
                        </span>
                    </span>
                </div>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('product_name')">
                                <div class="sortable-header">
                                    <span>Product</span>
                                    <v-icon v-if="sortBy === 'product_name'" size="18" class="sort-icon active">
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
                            <th class="sortable" @click="onSort('category_name')">
                                <div class="sortable-header">
                                    <span>Category</span>
                                    <v-icon v-if="sortBy === 'category_name'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('warehouse_name')">
                                <div class="sortable-header">
                                    <span>Warehouse</span>
                                    <v-icon v-if="sortBy === 'warehouse_name'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-center sortable" @click="onSort('quantity')">
                                <div class="sortable-header justify-center">
                                    <span>Quantity</span>
                                    <v-icon v-if="sortBy === 'quantity'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-center sortable" @click="onSort('minimum_stock_level')">
                                <div class="sortable-header justify-center">
                                    <span>Min Level</span>
                                    <v-icon v-if="sortBy === 'minimum_stock_level'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end sortable" @click="onSort('stock_value')">
                                <div class="sortable-header justify-end">
                                    <span>Stock Value</span>
                                    <v-icon v-if="sortBy === 'stock_value'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('status')">
                                <div class="sortable-header">
                                    <span>Status</span>
                                    <v-icon v-if="sortBy === 'status'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                        </tr>
                        <!-- Actual Report Data -->
                        <template v-else>
                            <tr v-for="item in reportData" :key="item.id || item.product_id || item.sku">
                                <td>{{ item.product_name }}</td>
                                <td>{{ item.sku }}</td>
                                <td>{{ item.category_name }}</td>
                                <td>{{ item.warehouse_name }}</td>
                                <td class="text-center">
                                    <v-chip :color="getStockColor(item)" size="small" variant="flat">
                                        {{ item.quantity || 0 }}
                                    </v-chip>
                                </td>
                                <td class="text-center">{{ item.minimum_stock_level || 0 }}</td>
                                <td class="text-end">{{ formatCurrency((item.quantity || 0) * (item.purchase_price ||
                                    0)) }}</td>
                                <td>
                                    <v-chip :color="getStockColor(item)" size="small" variant="flat">
                                        {{ getStockStatus(item) }}
                                    </v-chip>
                                </td>
                            </tr>
                            <tr v-if="reportData.length === 0">
                                <td colspan="8" class="text-center py-4">No stock data found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="reportData.length > 0 && pagination.total > 0">
                            <span v-if="perPage === 'all'">
                                Showing <strong>all {{ pagination.total.toLocaleString() }}</strong> records
                            </span>
                            <span v-else>
                                Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                                <strong>{{ Math.min(currentPage * perPage, pagination.total).toLocaleString()
                                }}</strong> of
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
    </div>
</template>

<script>
import commonMixin from '../../../../mixins/commonMixin';
import PaginationControls from '../../../common/PaginationControls.vue';
import { paginationMixin } from '../../../../utils/pagination.js';

export default {
    name: 'StockReports',
    components: {
        PaginationControls
    },
    mixins: [commonMixin, paginationMixin],
    data() {
        return {
            loading: false,
            exporting: false,
            filters: {
                warehouse_id: null,
                category_id: null,
                report_type: 'current',
                low_stock_only: false,
            },
            reportData: [],
            summary: null,
            warehouses: [],
            categories: [],
            reportTypes: [
                { title: 'Current Stock', value: 'current' },
                { title: 'Stock Ledger', value: 'ledger' },
                { title: 'Low Stock', value: 'low_stock' },
            ],
        };
    },
    mounted() {
        this.fetchOptions();
        this.generateReport();
    },
    methods: {
        async fetchOptions() {
            try {
                const [warehousesRes, categoriesRes] = await Promise.all([
                    this.$axios.get('/api/v1/warehouses', {
                        headers: this.getAuthHeaders()
                    }),
                    this.$axios.get('/api/v1/categories', {
                        headers: this.getAuthHeaders()
                    }),
                ]);
                this.warehouses = warehousesRes.data.data || warehousesRes.data.warehouses || [];
                this.categories = categoriesRes.data.data || categoriesRes.data.categories || [];
            } catch (error) {
                console.error('Failed to fetch options', error);
            }
        },
        async generateReport() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                // Handle "Show All" option
                if (this.perPage === 'all') {
                    params.per_page = 999999;
                }

                // Add filter params
                if (this.filters.warehouse_id) {
                    params.warehouse_id = this.filters.warehouse_id;
                }
                if (this.filters.category_id) {
                    params.category_id = this.filters.category_id;
                }
                if (this.filters.report_type) {
                    params.report_type = this.filters.report_type;
                }
                if (this.filters.low_stock_only) {
                    params.low_stock_only = this.filters.low_stock_only;
                }

                const response = await this.$axios.get('/api/v1/reports/stock', {
                    params,
                    headers: this.getAuthHeaders()
                });
                // Handle both paginated response (data) and legacy format (stock)
                this.reportData = response.data.data || response.data.stock || [];
                this.summary = response.data.summary || null;
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to generate stock report');
            } finally {
                this.loading = false;
            }
        },
        async exportExcel() {
            try {
                this.exporting = true;
                const response = await this.$axios.get('/api/v1/reports/stock/export/excel', {
                    params: this.filters,
                    responseType: 'blob',
                    headers: this.getAuthHeaders()
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `stock_report_${Date.now()}.xlsx`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
                this.$toast?.success('Report exported successfully');
            } catch (error) {
                console.error('Failed to export Excel', error);
                this.$toast?.error('Failed to export report');
            } finally {
                this.exporting = false;
            }
        },
        async exportPDF() {
            try {
                this.exporting = true;
                // Prepare params - only send filters that the controller uses
                const params = {};
                if (this.filters.warehouse_id) {
                    params.warehouse_id = this.filters.warehouse_id;
                }
                if (this.filters.category_id) {
                    params.category_id = this.filters.category_id;
                }
                if (this.filters.low_stock_only) {
                    params.low_stock_only = this.filters.low_stock_only;
                }
                const response = await this.$axios.get('/api/v1/reports/stock/export/pdf', {
                    params,
                    responseType: 'blob',
                    headers: this.getAuthHeaders()
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `stock_report_${Date.now()}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
                this.$toast?.success('Report exported successfully');
            } catch (error) {
                console.error('Failed to export PDF', error);
                this.$toast?.error('Failed to export report');
            } finally {
                this.exporting = false;
            }
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '৳0.00';
            return '৳' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },
        getStockColor(item) {
            if (!item.quantity || item.quantity === 0) return 'error';
            if (item.quantity <= item.minimum_stock_level) return 'warning';
            return 'success';
        },
        getStockStatus(item) {
            if (!item.quantity || item.quantity === 0) return 'Out of Stock';
            if (item.quantity <= item.minimum_stock_level) return 'Low Stock';
            return 'In Stock';
        },
        onFilterChange() {
            this.resetPagination();
            this.generateReport();
        },
    },
};
</script>

<style scoped>
.summary-card {
    height: 100%;
}

.summary-card .v-card-text {
    padding: 12px 16px;
}

.gap-2 {
    gap: 8px;
}

.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.04);
}

.sortable-header {
    display: flex;
    align-items: center;
    gap: 4px;
}

.sort-icon {
    opacity: 0.5;
    transition: opacity 0.2s;
}

.sort-icon.active {
    opacity: 1;
    color: rgb(var(--v-theme-primary));
}
</style>
