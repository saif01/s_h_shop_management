<template>
    <div>
        <v-card variant="outlined" class="mb-4">
            <v-card-title>Sales Report Filters</v-card-title>
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
                        <v-select v-model="filters.customer_id" :items="customers" item-value="id" item-title="name"
                            label="Customer" clearable />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.status" :items="statusOptions" label="Status" clearable />
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
                        <div class="text-caption">Total Sales</div>
                        <div class="text-h5">${{ summary.total_sales?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="success" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Paid</div>
                        <div class="text-h5">${{ summary.total_paid?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="warning" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Due</div>
                        <div class="text-h5">${{ summary.total_due?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="3">
                <v-card color="info" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Invoices</div>
                        <div class="text-h5">{{ summary.total_count || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Report Table -->
        <v-card variant="outlined">
            <v-data-table :headers="headers" :items="reportData" :loading="loading" :items-per-page="25">
                <template #item.invoice_date="{ item }">
                    {{ formatDate(item.invoice_date) }}
                </template>
                <template #item.total_amount="{ item }">
                    ${{ parseFloat(item.total_amount).toFixed(2) }}
                </template>
                <template #item.paid_amount="{ item }">
                    ${{ parseFloat(item.paid_amount).toFixed(2) }}
                </template>
                <template #item.balance_amount="{ item }">
                    <span :class="item.balance_amount > 0 ? 'text-error' : 'text-success'">
                        ${{ parseFloat(item.balance_amount).toFixed(2) }}
                    </span>
                </template>
                <template #item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" size="small">
                        {{ item.status }}
                    </v-chip>
                </template>
            </v-data-table>
        </v-card>

        <!-- Top Selling Products -->
        <v-card variant="outlined" class="mt-4" v-if="topProducts.length">
            <v-card-title>Top Selling Products</v-card-title>
            <v-card-text>
                <v-table density="compact">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Quantity Sold</th>
                            <th class="text-right">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in topProducts" :key="product.product_id">
                            <td>{{ product.product_name }}</td>
                            <td class="text-right">{{ product.total_quantity }}</td>
                            <td class="text-right">${{ parseFloat(product.total_revenue).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </v-table>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import axios from '@/utils/axios.config';
import DatePicker from '../../common/DatePicker.vue';

export default {
    name: 'SalesReports',
    components: {
        DatePicker
    },
    data() {
        return {
            loading: false,
            filters: {
                date_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                date_to: new Date().toISOString().split('T')[0],
                customer_id: null,
                status: null,
            },
            reportData: [],
            topProducts: [],
            summary: null,
            customers: [],
            headers: [
                { title: 'Invoice #', key: 'invoice_number' },
                { title: 'Date', key: 'invoice_date' },
                { title: 'Customer', key: 'customer_name' },
                { title: 'Total', key: 'total_amount', align: 'end' },
                { title: 'Paid', key: 'paid_amount', align: 'end' },
                { title: 'Due', key: 'balance_amount', align: 'end' },
                { title: 'Status', key: 'status' },
            ],
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Pending', value: 'pending' },
                { title: 'Partial', value: 'partial' },
                { title: 'Paid', value: 'paid' },
                { title: 'Cancelled', value: 'cancelled' },
            ],
        };
    },
    mounted() {
        this.fetchCustomers();
        this.generateReport();
    },
    methods: {
        async fetchCustomers() {
            try {
                const { data } = await axios.get('/api/v1/customers');
                this.customers = data.data || data.customers || [];
            } catch (error) {
                console.error('Failed to fetch customers', error);
            }
        },
        async generateReport() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/reports/sales', { params: this.filters });
                this.reportData = data.sales || [];
                this.topProducts = data.top_products || [];
                this.summary = data.summary || null;
            } catch (error) {
                console.error('Failed to generate report', error);
                this.$toast?.error('Failed to generate sales report');
            } finally {
                this.loading = false;
            }
        },
        async exportExcel() {
            try {
                const response = await axios.get('/api/v1/reports/sales/export/excel', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `sales_report_${Date.now()}.xlsx`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                this.$toast?.success('Report exported successfully');
            } catch (error) {
                console.error('Failed to export Excel', error);
                this.$toast?.error('Failed to export report');
            }
        },
        async exportPDF() {
            try {
                const response = await axios.get('/api/v1/reports/sales/export/pdf', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `sales_report_${Date.now()}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                this.$toast?.success('Report exported successfully');
            } catch (error) {
                console.error('Failed to export PDF', error);
                this.$toast?.error('Failed to export report');
            }
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
        },
        getStatusColor(status) {
            const colors = {
                draft: 'grey',
                pending: 'warning',
                partial: 'info',
                paid: 'success',
                cancelled: 'error',
            };
            return colors[status] || 'grey';
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
