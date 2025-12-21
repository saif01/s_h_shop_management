<template>
    <div>
        <!-- Filters -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="filters.date_from" label="From Date" density="compact"
                            @update:model-value="onDateFromChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="filters.date_to" label="To Date" density="compact"
                            @update:model-value="onDateToChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.supplier_id" :items="suppliers" item-value="id" item-title="name"
                            label="Supplier" variant="outlined" density="compact" clearable
                            @update:model-value="onFilterChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.status" :items="statusOptions" label="Status" variant="outlined"
                            density="compact" clearable @update:model-value="onFilterChange" />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Summary Cards -->
        <v-row v-if="summary" class="mb-4" dense>
            <v-col cols="12" sm="6" md="3">
                <v-card color="primary" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Total Purchases</div>
                        <div class="text-h6 font-weight-bold">{{ formatCurrency(summary.total_purchases) }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card color="success" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Total Paid</div>
                        <div class="text-h6 font-weight-bold">{{ formatCurrency(summary.total_paid) }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card color="warning" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Total Due</div>
                        <div class="text-h6 font-weight-bold">{{ formatCurrency(summary.total_due) }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card color="info" variant="tonal" class="summary-card">
                    <v-card-text class="py-3">
                        <div class="text-caption text-grey-darken-1 mb-1">Total Orders</div>
                        <div class="text-h6 font-weight-bold">{{ summary.total_count || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Report Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Purchase Report</span>
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
                            <th class="sortable" @click="onSort('supplier_name')">
                                <div class="sortable-header">
                                    <span>Supplier</span>
                                    <v-icon v-if="sortBy === 'supplier_name'" size="18" class="sort-icon active">
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
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                        </tr>
                        <!-- Actual Report Data -->
                        <template v-else>
                            <tr v-for="item in reportData" :key="item.id || item.invoice_number">
                                <td>{{ item.invoice_number }}</td>
                                <td>{{ formatDateDDMMYYYY(item.invoice_date) }}</td>
                                <td>{{ item.supplier_name }}</td>
                                <td class="text-end">{{ formatCurrency(item.total_amount) }}</td>
                                <td class="text-end">{{ formatCurrency(item.paid_amount) }}</td>
                                <td class="text-end">
                                    <span
                                        :class="item.balance_amount > 0 ? 'text-error font-weight-medium' : 'text-success'">
                                        {{ formatCurrency(item.balance_amount) }}
                                    </span>
                                </td>
                                <td>
                                    <v-chip :color="getStatusColor(item.status)" size="small" variant="flat">
                                        {{ item.status }}
                                    </v-chip>
                                </td>
                            </tr>
                            <tr v-if="reportData.length === 0">
                                <td colspan="7" class="text-center py-4">No purchase data found</td>
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
import DatePicker from '../../../common/DatePicker.vue';
import { paginationMixin } from '../../../../utils/pagination.js';

export default {
    name: 'PurchaseReports',
    components: {
        PaginationControls,
        DatePicker
    },
    mixins: [commonMixin, paginationMixin],
    data() {
        return {
            loading: false,
            exporting: false,
            filters: {
                date_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                date_to: new Date().toISOString().split('T')[0],
                supplier_id: null,
                status: null,
            },
            reportData: [],
            summary: null,
            suppliers: [],
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Pending', value: 'pending' },
                { title: 'Partial', value: 'partial' },
                { title: 'Paid', value: 'paid' },
                { title: 'Cancelled', value: 'cancelled' },
            ],
        };
    },
    mounted() {
        this.fetchSuppliers();
        this.generateReport();
    },
    methods: {
        async fetchSuppliers() {
            try {
                const response = await this.$axios.get('/api/v1/suppliers', {
                    headers: this.getAuthHeaders()
                });
                this.suppliers = response.data.data || response.data.suppliers || [];
            } catch (error) {
                console.error('Failed to fetch suppliers', error);
            }
        },
        async generateReport() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                // Handle "Show All" option
                if (this.perPage === 'all') {
                    params.per_page = 999999; // Very large number to get all records
                }

                // Add filter params
                if (this.filters.date_from) {
                    params.date_from = this.filters.date_from;
                }
                if (this.filters.date_to) {
                    params.date_to = this.filters.date_to;
                }
                if (this.filters.supplier_id) {
                    params.supplier_id = this.filters.supplier_id;
                }
                if (this.filters.status) {
                    params.status = this.filters.status;
                }

                const response = await this.$axios.get('/api/v1/reports/purchases', {
                    params,
                    headers: this.getAuthHeaders()
                });
                // Handle both paginated response (data) and legacy format (purchases)
                this.reportData = response.data.data || response.data.purchases || [];
                this.summary = response.data.summary || null;
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to generate purchase report');
            } finally {
                this.loading = false;
            }
        },
        async exportExcel() {
            try {
                this.exporting = true;
                const response = await this.$axios.get('/api/v1/reports/purchases/export/excel', {
                    params: this.filters,
                    responseType: 'blob',
                    headers: this.getAuthHeaders()
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `purchase_report_${Date.now()}.xlsx`);
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
                const response = await this.$axios.get('/api/v1/reports/purchases/export/pdf', {
                    params: this.filters,
                    responseType: 'blob',
                    headers: this.getAuthHeaders()
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `purchase_report_${Date.now()}.pdf`);
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
        // Date change handlers - explicitly set the filter value and fetch
        onDateFromChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_from = value || '';
            this.resetPagination(); // Reset to first page when filter changes
            this.generateReport();
        },
        onDateToChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_to = value || '';
            this.resetPagination(); // Reset to first page when filter changes
            this.generateReport();
        },
        onFilterChange() {
            this.resetPagination(); // Reset to first page when filter changes
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
