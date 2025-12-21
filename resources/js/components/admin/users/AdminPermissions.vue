<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Permission Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Permission
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-select v-model="selectedGroup" :items="groups"
                            :item-title="item => typeof item === 'string' ? item : item.name"
                            :item-value="item => typeof item === 'string' ? item : item.name" label="Filter by Group"
                            prepend-inner-icon="mdi-filter" variant="outlined" density="compact" clearable
                            @update:model-value="loadPermissions"></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="searchQuery" label="Search permissions" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadPermissions"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Permission Groups - Compact Clickable Chips -->
        <v-card class="mb-4">
            <v-card-text class="py-3">
                <div class="d-flex align-center flex-wrap" style="gap: 8px;">
                    <v-btn-toggle v-model="viewMode" mandatory density="compact" class="mr-4">
                        <v-btn value="flat" size="small">
                            <v-icon start>mdi-format-list-bulleted</v-icon>
                            Flat View
                        </v-btn>
                        <v-btn value="grouped" size="small">
                            <v-icon start>mdi-folder</v-icon>
                            Grouped View
                        </v-btn>
                    </v-btn-toggle>
                    <span class="text-caption text-grey mr-2">Filter by Group:</span>
                    <v-chip v-for="group in groups" :key="group.name" :color="getGroupColor(group.name)" size="small"
                        :variant="selectedGroup === group.name ? 'flat' : 'outlined'"
                        :class="{ 'font-weight-bold': selectedGroup === group.name }" @click="filterByGroup(group.name)"
                        style="cursor: pointer;" class="group-filter-chip">
                        {{ group.name }}
                        <v-chip size="x-small"
                            :color="selectedGroup === group.name ? 'white' : getGroupColor(group.name)" variant="flat"
                            class="ml-2">
                            {{ group.permissions_count || 0 }}
                        </v-chip>
                    </v-chip>
                    <v-chip v-if="selectedGroup" size="small" color="grey" variant="text" prepend-icon="mdi-close"
                        @click="clearGroupFilter" style="cursor: pointer;" class="ml-2">
                        Clear Filter
                    </v-chip>
                </div>
            </v-card-text>
        </v-card>

        <!-- Permissions List -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Permissions</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ (pagination.total || 0).toLocaleString() }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <!-- Grouped View -->
                <div v-if="viewMode === 'grouped'">
                    <div v-for="(permissions, group) in groupedPermissions" :key="group" class="mb-6">
                        <h3 class="text-h6 mb-3">
                            {{ group.charAt(0).toUpperCase() + group.slice(1) }}
                            <v-chip size="small" color="primary" variant="text" class="ml-2">
                                {{ permissions.length }} permission{{ permissions.length !== 1 ? 's' : '' }}
                            </v-chip>
                        </h3>
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
                                    <th>Description</th>
                                    <th>Roles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Skeleton Loaders for Grouped View -->
                                <template v-if="loading">
                                    <tr v-for="n in 5" :key="`skeleton-grouped-${n}`">
                                        <td>
                                            <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <v-skeleton-loader type="chip" width="120" height="24"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <v-skeleton-loader type="chip" width="60" height="24"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <v-skeleton-loader type="button" width="32" height="32"
                                                    class="mr-1"></v-skeleton-loader>
                                                <v-skeleton-loader type="button" width="32"
                                                    height="32"></v-skeleton-loader>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <!-- Actual Permission Data for Grouped View -->
                                <template v-else>
                                    <tr v-for="permission in permissions" :key="permission.id">
                                        <td>
                                            <div class="font-weight-medium">{{ permission.name }}</div>
                                        </td>
                                        <td>
                                            <v-chip size="small" variant="outlined">{{ permission.slug }}</v-chip>
                                        </td>
                                        <td>
                                            <span class="text-caption text-grey">
                                                {{ permission.description || 'No description' }}
                                            </span>
                                        </td>
                                        <td>
                                            <v-chip size="small" color="info" variant="text">
                                                {{ permission.roles_count || 0 }} role{{ (permission.roles_count || 0)
                                                    !== 1
                                                    ? 's' : '' }}
                                            </v-chip>
                                        </td>
                                        <td>
                                            <v-btn size="small" icon="mdi-pencil" @click="openDialog(permission)"
                                                variant="text"></v-btn>
                                            <v-btn size="small" icon="mdi-delete" @click="deletePermission(permission)"
                                                variant="text" color="error"
                                                :disabled="(permission.roles_count || 0) > 0"></v-btn>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </v-table>
                    </div>
                </div>

                <!-- Flat View -->
                <v-table v-else>
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
                            <th class="sortable" @click="onSort('group')">
                                <div class="sortable-header">
                                    <span>Group</span>
                                    <v-icon v-if="sortBy === 'group'" size="18" class="sort-icon active">
                                        {{ sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                                    </v-icon>
                                    <v-icon v-else size="18" class="sort-icon inactive">
                                        mdi-unfold-more-horizontal
                                    </v-icon>
                                </div>
                            </th>
                            <th>Description</th>
                            <th>Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders for Flat View -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-flat-${n}`">
                            <td>
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="120" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="100" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="60" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Permission Data for Flat View -->
                        <template v-else>
                            <tr v-for="permission in permissions" :key="permission.id">
                                <td>
                                    <div class="font-weight-medium">{{ permission.name }}</div>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ permission.slug }}</v-chip>
                                </td>
                                <td>
                                    <v-chip size="small" :color="getGroupColor(permission.group)">
                                        {{ permission.group }}
                                    </v-chip>
                                </td>
                                <td>
                                    <span class="text-caption text-grey">
                                        {{ permission.description || 'No description' }}
                                    </span>
                                </td>
                                <td>
                                    <v-chip size="small" color="info" variant="text">
                                        {{ permission.roles_count || 0 }} role{{ (permission.roles_count || 0) !== 1 ?
                                            's' :
                                            '' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(permission)"
                                        variant="text"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deletePermission(permission)"
                                        variant="text" color="error"
                                        :disabled="(permission.roles_count || 0) > 0"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="permissions.length === 0">
                                <td colspan="6" class="text-center py-4">No permissions found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination (only for flat view) -->
                <div v-if="viewMode === 'flat'"
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="permissions.length > 0 && pagination.total > 0">
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

        <!-- Permission Dialog -->
        <PermissionDialog v-model="dialog" :permission="editingPermission" :groups="groups" @save="handlePermissionSave"
            @add-group="handleAddGroup" />

    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import PaginationControls from '../../common/PaginationControls.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';
import PermissionDialog from './dialogs/PermissionDialog.vue';

export default {
    components: {
        PaginationControls,
        PermissionDialog
    },
    mixins: [commonMixin],
    data() {
        return {
            permissions: [],
            groupedPermissions: {},
            groups: [],
            dialog: false,
            editingPermission: null,
            viewMode: 'flat',
            searchQuery: '', // Note: Using searchQuery instead of search for this component
            selectedGroup: null,
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    async mounted() {
        await this.loadGroups();
        await this.loadPermissions();
    },
    methods: {
        /**
         * Load all permissions from the API
         * Supports filtering by group and search query
         */
        async loadPermissions() {
            try {
                this.loading = true;
                const params = {};

                if (this.searchQuery) {
                    params.search = this.searchQuery;
                }

                if (this.selectedGroup) {
                    params.group = this.selectedGroup;
                }

                // Load grouped permissions for grouped view (no pagination)
                if (this.viewMode === 'grouped') {
                    params.grouped = true;
                } else {
                    // Add pagination for flat view
                    const paginationParams = this.buildPaginationParams();
                    params.page = paginationParams.page;
                    params.per_page = paginationParams.per_page;

                    // Handle "Show All" option
                    if (this.perPage === 'all') {
                        params.per_page = 999999; // Very large number to get all records
                    }

                    // Add sorting if set
                    if (this.sortBy) {
                        params.sort_by = this.sortBy;
                        params.sort_direction = this.sortDirection;
                    }
                }

                const response = await this.$axios.get('/api/v1/permissions', {
                    params,
                    headers: this.getAuthHeaders()
                });

                if (this.viewMode === 'grouped') {
                    this.groupedPermissions = response.data;
                    // Flatten for easier access
                    this.permissions = [];
                    Object.values(response.data).forEach(group => {
                        this.permissions.push(...group);
                    });
                    // Reset pagination for grouped view
                    paginationUtils.updatePagination(this, {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.permissions.length,
                        total: this.permissions.length
                    });
                } else {
                    // Handle paginated response
                    if (response.data.data) {
                        this.permissions = response.data.data || [];
                        this.updatePagination(response.data);
                    } else {
                        // Fallback for non-paginated response
                        this.permissions = response.data || [];
                        paginationUtils.updatePagination(this, {
                            current_page: 1,
                            last_page: 1,
                            per_page: this.permissions.length,
                            total: this.permissions.length
                        });
                    }
                    // Group them for reference
                    this.groupedPermissions = {};
                    this.permissions.forEach(permission => {
                        if (!this.groupedPermissions[permission.group]) {
                            this.groupedPermissions[permission.group] = [];
                        }
                        this.groupedPermissions[permission.group].push(permission);
                    });
                }
            } catch (error) {
                this.handleApiError(error, 'Failed to load permissions');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Load available permission groups with permission counts
         */
        async loadGroups() {
            try {
                const response = await this.$axios.get('/api/v1/permissions/groups', {
                    headers: this.getAuthHeaders()
                });
                // Handle both old format (array of strings) and new format (array of objects)
                if (response.data && response.data.length > 0) {
                    if (typeof response.data[0] === 'string') {
                        // Old format - convert to new format
                        this.groups = response.data.map(name => ({
                            name: name,
                            permissions_count: 0
                        }));
                    } else {
                        // New format
                        this.groups = response.data;
                    }
                } else {
                    this.groups = [];
                }
            } catch (error) {
                console.error('Error loading groups:', error);
                this.groups = [];
            }
        },

        /**
         * Open dialog for creating or editing a permission
         */
        openDialog(permission) {
            this.editingPermission = permission || null;
            this.dialog = true;
        },

        /**
         * Handle permission save from dialog
         */
        async handlePermissionSave() {
            await this.loadGroups();
            await this.loadPermissions();
        },

        /**
         * Handle add new group from dialog
         */
        handleAddGroup(groupName) {
            if (!this.groups.find(g => g.name === groupName || (typeof g === 'string' && g === groupName))) {
                this.groups.push({
                    name: groupName,
                    permissions_count: 0
                });
            }
        },

        /**
         * Delete a permission
         */
        async deletePermission(permission) {
            if ((permission.roles_count || 0) > 0) {
                this.showError('Cannot delete permission that is assigned to roles. Please remove it from all roles first.');
                return;
            }

            if (!confirm(`Are you sure you want to delete the permission "${permission.name}"?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/permissions/${permission.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Permission deleted successfully');
                await this.loadPermissions();
            } catch (error) {
                this.handleApiError(error, 'Error deleting permission');
            }
        },

        /**
         * Get color for group chip
         */
        getGroupColor(group) {
            const colors = {
                'general': 'primary',
                'content': 'success',
                'users': 'info',
                'leads': 'warning',
                'careers': 'purple',
                'settings': 'orange',
                'system': 'error',
            };
            return colors[group] || 'grey';
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
            this.loadPermissions();
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadPermissions();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadPermissions();
        },

        /**
         * Filter permissions by group
         */
        filterByGroup(groupName) {
            if (this.selectedGroup === groupName) {
                // If clicking the same group, clear the filter
                this.clearGroupFilter();
            } else {
                // Set the selected group and reload permissions
                this.selectedGroup = groupName;
                this.resetPagination();
                this.loadPermissions();
            }
        },

        /**
         * Clear group filter
         */
        clearGroupFilter() {
            this.selectedGroup = null;
            this.resetPagination();
            this.loadPermissions();
        }
    },
    watch: {
        viewMode() {
            // Reset to page 1 when switching views
            this.currentPage = 1;
            this.loadPermissions();
        }
    }
};
</script>

<style scoped>
</style>
