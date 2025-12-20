<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Stock Ledger</h1>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="productFilter" :items="productOptions" label="Filter by Product"
                            variant="outlined" density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="warehouseFilter" :items="warehouseOptions" label="Filter by Warehouse"
                            variant="outlined" density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="typeFilter" :items="typeOptions" label="Filter by Type" variant="outlined"
                            density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="referenceTypeFilter" :items="referenceTypeOptions"
                            label="Filter by Reference" variant="outlined" density="compact" clearable
                            @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="dateFrom" label="Date From" density="compact"
                            @update:model-value="onDateFromChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="dateTo" label="Date To" density="compact"
                            @update:model-value="onDateToChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by reference number or product"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadLedger"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Stock Ledger Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Stock Movements</span>
                <div class="d-flex align-center gap-2">
                    <v-btn color="success" size="small" prepend-icon="mdi-file-excel" @click="exportToExcel"
                        :disabled="loading || ledgers.length === 0" :loading="exporting">
                        Export Excel
                    </v-btn>
                    <span class="text-caption text-grey">
                        Total Records: <strong>{{ pagination.total || 0 }}</strong>
                        <span v-if="ledgers.length > 0">
                            | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage,
                                pagination.total) }} of {{ pagination.total }}
                        </span>
                    </span>
                </div>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('transaction_date')">
                                <div class="sortable-header">
                                    <span>Date</span>
                                    <v-icon v-if="sortBy === 'transaction_date'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th class="text-end sortable" @click="onSort('quantity')">
                                <div class="sortable-header justify-end">
                                    <span>Quantity</span>
                                    <v-icon v-if="sortBy === 'quantity'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Balance After</th>
                            <th>Created By</th>
                            <th class="sortable" @click="onSort('created_at')">
                                <div class="sortable-header">
                                    <span>Created At</span>
                                    <v-icon v-if="sortBy === 'created_at'" size="18" class="sort-icon active">
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
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                        </tr>
                        <!-- Actual Ledger Data -->
                        <template v-else>
                            <tr v-for="ledger in ledgers" :key="ledger.id">
                                <td>{{ formatDateDDMMYYYY(ledger.transaction_date) }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-medium">{{ ledger.product?.name }}</span>
                                        <span class="text-caption text-grey">{{ ledger.product?.sku }}</span>
                                    </div>
                                </td>
                                <td>{{ ledger.warehouse?.name }}</td>
                                <td>
                                    <v-chip :color="ledger.type === 'in' ? 'success' : 'error'" size="small">
                                        {{ ledger.type === 'in' ? 'IN' : 'OUT' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-body-2">{{ formatReferenceType(ledger.reference_type)
                                            }}</span>
                                        <span class="text-caption text-grey">{{ ledger.reference_number || '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-right">{{ ledger.quantity }}</td>
                                <td class="text-right">{{ formatCurrency(ledger.unit_cost) }}</td>
                                <td class="text-right">{{ formatCurrency(ledger.total_cost) }}</td>
                                <td class="text-right">
                                    <v-chip size="small" :color="ledger.balance_after < 0 ? 'error' : 'default'">
                                        {{ ledger.balance_after }}
                                    </v-chip>
                                </td>
                                <td>{{ ledger.creator?.name || '-' }}</td>
                                <td>{{ formatDateShort(ledger.created_at) }}</td>
                            </tr>
                            <tr v-if="ledgers.length === 0">
                                <td colspan="11" class="text-center py-4">No stock movements found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="ledgers.length > 0 && pagination.total > 0">
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
    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import PaginationControls from '../../common/PaginationControls.vue';
import DatePicker from '../../common/DatePicker.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';

export default {
    components: {
        PaginationControls,
        DatePicker
    },
    mixins: [commonMixin],
    data() {
        return {
            ledgers: [],
            products: [],
            warehouses: [],
            productFilter: null,
            productOptions: [],
            warehouseFilter: null,
            warehouseOptions: [],
            typeFilter: null,
            typeOptions: [
                { title: 'Stock In', value: 'in' },
                { title: 'Stock Out', value: 'out' }
            ],
            referenceTypeFilter: null,
            referenceTypeOptions: [
                { title: 'Purchase', value: 'purchase' },
                { title: 'Sales', value: 'sales' },
                { title: 'Return', value: 'return' },
                { title: 'Damage', value: 'damage' },
                { title: 'Lost', value: 'lost' }
            ],
            dateFrom: '',
            dateTo: '',
            exporting: false,
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    async mounted() {
        await this.loadProducts();
        await this.loadWarehouses();
        await this.loadLedger();
    },
    methods: {
        async loadLedger() {
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
                if (this.productFilter) {
                    params.product_id = this.productFilter;
                }
                if (this.warehouseFilter) {
                    params.warehouse_id = this.warehouseFilter;
                }
                if (this.typeFilter) {
                    params.type = this.typeFilter;
                }
                if (this.referenceTypeFilter) {
                    params.reference_type = this.referenceTypeFilter;
                }
                // Only add date filters if they have values (not null, not empty string)
                if (this.dateFrom && String(this.dateFrom).trim() !== '') {
                    params.date_from = this.dateFrom;
                }
                if (this.dateTo && String(this.dateTo).trim() !== '') {
                    params.date_to = this.dateTo;
                }

                const response = await this.$axios.get('/api/v1/stock-ledger', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.ledgers = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load stock ledger');
            } finally {
                this.loading = false;
            }
        },
        async loadProducts() {
            try {
                const response = await this.$axios.get('/api/v1/products', {
                    params: { per_page: 1000 },
                    headers: this.getAuthHeaders()
                });
                this.products = response.data.data || [];
                this.productOptions = this.products.map(p => ({
                    title: `${p.name} (${p.sku})`,
                    value: p.id
                }));
            } catch (error) {
                console.error('Error loading products:', error);
            }
        },
        async loadWarehouses() {
            try {
                const response = await this.$axios.get('/api/v1/stock-ledger/warehouses', {
                    headers: this.getAuthHeaders()
                });
                this.warehouses = response.data.warehouses || [];
                this.warehouseOptions = this.warehouses.map(w => ({
                    title: w.label,
                    value: w.value
                }));
            } catch (error) {
                console.error('Error loading warehouses:', error);
            }
        },
        formatReferenceType(type) {
            const types = {
                'purchase': 'Purchase',
                'sales': 'Sales',
                'return': 'Return',
                'damage': 'Damage',
                'lost': 'Lost'
            };
            return types[type] || type;
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '৳0.00';
            return '৳' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
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
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadLedger();
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadLedger();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadLedger();
        },
        // Date change handlers - explicitly set the filter value and fetch
        onDateFromChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.dateFrom = value || '';
            this.resetPagination(); // Reset to first page when filter changes
            this.loadLedger();
        },
        onDateToChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.dateTo = value || '';
            this.resetPagination(); // Reset to first page when filter changes
            this.loadLedger();
        },
        async exportToExcel() {
            try {
                this.exporting = true;

                // Build params with all filters but get all records
                const params = {};

                if (this.search) {
                    params.search = this.search;
                }
                if (this.productFilter) {
                    params.product_id = this.productFilter;
                }
                if (this.warehouseFilter) {
                    params.warehouse_id = this.warehouseFilter;
                }
                if (this.typeFilter) {
                    params.type = this.typeFilter;
                }
                if (this.referenceTypeFilter) {
                    params.reference_type = this.referenceTypeFilter;
                }
                if (this.dateFrom && String(this.dateFrom).trim() !== '') {
                    params.date_from = this.dateFrom;
                }
                if (this.dateTo && String(this.dateTo).trim() !== '') {
                    params.date_to = this.dateTo;
                }

                // Get all records for export (no pagination)
                params.per_page = 999999;

                const response = await this.$axios.get('/api/v1/stock-ledger', {
                    params,
                    headers: this.getAuthHeaders()
                });

                const allLedgers = response.data.data || [];

                if (allLedgers.length === 0) {
                    this.showError('No data to export');
                    return;
                }

                // Prepare CSV data
                const headers = [
                    'Date',
                    'Product Name',
                    'Product SKU',
                    'Warehouse',
                    'Type',
                    'Reference Type',
                    'Reference Number',
                    'Quantity',
                    'Unit Cost',
                    'Total Cost',
                    'Balance After',
                    'Created By',
                    'Created At'
                ];

                const rows = allLedgers.map(ledger => [
                    this.formatDateDDMMYYYY(ledger.transaction_date) || '',
                    ledger.product?.name || '',
                    ledger.product?.sku || '',
                    ledger.warehouse?.name || '',
                    ledger.type === 'in' ? 'IN' : 'OUT',
                    this.formatReferenceType(ledger.reference_type) || '',
                    ledger.reference_number || '',
                    ledger.quantity || 0,
                    parseFloat(ledger.unit_cost || 0).toFixed(2),
                    parseFloat(ledger.total_cost || 0).toFixed(2),
                    ledger.balance_after || 0,
                    ledger.creator?.name || '',
                    this.formatDateShort(ledger.created_at) || ''
                ]);

                // Convert to CSV
                const csvContent = [
                    headers.join(','),
                    ...rows.map(row => row.map(cell => {
                        // Escape commas and quotes in cell values
                        const cellStr = String(cell || '');
                        if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                            return `"${cellStr.replace(/"/g, '""')}"`;
                        }
                        return cellStr;
                    }).join(','))
                ].join('\n');

                // Add BOM for Excel UTF-8 support
                const BOM = '\uFEFF';
                const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;

                // Generate filename with current date
                const now = new Date();
                const dateStr = now.toISOString().split('T')[0];
                link.setAttribute('download', `stock_ledger_${dateStr}.csv`);

                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                this.showSuccess('Stock ledger exported successfully');
            } catch (error) {
                this.handleApiError(error, 'Failed to export stock ledger');
            } finally {
                this.exporting = false;
            }
        },
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
