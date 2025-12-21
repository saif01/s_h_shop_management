<template>
    <v-container fluid>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <div class="d-flex align-center gap-2">
                    <v-icon>mdi-warehouse</v-icon>
                    <span class="text-h5">Warehouses</span>
                </div>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog">Add Warehouse</v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
                <v-text-field v-model="search" label="Search Warehouses" prepend-inner-icon="mdi-magnify"
                    variant="outlined" density="compact" clearable @input="onSearch" />
            </v-card-text>
        </v-card>

        <!-- Warehouses Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Warehouses</span>
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
                            <th>Phone</th>
                            <th>Status</th>
                            <th class="sortable" @click="onSort('created_at')">
                                <div class="sortable-header">
                                    <span>Created</span>
                                    <v-icon v-if="sortBy === 'created_at'" size="18" class="sort-icon active">
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
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Warehouse Data -->
                        <template v-else>
                            <tr v-for="warehouse in warehouses" :key="warehouse.id">
                                <td>{{ warehouse.name }}</td>
                                <td>
                                    <v-chip size="small" variant="outlined" v-if="warehouse.code">{{ warehouse.code
                                    }}</v-chip>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>{{ warehouse.city || '-' }}</td>
                                <td>{{ warehouse.phone || '-' }}</td>
                                <td>
                                    <v-chip :color="warehouse.is_active ? 'success' : 'error'" size="small">
                                        {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>{{ formatDate(warehouse.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewWarehouse(warehouse)" variant="text"
                                        color="info" :title="'View Warehouse Details'"></v-btn>
                                    <v-btn size="small" icon="mdi-pencil" @click="editWarehouse(warehouse)"
                                        variant="text" :title="'Edit Warehouse'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="confirmDelete(warehouse)"
                                        variant="text" color="error" :title="'Delete Warehouse'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="warehouses.length === 0">
                                <td colspan="7" class="text-center py-4">No warehouses found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="warehouses.length > 0 && pagination.total > 0">
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

        <!-- Warehouse Dialog -->
        <WarehouseDialog v-model="dialog" :warehouse="selectedWarehouse" :saving="saving" @save="handleSave" />

        <!-- View Warehouse Dialog -->
        <WarehouseViewDialog v-model="viewDialog" :warehouse="selectedWarehouse" />

        <!-- Delete Confirmation -->
        <WarehouseDeleteDialog v-model="deleteDialog" :warehouse="selectedWarehouse" :deleting="deleting"
            @confirm="deleteWarehouse" />
    </v-container>
</template>

<script>
import axios from '@/utils/axios.config';
import commonMixin from '../../../mixins/commonMixin';
import PaginationControls from '../../common/PaginationControls.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';
import WarehouseDialog from './dialogs/WarehouseDialog.vue';
import WarehouseViewDialog from './dialogs/WarehouseViewDialog.vue';
import WarehouseDeleteDialog from './dialogs/WarehouseDeleteDialog.vue';

export default {
    name: 'AdminWarehouses',
    components: {
        PaginationControls,
        WarehouseDialog,
        WarehouseViewDialog,
        WarehouseDeleteDialog,
    },
    mixins: [commonMixin],
    data() {
        return {
            warehouses: [],
            dialog: false,
            viewDialog: false,
            deleteDialog: false,
            saving: false,
            deleting: false,
            selectedWarehouse: null,
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    async mounted() {
        await this.loadWarehouses();
    },
    methods: {
        async loadWarehouses() {
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

                const response = await axios.get('/api/v1/warehouses', { params });

                // Laravel paginate() returns data in 'data' property
                this.warehouses = response.data.data || [];

                // Update pagination from response
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load warehouses');
            } finally {
                this.loading = false;
            }
        },
        openDialog() {
            this.selectedWarehouse = null;
            this.dialog = true;
        },
        editWarehouse(warehouse) {
            this.selectedWarehouse = warehouse;
            this.dialog = true;
        },
        viewWarehouse(warehouse) {
            this.selectedWarehouse = warehouse;
            this.viewDialog = true;
        },
        async handleSave(formData) {
            this.saving = true;
            try {
                if (formData.id) {
                    await axios.put(`/api/v1/warehouses/${formData.id}`, formData);
                    this.showSuccess('Warehouse updated successfully');
                } else {
                    await axios.post('/api/v1/warehouses', formData);
                    this.showSuccess('Warehouse created successfully');
                }
                this.dialog = false;
                this.selectedWarehouse = null;
                await this.loadWarehouses();
            } catch (error) {
                this.handleApiError(error, 'Failed to save warehouse');
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(warehouse) {
            this.selectedWarehouse = warehouse;
            this.deleteDialog = true;
        },
        async deleteWarehouse() {
            if (!this.selectedWarehouse?.id) return;

            this.deleting = true;
            try {
                await axios.delete(`/api/v1/warehouses/${this.selectedWarehouse.id}`);
                this.showSuccess('Warehouse deleted successfully');
                this.deleteDialog = false;
                this.selectedWarehouse = null;
                await this.loadWarehouses();
            } catch (error) {
                this.handleApiError(error, 'Failed to delete warehouse');
            } finally {
                this.deleting = false;
            }
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
            this.loadWarehouses();
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadWarehouses();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadWarehouses();
        },
        onSearch() {
            this.resetPagination();
            this.loadWarehouses();
        },
    },
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
