<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">User Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New User
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select v-model="roleFilter" :items="roleOptions" label="Filter by Role" variant="outlined"
                            density="compact" clearable @update:model-value="loadUsers"></v-select>
                    </v-col>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="search" label="Search by name, email, phone, city, or country"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadUsers"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Users Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Users</span>
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
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Role</th>
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
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <v-skeleton-loader type="avatar" width="32" height="32"></v-skeleton-loader>
                                    <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                                </div>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <v-skeleton-loader type="chip" width="80" height="24"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="chip" width="70" height="24"></v-skeleton-loader>
                                </div>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual User Data -->
                        <template v-else>
                            <tr v-for="user in users" :key="user.id">
                                <td>
                                    <div class="d-flex align-center gap-2">
                                        <v-avatar size="32" color="primary">
                                            <v-img v-if="user.avatar" :src="user.avatar" :alt="user.name"></v-img>
                                            <span v-else class="text-white">{{ user.name.charAt(0).toUpperCase()
                                                }}</span>
                                        </v-avatar>
                                        {{ user.name }}
                                    </div>
                                </td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span v-if="user.phone" class="d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-phone</v-icon>
                                        {{ user.phone }}
                                    </span>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <div v-if="user.city || user.country" class="d-flex flex-column">
                                        <span v-if="user.city" class="text-body-2">{{ user.city }}</span>
                                        <span v-if="user.country" class="text-caption text-grey">{{ user.country
                                        }}</span>
                                        <span v-if="!user.city && !user.country" class="text-caption text-grey">-</span>
                                    </div>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <div v-if="user.roles && user.roles.length > 0" class="d-flex flex-wrap gap-1">
                                        <v-chip v-for="role in user.roles" :key="role.id"
                                            :color="getRoleColor(role.slug)" size="small">
                                            {{ role.name }}
                                        </v-chip>
                                    </div>
                                    <span v-else class="text-caption text-grey">No roles</span>
                                </td>
                                <td>{{ formatDate(user.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewUserProfile(user)" variant="text"
                                        color="info" :title="'View Profile'"></v-btn>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(user)" variant="text"
                                        :title="'Edit User'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteUser(user)" variant="text"
                                        color="error" :disabled="user.id === currentUserId"
                                        :title="'Delete User'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="7" class="text-center py-4">No users found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="users.length > 0 && pagination.total > 0">
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

        <!-- User Dialog -->
        <UserDialog v-model="dialog" :user="editingUser" :roles="roles" @save="onUserSaved" />

        <!-- User Profile View Dialog -->
        <UserProfileDialog v-model="profileDialogVisible" :user="selectedUser" />
    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import UserProfileDialog from './dialogs/UserProfileDialog.vue';
import UserDialog from './dialogs/UserDialog.vue';
import PaginationControls from '../../common/PaginationControls.vue';
import { defaultPaginationState, paginationUtils } from '../../../utils/pagination.js';

export default {
    components: {
        UserProfileDialog,
        UserDialog,
        PaginationControls
    },
    mixins: [commonMixin],
    data() {
        return {
            users: [],
            roles: [],
            roleFilter: null,
            roleOptions: [],
            dialog: false,
            editingUser: null,
            currentUserId: null,
            profileDialogVisible: false, // User profile dialog visibility
            selectedUser: null, // User selected for profile view
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },
    async mounted() {
        await this.loadRoles();
        await this.loadUsers();
        this.loadCurrentUser();
    },
    methods: {
        async loadUsers() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.roleFilter) {
                    params.role = this.roleFilter;
                }

                const response = await this.$axios.get('/api/v1/users', {
                    params,
                    headers: this.getAuthHeaders()
                });

                const users = response.data.data || [];
                // Backend already returns full URLs via transformUserAvatar, so we keep them as-is
                this.users = users;
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load users');
            } finally {
                this.loading = false;
            }
        },
        async loadRoles() {
            try {
                const response = await this.$axios.get('/api/v1/users/roles', {
                    headers: this.getAuthHeaders()
                });
                this.roles = response.data.roles;

                // Populate roleOptions for filter
                this.roleOptions = this.roles.map(role => ({
                    title: role.label,
                    value: role.value // This is the role ID
                }));
            } catch (error) {
                console.error('Error loading roles:', error);
            }
        },
        loadCurrentUser() {
            // Get current user ID from token or API
            // For now, we'll get it from the auth user endpoint
            const token = localStorage.getItem('admin_token');
            this.$axios.get('/api/v1/auth/user', {
                headers: { Authorization: `Bearer ${token}` }
            }).then(response => {
                this.currentUserId = response.data.id;
            }).catch(() => {
                // Ignore if can't load current user
            });
        },
        openDialog(user) {
            this.editingUser = user;
            this.dialog = true;
        },
        /**
         * Handle user saved event from UserDialog
         */
        async onUserSaved() {
            this.editingUser = null;
            await this.loadUsers();
        },
        async deleteUser(user) {
            if (user.id === this.currentUserId) {
                this.showError('You cannot delete your own account');
                return;
            }

            if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/users/${user.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('User deleted successfully');
                await this.loadUsers();
            } catch (error) {
                this.handleApiError(error, 'Error deleting user');
            }
        },
        getRoleLabel(roleSlug) {
            const roleObj = this.roles.find(r => r.slug === roleSlug);
            return roleObj ? roleObj.label : roleSlug;
        },
        getRoleColor(roleSlug) {
            // Map role slugs to colors
            const colors = {
                administrator: 'error',
                editor: 'primary',
                'hr-user': 'success',
                staff: 'info'
            };
            return colors[roleSlug] || 'primary';
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
            this.loadUsers();
        },
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadUsers();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1; // Reset to first page when sorting changes
            this.loadUsers();
        },
        /**
         * View user profile
         */
        async viewUserProfile(user) {
            try {
                // Fetch full user details with roles and permissions
                const token = localStorage.getItem('admin_token');
                const response = await this.$axios.get(`/api/v1/users/${user.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                // Use the full user data with permissions loaded
                this.selectedUser = response.data;
                this.profileDialogVisible = true;
            } catch (error) {
                console.error('Error loading user profile:', error);
                // Fallback to the user data from the list if API call fails
                this.selectedUser = user;
                this.profileDialogVisible = true;
                this.handleApiError(error, 'Failed to load user profile details');
            }
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
