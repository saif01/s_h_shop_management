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

