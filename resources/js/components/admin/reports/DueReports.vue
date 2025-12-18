<template>
    <div>
        <v-card variant="outlined" class="mb-4">
            <v-card-title>Due Report Filters</v-card-title>
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" md="4">
                        <v-select v-model="filters.party_type" :items="partyTypes" 
                            label="Party Type" />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-select v-model="filters.party_id" :items="partyOptions" 
                            item-value="id" item-title="name" label="Select Party" clearable />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-switch v-model="filters.overdue_only" label="Overdue Only" />
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
            <v-col cols="12" sm="4">
                <v-card color="error" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Due Amount</div>
                        <div class="text-h5">${{ summary.total_due?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="4">
                <v-card color="warning" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Overdue Amount</div>
                        <div class="text-h5">${{ summary.overdue_amount?.toFixed(2) || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="4">
                <v-card color="info" variant="tonal">
                    <v-card-text>
                        <div class="text-caption">Total Parties</div>
                        <div class="text-h5">{{ summary.total_parties || 0 }}</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Report Table -->
        <v-card variant="outlined">
            <v-data-table :headers="currentHeaders" :items="reportData" :loading="loading" 
                :items-per-page="25">
                <template #item.total_amount="{ item }">
                    ${{ parseFloat(item.total_amount || 0).toFixed(2) }}
                </template>
                <template #item.paid_amount="{ item }">
                    ${{ parseFloat(item.paid_amount || 0).toFixed(2) }}
                </template>
                <template #item.due_amount="{ item }">
                    <v-chip color="error" size="small">
                        ${{ parseFloat(item.due_amount || item.balance_amount || 0).toFixed(2) }}
                    </v-chip>
                </template>
                <template #item.due_date="{ item }">
                    <span :class="isOverdue(item.due_date) ? 'text-error font-weight-bold' : ''">
                        {{ formatDate(item.due_date) }}
                    </span>
                </template>
                <template #item.invoice_date="{ item }">
                    {{ formatDate(item.invoice_date) }}
                </template>
                <template #item.actions="{ item }">
                    <v-btn size="small" color="success" @click="collectPayment(item)">
                        <v-icon>mdi-cash</v-icon> Collect
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Payment Collection Dialog -->
        <v-dialog v-model="paymentDialog" max-width="500px">
            <v-card v-if="selectedItem">
                <v-card-title>Collect Payment</v-card-title>
                <v-card-text>
                    <v-form ref="paymentForm">
                        <v-text-field v-model="selectedItem.party_name" label="Party" readonly />
                        <v-text-field v-model="selectedItem.invoice_number" label="Invoice" readonly />
                        <v-text-field :model-value="parseFloat(selectedItem.due_amount || selectedItem.balance_amount).toFixed(2)" 
                            label="Due Amount" readonly />
                        <v-text-field v-model.number="paymentAmount" type="number" 
                            label="Payment Amount *" :rules="[rules.required, rules.maxDue]" />
                        <v-select v-model="paymentMethod" :items="paymentMethods" 
                            label="Payment Method *" :rules="[rules.required]" />
                        <v-text-field v-model="paymentDate" type="date" 
                            label="Payment Date *" :rules="[rules.required]" />
                        <v-textarea v-model="paymentNotes" label="Notes" rows="2" />
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="paymentDialog = false">Cancel</v-btn>
                    <v-btn color="success" :loading="saving" @click="savePayment">Save Payment</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'DueReports',
    data() {
        return {
            loading: false,
            saving: false,
            filters: {
                party_type: 'customer',
                party_id: null,
                overdue_only: false,
            },
            reportData: [],
            summary: null,
            customers: [],
            suppliers: [],
            paymentDialog: false,
            selectedItem: null,
            paymentAmount: 0,
            paymentMethod: 'cash',
            paymentDate: new Date().toISOString().split('T')[0],
            paymentNotes: '',
            partyTypes: [
                { title: 'Customer Due', value: 'customer' },
                { title: 'Supplier Due', value: 'supplier' },
            ],
            customerHeaders: [
                { title: 'Customer', key: 'party_name' },
                { title: 'Invoice #', key: 'invoice_number' },
                { title: 'Date', key: 'invoice_date' },
                { title: 'Due Date', key: 'due_date' },
                { title: 'Total', key: 'total_amount', align: 'end' },
                { title: 'Paid', key: 'paid_amount', align: 'end' },
                { title: 'Due', key: 'due_amount', align: 'end' },
                { title: 'Actions', key: 'actions', sortable: false },
            ],
            supplierHeaders: [
                { title: 'Supplier', key: 'party_name' },
                { title: 'Invoice #', key: 'invoice_number' },
                { title: 'Date', key: 'invoice_date' },
                { title: 'Due Date', key: 'due_date' },
                { title: 'Total', key: 'total_amount', align: 'end' },
                { title: 'Paid', key: 'paid_amount', align: 'end' },
                { title: 'Due', key: 'due_amount', align: 'end' },
                { title: 'Actions', key: 'actions', sortable: false },
            ],
            paymentMethods: [
                { title: 'Cash', value: 'cash' },
                { title: 'Card', value: 'card' },
                { title: 'Mobile Banking', value: 'mobile_banking' },
                { title: 'Bank Transfer', value: 'bank_transfer' },
                { title: 'Cheque', value: 'cheque' },
            ],
            rules: {
                required: v => !!v || 'Required',
                maxDue: v => {
                    const due = parseFloat(this.selectedItem?.due_amount || this.selectedItem?.balance_amount || 0);
                    return v <= due || `Cannot exceed due amount ($${due.toFixed(2)})`;
                },
            },
        };
    },
    computed: {
        partyOptions() {
            return this.filters.party_type === 'customer' ? this.customers : this.suppliers;
        },
        currentHeaders() {
            return this.filters.party_type === 'customer' ? this.customerHeaders : this.supplierHeaders;
        },
    },
    watch: {
        'filters.party_type'() {
            this.filters.party_id = null;
            this.generateReport();
        },
    },
    mounted() {
        this.fetchParties();
        this.generateReport();
    },
    methods: {
        async fetchParties() {
            try {
                const [customersRes, suppliersRes] = await Promise.all([
                    axios.get('/api/v1/customers'),
                    axios.get('/api/v1/suppliers'),
                ]);
                this.customers = customersRes.data.data || customersRes.data.customers || [];
                this.suppliers = suppliersRes.data.data || suppliersRes.data.suppliers || [];
            } catch (error) {
                console.error('Failed to fetch parties', error);
            }
        },
        async generateReport() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/reports/due', { params: this.filters });
                this.reportData = data.due || [];
                this.summary = data.summary || null;
            } catch (error) {
                console.error('Failed to generate report', error);
                this.$toast?.error('Failed to generate due report');
            } finally {
                this.loading = false;
            }
        },
        async exportExcel() {
            try {
                const response = await axios.get('/api/v1/reports/due/export/excel', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `due_report_${Date.now()}.xlsx`);
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
                const response = await axios.get('/api/v1/reports/due/export/pdf', {
                    params: this.filters,
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `due_report_${Date.now()}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Failed to export PDF', error);
                this.$toast?.error('Failed to export report');
            }
        },
        collectPayment(item) {
            this.selectedItem = item;
            this.paymentAmount = parseFloat(item.due_amount || item.balance_amount);
            this.paymentDialog = true;
        },
        async savePayment() {
            const valid = await this.$refs.paymentForm.validate();
            if (!valid.valid) return;

            this.saving = true;
            try {
                await axios.post('/api/v1/payments', {
                    payment_type: this.filters.party_type === 'customer' ? 'sale' : 'purchase',
                    party_type: this.filters.party_type,
                    party_id: this.selectedItem.party_id || this.selectedItem.customer_id || this.selectedItem.supplier_id,
                    reference_type: this.filters.party_type === 'customer' ? 'sale' : 'purchase',
                    reference_id: this.selectedItem.id,
                    reference_number: this.selectedItem.invoice_number,
                    payment_date: this.paymentDate,
                    amount: this.paymentAmount,
                    payment_method: this.paymentMethod,
                    notes: this.paymentNotes,
                });
                this.$toast?.success('Payment recorded successfully');
                this.paymentDialog = false;
                this.generateReport();
            } catch (error) {
                console.error('Failed to save payment', error);
                this.$toast?.error('Failed to record payment');
            } finally {
                this.saving = false;
            }
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
        },
        isOverdue(dueDate) {
            if (!dueDate) return false;
            return new Date(dueDate) < new Date();
        },
    },
};
</script>

