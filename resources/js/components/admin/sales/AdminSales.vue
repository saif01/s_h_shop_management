<template>
    <v-container fluid>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <div class="d-flex align-center gap-2">
                    <v-icon>mdi-point-of-sale</v-icon>
                    <span class="text-h5">Sales / POS</span>
                </div>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog">New Sale</v-btn>
            </v-card-title>
            <v-divider />
            
            <!-- Filters -->
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.search" label="Search Invoice/Customer" 
                            prepend-inner-icon="mdi-magnify" clearable dense hide-details />
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="filters.status" :items="statusOptions" 
                            label="Status" clearable dense hide-details />
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-text-field v-model="filters.date_from" type="date" 
                            label="From Date" dense hide-details />
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-text-field v-model="filters.date_to" type="date" 
                            label="To Date" dense hide-details />
                    </v-col>
                    <v-col cols="12" md="3" class="d-flex gap-2">
                        <v-btn color="primary" @click="fetchSales">
                            <v-icon>mdi-magnify</v-icon> Search
                        </v-btn>
                        <v-btn @click="resetFilters">
                            <v-icon>mdi-refresh</v-icon> Reset
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <!-- Sales Table -->
            <v-data-table :headers="headers" :items="sales" :loading="loading" 
                :items-per-page="pagination.per_page" hide-default-footer>
                <template #item.invoice_number="{ item }">
                    <span class="font-weight-bold">{{ item.invoice_number }}</span>
                </template>
                <template #item.customer="{ item }">
                    {{ item.customer?.name || 'Walk-in' }}
                </template>
                <template #item.invoice_date="{ item }">
                    {{ formatDate(item.invoice_date) }}
                </template>
                <template #item.total_amount="{ item }">
                    ${{ parseFloat(item.total_amount).toFixed(2) }}
                </template>
                <template #item.paid_amount="{ item }">
                    ${{ parseFloat(item.paid_amount).toFixed(2) }}
                </template>
                <template #item.balance_amount="{ item }">
                    <v-chip :color="item.balance_amount > 0 ? 'error' : 'success'" size="small">
                        ${{ parseFloat(item.balance_amount).toFixed(2) }}
                    </v-chip>
                </template>
                <template #item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" size="small">
                        {{ item.status }}
                    </v-chip>
                </template>
                <template #item.actions="{ item }">
                    <v-btn icon="mdi-eye" size="small" variant="text" @click="viewSale(item)" />
                    <v-btn icon="mdi-pencil" size="small" variant="text" @click="editSale(item)" />
                    <v-btn icon="mdi-printer" size="small" variant="text" @click="printInvoice(item)" />
                    <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDelete(item)" />
                </template>
            </v-data-table>

            <!-- Pagination -->
            <v-card-actions class="justify-center">
                <v-pagination v-model="pagination.current_page" :length="pagination.last_page" 
                    @update:model-value="fetchSales" />
            </v-card-actions>
        </v-card>

        <!-- Sale Dialog (POS) -->
        <SaleDialog :model-value="dialog" @update:model-value="dialog = $event" 
            :sale="selectedSale" @saved="handleSaved" />

        <!-- View Sale Dialog -->
        <ViewSaleDialog :model-value="viewDialog" @update:model-value="viewDialog = $event" 
            :sale="selectedSale" />

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="400">
            <v-card>
                <v-card-title>Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this sale?</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="deleteDialog = false">Cancel</v-btn>
                    <v-btn color="error" :loading="deleting" @click="deleteSale">Delete</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import axios from '@/utils/axios.config';
import SaleDialog from './dialogs/SaleDialog.vue';
import ViewSaleDialog from './dialogs/ViewSaleDialog.vue';

export default {
    name: 'AdminSales',
    components: {
        SaleDialog,
        ViewSaleDialog,
    },
    data() {
        return {
            sales: [],
            loading: false,
            dialog: false,
            viewDialog: false,
            deleteDialog: false,
            deleting: false,
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
            },
            headers: [
                { title: 'Invoice #', key: 'invoice_number' },
                { title: 'Customer', key: 'customer' },
                { title: 'Date', key: 'invoice_date' },
                { title: 'Total', key: 'total_amount', align: 'end' },
                { title: 'Paid', key: 'paid_amount', align: 'end' },
                { title: 'Due', key: 'balance_amount', align: 'end' },
                { title: 'Status', key: 'status' },
                { title: 'Actions', key: 'actions', sortable: false, align: 'center' },
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
                    ...this.filters,
                };
                const { data } = await axios.get('/api/v1/sales', { params });
                this.sales = data.data || data.sales || [];
                this.pagination = {
                    current_page: data.current_page || 1,
                    per_page: data.per_page || 15,
                    last_page: data.last_page || 1,
                };
            } catch (error) {
                console.error('Failed to fetch sales', error);
                this.$toast?.error('Failed to load sales');
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
        confirmDelete(sale) {
            this.selectedSale = sale;
            this.deleteDialog = true;
        },
        async deleteSale() {
            this.deleting = true;
            try {
                await axios.delete(`/api/v1/sales/${this.selectedSale.id}`);
                this.$toast?.success('Sale deleted successfully');
                this.deleteDialog = false;
                this.fetchSales();
            } catch (error) {
                console.error('Failed to delete sale', error);
                this.$toast?.error('Failed to delete sale');
            } finally {
                this.deleting = false;
            }
        },
        printInvoice(sale) {
            // TODO: Implement invoice printing
            console.log('Print invoice:', sale.invoice_number);
            this.$toast?.info('Print feature coming soon');
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
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
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
    },
};
</script>

