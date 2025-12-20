<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Role Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Role
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            prepend-inner-icon="mdi-filter" variant="outlined" density="compact" clearable
                            @update:model-value="loadRoles"></v-select>
                    </v-col>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="search" label="Search roles" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadRoles"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Roles List -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Roles</span>
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
                            <th class="sortable" @click="onSort('slug')">
                                <div class="sortable-header">
                                    <span>Slug</span>
                                    <v-icon v-if="sortBy === 'slug'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Permissions</th>
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
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="70" height="24"></v-skeleton-loader></td>
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
                        <!-- Actual Role Data -->
                        <template v-else>
                            <tr v-for="role in roles" :key="role.id">
                                <td>
                                    <div class="font-weight-medium">{{ role.name }}</div>
                                    <div class="text-caption text-grey">{{ role.description || 'No description' }}</div>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ role.slug }}</v-chip>
                                </td>
                                <td>
                                    <v-chip size="small" color="primary" variant="text">
                                        {{ role.permissions ? role.permissions.length : 0 }} permissions
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="role.is_active ? 'success' : 'error'" size="small">
                                        {{ role.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip v-if="role.is_system" color="warning" size="small">
                                        System
                                    </v-chip>
                                    <span v-else class="text-caption">Custom</span>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(role)" variant="text"
                                        :disabled="role.is_system"></v-btn>
                                    <v-btn size="small" icon="mdi-shield-key" @click="openPermissionDialog(role)"
                                        variant="text"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteRole(role)" variant="text"
                                        color="error" :disabled="role.is_system"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="roles.length === 0">
                                <td colspan="6" class="text-center py-4">No roles found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="roles.length > 0 && pagination.total > 0">
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

        <!-- Role Dialog -->
        <RoleDialog v-model="dialog" :role="editingRole" :grouped-permissions="groupedPermissions"
            @save="onRoleSaved" />

        <!-- Permissions Dialog -->
        <RolePermissionsDialog v-model="permissionDialog" :role="selectedRole" :grouped-permissions="groupedPermissions"
            @save="onPermissionsSaved" />
    </div>
</template>

<script>
/**
 * AdminRoles Component
 * 
 * This component manages roles in the admin panel. It provides functionality to:
 * - View all roles with pagination, search, and filtering
 * - Create new roles with permission assignment
 * - Edit existing roles (except system roles)
 * - Delete custom roles
 * - Assign/update permissions for roles
 * 
 * Features:
 * - Full CRUD operations for roles
 * - Permission management with search and bulk selection
 * - System role protection (cannot edit/delete system roles)
 * - Real-time permission filtering
 */

import commonMixin from '../../../mixins/commonMixin';
import PaginationControls from '../../common/PaginationControls.vue';
import RoleDialog from './dialogs/RoleDialog.vue';
import RolePermissionsDialog from './dialogs/RolePermissionsDialog.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';

export default {
    components: {
        PaginationControls,
        RoleDialog,
        RolePermissionsDialog
    },
    mixins: [commonMixin],
    data() {
        return {
            // List of all roles fetched from API
            roles: [],

            // Flat array of all permissions (for easy iteration)
            permissions: [],

            // Permissions grouped by their 'group' property (e.g., { 'users': [...], 'posts': [...] })
            groupedPermissions: {},

            // Controls visibility of role create/edit dialog
            dialog: false,

            // Controls visibility of separate permissions management dialog
            permissionDialog: false,

            // Currently editing role object (null when creating new role)
            editingRole: null,

            // Role selected for permission management in separate dialog
            selectedRole: null,

            // Filter for active/inactive roles (null = all roles)
            activeFilter: null,

            // Options for active status filter dropdown
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    computed: {},
    /**
     * Component lifecycle hook - runs when component is mounted
     * Loads initial data (roles and permissions)
     */
    async mounted() {
        await this.loadRoles();
        await this.loadPermissions();
    },
    methods: {
        /**
         * Load roles from API with pagination, search, and filtering
         * 
         * Supports:
         * - Pagination (via mixin)
         * - Search by name/slug
         * - Filter by active status
         * 
         * Handles both paginated and non-paginated API responses
         */
        async loadRoles() {
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
                    params.active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/roles', {
                    params,
                    headers: this.getAuthHeaders()
                });

                // Handle both paginated and non-paginated responses
                if (response.data.data) {
                    // Paginated response
                    this.roles = response.data.data || [];
                    this.updatePagination(response.data);
                } else {
                    // Non-paginated response (fallback)
                    this.roles = response.data || [];
                    paginationUtils.updatePagination(this, {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.roles.length,
                        total: this.roles.length
                    });
                }

                // If no roles exist, show a message
                if (this.roles.length === 0) {
                    console.warn('No roles found. Run the seeder to create default roles.');
                }
            } catch (error) {
                if (error.response?.status === 404) {
                    this.handleApiError(error, 'Roles endpoint not found. Please ensure migrations and seeders have run.');
                } else {
                    this.handleApiError(error, 'Failed to load roles');
                }
            } finally {
                this.loading = false;
            }
        },
        /**
         * Load all permissions from API
         * 
         * Uses grouped=true parameter to get ALL permissions without pagination.
         * This is important because we need all permissions available when assigning
         * to roles, not just the first page.
         * 
         * Handles two response formats:
         * 1. Grouped object: { 'group1': [...permissions], 'group2': [...permissions] }
         * 2. Flat array: [...permissions] (will be grouped by 'group' property)
         * 
         * Also creates a flat array of all permissions for easy iteration.
         */
        async loadPermissions() {
            try {
                // Request grouped permissions to get ALL permissions (no pagination)
                // The grouped=true parameter ensures we get all permissions, not just first 10
                const response = await this.$axios.get('/api/v1/permissions', {
                    params: {
                        grouped: true  // This ensures we get all permissions without pagination
                    },
                    headers: this.getAuthHeaders()
                });

                // Handle different response structures
                let permissionsData = response.data;

                // If response has a data property, use it (shouldn't happen with grouped=true, but just in case)
                if (response.data && response.data.data) {
                    permissionsData = response.data.data;
                }

                // Initialize as empty object if not valid
                if (!permissionsData || typeof permissionsData !== 'object') {
                    console.warn('Invalid permissions response structure:', permissionsData);
                    this.groupedPermissions = {};
                    this.permissions = [];
                    return;
                }

                // Check if it's an array (ungrouped format from API)
                if (Array.isArray(permissionsData)) {
                    // Group by group property if available, otherwise use 'general' as default
                    this.groupedPermissions = {};
                    permissionsData.forEach(permission => {
                        // Get group name from permission object, fallback to 'general'
                        const group = permission.group || permission.group_name || 'general';
                        if (!this.groupedPermissions[group]) {
                            this.groupedPermissions[group] = [];
                        }
                        this.groupedPermissions[group].push(permission);
                    });
                } else {
                    // It's already an object (grouped format from API)
                    this.groupedPermissions = permissionsData;
                }

                // Flatten for easier access (create flat array of all permissions)
                // This is useful for operations like "select all permissions"
                this.permissions = [];
                Object.values(this.groupedPermissions).forEach(group => {
                    // Ensure group is an array before spreading (safety check)
                    if (Array.isArray(group)) {
                        this.permissions.push(...group);
                    } else {
                        console.warn('Invalid group structure:', group);
                    }
                });
            } catch (error) {
                console.error('Error loading permissions:', error);
                this.groupedPermissions = {};
                this.permissions = [];
                this.handleApiError(error, 'Failed to load permissions');
            }
        },
        /**
         * Open role create/edit dialog
         * 
         * @param {Object|null} role - Role object to edit, or null to create new role
         */
        async openDialog(role) {
            // Ensure permissions are loaded before opening dialog
            if (Object.keys(this.groupedPermissions).length === 0) {
                await this.loadPermissions();
            }

            this.editingRole = role;
            this.dialog = true;
        },
        /**
         * Handle role saved event from RoleDialog
         */
        async onRoleSaved() {
            this.editingRole = null;
            await this.loadRoles();
        },
        /**
         * Delete a role
         * 
         * @param {Object} role - Role object to delete
         * 
         * System roles cannot be deleted (protected).
         * Shows confirmation dialog before deletion.
         */
        async deleteRole(role) {
            // Prevent deletion of system roles
            if (role.is_system) {
                this.showError('System roles cannot be deleted');
                return;
            }

            // Confirm deletion with user
            if (!confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/roles/${role.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Role deleted successfully');
                await this.loadRoles();  // Refresh roles list
            } catch (error) {
                this.handleApiError(error, 'Error deleting role');
            }
        },

        /**
         * Open separate permissions management dialog
         * 
         * @param {Object} role - Role to manage permissions for
         */
        async openPermissionDialog(role) {
            // Ensure permissions are loaded before opening dialog
            if (Object.keys(this.groupedPermissions).length === 0) {
                await this.loadPermissions();
            }
            this.selectedRole = role;
            this.permissionDialog = true;
        },
        /**
         * Handle permissions saved event from RolePermissionsDialog
         */
        async onPermissionsSaved() {
            this.selectedRole = null;
            await this.loadRoles();
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
        /**
         * Handle pagination per page change
         * 
         * Resets to first page and reloads roles with new page size.
         */
        onPerPageChange() {
            this.resetPagination();
            this.loadRoles();
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadRoles();
        },
        /**
         * Handle table column sorting
         * 
         * @param {string} field - Field name to sort by
         * 
         * Uses mixin's handleSort method and reloads roles with new sort order.
         */
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadRoles();
        }
    }
};
</script>

<style scoped>
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
