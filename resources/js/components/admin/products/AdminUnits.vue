<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Unit Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Unit
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="search" label="Search by name, code" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable @input="loadUnits"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Units Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Units</span>
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
                            <th class="sortable" @click="onSort('description')">
                                <div class="sortable-header">
                                    <span>Description</span>
                                    <v-icon v-if="sortBy === 'description'" size="18" class="sort-icon active">
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
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="200"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Unit Data -->
                        <template v-else>
                            <tr v-for="unit in units" :key="unit.id">
                                <td>
                                    <div class="d-flex align-center">
                                        <v-icon size="small" color="primary" class="mr-2">
                                            mdi-weight-kilogram
                                        </v-icon>
                                        {{ unit.name }}
                                    </div>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ unit.code }}</v-chip>
                                </td>
                                <td>{{ unit.description || '-' }}</td>
                                <td>
                                    <v-chip :color="unit.is_active ? 'success' : 'error'" size="small">
                                        {{ unit.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>{{ formatDate(unit.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(unit)" variant="text"
                                        :title="'Edit Unit'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteUnit(unit)" variant="text"
                                        color="error" :title="'Delete Unit'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="units.length === 0">
                                <td colspan="6" class="text-center py-4">No units found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="units.length > 0 && pagination.total > 0">
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

        <!-- Unit Dialog -->
        <v-dialog v-model="dialog" max-width="700" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingUnit ? 'Edit Unit' : 'Add New Unit' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="formRef" @submit.prevent="saveUnit">
                        <div class="pa-6">
                            <v-text-field v-model="form.name"
                                placeholder="Enter unit name (e.g., Kilogram, Piece, Liter)" :rules="[rules.required]"
                                required variant="outlined" density="comfortable" hide-details="auto"
                                hint="Enter the full name of the measurement unit" persistent-hint class="mb-4">
                                <template v-slot:label>
                                    Unit Name <span class="text-error"
                                        style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-text-field>

                            <v-text-field v-model="form.code" placeholder="Enter unit code (e.g., KG, PCS, LTR)"
                                hint="Short code or abbreviation for the unit (e.g., KG, PCS, LTR)" persistent-hint
                                :rules="[rules.required]" required variant="outlined" density="comfortable"
                                hide-details="auto" class="mb-4">
                                <template v-slot:label>
                                    Code <span class="text-error" style="font-size: 1.2em; font-weight: bold;">*</span>
                                </template>
                            </v-text-field>

                            <v-textarea v-model="form.description" label="Description"
                                placeholder="Enter a brief description about the unit (optional)" variant="outlined"
                                density="comfortable" rows="3" hint="Optional: Brief description about the unit"
                                persistent-hint hide-details="auto" class="mb-4" />

                            <v-switch v-model="form.is_active" label="Active Unit" color="success"
                                class="mb-4"></v-switch>
                        </div>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveUnit" color="primary" :loading="saving">
                        {{ editingUnit ? 'Update' : 'Create' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import axios from '@/utils/axios.config';
import commonMixin from '../../../mixins/commonMixin';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';
import PaginationControls from '../../common/PaginationControls.vue';

export default {
    name: 'AdminUnits',
    components: {
        PaginationControls
    },
    mixins: [commonMixin],
    data() {
        return {
            units: [],
            dialog: false,
            editingUnit: null,
            saving: false,
            form: this.getEmptyForm(),
            rules: {
                required: value => !!value || 'This field is required',
            },
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    mounted() {
        this.loadUnits();
    },
    methods: {
        getEmptyForm() {
            return {
                id: null,
                name: '',
                code: '',
                description: '',
                is_active: true,
            };
        },
        async loadUnits() {
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

                const response = await this.$axios.get('/api/v1/units', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.units = response.data.data || response.data.units || [];

                // Update pagination - if API doesn't provide pagination data, use units array length
                if (response.data.total !== undefined || response.data.last_page !== undefined) {
                    this.updatePagination(response.data);
                } else {
                    // Handle non-paginated response
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.units.length,
                        total: this.units.length
                    };
                }
            } catch (error) {
                console.error('Failed to fetch units', error);
                this.handleApiError(error, 'Failed to load units');
            } finally {
                this.loading = false;
            }
        },
        openDialog(unit) {
            if (unit) {
                this.editingUnit = unit;
                this.form = {
                    id: unit.id,
                    name: unit.name || '',
                    code: unit.code || '',
                    description: unit.description || '',
                    is_active: unit.is_active !== undefined ? unit.is_active : true,
                };
            } else {
                this.editingUnit = null;
                this.form = this.getEmptyForm();
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingUnit = null;
            this.form = this.getEmptyForm();
            if (this.$refs.formRef) {
                this.$refs.formRef.resetValidation();
            }
        },
        async saveUnit() {
            if (!this.$refs.formRef.validate()) {
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingUnit
                    ? `/api/v1/units/${this.editingUnit.id}`
                    : '/api/v1/units';

                const method = this.editingUnit ? 'put' : 'post';

                const data = { ...this.form };

                await axios[method](url, data, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingUnit ? 'Unit updated successfully' : 'Unit created successfully'
                );
                this.closeDialog();
                await this.loadUnits();
            } catch (error) {
                console.error('Error saving unit:', error);
                let message = 'Error saving unit';

                if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    const errorMessages = [];
                    Object.keys(errors).forEach(key => {
                        errorMessages.push(errors[key][0]);
                    });
                    message = errorMessages.join(', ');
                } else if (error.response?.data?.message) {
                    message = error.response.data.message;
                }

                this.showError(message);
            } finally {
                this.saving = false;
            }
        },
        async deleteUnit(unit) {
            if (!confirm(`Are you sure you want to delete ${unit.name}?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/units/${unit.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Unit deleted successfully');
                await this.loadUnits();
            } catch (error) {
                this.handleApiError(error, 'Error deleting unit');
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
            this.loadUnits();
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadUnits();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadUnits();
        },
    },
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
