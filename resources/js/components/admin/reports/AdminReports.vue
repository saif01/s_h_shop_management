<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Reports & Analytics</h1>
        </div>

        <v-card>
            <v-card-text class="pa-0">
                <v-tabs v-model="activeTab" bg-color="grey-lighten-5" color="primary" slider-color="primary"
                    class="reports-tabs">
                    <v-tab value="sales">
                        <v-icon start size="small">mdi-cash-register</v-icon>
                        <span>Sales Reports</span>
                    </v-tab>
                    <v-tab value="purchase">
                        <v-icon start size="small">mdi-cart-arrow-down</v-icon>
                        <span>Purchase Reports</span>
                    </v-tab>
                    <v-tab value="stock">
                        <v-icon start size="small">mdi-package-variant</v-icon>
                        <span>Stock Reports</span>
                    </v-tab>
                    <v-tab value="due">
                        <v-icon start size="small">mdi-account-alert</v-icon>
                        <span>Due Reports</span>
                    </v-tab>
                    <v-tab value="profit">
                        <v-icon start size="small">mdi-chart-line</v-icon>
                        <span>Profit Reports</span>
                    </v-tab>
                </v-tabs>

                <v-window v-model="activeTab" class="reports-window">
                    <!-- Sales Reports -->
                    <v-window-item value="sales">
                        <div class="pa-4">
                            <SalesReports />
                        </div>
                    </v-window-item>

                    <!-- Purchase Reports -->
                    <v-window-item value="purchase">
                        <div class="pa-4">
                            <PurchaseReports />
                        </div>
                    </v-window-item>

                    <!-- Stock Reports -->
                    <v-window-item value="stock">
                        <div class="pa-4">
                            <StockReports />
                        </div>
                    </v-window-item>

                    <!-- Due Reports -->
                    <v-window-item value="due">
                        <div class="pa-4">
                            <DueReports />
                        </div>
                    </v-window-item>

                    <!-- Profit Reports -->
                    <v-window-item value="profit">
                        <div class="pa-4">
                            <ProfitReports />
                        </div>
                    </v-window-item>
                </v-window>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import SalesReports from './SalesReports.vue';
import PurchaseReports from './PurchaseReports.vue';
import StockReports from './StockReports.vue';
import DueReports from './DueReports.vue';
import ProfitReports from './ProfitReports.vue';

export default {
    name: 'AdminReports',
    components: {
        SalesReports,
        PurchaseReports,
        StockReports,
        DueReports,
        ProfitReports,
    },
    data() {
        return {
            activeTab: 'sales',
        };
    },
};
</script>

<style scoped>
/* Reports Tabs Styling */
.reports-tabs :deep(.v-tab) {
    text-transform: none;
    font-weight: 500;
    letter-spacing: 0.02em;
    transition: all 0.3s ease;
    min-height: 56px;
    padding: 0 24px;
    font-size: 14px;
}

.reports-tabs :deep(.v-tab:hover) {
    background-color: rgba(var(--v-theme-primary), 0.08);
}

.reports-tabs :deep(.v-tab--selected) {
    background-color: rgba(var(--v-theme-primary), 0.12);
    color: rgb(var(--v-theme-primary));
    font-weight: 600;
}

.reports-tabs :deep(.v-tab--selected .v-icon) {
    color: rgb(var(--v-theme-primary));
}

.reports-tabs :deep(.v-tabs-slider) {
    height: 3px;
    border-radius: 2px 2px 0 0;
}

.reports-tabs :deep(.v-tab__content) {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.reports-window {
    min-height: 400px;
}

/* Page Header Styling */
.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-weight: 600;
    color: rgba(var(--v-theme-on-surface), 0.87);
}
</style>
