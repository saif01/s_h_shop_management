<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Sales / POS</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog" class="add-button">
                New Sale
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.status" :items="statusOptions" label="Filter by Status"
                            prepend-inner-icon="mdi-filter" variant="outlined" density="compact" clearable
                            @update:model-value="fetchSales"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="filters.date_from" label="From Date" density="compact"
                            @update:model-value="onDateFromChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="filters.date_to" label="To Date" density="compact"
                            @update:model-value="onDateToChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.search" label="Search Invoice/Customer"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="fetchSales"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Sales List -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Sales</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('invoice_number')">
                                <div class="sortable-header">
                                    <span>Invoice #</span>
                                    <v-icon v-if="sortBy === 'invoice_number'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Customer</th>
                            <th class="sortable" @click="onSort('invoice_date')">
                                <div class="sortable-header">
                                    <span>Date</span>
                                    <v-icon v-if="sortBy === 'invoice_date'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end sortable" @click="onSort('total_amount')">
                                <div class="sortable-header justify-end">
                                    <span>Total</span>
                                    <v-icon v-if="sortBy === 'total_amount'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end sortable" @click="onSort('paid_amount')">
                                <div class="sortable-header justify-end">
                                    <span>Paid</span>
                                    <v-icon v-if="sortBy === 'paid_amount'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end sortable" @click="onSort('balance_amount')">
                                <div class="sortable-header justify-end">
                                    <span>Due</span>
                                    <v-icon v-if="sortBy === 'balance_amount'" size="18" class="sort-icon active">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="90"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Sales Data -->
                        <template v-else>
                            <tr v-for="sale in sales" :key="sale.id">
                                <td>
                                    <span class="font-weight-bold">{{ sale.invoice_number }}</span>
                                </td>
                                <td>
                                    {{ sale.customer?.name || 'Walk-in' }}
                                </td>
                                <td>
                                    {{ formatDateDDMMYYYY(sale.invoice_date) }}
                                </td>
                                <td class="text-end">
                                    ৳{{ parseFloat(sale.total_amount).toFixed(2) }}
                                </td>
                                <td class="text-end">
                                    ৳{{ parseFloat(sale.paid_amount).toFixed(2) }}
                                </td>
                                <td class="text-end">
                                    <v-chip :color="sale.balance_amount > 0 ? 'error' : 'success'" size="small">
                                        ৳{{ parseFloat(sale.balance_amount).toFixed(2) }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="getStatusColor(sale.status)" size="small">
                                        {{ sale.status }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn icon="mdi-eye" size="small" variant="text" @click="viewSale(sale)" />
                                    <v-btn icon="mdi-pencil" size="small" variant="text" @click="editSale(sale)" />
                                    <v-btn icon="mdi-printer" size="small" variant="text" @click="printInvoice(sale)" />
                                    <v-btn icon="mdi-delete" size="small" variant="text" color="error"
                                        @click="confirmDelete(sale)" />
                                </td>
                            </tr>
                            <tr v-if="sales.length === 0">
                                <td colspan="8" class="text-center py-4">No sales found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="sales.length > 0 && pagination.total > 0">
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

        <!-- Sale Dialog (POS) -->
        <SaleDialog :model-value="dialog" @update:model-value="dialog = $event" :sale="selectedSale"
            @saved="handleSaved" />

        <!-- View Sale Dialog -->
        <ViewSaleDialog :model-value="viewDialog" @update:model-value="viewDialog = $event" :sale="selectedSale" />

    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import SaleDialog from './dialogs/SaleDialog.vue';
import ViewSaleDialog from './dialogs/ViewSaleDialog.vue';
import DatePicker from '../../common/DatePicker.vue';
import PaginationControls from '../../common/PaginationControls.vue';
import { formatDateDDMMYYYY } from '../../../utils/formatters';
import InvoicePrint from './InvoicePrint.js';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';

export default {
    name: 'AdminSales',
    mixins: [commonMixin],
    components: {
        SaleDialog,
        ViewSaleDialog,
        DatePicker,
        PaginationControls,
    },
    data() {
        return {
            sales: [],
            dialog: false,
            viewDialog: false,
            selectedSale: null,
            filters: {
                search: '',
                status: null,
                date_from: '',
                date_to: '',
            },
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Pending', value: 'pending' },
                { title: 'Partial', value: 'partial' },
                { title: 'Paid', value: 'paid' },
                { title: 'Cancelled', value: 'cancelled' },
            ],
            // Pagination state
            currentPage: 1,
            perPage: 10,
            perPageOptions: [
                { title: '10', value: 10, description: 'Quick view' },
                { title: '25', value: 25, description: 'Standard' },
                { title: '50', value: 50, description: 'Comfortable' },
                { title: '100', value: 100, description: 'Extended' },
                { title: '500', value: 500, description: 'Large dataset' },
                { title: 'Show All', value: 'all', description: 'All records' }
            ],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0
            },
        };
    },
    async mounted() {
        await this.fetchSales();
    },
    methods: {
        async fetchSales() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                // Handle "Show All" option
                if (this.perPage === 'all') {
                    params.per_page = 999999; // Very large number to get all records
                }

                if (this.filters.search) {
                    params.search = this.filters.search;
                }
                if (this.filters.status) {
                    params.status = this.filters.status;
                }

                // Only add date filters if they have values (not null, not empty string)
                if (this.filters.date_from && String(this.filters.date_from).trim() !== '') {
                    params.from_date = this.filters.date_from;
                }
                if (this.filters.date_to && String(this.filters.date_to).trim() !== '') {
                    params.to_date = this.filters.date_to;
                }

                const response = await this.$axios.get('/api/v1/sales', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.sales = response.data.data || response.data.sales || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load sales');
            } finally {
                this.loading = false;
            }
        },
        openDialog() {
            this.selectedSale = null;
            this.dialog = true;
        },
        editSale(sale) {
            this.selectedSale = sale;
            this.dialog = true;
        },
        viewSale(sale) {
            this.selectedSale = sale;
            this.viewDialog = true;
        },
        async confirmDelete(sale) {
            if (!confirm(`Are you sure you want to delete sale ${sale.invoice_number}?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/sales/${sale.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Sale deleted successfully');
                await this.fetchSales();
            } catch (error) {
                this.handleApiError(error, 'Error deleting sale');
            }
        },
        printInvoice(sale) {
            InvoicePrint.printInvoice(sale, (errorMessage) => {
                this.showError(errorMessage);
            });
        },
        handleSaved() {
            this.fetchSales();
        },
        resetFilters() {
            this.filters = {
                search: '',
                status: null,
                date_from: '',
                date_to: '',
            };
            this.resetPagination();
            this.fetchSales();
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
            this.fetchSales();
        },
        onPerPageChange() {
            this.resetPagination();
            this.fetchSales();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.fetchSales();
        },
        // Date change handlers - explicitly set the filter value and fetch
        onDateFromChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_from = value || '';
            this.resetPagination(); // Reset to first page when filter changes
            this.fetchSales();
        },
        onDateToChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_to = value || '';
            this.resetPagination(); // Reset to first page when filter changes
            this.fetchSales();
        },
        formatDateDDMMYYYY,
        getStatusColor(status) {
            const colors = {
                draft: 'grey',
                pending: 'warning',
                partial: 'info',
                paid: 'success',
                cancelled: 'error',
            };
            return colors[status] || 'grey';
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
