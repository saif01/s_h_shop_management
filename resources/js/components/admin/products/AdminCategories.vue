<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Category Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Category
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadCategories"></v-select>
                    </v-col>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="search" label="Search by name, slug" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable @input="loadCategories"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Categories Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Categories</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ (pagination.total || 0).toLocaleString() }}</strong>
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
                            <th>Image</th>
                            <th class="sortable" @click="onSort('order')">
                                <div class="sortable-header">
                                    <span>Order</span>
                                    <v-icon v-if="sortBy === 'order'" size="18" class="sort-icon active">
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
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="image" width="50" height="50"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="40"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Category Data -->
                        <template v-else>
                            <tr v-for="category in categories" :key="category.id">
                                <td>
                                    <div class="d-flex align-center">
                                        <v-icon size="small" color="primary" class="mr-2">
                                            mdi-folder
                                        </v-icon>
                                        {{ category.name }}
                                    </div>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ category.slug }}</v-chip>
                                </td>
                                <td>
                                    <v-avatar size="50" v-if="category.image">
                                        <v-img :src="category.image" :alt="category.name"></v-img>
                                    </v-avatar>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <v-chip size="small" color="grey-lighten-2">
                                        {{ category.order || 0 }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="category.is_active ? 'success' : 'error'" size="small">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="openViewDialog(category)" variant="text"
                                        color="info" :title="'View Category Details'"></v-btn>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(category)" variant="text"
                                        :title="'Edit Category'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteCategory(category)"
                                        variant="text" color="error" :title="'Delete Category'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="categories.length === 0">
                                <td colspan="6" class="text-center py-4">No categories found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <!-- Left: Records Info -->
                    <div class="text-caption text-grey">
                        <span v-if="categories.length > 0 && pagination.total > 0">
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

        <!-- Category View Dialog -->
        <CategoryViewDialog v-model="viewDialog" :category="selectedCategory" />

        <!-- Category Dialog -->
        <CategoryDialog v-model="dialog" :category="editingCategory" @saved="handleCategorySaved" />
    </div>
</template>

<script>
import commonMixin from '../../../mixins/commonMixin';
import PaginationControls from '../../common/PaginationControls.vue';
import { paginationMixin } from '../../../utils/pagination.js';
import CategoryViewDialog from './dialogs/category/CategoryViewDialog.vue';
import CategoryDialog from './dialogs/category/CategoryDialog.vue';

export default {
    components: {
        PaginationControls,
        CategoryViewDialog,
        CategoryDialog
    },
    mixins: [commonMixin, paginationMixin],
    data() {
        return {
            categories: [],
            activeFilter: null,
            dialog: false,
            editingCategory: null,
            viewDialog: false,
            selectedCategory: null,
        };
    },
    async mounted() {
        await this.loadCategories();
    },
    methods: {
        async loadCategories() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                // Handle "Show All" option
                if (this.perPage === 'all') {
                    params.per_page = 999999;
                }

                if (this.search) {
                    params.search = this.search;
                }
                if (this.activeFilter !== null) {
                    params.is_active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/categories', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.categories = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load categories');
            } finally {
                this.loading = false;
            }
        },
        // Override pagination mixin methods to call loadCategories
        onPerPageChange() {
            this.resetPagination();
            this.loadCategories();
        },
        onPageChange(page) {
            this.currentPage = page;
            this.loadCategories();
        },
        onSort(field) {
            this.handleSort(field);
            this.currentPage = 1;
            this.loadCategories();
        },
        // Override openDialog to use component-specific property names
        openDialog(category) {
            this.editingCategory = category;
            this.dialog = true;
        },
        // Override openViewDialog to use component-specific property names
        openViewDialog(category) {
            this.selectedCategory = category;
            this.viewDialog = true;
        },
        handleCategorySaved() {
            this.loadCategories();
        },
        deleteCategory(category) {
            this.deleteItem(category, {
                apiEndpoint: '/api/v1/categories',
                itemName: 'category',
                itemLabel: category.name,
                successMessage: 'Category deleted successfully',
                errorMessage: 'Error deleting category',
                onSuccess: () => this.loadCategories()
            });
        },
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
