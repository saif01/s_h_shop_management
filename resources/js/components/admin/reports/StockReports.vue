<template>
    <div>
        <v-card variant="outlined" class="mb-4">
            <v-card-title>Stock Report Filters</v-card-title>
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.warehouse_id" :items="warehouses" 
                            item-value="id" item-title="name" label="Warehouse" clearable />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.category_id" :items="categories" 
                            item-value="id" item-title="name" label="Category" clearable />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.report_type" :items="reportTypes" 
                            label="Report Type" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-switch v-model="filters.low_stock_only" label="Low Stock Only" />
                    </v-col>
                    <v-col cols="12" class="d-flex gap-2">
                        <v-btn color="primary" @click="generateReport" :loading="loading">
                            <v-icon>mdi-chart-line</v-icon> Generate Report
                        </v-btn>
                        <v-btn color="success" @click="exportExcel" :disabled="!reportData.length">
                            <v-icon>mdi-file-excel</v-icon> Export Excel
                        </v-btn>
                        <v-btn color="error" @click="exportPDF" :disabled="!reportData.length">
                            <v-icon>mdi-file-pdf-box</v-icon> Export PDF
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Summary Cards -->
        <v-row v-if="summary" class="mb-4">
            <v-col cols="12" sm="3">
                <v-card color="primary" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Products</div>
                        <div class="text-h5">{{ summary.total_products || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="success" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Stock Value</div>
                        <div class="text-h5">${{ summary.total_stock_value?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="warning" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Low Stock Items</div>
                        <div class="text-h5">{{ summary.low_stock_count || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="error" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Out of Stock</div>
                        <div class="text-h5">{{ summary.out_of_stock_count || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Report Table -->
        <v-card variant="outlined">
            <v-data-table :headers="headers" :items="reportData" :loading="loading" 
                :items-per-page="25">
                <template #item.quantity="{ item }">
                    <v-chip :color="getStockColor(item)" size="small">
                        {{ item.quantity || 0 }}
                    </v-chip>
                </template>
                <template #item.minimum_stock_level="{ item }">
                    {{ item.minimum_stock_level || 0 }}
                </template>
                <template #item.stock_value="{ item }">
                    ${{ (item.quantity * item.purchase_price).toFixed(2) }}
                </template>
                <template #item.status="{ item }">
                    <v-chip :color="getStockColor(item)" size="small">
                        {{ getStockStatus(item) }}
                    </v-chip>
                </template>
            </v-data-table>
        </v-card>
    </div>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'StockReports',
    data() {
        return {
            loading: false,
            filters: {
                warehouse_id: null,
                category_id: null,
                report_type: 'current',
                low_stock_only: false,
            },
            reportData: [],
            summary: null,
            warehouses: [],
            categories: [],
            headers: [
                { title: 'Product', key: 'product_name' },
                { title: 'SKU', key: 'sku' },
                { title: 'Category', key: 'category_name' },
                { title: 'Warehouse', key: 'warehouse_name' },
                { title: 'Quantity', key: 'quantity', align: 'center' },
                { title: 'Min Level', key: 'minimum_stock_level', align: 'center' },
                { title: 'Stock Value', key: 'stock_value', align: 'end' },
                { title: 'Status', key: 'status' },
            ],
            reportTypes: [
                { title: 'Current Stock', value: 'current' },
                { title: 'Stock Ledger', value: 'ledger' },
                { title: 'Low Stock', value: 'low_stock' },
            ],
        };
    },
    mounted() {
        this.fetchOptions();
        this.generateReport();
    },
    methods: {
        async fetchOptions() {
            try {
                const [warehousesRes, categoriesRes] = await Promise.all([
                    axios.get('/api/v1/warehouses'),
                    axios.get('/api/v1/categories'),
                ]);
                this.warehouses = warehousesRes.data.data || warehousesRes.data.warehouses || [];
                this.categories = categoriesRes.data.data || categoriesRes.data.categories || [];
            } catch (error) {
                console.error('Failed to fetch options', error);
            }
        },
        async generateReport() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/reports/stock', { params: this.filters });
                this.reportData = data.stock || [];
                this.summary = data.summary || null;
            } catch (error) {
                console.error('Failed to generate report', error);
                this.$toast?.error('Failed to generate stock report');
            } finally {
                this.loading = false;
            }
        },
        async exportExcel() {
            try {
                const response = await axios.get('/api/v1/reports/stock/export/excel', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `stock_report_${Date.now()}.xlsx`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Failed to export Excel', error);
                this.$toast?.error('Failed to export report');
            }
        },
        async exportPDF() {
            try {
                const response = await axios.get('/api/v1/reports/stock/export/pdf', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `stock_report_${Date.now()}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Failed to export PDF', error);
                this.$toast?.error('Failed to export report');
            }
        },
        getStockColor(item) {
            if (!item.quantity || item.quantity === 0) return 'error';
            if (item.quantity <= item.minimum_stock_level) return 'warning';
            return 'success';
        },
        getStockStatus(item) {
            if (!item.quantity || item.quantity === 0) return 'Out of Stock';
            if (item.quantity <= item.minimum_stock_level) return 'Low Stock';
            return 'In Stock';
        },
    },
};
</script>

