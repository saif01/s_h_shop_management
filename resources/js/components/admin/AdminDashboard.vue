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

        <!-- Ultra Compact Modern KPI Cards -->
        <v-row class="mb-3 kpi-cards-row" dense>
            <v-col cols="6" sm="4" md="3" lg="2" v-for="card in allKPICards" :key="card.title">
                <v-card class="kpi-card-ultra" :class="card.colorClass" elevation="0">
                    <div class="kpi-ultra-wrapper">
                        <div class="kpi-icon-ultra" :class="card.iconBg">
                            <v-icon :icon="card.icon" size="18" />
                        </div>
                        <div class="kpi-ultra-content">
                            <div class="kpi-ultra-header">
                                <span class="kpi-ultra-title">{{ card.title }}</span>
                                <span v-if="card.trend !== null" class="kpi-trend-indicator"
                                    :class="card.trend >= 0 ? 'trend-up' : 'trend-down'">
                                    <v-icon size="12">{{ card.trend >= 0 ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
                                    {{ Math.abs(card.trend).toFixed(1) }}%
                                </span>
                                <span v-else class="kpi-trend-placeholder"></span>
                            </div>
                            <div class="kpi-ultra-value">{{ card.value }}</div>
                            <div class="kpi-ultra-subtitle-wrapper">
                                <span v-if="card.subtitle" class="kpi-ultra-subtitle">{{ card.subtitle }}</span>
                                <span v-else class="kpi-ultra-subtitle-placeholder"></span>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-glow-effect" :class="card.colorClass"></div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Modern AI Insights -->
        <v-row class="mb-3" dense>
            <v-col cols="12">
                <v-card elevation="0" class="ai-insights-modern">
                    <div class="ai-header-modern">
                        <div class="ai-header-left">
                            <div class="ai-icon-wrapper">
                                <v-icon icon="mdi-brain" color="primary" size="24" />
                            </div>
                            <div>
                                <div class="ai-title">AI Insights</div>
                                <div class="ai-subtitle">Smart Business Analysis</div>
                            </div>
                        </div>
                        <v-chip size="x-small" color="primary" variant="flat" class="ai-badge">
                            <v-icon start size="12">mdi-sparkles</v-icon>
                            AI
                        </v-chip>
                    </div>

                    <div v-if="loading" class="ai-loading">
                        <v-progress-circular indeterminate color="primary" size="32" />
                        <div class="ai-loading-text">Analyzing data...</div>
                    </div>

                    <div v-else class="ai-content">
                        <div class="ai-meta-row">
                            <div class="ai-meta-card" v-if="aiInsights.performanceScore">
                                <div class="ai-meta-icon success">
                                    <v-icon icon="mdi-speedometer" size="16" />
                                </div>
                                <div class="ai-meta-body">
                                    <div class="ai-meta-label">Performance</div>
                                    <div class="ai-meta-value">{{ aiInsights.performanceScore.score }}</div>
                                    <div class="ai-meta-note">{{ aiInsights.performanceScore.label }}</div>
                                </div>
                            </div>

                            <div class="ai-meta-card" :class="aiInsights.anomalies && aiInsights.anomalies.length > 0 ? 'meta-alert' : ''">
                                <div class="ai-meta-icon" :class="aiInsights.anomalies && aiInsights.anomalies.length > 0 ? 'warning' : 'success'">
                                    <v-icon :icon="aiInsights.anomalies && aiInsights.anomalies.length > 0 ? 'mdi-alert-octagon' : 'mdi-shield-check'" size="16" />
                                </div>
                                <div class="ai-meta-body">
                                    <div class="ai-meta-label">Alerts</div>
                                    <div class="ai-meta-value">{{ aiInsights.anomalies && aiInsights.anomalies.length > 0 ? aiInsights.anomalies.length : 0 }}</div>
                                    <div class="ai-meta-note">{{ aiInsights.anomalies && aiInsights.anomalies.length > 0 ? aiInsights.anomalies[0].message : 'All clear' }}</div>
                                </div>
                            </div>

                            <div class="ai-meta-card" v-if="aiInsights.salesForecast">
                                <div class="ai-meta-icon info">
                                    <v-icon icon="mdi-trending-up" size="16" />
                                </div>
                                <div class="ai-meta-body">
                                    <div class="ai-meta-label">Forecast</div>
                                    <div class="ai-meta-value">{{ formatCurrency(aiInsights.salesForecast.predicted) }}</div>
                                    <div class="ai-meta-note">Next {{ aiInsights.salesForecast.period }}</div>
                                </div>
                            </div>

                            <div class="ai-meta-card" v-if="aiInsights.recommendations && aiInsights.recommendations.length > 0">
                                <div class="ai-meta-icon primary">
                                    <v-icon icon="mdi-lightbulb-on-outline" size="16" />
                                </div>
                                <div class="ai-meta-body">
                                    <div class="ai-meta-label">Recommendations</div>
                                    <div class="ai-meta-value">{{ aiInsights.recommendations.length }}</div>
                                    <div class="ai-meta-note">Top priority: {{ aiInsights.recommendations[0].priority }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Insights Grid -->
                        <div class="ai-insights-grid ai-compact-grid">
                            <!-- Performance Score -->
                            <div v-if="aiInsights.performanceScore" class="ai-insight-card ai-card-performance">
                                <div class="ai-insight-header">
                                    <div class="ai-header-chip success">
                                        <v-icon icon="mdi-chart-bell-curve" size="14" />
                                        Score Pulse
                                    </div>
                                    <span class="ai-insight-title">Performance</span>
                                </div>
                                <div class="ai-performance-content">
                                    <div class="ai-score-circle">
                                        <v-progress-circular :model-value="aiInsights.performanceScore.score"
                                            :color="getPerformanceColor(aiInsights.performanceScore.score)" size="52"
                                            width="5">
                                            <span class="ai-score-value">{{ aiInsights.performanceScore.score }}</span>
                                        </v-progress-circular>
                                    </div>
                                    <div class="ai-score-info">
                                        <div class="ai-score-label">{{ aiInsights.performanceScore.label }}</div>
                                        <div class="ai-score-desc">{{ aiInsights.performanceScore.description }}</div>
                                        <div class="ai-score-tags">
                                            <span class="ai-tag">{{ getPerformanceColor(aiInsights.performanceScore.score) }} mode</span>
                                            <span class="ai-tag subtle">Live</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Anomaly Alert -->
                            <div v-if="aiInsights.anomalies && aiInsights.anomalies.length > 0"
                                class="ai-insight-card ai-card-anomaly"
                                :class="aiInsights.anomalies[0].severity === 'high' ? 'anomaly-high' : 'anomaly-medium'">
                                <div class="ai-insight-header">
                                    <div class="ai-header-chip" :class="aiInsights.anomalies[0].severity === 'high' ? 'error' : 'warning'">
                                        <v-icon icon="mdi-radar" size="14" />
                                        Signal
                                    </div>
                                    <span class="ai-insight-title">Alert</span>
                                </div>
                                <div class="ai-anomaly-message">{{ aiInsights.anomalies[0].message }}</div>
                                <div class="ai-anomaly-badge"
                                    :class="aiInsights.anomalies[0].severity === 'high' ? 'badge-error' : 'badge-warning'">
                                    {{ aiInsights.anomalies[0].severity.toUpperCase() }}
                                </div>
                            </div>

                            <!-- Sales Forecast -->
                            <div v-if="aiInsights.salesForecast" class="ai-insight-card ai-card-forecast">
                                <div class="ai-insight-header">
                                    <div class="ai-header-chip info">
                                        <v-icon icon="mdi-timeline-clock-outline" size="14" />
                                        Projection
                                    </div>
                                    <span class="ai-insight-title">Forecast</span>
                                </div>
                                <div class="ai-forecast-value">{{ formatCurrency(aiInsights.salesForecast.predicted) }}
                                </div>
                                <div class="ai-forecast-period">Expected {{ aiInsights.salesForecast.period }}</div>
                                <div class="ai-forecast-message">{{ aiInsights.salesForecast.message }}</div>
                            </div>
                        </div>

                        <!-- Trend Insights - Compact -->
                        <div v-if="aiInsights.trendInsights && aiInsights.trendInsights.length > 0" class="ai-trends">
                            <div class="ai-trends-label">
                                <v-icon icon="mdi-chart-timeline-variant" size="16" color="info" />
                                <span>Trends</span>
                            </div>
                            <div class="ai-trends-chips">
                                <div v-for="(insight, index) in aiInsights.trendInsights.slice(0, 4)" :key="index"
                                    class="ai-trend-chip"
                                    :class="insight.type === 'positive' ? 'trend-positive' : insight.type === 'negative' ? 'trend-negative' : 'trend-info'">
                                    <v-icon
                                        :icon="insight.type === 'positive' ? 'mdi-arrow-up' : insight.type === 'negative' ? 'mdi-arrow-down' : 'mdi-information'"
                                        size="12" />
                                    <span>{{ insight.message }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recommendations - Compact -->
                        <div v-if="aiInsights.recommendations && aiInsights.recommendations.length > 0"
                            class="ai-recommendations">
                            <div class="ai-recommendations-label">
                                <v-icon icon="mdi-lightbulb-on" size="16" color="warning" />
                                <span>Recommendations</span>
                            </div>
                            <div class="ai-recommendations-list">
                                <div v-for="(rec, index) in aiInsights.recommendations.slice(0, 3)" :key="index"
                                    class="ai-recommendation-item" :class="'priority-' + rec.priority">
                                    <div class="ai-rec-icon" :class="'icon-' + rec.priority">
                                        <v-icon :icon="getRecommendationIcon(rec.priority)" size="14" />
                                    </div>
                                    <div class="ai-rec-content">
                                        <div class="ai-rec-title">{{ rec.title }}</div>
                                        <div class="ai-rec-desc">{{ rec.description }}</div>
                                    </div>
                                    <div class="ai-rec-badge" :class="'badge-' + rec.priority">
                                        {{ rec.priority }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
            aiInsights: {
                salesForecast: null,
                anomalies: [],
                performanceScore: null,
                recommendations: [],
                trendInsights: [],
            },
        };
    },
    computed: {
        allKPICards() {
            return [
                {
                    title: 'Today Sales',
                    value: this.formatCurrency(this.metrics.today_sales),
                    icon: 'mdi-cash-register',
                    iconBg: 'bg-primary-modern',
                    colorClass: 'kpi-primary',
                    trend: this.metrics.sales_growth,
                    subtitle: 'vs Yesterday',
                },
                {
                    title: 'Month Sales',
                    value: this.formatCurrency(this.metrics.month_sales),
                    icon: 'mdi-chart-line',
                    iconBg: 'bg-success-modern',
                    colorClass: 'kpi-success',
                    trend: this.metrics.month_growth,
                    subtitle: 'vs Last Month',
                },
                {
                    title: 'Profit',
                    value: this.formatCurrency(this.metrics.profit),
                    icon: 'mdi-trending-up',
                    iconBg: 'bg-info-modern',
                    colorClass: 'kpi-info',
                    trend: null,
                    subtitle: 'This Month',
                },
                {
                    title: 'Customer Due',
                    value: this.formatCurrency(this.metrics.customer_due),
                    icon: 'mdi-account-alert',
                    iconBg: 'bg-warning-modern',
                    colorClass: 'kpi-warning',
                    trend: null,
                    subtitle: 'Outstanding',
                },
                {
                    title: 'Purchases',
                    value: this.formatCurrency(this.metrics.today_purchases),
                    icon: 'mdi-cart-arrow-down',
                    iconBg: 'bg-info-modern',
                    colorClass: 'kpi-info',
                    trend: null,
                    subtitle: 'Today',
                },
                {
                    title: 'Supplier Due',
                    value: this.formatCurrency(this.metrics.supplier_due),
                    icon: 'mdi-account-outline',
                    iconBg: 'bg-warning-modern',
                    colorClass: 'kpi-warning',
                    trend: null,
                    subtitle: 'Payable',
                },
                {
                    title: 'Products',
                    value: this.metrics.product_count,
                    icon: 'mdi-package-variant',
                    iconBg: 'bg-primary-modern',
                    colorClass: 'kpi-primary',
                    trend: null,
                    subtitle: 'Total Items',
                },
                {
                    title: 'Low Stock',
                    value: this.metrics.low_stock_items,
                    icon: 'mdi-alert',
                    iconBg: 'bg-error-modern',
                    colorClass: 'kpi-error',
                    trend: null,
                    subtitle: 'Need Restock',
                },
            ];
        },
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
                                const formatted = '৳' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return `Sales: ${formatted}`;
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
                                    return '৳' + (value / 1000).toFixed(1) + 'k';
                                }
                                return '৳' + value;
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

                // Generate AI insights after data is loaded
                this.generateAIInsights();
            } catch (error) {
                console.error('Failed to load dashboard', error);
                this.handleApiError(error, 'Failed to load dashboard data');
            } finally {
                this.loading = false;
            }
        },
        generateAIInsights() {
            // Sales Forecast
            this.aiInsights.salesForecast = this.calculateSalesForecast();

            // Anomaly Detection
            this.aiInsights.anomalies = this.detectAnomalies();

            // Performance Score
            this.aiInsights.performanceScore = this.calculatePerformanceScore();

            // Recommendations
            this.aiInsights.recommendations = this.generateRecommendations();

            // Trend Insights
            this.aiInsights.trendInsights = this.analyzeTrends();
        },
        calculateSalesForecast() {
            const trendData = this.chartPeriod === '7'
                ? (this.charts.sales_trend_7 || [])
                : (this.charts.sales_trend_30 || []);

            if (!trendData || trendData.length < 3) {
                return null;
            }

            // Simple linear regression for forecasting
            const sales = trendData.map(item => item.sales || 0);
            const avgSales = sales.reduce((a, b) => a + b, 0) / sales.length;

            // Calculate trend (simple average of last 3 days/weeks)
            const recentSales = sales.slice(-3);
            const recentAvg = recentSales.reduce((a, b) => a + b, 0) / recentSales.length;
            const trend = recentAvg > avgSales ? 'increasing' : 'decreasing';

            // Predict next period (simple projection)
            const growthRate = recentAvg > 0 ? (recentAvg - avgSales) / avgSales : 0;
            const predicted = this.metrics.month_sales * (1 + growthRate * 0.1); // Conservative projection

            const period = this.chartPeriod === '7' ? 'next week' : 'next month';

            return {
                predicted: Math.max(0, predicted),
                period: period,
                trend: trend,
                message: trend === 'increasing'
                    ? 'Sales are trending upward. Expected growth continues.'
                    : 'Sales are declining. Consider promotional strategies.',
            };
        },
        detectAnomalies() {
            const anomalies = [];

            // Check for sudden sales drop
            if (this.metrics.sales_growth < -20) {
                anomalies.push({
                    severity: 'high',
                    message: `Sales dropped ${Math.abs(this.metrics.sales_growth).toFixed(1)}% compared to yesterday. Immediate attention required.`,
                    type: 'sales_drop',
                });
            } else if (this.metrics.sales_growth < -10) {
                anomalies.push({
                    severity: 'medium',
                    message: `Sales decreased ${Math.abs(this.metrics.sales_growth).toFixed(1)}% compared to yesterday. Monitor closely.`,
                    type: 'sales_drop',
                });
            }

            // Check for low stock items
            if (this.metrics.low_stock_items > 5) {
                anomalies.push({
                    severity: 'high',
                    message: `${this.metrics.low_stock_items} products are running low on stock. Restock needed.`,
                    type: 'low_stock',
                });
            } else if (this.metrics.low_stock_items > 0) {
                anomalies.push({
                    severity: 'medium',
                    message: `${this.metrics.low_stock_items} product(s) need restocking.`,
                    type: 'low_stock',
                });
            }

            // Check for high customer due
            if (this.metrics.customer_due > this.metrics.month_sales * 0.3) {
                anomalies.push({
                    severity: 'medium',
                    message: `Customer dues (${this.formatCurrency(this.metrics.customer_due)}) are high. Follow up on payments.`,
                    type: 'high_due',
                });
            }

            // Check for negative profit
            if (this.metrics.profit < 0) {
                anomalies.push({
                    severity: 'high',
                    message: 'Negative profit detected this month. Review pricing and costs.',
                    type: 'negative_profit',
                });
            }

            return anomalies;
        },
        calculatePerformanceScore() {
            let score = 100;
            const factors = [];

            // Sales growth factor
            if (this.metrics.sales_growth > 0) {
                score += Math.min(10, this.metrics.sales_growth / 2);
                factors.push('positive');
            } else {
                score -= Math.min(15, Math.abs(this.metrics.sales_growth));
                factors.push('negative');
            }

            // Month growth factor
            if (this.metrics.month_growth > 0) {
                score += Math.min(10, this.metrics.month_growth / 2);
            } else {
                score -= Math.min(15, Math.abs(this.metrics.month_growth));
            }

            // Low stock penalty
            score -= this.metrics.low_stock_items * 2;

            // Profit factor
            if (this.metrics.profit > 0) {
                const profitMargin = (this.metrics.profit / this.metrics.month_sales) * 100;
                if (profitMargin > 20) {
                    score += 10;
                } else if (profitMargin > 10) {
                    score += 5;
                }
            } else {
                score -= 20;
            }

            // Due management
            const dueRatio = this.metrics.customer_due / (this.metrics.month_sales || 1);
            if (dueRatio > 0.3) {
                score -= 10;
            } else if (dueRatio < 0.1) {
                score += 5;
            }

            score = Math.max(0, Math.min(100, score));

            let label = 'Excellent';
            let description = 'Your business is performing exceptionally well.';

            if (score < 50) {
                label = 'Needs Improvement';
                description = 'Several areas need attention to improve performance.';
            } else if (score < 70) {
                label = 'Good';
                description = 'Business is performing well with room for optimization.';
            } else if (score < 85) {
                label = 'Very Good';
                description = 'Strong performance with minor areas to enhance.';
            }

            return {
                score: Math.round(score),
                label: label,
                description: description,
            };
        },
        generateRecommendations() {
            const recommendations = [];

            // Sales recommendations
            if (this.metrics.sales_growth < -10) {
                recommendations.push({
                    title: 'Boost Sales Performance',
                    description: 'Sales are declining. Consider running promotions, improving marketing, or reviewing pricing strategy.',
                    priority: 'high',
                    category: 'sales',
                });
            }

            // Stock recommendations
            if (this.lowStockItems.length > 0) {
                const topLowStock = this.lowStockItems.slice(0, 3).map(item => item.name).join(', ');
                recommendations.push({
                    title: 'Restock Low Inventory Items',
                    description: `Priority items: ${topLowStock}. Reorder to prevent stockouts.`,
                    priority: this.metrics.low_stock_items > 5 ? 'high' : 'medium',
                    category: 'inventory',
                });
            }

            // Profit recommendations
            if (this.metrics.profit < this.metrics.month_sales * 0.1) {
                recommendations.push({
                    title: 'Optimize Profit Margins',
                    description: 'Profit margins are low. Review product pricing, negotiate better supplier rates, or reduce operational costs.',
                    priority: 'high',
                    category: 'profit',
                });
            }

            // Customer due recommendations
            if (this.metrics.customer_due > this.metrics.month_sales * 0.2) {
                recommendations.push({
                    title: 'Collect Outstanding Payments',
                    description: `Customer dues of ${this.formatCurrency(this.metrics.customer_due)} need attention. Follow up with customers.`,
                    priority: 'medium',
                    category: 'finance',
                });
            }

            // Top product recommendations
            if (this.topProducts.length > 0 && this.topProducts[0].sales > 0) {
                recommendations.push({
                    title: 'Leverage Top Performing Products',
                    description: `${this.topProducts[0].name} is your best seller. Consider increasing inventory and promoting similar products.`,
                    priority: 'low',
                    category: 'marketing',
                });
            }

            // Growth recommendations
            if (this.metrics.month_growth > 10 && this.metrics.sales_growth > 5) {
                recommendations.push({
                    title: 'Capitalize on Growth Momentum',
                    description: 'Strong growth detected. Consider expanding inventory, hiring staff, or investing in marketing to sustain growth.',
                    priority: 'low',
                    category: 'growth',
                });
            }

            return recommendations;
        },
        analyzeTrends() {
            const insights = [];

            // Sales trend
            if (this.metrics.sales_growth > 10) {
                insights.push({
                    type: 'positive',
                    message: `Sales up ${this.metrics.sales_growth.toFixed(1)}% today`,
                });
            } else if (this.metrics.sales_growth < -10) {
                insights.push({
                    type: 'negative',
                    message: `Sales down ${Math.abs(this.metrics.sales_growth).toFixed(1)}% today`,
                });
            }

            // Month growth
            if (this.metrics.month_growth > 15) {
                insights.push({
                    type: 'positive',
                    message: `Month-over-month growth: ${this.metrics.month_growth.toFixed(1)}%`,
                });
            } else if (this.metrics.month_growth < -10) {
                insights.push({
                    type: 'negative',
                    message: `Month-over-month decline: ${Math.abs(this.metrics.month_growth).toFixed(1)}%`,
                });
            }

            // Profit trend
            if (this.metrics.profit > 0) {
                const profitMargin = (this.metrics.profit / this.metrics.month_sales) * 100;
                if (profitMargin > 20) {
                    insights.push({
                        type: 'positive',
                        message: `Strong profit margin: ${profitMargin.toFixed(1)}%`,
                    });
                } else if (profitMargin < 5) {
                    insights.push({
                        type: 'negative',
                        message: `Low profit margin: ${profitMargin.toFixed(1)}%`,
                    });
                }
            }

            // Customer activity
            if (this.topCustomers.length > 0) {
                const avgOrderValue = this.topCustomers.reduce((sum, c) => sum + (c.sales / c.orders), 0) / this.topCustomers.length;
                insights.push({
                    type: 'info',
                    message: `Average order value: ${this.formatCurrency(avgOrderValue)}`,
                });
            }

            return insights;
        },
        getPerformanceColor(score) {
            if (score >= 85) return 'success';
            if (score >= 70) return 'info';
            if (score >= 50) return 'warning';
            return 'error';
        },
        getRecommendationColor(priority) {
            const colorMap = {
                'high': 'error',
                'medium': 'warning',
                'low': 'info',
            };
            return colorMap[priority] || 'info';
        },
        getRecommendationIcon(priority) {
            const iconMap = {
                'high': 'mdi-alert',
                'medium': 'mdi-information',
                'low': 'mdi-lightbulb',
            };
            return iconMap[priority] || 'mdi-information';
        },
        formatCurrency(value) {
            const number = Number(value) || 0;
            // Format with BDT symbol (৳) and comma separators
            return '৳' + number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

/* Ultra Compact Modern KPI Cards */
.kpi-cards-row .v-col {
    display: flex;
}

.kpi-card-ultra {
    border-radius: 14px !important;
    border: 1px solid rgba(var(--v-border-color), 0.08);
    background: rgba(var(--v-theme-surface), 0.6) !important;
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 120px;
}

.kpi-card-ultra::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, currentColor, transparent);
    opacity: 0;
    transition: opacity 0.35s;
    z-index: 2;
}

.kpi-card-ultra::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 14px;
    padding: 1px;
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.3), transparent, transparent);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.35s;
}

.kpi-card-ultra:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04) !important;
    border-color: rgba(var(--v-border-color), 0.2);
    background: rgba(var(--v-theme-surface), 0.95) !important;
}

.kpi-card-ultra:hover::before,
.kpi-card-ultra:hover::after {
    opacity: 1;
}

.kpi-card-ultra.kpi-primary::before {
    color: rgb(var(--v-theme-primary));
}

.kpi-card-ultra.kpi-success::before {
    color: rgb(var(--v-theme-success));
}

.kpi-card-ultra.kpi-info::before {
    color: rgb(var(--v-theme-info));
}

.kpi-card-ultra.kpi-warning::before {
    color: rgb(var(--v-theme-warning));
}

.kpi-card-ultra.kpi-error::before {
    color: rgb(var(--v-theme-error));
}

.kpi-ultra-wrapper {
    padding: 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    position: relative;
    z-index: 1;
    flex: 1;
    width: 100%;
}

.kpi-icon-ultra {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.kpi-card-ultra:hover .kpi-icon-ultra {
    transform: scale(1.1) rotate(5deg);
}

.bg-primary-modern {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
}

.bg-success-modern {
    background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
}

.bg-info-modern {
    background: linear-gradient(135deg, #0288d1 0%, #0277bd 100%);
}

.bg-warning-modern {
    background: linear-gradient(135deg, #f57c00 0%, #ef6c00 100%);
}

.bg-error-modern {
    background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%);
}

.kpi-ultra-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    width: 100%;
}

.kpi-ultra-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
    gap: 4px;
    min-height: 20px;
}

.kpi-ultra-title {
    font-size: 0.7rem;
    font-weight: 600;
    color: rgba(var(--v-theme-on-surface), 0.65);
    line-height: 1.2;
    flex: 1;
    letter-spacing: 0.01em;
}

.kpi-trend-indicator {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 6px;
    line-height: 1;
    transition: all 0.2s;
}

.kpi-trend-indicator.trend-up {
    color: rgb(var(--v-theme-success));
    background: rgba(var(--v-theme-success), 0.1);
}

.kpi-trend-indicator.trend-down {
    color: rgb(var(--v-theme-error));
    background: rgba(var(--v-theme-error), 0.1);
}

.kpi-ultra-value {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--v-theme-on-surface));
    line-height: 1.1;
    margin-bottom: 3px;
    word-break: break-word;
    letter-spacing: -0.02em;
    min-height: 24px;
    display: flex;
    align-items: center;
}

.kpi-ultra-subtitle-wrapper {
    min-height: 16px;
    display: flex;
    align-items: flex-start;
}

.kpi-ultra-subtitle {
    font-size: 0.65rem;
    color: rgba(var(--v-theme-on-surface), 0.55);
    line-height: 1.2;
    font-weight: 500;
}

.kpi-trend-placeholder,
.kpi-ultra-subtitle-placeholder {
    visibility: hidden;
    height: 0;
    width: 0;
}

.kpi-glow-effect {
    position: absolute;
    inset: -2px;
    border-radius: 14px;
    opacity: 0;
    transition: opacity 0.35s;
    pointer-events: none;
    z-index: 0;
}

.kpi-card-ultra:hover .kpi-glow-effect {
    opacity: 0.6;
}

.kpi-glow-effect.kpi-primary {
    background: radial-gradient(circle at 50% 0%, rgba(25, 118, 210, 0.4), transparent 70%);
    filter: blur(8px);
}

.kpi-glow-effect.kpi-success {
    background: radial-gradient(circle at 50% 0%, rgba(56, 142, 60, 0.4), transparent 70%);
    filter: blur(8px);
}

.kpi-glow-effect.kpi-info {
    background: radial-gradient(circle at 50% 0%, rgba(2, 136, 209, 0.4), transparent 70%);
    filter: blur(8px);
}

.kpi-glow-effect.kpi-warning {
    background: radial-gradient(circle at 50% 0%, rgba(245, 124, 0, 0.4), transparent 70%);
    filter: blur(8px);
}

.kpi-glow-effect.kpi-error {
    background: radial-gradient(circle at 50% 0%, rgba(211, 47, 47, 0.4), transparent 70%);
    filter: blur(8px);
}

/* Modern Compact KPI Cards (Legacy) */
.kpi-card-modern {
    border-radius: 12px !important;
    border: 1px solid rgba(var(--v-border-color), 0.12);
    background: rgb(var(--v-theme-surface)) !important;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
}

/* Legacy styles for backward compatibility */
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

/* Modern AI Insights */
.ai-insights-modern {
    border-radius: 16px !important;
    border: 1px solid rgba(var(--v-border-color), 0.08);
    background: rgba(var(--v-theme-surface), 0.6) !important;
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
}

.ai-header-modern {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(var(--v-border-color), 0.08);
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.04) 0%, transparent 100%);
}

.ai-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.15), rgba(25, 118, 210, 0.05));
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-title {
    font-size: 1rem;
    font-weight: 700;
    color: rgb(var(--v-theme-on-surface));
    line-height: 1.2;
}

.ai-subtitle {
    font-size: 0.7rem;
    color: rgba(var(--v-theme-on-surface), 0.6);
    margin-top: 2px;
}

.ai-badge {
    font-weight: 600;
    letter-spacing: 0.5px;
}

.ai-loading {
    padding: 48px 20px;
    text-align: center;
}

.ai-loading-text {
    font-size: 0.75rem;
    color: rgba(var(--v-theme-on-surface), 0.6);
    margin-top: 12px;
}

.ai-content {
    padding: 20px;
}

.ai-meta-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}

.ai-meta-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(var(--v-border-color), 0.1);
    background: linear-gradient(140deg, rgba(var(--v-theme-surface), 0.9), rgba(var(--v-theme-surface), 0.6));
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.ai-meta-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
}

.ai-meta-icon.success {
    background: linear-gradient(135deg, rgba(var(--v-theme-success), 0.9), rgba(var(--v-theme-success), 0.7));
}

.ai-meta-icon.warning {
    background: linear-gradient(135deg, rgba(var(--v-theme-warning), 0.95), rgba(var(--v-theme-warning), 0.75));
}

.ai-meta-icon.info {
    background: linear-gradient(135deg, rgba(var(--v-theme-info), 0.9), rgba(var(--v-theme-info), 0.7));
}

.ai-meta-icon.primary {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.95), rgba(var(--v-theme-primary), 0.75));
}

.ai-meta-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.ai-meta-label {
    font-size: 0.65rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: rgba(var(--v-theme-on-surface), 0.65);
}

.ai-meta-value {
    font-size: 1rem;
    font-weight: 800;
    color: rgb(var(--v-theme-on-surface));
    letter-spacing: -0.01em;
}

.ai-meta-note {
    font-size: 0.65rem;
    color: rgba(var(--v-theme-on-surface), 0.55);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.meta-alert {
    border-color: rgba(var(--v-theme-error), 0.25);
    background: linear-gradient(140deg, rgba(var(--v-theme-error), 0.14), rgba(var(--v-theme-surface), 0.75));
}

.ai-insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.ai-compact-grid {
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 14px;
}

.ai-insight-card {
    border-radius: 12px;
    border: 1px solid rgba(var(--v-border-color), 0.1);
    background: linear-gradient(180deg, rgba(var(--v-theme-surface), 0.9), rgba(var(--v-theme-surface), 0.7));
    padding: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.ai-insight-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, currentColor, transparent);
    opacity: 0;
    transition: opacity 0.3s;
}

.ai-insight-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    border-color: rgba(var(--v-border-color), 0.2);
}

.ai-insight-card:hover::before {
    opacity: 1;
}

.ai-card-performance::before {
    color: rgb(var(--v-theme-success));
}

.ai-card-anomaly.anomaly-high::before {
    color: rgb(var(--v-theme-error));
}

.ai-card-anomaly.anomaly-medium::before {
    color: rgb(var(--v-theme-warning));
}

.ai-card-forecast::before {
    color: rgb(var(--v-theme-info));
}

.ai-insight-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.ai-header-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 0.65rem;
    letter-spacing: 0.02em;
    background: rgba(var(--v-border-color), 0.08);
    color: rgba(var(--v-theme-on-surface), 0.8);
    text-transform: uppercase;
    font-weight: 700;
}

.ai-header-chip.success {
    background: rgba(var(--v-theme-success), 0.15);
    color: rgb(var(--v-theme-success));
}

.ai-header-chip.info {
    background: rgba(var(--v-theme-info), 0.15);
    color: rgb(var(--v-theme-info));
}

.ai-header-chip.warning {
    background: rgba(var(--v-theme-warning), 0.15);
    color: rgb(var(--v-theme-warning));
}

.ai-header-chip.error {
    background: rgba(var(--v-theme-error), 0.15);
    color: rgb(var(--v-theme-error));
}

.ai-insight-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(var(--v-theme-on-surface), 0.8);
}

.ai-performance-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-score-circle {
    flex-shrink: 0;
}

.ai-score-value {
    font-size: 0.9rem;
    font-weight: 700;
}

.ai-score-info {
    flex: 1;
}

.ai-score-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: rgb(var(--v-theme-on-surface));
    margin-bottom: 4px;
}

.ai-score-desc {
    font-size: 0.7rem;
    color: rgba(var(--v-theme-on-surface), 0.6);
    line-height: 1.3;
}

.ai-score-tags {
    display: flex;
    gap: 6px;
    margin-top: 8px;
    flex-wrap: wrap;
}

.ai-tag {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    background: rgba(var(--v-border-color), 0.08);
    color: rgba(var(--v-theme-on-surface), 0.65);
}

.ai-tag.subtle {
    background: rgba(var(--v-theme-primary), 0.12);
    color: rgb(var(--v-theme-primary));
}

.ai-anomaly-message {
    font-size: 0.75rem;
    color: rgba(var(--v-theme-on-surface), 0.8);
    margin-bottom: 8px;
    line-height: 1.4;
}

.ai-anomaly-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.badge-error {
    background: rgba(var(--v-theme-error), 0.15);
    color: rgb(var(--v-theme-error));
}

.badge-warning {
    background: rgba(var(--v-theme-warning), 0.15);
    color: rgb(var(--v-theme-warning));
}

.ai-forecast-value {
    font-size: 1.1rem;
    font-weight: 800;
    color: rgb(var(--v-theme-info));
    margin-bottom: 4px;
}

.ai-forecast-period {
    font-size: 0.7rem;
    color: rgba(var(--v-theme-on-surface), 0.6);
    margin-bottom: 8px;
}

.ai-forecast-message {
    font-size: 0.75rem;
    color: rgba(var(--v-theme-on-surface), 0.7);
    line-height: 1.4;
}

.ai-trends,
.ai-recommendations {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(var(--v-border-color), 0.08);
}

.ai-trends-label,
.ai-recommendations-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(var(--v-theme-on-surface), 0.8);
    margin-bottom: 12px;
}

.ai-trends-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ai-trend-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
    transition: all 0.2s;
}

.trend-positive {
    background: rgba(var(--v-theme-success), 0.1);
    color: rgb(var(--v-theme-success));
}

.trend-negative {
    background: rgba(var(--v-theme-error), 0.1);
    color: rgb(var(--v-theme-error));
}

.trend-info {
    background: rgba(var(--v-theme-info), 0.1);
    color: rgb(var(--v-theme-info));
}

.ai-trend-chip:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.ai-recommendations-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.ai-recommendation-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid rgba(var(--v-border-color), 0.1);
    background: rgba(var(--v-theme-surface), 0.5);
    transition: all 0.2s;
}

.ai-recommendation-item:hover {
    background: rgba(var(--v-theme-surface), 0.8);
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.ai-rec-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icon-high {
    background: rgba(var(--v-theme-error), 0.15);
    color: rgb(var(--v-theme-error));
}

.icon-medium {
    background: rgba(var(--v-theme-warning), 0.15);
    color: rgb(var(--v-theme-warning));
}

.icon-low {
    background: rgba(var(--v-theme-info), 0.15);
    color: rgb(var(--v-theme-info));
}

.ai-rec-content {
    flex: 1;
    min-width: 0;
}

.ai-rec-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: rgb(var(--v-theme-on-surface));
    margin-bottom: 2px;
}

.ai-rec-desc {
    font-size: 0.7rem;
    color: rgba(var(--v-theme-on-surface), 0.6);
    line-height: 1.3;
}

.ai-rec-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-high {
    background: rgba(var(--v-theme-error), 0.15);
    color: rgb(var(--v-theme-error));
}

.badge-medium {
    background: rgba(var(--v-theme-warning), 0.15);
    color: rgb(var(--v-theme-warning));
}

.badge-low {
    background: rgba(var(--v-theme-info), 0.15);
    color: rgb(var(--v-theme-info));
}

/* Legacy styles */
.ai-insights-card {
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.05) 0%, rgba(25, 118, 210, 0.02) 100%);
    border: 1px solid rgba(25, 118, 210, 0.1);
}

.insight-card {
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}

.insight-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}
</style>
