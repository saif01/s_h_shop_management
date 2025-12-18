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
                        <v-select v-model="pagination.per_page" :items="perPageOptions" label="Items per page"
                            prepend-inner-icon="mdi-format-list-numbered" variant="outlined" density="compact"
                            @update:model-value="onPerPageChange"></v-select>
                    </v-col>

                    <v-col cols="12" md="2">
                        <v-select v-model="filters.status" :items="statusOptions" label="Status"
                            prepend-inner-icon="mdi-filter" variant="outlined" density="compact" clearable
                            @update:model-value="fetchSales"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <DatePicker v-model="filters.date_from" label="From Date" density="compact"
                            @update:model-value="onDateFromChange" />
                    </v-col>
                    <v-col cols="12" md="2">
                        <DatePicker v-model="filters.date_to" label="To Date" density="compact"
                            @update:model-value="onDateToChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.search" label="Search Invoice/Customer"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @update:model-value="fetchSales"></v-text-field>
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
                    <span v-if="sales.length > 0">
                        | Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to
                        {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
                        {{ pagination.total }}
                    </span>
                </span>
            </v-card-title>
            <v-card-text>
                <div v-if="loading" class="text-center py-4">
                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                </div>
                <v-table v-else>
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
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
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="sales.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</strong> to
                            <strong>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total)
                                }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                            <span v-if="pagination.last_page > 1" class="ml-2">
                                (Page {{ pagination.current_page }} of {{ pagination.last_page }})
                            </span>
                        </span>
                        <span v-else>
                            No records found
                        </span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="pagination.current_page" :length="pagination.last_page"
                            :total-visible="7" density="comfortable" @update:model-value="fetchSales">
                        </v-pagination>
                    </div>
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
import axios from '@/utils/axios.config';
import SaleDialog from './dialogs/SaleDialog.vue';
import ViewSaleDialog from './dialogs/ViewSaleDialog.vue';
import DatePicker from '@/components/common/DatePicker.vue';
import adminPaginationMixin from '@/mixins/adminPaginationMixin';

export default {
    name: 'AdminSales',
    mixins: [adminPaginationMixin],
    components: {
        SaleDialog,
        ViewSaleDialog,
        DatePicker,
    },
    data() {
        return {
            sales: [],
            loading: false,
            dialog: false,
            viewDialog: false,
            selectedSale: null,
            filters: {
                search: '',
                status: null,
                date_from: '',
                date_to: '',
            },
            pagination: {
                current_page: 1,
                per_page: 15,
                last_page: 1,
                total: 0,
            },
            perPageOptions: [
                { title: '10', value: 10 },
                { title: '15', value: 15 },
                { title: '25', value: 25 },
                { title: '50', value: 50 },
                { title: '100', value: 100 },
            ],
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
        this.fetchSales();
    },
    methods: {
        async fetchSales() {
            this.loading = true;
            try {
                const params = {
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    search: this.filters.search || '',
                    status: this.filters.status || null,
                };

                // Only add date filters if they have values (not null, not empty string)
                if (this.filters.date_from && String(this.filters.date_from).trim() !== '') {
                    params.from_date = this.filters.date_from;
                }
                if (this.filters.date_to && String(this.filters.date_to).trim() !== '') {
                    params.to_date = this.filters.date_to;
                }

                const { data } = await axios.get('/api/v1/sales', { params });
                this.sales = data.data || data.sales || [];
                this.pagination = {
                    current_page: data.current_page || 1,
                    per_page: data.per_page || 15,
                    last_page: data.last_page || 1,
                    total: data.total || 0,
                };
            } catch (error) {
                console.error('Failed to fetch sales', error);
                this.showError('Failed to load sales');
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
            this.selectedSale = sale;

            const result = await window.Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete sale ${sale.invoice_number}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/v1/sales/${sale.id}`);
                    this.showSuccess('Sale deleted successfully');
                    this.fetchSales();
                } catch (error) {
                    console.error('Failed to delete sale', error);
                    this.showError(error.response?.data?.message || 'Failed to delete sale');
                }
            }
        },
        printInvoice(sale) {
            // TODO: Implement invoice printing
            console.log('Print invoice:', sale.invoice_number);
            this.showInfo('Print feature coming soon');
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
            this.pagination.current_page = 1;
            this.fetchSales();
        },
        onPerPageChange() {
            this.pagination.current_page = 1;
            this.fetchSales();
        },
        // Date change handlers - explicitly set the filter value and fetch
        onDateFromChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_from = value || '';
            this.pagination.current_page = 1; // Reset to first page when filter changes
            this.fetchSales();
        },
        onDateToChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_to = value || '';
            this.pagination.current_page = 1; // Reset to first page when filter changes
            this.fetchSales();
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
        showInfo(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'info',
                    title: message
                });
            } else if (window.Swal) {
                window.Swal.fire({
                    icon: 'info',
                    title: message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
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
