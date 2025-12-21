<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Supplier Invoices (Purchases)</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Purchase Invoice
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="statusFilter" :items="statusOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadPurchases"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="supplierFilter" :items="supplierOptions" label="Filter by Supplier"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadPurchases"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="warehouseFilter" :items="warehouseOptions" label="Filter by Warehouse"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadPurchases"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by invoice number" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable @input="loadPurchases"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Purchases Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Supplier Invoices</span>
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
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th class="sortable" @click="onSort('invoice_date')">
                                <div class="sortable-header">
                                    <span>Invoice Date</span>
                                    <v-icon v-if="sortBy === 'invoice_date'" size="18" class="sort-icon active">
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
                            <th class="text-end sortable" @click="onSort('total_amount')">
                                <div class="sortable-header justify-end">
                                    <span>Total Amount</span>
                                    <v-icon v-if="sortBy === 'total_amount'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="text-end">
                                <span>Paid</span>
                            </th>
                            <th class="text-end">
                                <span>Balance</span>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="purchase in purchases" :key="purchase.id">
                                <td>{{ purchase.invoice_number }}</td>
                                <td>{{ purchase.supplier?.name }}</td>
                                <td>{{ purchase.warehouse?.name }}</td>
                                <td>{{ formatDate(purchase.invoice_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(purchase.status)" size="small">
                                        {{ purchase.status }}
                                    </v-chip>
                                </td>
                                <td>{{ formatCurrency(purchase.total_amount) }}</td>
                                <td>{{ formatCurrency(purchase.paid_amount) }}</td>
                                <td>
                                    <v-chip :color="purchase.balance_amount > 0 ? 'warning' : 'success'" size="small">
                                        {{ formatCurrency(purchase.balance_amount) }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewPurchase(purchase)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="purchase.status === 'draft'" size="small" icon="mdi-pencil"
                                        @click="openDialog(purchase)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="purchase.status === 'draft'" size="small" icon="mdi-package-variant"
                                        @click="receivePurchase(purchase)" variant="text" color="success"
                                        title="Receive Stock"></v-btn>
                                    <v-btn v-if="purchase.status !== 'draft' && purchase.balance_amount > 0"
                                        size="small" icon="mdi-cash" @click="openPaymentDialog(purchase)" variant="text"
                                        color="primary" title="Add Payment"></v-btn>
                                    <v-btn v-if="purchase.status === 'draft'" size="small" icon="mdi-delete"
                                        @click="deletePurchase(purchase)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="purchases.length === 0">
                                <td colspan="9" class="text-center py-4">No purchases found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="purchases.length > 0 && pagination.total > 0">
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

        <!-- Purchase Dialog -->
        <PurchaseDialog v-model="dialog" :editing-purchase="editingPurchase" :supplier-options="supplierOptions"
            :warehouse-options="warehouseOptions" :product-options="productOptions" :saving="saving"
            @save="handleSavePurchase" @cancel="closeDialog" />

        <!-- View Purchase Dialog -->
        <ViewPurchaseDialog v-model="viewDialog" :purchase="viewingPurchase" />

        <!-- Payment Dialog -->
        <PaymentDialog v-model="paymentDialog" :purchase="selectedPurchase" :saving="savingPayment"
            @save="handleSavePayment" @cancel="closePaymentDialog" />
    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import PurchaseDialog from './dialogs/PurchaseDialog.vue';
import ViewPurchaseDialog from './dialogs/ViewPurchaseDialog.vue';
import PaymentDialog from './dialogs/PaymentDialog.vue';
import PaginationControls from '../../common/PaginationControls.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';

export default {
    components: {
        PurchaseDialog,
        ViewPurchaseDialog,
        PaymentDialog,
        PaginationControls
    },
    mixins: [commonMixin],
    data() {
        return {
            purchases: [],
            suppliers: [],
            supplierOptions: [],
            warehouses: [],
            warehouseOptions: [],
            products: [],
            productOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Pending', value: 'pending' },
                { title: 'Partial', value: 'partial' },
                { title: 'Paid', value: 'paid' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            supplierFilter: null,
            warehouseFilter: null,
            dialog: false,
            editingPurchase: null,
            viewDialog: false,
            viewingPurchase: null,
            paymentDialog: false,
            selectedPurchase: null,
            savingPayment: false,
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
        await this.loadSuppliers();
        await this.loadWarehouses();
        await this.loadProducts();
        await this.loadPurchases();
    },
    methods: {
        async loadPurchases() {
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
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.supplierFilter) {
                    params.supplier_id = this.supplierFilter;
                }
                if (this.warehouseFilter) {
                    params.warehouse_id = this.warehouseFilter;
                }

                const response = await this.$axios.get('/api/v1/purchases', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.purchases = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load purchases');
            } finally {
                this.loading = false;
            }
        },
        async loadSuppliers() {
            try {
                const response = await this.$axios.get('/api/v1/suppliers', {
                    params: { per_page: 1000 },
                    headers: this.getAuthHeaders()
                });
                const list = response.data.data || response.data || [];
                this.suppliers = list;
                this.supplierOptions = list.map(s => ({ label: s.name, value: s.id }));
            } catch (error) {
                console.error('Error loading suppliers:', error);
            }
        },
        async loadWarehouses() {
            try {
                const response = await this.$axios.get('/api/v1/stock-ledger/warehouses', {
                    headers: this.getAuthHeaders()
                });
                // Backend already returns warehouses with label and value keys, so use them directly
                this.warehouseOptions = response.data.warehouses || [];
            } catch (error) {
                console.error('Error loading warehouses:', error);
                this.warehouseOptions = [];
            }
        },
        async loadProducts() {
            try {
                const response = await this.$axios.get('/api/v1/products', {
                    params: { per_page: 1000, is_active: true },
                    headers: this.getAuthHeaders()
                });
                // Handle both paginated and non-paginated responses
                const productsList = response.data.data || response.data || [];
                this.products = productsList;
                this.productOptions = productsList
                    .filter(p => p && p.id && p.name) // Filter out invalid products
                    .map(p => ({
                        label: p.sku ? `${p.name} (${p.sku})` : (p.name || 'Unknown Product'),
                        value: p.id
                    }));
                console.log('Products loaded:', this.productOptions.length, 'options');
            } catch (error) {
                console.error('Error loading products:', error);
                this.productOptions = [];
                this.products = [];
            }
        },
        openDialog(purchase) {
            this.editingPurchase = purchase || null;
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingPurchase = null;
        },
        async handleSavePurchase(formData, errorMessage) {
            if (errorMessage) {
                this.showError(errorMessage);
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingPurchase
                    ? `/api/v1/purchases/${this.editingPurchase.id}`
                    : '/api/v1/purchases';

                const method = this.editingPurchase ? 'put' : 'post';

                await axios[method](url, formData, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingPurchase ? 'Purchase updated successfully' : 'Purchase created successfully'
                );
                this.closeDialog();
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error saving purchase');
            } finally {
                this.saving = false;
            }
        },
        async receivePurchase(purchase) {
            // Show SweetAlert confirmation
            const result = await window.Swal.fire({
                title: 'Receive Stock?',
                text: `Receive stock for purchase invoice "${purchase.invoice_number}"? This will update stock levels.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, receive stock!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/purchases/${purchase.id}/receive`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase received and stock updated successfully');
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error receiving purchase');
            }
        },
        async deletePurchase(purchase) {
            if (!confirm(`Delete purchase ${purchase.invoice_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/purchases/${purchase.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase deleted successfully');
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error deleting purchase');
            }
        },
        async viewPurchase(purchase) {
            try {
                const response = await this.$axios.get(`/api/v1/purchases/${purchase.id}`, {
                    headers: this.getAuthHeaders()
                });
                this.viewingPurchase = response.data;
                this.viewDialog = true;
            } catch (error) {
                this.handleApiError(error, 'Failed to load purchase details');
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
                'draft': 'grey',
                'pending': 'warning',
                'partial': 'info',
                'paid': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
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
            this.loadPurchases();
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadPurchases();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadPurchases();
        },
        openPaymentDialog(purchase) {
            this.selectedPurchase = purchase;
            this.paymentDialog = true;
        },
        closePaymentDialog() {
            this.paymentDialog = false;
            this.selectedPurchase = null;
        },
        async handleSavePayment(paymentData) {
            this.savingPayment = true;
            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post('/api/v1/payments', {
                    party_type: 'supplier',
                    party_id: this.selectedPurchase.supplier_id,
                    reference_type: 'purchase',
                    reference_id: this.selectedPurchase.id,
                    reference_number: this.selectedPurchase.invoice_number,
                    payment_date: paymentData.payment_date,
                    amount: paymentData.amount,
                    payment_method: paymentData.payment_method,
                    notes: paymentData.notes,
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Payment added successfully');
                this.closePaymentDialog();
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error adding payment');
            } finally {
                this.savingPayment = false;
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
