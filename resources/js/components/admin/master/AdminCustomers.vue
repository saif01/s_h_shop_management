<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Customer Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Customer
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadCustomers"></v-select>
                    </v-col>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="search" label="Search by name, code, company, email, phone"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadCustomers"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Customers Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Customers</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ (pagination.total || 0).toLocaleString() }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('code')">
                                <div class="sortable-header">
                                    <span>Code</span>
                                    <v-icon v-if="sortBy === 'code'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
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
                            <th class="sortable" @click="onSort('company_name')">
                                <div class="sortable-header">
                                    <span>Company</span>
                                    <v-icon v-if="sortBy === 'company_name'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('email')">
                                <div class="sortable-header">
                                    <span>Email</span>
                                    <v-icon v-if="sortBy === 'email'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('phone')">
                                <div class="sortable-header">
                                    <span>Phone</span>
                                    <v-icon v-if="sortBy === 'phone'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('city')">
                                <div class="sortable-header">
                                    <span>City</span>
                                    <v-icon v-if="sortBy === 'city'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('current_balance')">
                                <div class="sortable-header">
                                    <span>Balance</span>
                                    <v-icon v-if="sortBy === 'current_balance'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="customer in customers" :key="customer.id">
                                <td>{{ customer.code || '-' }}</td>
                                <td>{{ customer.name }}</td>
                                <td>{{ customer.company_name || '-' }}</td>
                                <td>{{ customer.email || '-' }}</td>
                                <td>{{ customer.phone || customer.mobile || '-' }}</td>
                                <td>{{ customer.city || '-' }}</td>
                                <td>
                                    <v-chip :color="customer.current_balance >= 0 ? 'success' : 'error'" size="small">
                                        {{ formatCurrency(customer.current_balance) }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="customer.is_active ? 'success' : 'error'" size="small">
                                        {{ customer.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewCustomer(customer)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(customer)" variant="text"
                                        title="Edit"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteCustomer(customer)"
                                        variant="text" color="error" title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="customers.length === 0">
                                <td colspan="9" class="text-center py-4">No customers found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="customers.length > 0 && pagination.total > 0">
                            <span v-if="perPage === 'all'">
                                Showing <strong>all {{ pagination.total.toLocaleString() }}</strong> records
                            </span>
                            <span v-else>
                                Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                                <strong>{{ Math.min(currentPage * perPage, pagination.total).toLocaleString() }}</strong> of
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

        <!-- View Customer Dialog -->
        <CustomerViewDialog v-model="viewDialog" :customer="selectedCustomer" />

        <!-- Customer Dialog -->
        <CustomerDialog v-model="dialog" :customer="editingCustomer" @save="handleSaveSuccess" @error="handleSaveError"
            @cancel="closeDialog" />
    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import PaginationControls from '../../common/PaginationControls.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';
import CustomerDialog from './dialogs/CustomerDialog.vue';
import CustomerViewDialog from './dialogs/CustomerViewDialog.vue';

export default {
    components: {
        PaginationControls,
        CustomerDialog,
        CustomerViewDialog
    },
    mixins: [commonMixin],
    data() {
        return {
            customers: [],
            activeFilter: null,
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            dialog: false,
            viewDialog: false,
            selectedCustomer: null,
            editingCustomer: null,
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    async mounted() {
        await this.loadCustomers();
    },
    methods: {
        async loadCustomers() {
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
                if (this.activeFilter !== null) {
                    params.is_active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/customers', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.customers = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load customers');
            } finally {
                this.loading = false;
            }
        },
        openDialog(customer) {
            this.editingCustomer = customer;
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingCustomer = null;
        },
        handleSaveSuccess({ data, isEditing }) {
            this.showSuccess(
                isEditing ? 'Customer updated successfully' : 'Customer created successfully'
            );
            this.closeDialog();
            this.loadCustomers();
        },
        handleSaveError(error) {
            this.handleApiError(error, 'Error saving customer');
        },
        async deleteCustomer(customer) {
            if (!confirm(`Delete customer "${customer.name}"?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/customers/${customer.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Customer deleted successfully');
                await this.loadCustomers();
            } catch (error) {
                this.handleApiError(error, 'Error deleting customer');
            }
        },
        viewCustomer(customer) {
            this.selectedCustomer = customer;
            this.viewDialog = true;
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
        onPerPageChange() {
            this.resetPagination();
            this.loadCustomers();
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadCustomers();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadCustomers();
        },
        isSortingBy(field) {
            return this.sortBy === field;
        },
        getSortDirection(field) {
            if (this.sortBy === field) {
                return this.sortDirection;
            }
            return null;
        },
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
