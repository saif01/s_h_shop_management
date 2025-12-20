<template>
    <div>
        <v-card variant="outlined" class="mb-4">
            <v-card-title>Profit Report Filters</v-card-title>
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="filters.date_from" label="From Date" density="compact"
                            @update:model-value="onDateFromChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <DatePicker v-model="filters.date_to" label="To Date" density="compact"
                            @update:model-value="onDateToChange" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.category_id" :items="categories" 
                            item-value="id" item-title="name" label="Category" clearable />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.group_by" :items="groupByOptions" 
                            label="Group By" />
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
                        <div class="text-caption">Total Revenue</div>
                        <div class="text-h5">${{ summary.total_revenue?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="warning" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Cost</div>
                        <div class="text-h5">${{ summary.total_cost?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="success" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Gross Profit</div>
                        <div class="text-h5">${{ summary.gross_profit?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="info" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Profit Margin</div>
                        <div class="text-h5">{{ summary.profit_margin?.toFixed(2) || 0 }}%</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Report Table -->
        <v-card variant="outlined">
            <v-data-table :headers="headers" :items="reportData" :loading="loading" 
                :items-per-page="25">
                <template #item.revenue="{ item }">
                    ${{ parseFloat(item.revenue || 0).toFixed(2) }}
                </template>
                <template #item.cost="{ item }">
                    ${{ parseFloat(item.cost || 0).toFixed(2) }}
                </template>
                <template #item.discount="{ item }">
                    ${{ parseFloat(item.discount || 0).toFixed(2) }}
                </template>
                <template #item.profit="{ item }">
                    <v-chip :color="item.profit >= 0 ? 'success' : 'error'" size="small">
                        ${{ parseFloat(item.profit || 0).toFixed(2) }}
                    </v-chip>
                </template>
                <template #item.profit_margin="{ item }">
                    {{ parseFloat(item.profit_margin || 0).toFixed(2) }}%
                </template>
            </v-data-table>
        </v-card>

        <!-- Profit Chart -->
        <v-card variant="outlined" class="mt-4" v-if="chartData.length">
            <v-card-title>Profit Trend</v-card-title>
            <v-card-text>
                <div class="text-center text-caption">
                    Chart visualization would go here (integrate with Chart.js or similar)
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import axios from '@/utils/axios.config';
import DatePicker from '../../common/DatePicker.vue';

export default {
    name: 'ProfitReports',
    components: {
        DatePicker
    },
    data() {
        return {
            loading: false,
            filters: {
                date_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                date_to: new Date().toISOString().split('T')[0],
                category_id: null,
                group_by: 'daily',
            },
            reportData: [],
            chartData: [],
            summary: null,
            categories: [],
            headers: [
                { title: 'Period', key: 'period' },
                { title: 'Product/Category', key: 'name' },
                { title: 'Qty Sold', key: 'quantity_sold', align: 'center' },
                { title: 'Revenue', key: 'revenue', align: 'end' },
                { title: 'Cost', key: 'cost', align: 'end' },
                { title: 'Discount', key: 'discount', align: 'end' },
                { title: 'Profit', key: 'profit', align: 'end' },
                { title: 'Margin %', key: 'profit_margin', align: 'end' },
            ],
            groupByOptions: [
                { title: 'Daily', value: 'daily' },
                { title: 'Weekly', value: 'weekly' },
                { title: 'Monthly', value: 'monthly' },
                { title: 'By Product', value: 'product' },
                { title: 'By Category', value: 'category' },
            ],
        };
    },
    mounted() {
        this.fetchCategories();
        this.generateReport();
    },
    methods: {
        async fetchCategories() {
            try {
                const { data } = await axios.get('/api/v1/categories');
                this.categories = data.data || data.categories || [];
            } catch (error) {
                console.error('Failed to fetch categories', error);
            }
        },
        async generateReport() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/reports/profit', { params: this.filters });
                this.reportData = data.profit || [];
                this.chartData = data.chart || [];
                this.summary = data.summary || null;
            } catch (error) {
                console.error('Failed to generate report', error);
                this.$toast?.error('Failed to generate profit report');
            } finally {
                this.loading = false;
            }
        },
        async exportExcel() {
            try {
                const response = await axios.get('/api/v1/reports/profit/export/excel', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `profit_report_${Date.now()}.xlsx`);
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
                const response = await axios.get('/api/v1/reports/profit/export/pdf', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `profit_report_${Date.now()}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Failed to export PDF', error);
                this.$toast?.error('Failed to export report');
            }
        },
        // Date change handlers - explicitly set the filter value and fetch
        onDateFromChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_from = value || '';
            this.generateReport();
        },
        onDateToChange(value) {
            // Set the filter value explicitly (handles both v-model update and direct value)
            this.filters.date_to = value || '';
            this.generateReport();
        },
    },
};
</script>

