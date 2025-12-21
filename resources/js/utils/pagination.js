/**
 * Pagination Utilities
 * Centralized pagination state defaults and utility methods
 */

// Default pagination state for components
export const defaultPaginationState = {
    currentPage: 1,
    perPage: 10,
    pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
    },
    perPageOptions: [
        { title: '10', value: 10, description: 'Quick view' },
        { title: '25', value: 25, description: 'Standard' },
        { title: '50', value: 50, description: 'Comfortable' },
        { title: '100', value: 100, description: 'Extended' },
        { title: '500', value: 500, description: 'Large dataset' },
        { title: 'Show All', value: 'all', description: 'All records' }
    ]
};

// Pagination utility methods
export const paginationUtils = {
    /**
     * Build pagination parameters for API requests
     * @param {number} currentPage - Current page number
     * @param {number|string} perPage - Items per page
     * @param {Object} additionalParams - Additional parameters
     * @param {string|null} sortBy - Field to sort by
     * @param {string} sortDirection - Sort direction ('asc' or 'desc')
     * @returns {Object} Parameters object for API request
     */
    buildPaginationParams(currentPage, perPage, additionalParams = {}, sortBy = null, sortDirection = 'asc') {
        const params = {
            page: currentPage,
            per_page: perPage === 'all' ? 999999 : perPage,
            ...additionalParams
        };

        // Add sorting parameters if provided
        if (sortBy) {
            params.sort_by = sortBy;
            params.sort_direction = sortDirection;
        }

        return params;
    },

    /**
     * Update pagination state from API response
     * @param {Object} paginationState - Component's pagination state object
     * @param {Object} responseData - Response data from API
     */
    updatePagination(paginationState, responseData) {
        if (responseData) {
            paginationState.pagination = {
                current_page: responseData.current_page || 1,
                last_page: responseData.last_page || 1,
                per_page: responseData.per_page || paginationState.perPage,
                total: responseData.total || 0
            };
        }
    },

    /**
     * Reset pagination to first page
     * @param {Object} paginationState - Component's pagination state object
     */
    resetPagination(paginationState) {
        paginationState.currentPage = 1;
    }
};

/**
 * Pagination Mixin
 * Provides reusable pagination functionality for Vue components
 * 
 * Components using this mixin should:
 * - Define a method to load data (e.g., loadCategories, loadProducts, etc.)
 * - Override onPerPageChange() and onPageChange() to call their load method
 * - Or define a loadData() method that will be called automatically
 */
export const paginationMixin = {
    data() {
        return {
            // Pagination state - using centralized defaults
            currentPage: defaultPaginationState.currentPage,
            perPage: defaultPaginationState.perPage,
            perPageOptions: defaultPaginationState.perPageOptions,
            pagination: { ...defaultPaginationState.pagination },
        };
    },

    methods: {
        /**
         * Build pagination parameters for API requests
         * @param {Object} additionalParams - Additional parameters to include
         * @returns {Object} Parameters object for API request
         */
        buildPaginationParams(additionalParams = {}) {
            return paginationUtils.buildPaginationParams(
                this.currentPage,
                this.perPage,
                additionalParams,
                this.sortBy,
                this.sortDirection
            );
        },

        /**
         * Update pagination state from API response
         * @param {Object} responseData - Response data from API
         */
        updatePagination(responseData) {
            paginationUtils.updatePagination(this, responseData);
        },

        /**
         * Reset pagination to first page
         */
        resetPagination() {
            paginationUtils.resetPagination(this);
        },

        /**
         * Handle per page change
         * Resets pagination and reloads data
         * Components can override this to call their specific load method
         */
        onPerPageChange() {
            this.resetPagination();
            // Try to call loadData if it exists, otherwise components should override this method
            if (typeof this.loadData === 'function') {
                this.loadData();
            }
        },

        /**
         * Handle per page update from PaginationControls component
         * @param {number|string} value - New per page value
         */
        onPerPageUpdate(value) {
            this.perPage = value;
            this.onPerPageChange();
        },

        /**
         * Handle page change
         * Updates current page and reloads data
         * Components can override this to call their specific load method
         * @param {number} page - Page number to navigate to
         */
        onPageChange(page) {
            this.currentPage = page;
            // Try to call loadData if it exists, otherwise components should override this method
            if (typeof this.loadData === 'function') {
                this.loadData();
            }
        },

        /**
         * Handle table column sort
         * Updates sort field/direction, resets to first page, and reloads data
         * Components can override this to call their specific load method
         * @param {string} field - Field name to sort by
         */
        onSort(field) {
            // Use handleSort from commonMixin if available
            if (typeof this.handleSort === 'function') {
                this.handleSort(field);
            } else {
                // Fallback sorting logic
                if (this.sortBy === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = field;
                    this.sortDirection = 'asc';
                }
            }
            this.currentPage = 1; // Reset to first page when sorting changes
            // Try to call loadData if it exists, otherwise components should override this method
            if (typeof this.loadData === 'function') {
                this.loadData();
            }
        }
    }
};

