<template>
    <v-container fluid>
        <v-card>
            <v-card-title class="d-flex align-center gap-2">
                <v-icon>mdi-chart-box</v-icon>
                <span class="text-h5">Reports & Analytics</span>
            </v-card-title>
            <v-divider />
            
            <v-card-text>
                <v-tabs v-model="activeTab" bg-color="primary" dark>
                    <v-tab value="sales">Sales Reports</v-tab>
                    <v-tab value="purchase">Purchase Reports</v-tab>
                    <v-tab value="stock">Stock Reports</v-tab>
                    <v-tab value="due">Due Reports</v-tab>
                    <v-tab value="profit">Profit Reports</v-tab>
                </v-tabs>

                <v-window v-model="activeTab" class="mt-4">
                    <!-- Sales Reports -->
                    <v-window-item value="sales">
                        <SalesReports />
                    </v-window-item>

                    <!-- Purchase Reports -->
                    <v-window-item value="purchase">
                        <PurchaseReports />
                    </v-window-item>

                    <!-- Stock Reports -->
                    <v-window-item value="stock">
                        <StockReports />
                    </v-window-item>

                    <!-- Due Reports -->
                    <v-window-item value="due">
                        <DueReports />
                    </v-window-item>

                    <!-- Profit Reports -->
                    <v-window-item value="profit">
                        <ProfitReports />
                    </v-window-item>
                </v-window>
            </v-card-text>
        </v-card>
    </v-container>
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

