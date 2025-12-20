<template>
    <div class="pagination-controls">
        <!-- Items Per Page Dropdown -->
        <v-menu location="top">
            <template v-slot:activator="{ props }">
                <v-btn v-bind="props" variant="outlined" density="compact" prepend-icon="mdi-format-list-numbered"
                    class="items-per-page-btn">
                    <span class="mr-2">Show: <strong>{{ getPerPageLabel() }}</strong></span>
                    <v-icon size="small">mdi-chevron-up</v-icon>
                </v-btn>
            </template>
            <v-list class="items-per-page-menu">
                <v-list-subheader class="text-caption font-weight-medium">
                    <v-icon size="small" class="mr-1">mdi-view-list</v-icon>
                    Items per page
                </v-list-subheader>
                <template v-for="(option, index) in computedPerPageOptions" :key="option.value">
                    <v-divider v-if="option.value === 'all'" class="my-2"></v-divider>
                    <v-list-item :value="option.value" :active="computedPerPage === option.value"
                        @click="selectPerPage(option.value)" class="items-per-page-item">
                        <template v-slot:prepend>
                            <v-icon v-if="computedPerPage === option.value" color="primary" size="small">
                                mdi-check-circle
                            </v-icon>
                            <v-icon v-else size="small" class="text-grey">
                                mdi-circle-outline
                            </v-icon>
                        </template>
                        <v-list-item-title>
                            <span class="font-weight-medium">{{ option.title }}</span>
                            <span v-if="option.description" class="text-caption text-grey ml-2">
                                {{ option.description }}
                            </span>
                        </v-list-item-title>
                    </v-list-item>
                </template>
            </v-list>
        </v-menu>

        <!-- Pagination -->
        <div v-if="computedPagination.last_page > 1 && computedPerPage !== 'all'" class="pagination-wrapper">
            <v-pagination :model-value="computedCurrentPage" :length="computedPagination.last_page" :total-visible="7"
                density="comfortable" @update:model-value="onPageChange" class="pagination-component"
                show-first-last-page first-icon="mdi-page-first" last-icon="mdi-page-last">
            </v-pagination>
        </div>
    </div>
</template>

<script>
import { defaultPaginationState, paginationUtils } from '../../utils/pagination.js';

export default {
    name: 'PaginationControls',
    props: {
        modelValue: {
            type: Number,
            default: 1
        },
        pagination: {
            type: Object,
            default: null
        },
        perPageValue: {
            type: [Number, String],
            default: 10
        },
        perPageOptions: {
            type: Array,
            default: null
        }
    },
    emits: ['update:modelValue', 'update:perPage', 'page-change', 'per-page-change', 'update:pagination'],
    data() {
        return {
            // Use centralized default pagination state
            currentPage: this.modelValue || defaultPaginationState.currentPage,
            localPerPage: this.perPageValue || defaultPaginationState.perPage,
            defaultPerPageOptions: defaultPaginationState.perPageOptions,
            localPagination: this.pagination || { ...defaultPaginationState.pagination }
        };
    },
    computed: {
        computedCurrentPage: {
            get() {
                return this.modelValue !== undefined ? this.modelValue : this.currentPage;
            },
            set(value) {
                this.currentPage = value;
                this.$emit('update:modelValue', value);
            }
        },
        computedPerPage: {
            get() {
                return this.perPageValue !== undefined ? this.perPageValue : this.localPerPage;
            },
            set(value) {
                this.localPerPage = value;
                this.$emit('update:perPage', value);
            }
        },
        computedPagination() {
            return this.pagination || this.localPagination;
        },
        computedPerPageOptions() {
            return this.perPageOptions || this.defaultPerPageOptions;
        }
    },
    watch: {
        modelValue(newValue) {
            if (newValue !== undefined) {
                this.currentPage = newValue;
            }
        },
        perPageValue(newValue) {
            if (newValue !== undefined) {
                this.localPerPage = newValue;
            }
        },
        pagination: {
            handler(newValue) {
                if (newValue) {
                    this.localPagination = { ...newValue };
                }
            },
            deep: true
        }
    },
    provide() {
        return {
            paginationState: {
                currentPage: () => this.computedCurrentPage,
                perPage: () => this.computedPerPage,
                pagination: () => this.computedPagination,
                perPageOptions: () => this.computedPerPageOptions,
                buildPaginationParams: this.buildPaginationParams,
                updatePagination: this.updatePagination,
                resetPagination: this.resetPagination
            }
        };
    },
    methods: {
        selectPerPage(value) {
            this.computedPerPage = value;
            this.$emit('update:perPage', value);
            this.$emit('per-page-change', value);
        },
        getPerPageLabel() {
            const option = this.computedPerPageOptions.find(opt => opt.value === this.computedPerPage);
            return option ? option.title : '10';
        },
        onPageChange(page) {
            this.computedCurrentPage = page;
            this.$emit('update:modelValue', page);
            this.$emit('page-change', page);
        },
        // Pagination utility methods using centralized utilities
        buildPaginationParams(additionalParams = {}, sortBy = null, sortDirection = 'asc') {
            return paginationUtils.buildPaginationParams(
                this.computedCurrentPage,
                this.computedPerPage,
                additionalParams,
                sortBy,
                sortDirection
            );
        },
        updatePagination(responseData) {
            if (responseData) {
                const paginationState = {
                    perPage: this.computedPerPage,
                    pagination: this.localPagination
                };
                paginationUtils.updatePagination(paginationState, responseData);
                this.localPagination = paginationState.pagination;
                this.$emit('update:pagination', paginationState.pagination);
            }
        },
        resetPagination() {
            this.computedCurrentPage = 1;
            this.$emit('update:modelValue', 1);
        }
    }
};
</script>

<style lang="scss" scoped>
// Items Per Page Dropdown Button
.items-per-page-btn {
    min-width: 160px;
    text-transform: none;
    letter-spacing: normal;
    font-weight: 400;
    height: 36px;
    min-height: 36px;
    display: flex;
    align-items: center;
    padding-inline: 12px;
}

.items-per-page-btn :deep(.v-btn__prepend) {
    margin-inline-end: 8px;
}

.items-per-page-btn :deep(.v-icon) {
    font-size: 18px;
}

// Pagination Controls Container
.pagination-controls {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

// Pagination Wrapper - Align first/last buttons with pagination prev/next
.pagination-wrapper {
    display: inline-flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 4px;
}

.pagination-wrapper :deep(.v-pagination) {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    margin: 0;
    padding: 0;
}

// Ensure all buttons in pagination wrapper have consistent styling
.pagination-wrapper :deep(.v-pagination .v-btn) {
    min-width: 36px;
    width: 36px;
    height: 36px;
}

// Match icon size in pagination buttons
.pagination-wrapper :deep(.v-pagination .v-btn .v-icon) {
    font-size: 20px;
}

// Hover effects to match pagination
.pagination-wrapper :deep(.v-pagination .v-btn:not(.v-btn--disabled):hover) {
    background-color: rgba(var(--v-theme-primary), 0.08);
}

// Active/selected state
.pagination-wrapper :deep(.v-pagination .v-btn--active) {
    background-color: rgb(var(--v-theme-primary));
    color: white;
}

// Disabled state to match pagination
.pagination-wrapper :deep(.v-pagination .v-btn--disabled) {
    opacity: 0.38;
    cursor: not-allowed;
}

// Items Per Page Menu Dropdown
.items-per-page-menu {
    min-width: 220px;
    padding: 8px 0;
}

.items-per-page-menu :deep(.v-list-subheader) {
    padding: 12px 16px 8px;
    opacity: 0.8;
    height: auto;
    min-height: 40px;
}

.items-per-page-item {
    padding: 8px 16px;
    min-height: 44px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.items-per-page-item:hover {
    background-color: rgba(var(--v-theme-primary), 0.08);
}

.items-per-page-item :deep(.v-list-item__prepend) {
    margin-inline-end: 12px;
}

.items-per-page-item :deep(.v-list-item-title) {
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.items-per-page-item.v-list-item--active {
    background-color: rgba(var(--v-theme-primary), 0.12);
}

.items-per-page-item.v-list-item--active :deep(.v-list-item-title) {
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
}

// Responsive adjustments for pagination
@media (max-width: 960px) {
    .items-per-page-btn {
        width: 100%;
        max-width: 100%;
        justify-content: space-between;
    }

    .pagination-controls {
        width: 100%;
        justify-content: space-between;
    }

    .pagination-wrapper {
        width: 100%;
        justify-content: flex-end;
    }

    .pagination-wrapper :deep(.v-pagination) {
        justify-content: flex-end;
        width: 100%;
    }
}
</style>