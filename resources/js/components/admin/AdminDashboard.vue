<template>
    <div>
        <!-- Page Header -->
        <div class="page-header mb-6 d-flex align-center justify-space-between">
            <div>
                <h1 class="text-h4 page-title mb-1">Dashboard</h1>
                <p class="text-body-2 text-grey">Welcome back! Here's what's happening with your business today.</p>
            </div>
            <v-btn icon="mdi-refresh" @click="loadDashboard" :loading="loading" variant="text" size="large" />
        </div>

        <!-- KPI Cards Row 1 - Primary Metrics -->
        <v-row class="mb-4" dense>
            <v-col cols="12" sm="6" md="3" v-for="card in primaryCards" :key="card.title">
                <v-card class="kpi-card" :class="card.color" elevation="2">
                    <v-card-text class="pa-4">
                        <div class="d-flex align-center justify-space-between mb-2">
                            <div class="kpi-icon" :class="card.iconColor">
                                <v-icon :icon="card.icon" size="32" />
                            </div>
                            <v-chip v-if="card.trend !== null" :color="card.trend >= 0 ? 'success' : 'error'"
                                size="small" variant="flat">
                                <v-icon start size="14">{{ card.trend >= 0 ? 'mdi-arrow-up' : 'mdi-arrow-down'
                                }}</v-icon>
                                {{ Math.abs(card.trend).toFixed(1) }}%
                            </v-chip>
                        </div>
                        <div class="text-caption text-grey-darken-1 mb-1">{{ card.title }}</div>
                        <div class="text-h5 font-weight-bold">{{ card.value }}</div>
                        <div v-if="card.subtitle" class="text-caption text-grey mt-1">{{ card.subtitle }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- KPI Cards Row 2 - Secondary Metrics -->
        <v-row class="mb-4" dense>
            <v-col cols="12" sm="6" md="3" v-for="card in secondaryCards" :key="card.title">
                <v-card class="kpi-card" elevation="1" variant="outlined">
                    <v-card-text class="pa-3">
                        <div class="d-flex align-center mb-2">
                            <v-icon :icon="card.icon" :color="card.color" size="24" class="mr-2" />
                            <div class="text-caption text-grey-darken-1">{{ card.title }}</div>
                        </div>
                        <div class="text-h6 font-weight-bold">{{ card.value }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Charts Row -->
        <v-row class="mb-4" dense>
            <!-- Sales Trend Chart -->
            <v-col cols="12" md="8">
                <v-card elevation="2">
                    <v-card-title class="d-flex justify-space-between align-center">
                        <div>
                            <span class="text-h6">Sales Trend</span>
                            <v-chip-group v-model="chartPeriod" mandatory class="ml-3">
                                <v-chip filter size="small" value="7">7 Days</v-chip>
                                <v-chip filter size="small" value="30">30 Days</v-chip>
                            </v-chip-group>
                        </div>
                    </v-card-title>
                    <v-divider />
                    <v-card-text class="pa-4">
                        <div v-if="loading" class="text-center py-12">
                            <v-progress-circular indeterminate color="primary" />
                        </div>
                        <div v-else-if="chartData.labels && chartData.labels.length > 0"
                            style="position: relative; height: 300px;">
                            <Line :key="chartPeriod" :data="chartData" :options="chartOptions" />
                        </div>
                        <div v-else class="text-center py-12 text-grey">
                            <v-icon icon="mdi-chart-line" size="48" class="mb-2" />
                            <div>No sales data available for the selected period</div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- Quick Stats -->
            <v-col cols="12" md="4">
                <v-card elevation="2" class="h-100">
                    <v-card-title>Quick Stats</v-card-title>
                    <v-divider />
                    <v-card-text class="pa-4">
                        <div v-for="stat in quickStats" :key="stat.label"
                            class="d-flex justify-space-between align-center mb-4">
                            <div>
                                <div class="text-caption text-grey">{{ stat.label }}</div>
                                <div class="text-h6 font-weight-bold mt-1">{{ stat.value }}</div>
                            </div>
                            <v-icon :icon="stat.icon" :color="stat.color" size="24" />
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Data Tables Row -->
        <v-row class="mb-4" dense>
            <!-- Top Products -->
            <v-col cols="12" md="6">
                <v-card elevation="2">
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>Top Products (This Month)</span>
                        <v-chip size="small" color="primary" variant="flat">Top 5</v-chip>
                    </v-card-title>
                    <v-divider />
                    <v-card-text class="pa-0">
                        <v-list v-if="topProducts.length > 0" lines="two">
                            <v-list-item v-for="(product, index) in topProducts" :key="product.id" class="px-4">
                                <template v-slot:prepend>
                                    <div class="rank-badge mr-3">{{ index + 1 }}</div>
                                </template>
                                <v-list-item-title class="font-weight-medium">{{ product.name }}</v-list-item-title>
                                <v-list-item-subtitle>
                                    <div class="d-flex justify-space-between align-center mt-1">
                                        <span>{{ product.quantity }} units sold</span>
                                        <span class="font-weight-bold text-primary">{{ formatCurrency(product.sales)
                                        }}</span>
                                    </div>
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                        <div v-else class="text-center py-8 text-grey">No data available</div>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- Top Customers -->
            <v-col cols="12" md="6">
                <v-card elevation="2">
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>Top Customers (This Month)</span>
                        <v-chip size="small" color="success" variant="flat">Top 5</v-chip>
                    </v-card-title>
                    <v-divider />
                    <v-card-text class="pa-0">
                        <v-list v-if="topCustomers.length > 0" lines="two">
                            <v-list-item v-for="(customer, index) in topCustomers" :key="customer.id" class="px-4">
                                <template v-slot:prepend>
                                    <v-avatar :color="getCustomerColor(index)" size="40" class="mr-3">
                                        <span class="text-white font-weight-bold">{{
                                            customer.name.charAt(0).toUpperCase() }}</span>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-medium">{{ customer.name }}</v-list-item-title>
                                <v-list-item-subtitle>
                                    <div class="d-flex justify-space-between align-center mt-1">
                                        <span>{{ customer.orders }} {{ customer.orders === 1 ? 'order' : 'orders'
                                        }}</span>
                                        <span class="font-weight-bold text-success">{{ formatCurrency(customer.sales)
                                        }}</span>
                                    </div>
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                        <div v-else class="text-center py-8 text-grey">No data available</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Alerts and Recent Sales Row -->
        <v-row dense>
            <!-- Low Stock Alerts -->
            <v-col cols="12" md="4">
                <v-card elevation="2" :color="lowStockItems.length > 0 ? 'error' : 'success'" variant="tonal">
                    <v-card-title class="d-flex align-center">
                        <v-icon :icon="lowStockItems.length > 0 ? 'mdi-alert' : 'mdi-check-circle'" class="mr-2" />
                        <span>Stock Alerts</span>
                        <v-chip v-if="lowStockItems.length > 0" size="small" color="error" class="ml-2">{{
                            lowStockItems.length
                        }}</v-chip>
                    </v-card-title>
                    <v-divider />
                    <v-card-text class="pa-0">
                        <v-list v-if="lowStockItems.length > 0" lines="two" max-height="300" style="overflow-y: auto;">
                            <v-list-item v-for="item in lowStockItems" :key="item.id" class="px-4">
                                <v-list-item-title class="font-weight-medium">{{ item.name }}</v-list-item-title>
                                <v-list-item-subtitle>
                                    <div class="d-flex justify-space-between align-center mt-1">
                                        <span class="text-error">Stock: {{ item.quantity }}</span>
                                        <span class="text-caption">Min: {{ item.minimum }}</span>
                                    </div>
                                    <div class="text-caption text-error mt-1">
                                        <v-icon icon="mdi-alert-circle" size="14" class="mr-1" />
                                        Need {{ item.needed }} more
                                    </div>
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                        <div v-else class="text-center py-8">
                            <v-icon icon="mdi-check-circle" size="48" color="success" class="mb-2" />
                            <div class="text-body-1">All products are well stocked</div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- Recent Sales -->
            <v-col cols="12" md="8">
                <v-card elevation="2">
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>Recent Sales</span>
                        <v-btn size="small" variant="text" to="/admin/reports">View All</v-btn>
                    </v-card-title>
                    <v-divider />
                    <v-card-text class="pa-0">
                        <v-table v-if="recentSales.length > 0" density="compact">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Due</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sale in recentSales" :key="sale.id">
                                    <td>
                                        <v-chip size="small" variant="outlined">{{ sale.invoice_number }}</v-chip>
                                    </td>
                                    <td>{{ sale.customer }}</td>
                                    <td class="text-right font-weight-bold">{{ formatCurrency(sale.total) }}</td>
                                    <td class="text-right text-success">{{ formatCurrency(sale.paid) }}</td>
                                    <td class="text-right">
                                        <span
                                            :class="sale.balance > 0 ? 'text-warning font-weight-bold' : 'text-success'">
                                            {{ formatCurrency(sale.balance) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate(sale.date) }}</td>
                                    <td>
                                        <v-chip :color="getStatusColor(sale.status)" size="small" variant="flat">
                                            {{ sale.status }}
                                        </v-chip>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                        <div v-else class="text-center py-8 text-grey">No sales recorded yet</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script>
import axios from '@/utils/axios.config';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import commonMixin from '../../mixins/commonMixin';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

export default {
    name: 'AdminDashboard',
    components: {
        Line
    },
    mixins: [commonMixin],
    data() {
        return {
            loading: false,
            chartPeriod: '7',
            metrics: {
                today_sales: 0,
                yesterday_sales: 0,
                sales_growth: 0,
                month_sales: 0,
                last_month_sales: 0,
                month_growth: 0,
                today_purchases: 0,
                month_purchases: 0,
                profit: 0,
                customer_due: 0,
                supplier_due: 0,
                low_stock_items: 0,
                product_count: 0,
                customer_count: 0,
                supplier_count: 0,
            },
            charts: {
                sales_trend_7: [],
                sales_trend_30: [],
            },
            topProducts: [],
            topCustomers: [],
            lowStockItems: [],
            recentSales: [],
        };
    },
    computed: {
        primaryCards() {
            return [
                {
                    title: 'Today Sales',
                    value: this.formatCurrency(this.metrics.today_sales),
                    icon: 'mdi-cash-register',
                    iconColor: 'bg-primary',
                    color: 'primary-card',
                    trend: this.metrics.sales_growth,
                    subtitle: 'vs Yesterday',
                },
                {
                    title: 'This Month Sales',
                    value: this.formatCurrency(this.metrics.month_sales),
                    icon: 'mdi-chart-line',
                    iconColor: 'bg-success',
                    color: 'success-card',
                    trend: this.metrics.month_growth,
                    subtitle: 'vs Last Month',
                },
                {
                    title: 'This Month Profit',
                    value: this.formatCurrency(this.metrics.profit),
                    icon: 'mdi-trending-up',
                    iconColor: 'bg-info',
                    color: 'info-card',
                    trend: null,
                    subtitle: 'Revenue - Cost',
                },
                {
                    title: 'Customer Due',
                    value: this.formatCurrency(this.metrics.customer_due),
                    icon: 'mdi-account-alert',
                    iconColor: 'bg-warning',
                    color: 'warning-card',
                    trend: null,
                    subtitle: 'Outstanding payments',
                },
            ];
        },
        secondaryCards() {
            return [
                {
                    title: 'Today Purchases',
                    value: this.formatCurrency(this.metrics.today_purchases),
                    icon: 'mdi-cart-arrow-down',
                    color: 'info',
                },
                {
                    title: 'Supplier Due',
                    value: this.formatCurrency(this.metrics.supplier_due),
                    icon: 'mdi-account-outline',
                    color: 'warning',
                },
                {
                    title: 'Products',
                    value: this.metrics.product_count,
                    icon: 'mdi-package-variant',
                    color: 'primary',
                },
                {
                    title: 'Low Stock Items',
                    value: this.metrics.low_stock_items,
                    icon: 'mdi-alert',
                    color: 'error',
                },
            ];
        },
        quickStats() {
            return [
                {
                    label: 'Customers',
                    value: this.metrics.customer_count,
                    icon: 'mdi-account-group',
                    color: 'primary',
                },
                {
                    label: 'Suppliers',
                    value: this.metrics.supplier_count,
                    icon: 'mdi-truck-delivery',
                    color: 'info',
                },
                {
                    label: 'Month Purchases',
                    value: this.formatCurrency(this.metrics.month_purchases),
                    icon: 'mdi-cart',
                    color: 'success',
                },
            ];
        },
        chartData() {
            const trendData = this.chartPeriod === '7'
                ? (this.charts.sales_trend_7 || [])
                : (this.charts.sales_trend_30 || []);

            if (!trendData || trendData.length === 0) {
                return {
                    labels: [],
                    datasets: []
                };
            }

            return {
                labels: trendData.map(item => item.label || item.week || item.date),
                datasets: [
                    {
                        label: 'Sales',
                        data: trendData.map(item => item.sales || 0),
                        borderColor: 'rgb(25, 118, 210)',
                        backgroundColor: 'rgba(25, 118, 210, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                ],
            };
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                        },
                        bodyFont: {
                            size: 13,
                        },
                        callbacks: {
                            label: (context) => {
                                const value = context.parsed.y || 0;
                                return `Sales: ${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)}`;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => {
                                if (value >= 1000) {
                                    return '$' + (value / 1000).toFixed(1) + 'k';
                                }
                                return '$' + value;
                            },
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                },
            };
        },
    },
    watch: {
        chartPeriod() {
            // Chart will automatically update via computed property
        },
    },
    created() {
        this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/dashboard', {
                    headers: this.getAuthHeaders()
                });
                const data = response.data;
                this.metrics = { ...this.metrics, ...(data.metrics || {}) };
                this.charts = { ...this.charts, ...(data.charts || {}) };
                this.topProducts = data.top_products || [];
                this.topCustomers = data.top_customers || [];
                this.lowStockItems = data.low_stock_items || [];
                this.recentSales = data.recent_sales || [];
            } catch (error) {
                console.error('Failed to load dashboard', error);
                this.handleApiError(error, 'Failed to load dashboard data');
            } finally {
                this.loading = false;
            }
        },
        formatCurrency(value) {
            const number = Number(value) || 0;
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(number);
        },
        getCustomerColor(index) {
            const colors = ['primary', 'success', 'info', 'warning', 'error'];
            return colors[index % colors.length];
        },
        getStatusColor(status) {
            const colorMap = {
                'completed': 'success',
                'pending': 'warning',
                'cancelled': 'error',
                'paid': 'success',
                'partial': 'info',
            };
            return colorMap[status?.toLowerCase()] || 'grey';
        },
    },
};
</script>

<style scoped>
.page-header {
    gap: 8px;
}

.kpi-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: default;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.kpi-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.bg-primary {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
}

.bg-success {
    background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
}

.bg-info {
    background: linear-gradient(135deg, #0288d1 0%, #0277bd 100%);
}

.bg-warning {
    background: linear-gradient(135deg, #f57c00 0%, #ef6c00 100%);
}

.primary-card {
    border-left: 4px solid #1976d2;
}

.success-card {
    border-left: 4px solid #388e3c;
}

.info-card {
    border-left: 4px solid #0288d1;
}

.warning-card {
    border-left: 4px solid #f57c00;
}

.rank-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

:deep(.v-chip-group .v-chip--selected) {
    background-color: rgb(var(--v-theme-primary));
    color: white;
}
</style>