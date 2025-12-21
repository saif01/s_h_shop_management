<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="500px">
        <v-card v-if="purchase">
            <v-card-title>Add Payment</v-card-title>
            <v-card-text>
                <v-form ref="paymentForm">
                    <v-text-field :model-value="purchase.supplier?.name" label="Supplier" readonly
                        variant="outlined" density="compact"></v-text-field>
                    <v-text-field :model-value="purchase.invoice_number" label="Invoice Number" readonly
                        variant="outlined" density="compact"></v-text-field>
                    <v-text-field :model-value="formatCurrency(purchase.total_amount)" label="Total Amount"
                        readonly variant="outlined" density="compact"></v-text-field>
                    <v-text-field :model-value="formatCurrency(purchase.paid_amount)" label="Paid Amount"
                        readonly variant="outlined" density="compact"></v-text-field>
                    <v-text-field :model-value="formatCurrency(purchase.balance_amount)"
                        label="Balance (Due)" readonly variant="outlined" density="compact"
                        :color="purchase.balance_amount > 0 ? 'warning' : 'success'"></v-text-field>
                    <v-text-field v-model.number="localPaymentAmount" type="number" label="Payment Amount *"
                        variant="outlined" density="compact" :rules="[rules.required, rules.maxDue]"
                        hint="Enter the amount to pay" persistent-hint></v-text-field>
                    <v-select v-model="localPaymentMethod" :items="paymentMethods" label="Payment Method *"
                        variant="outlined" density="compact" :rules="[rules.required]"></v-select>
                    <DatePickerVuetifyInput field-label="Payment Date" required="true" variant="outlined"
                        :initial-date="localPaymentDate" @trigerInputValue="(val) => { localPaymentDate = val; }" />
                    <v-textarea v-model="localPaymentNotes" label="Notes" variant="outlined" density="compact"
                        rows="2"></v-textarea>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="handleCancel" variant="text">Cancel</v-btn>
                <v-btn color="primary" :loading="saving" @click="handleSave" variant="flat">Save Payment</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import DatePickerVuetifyInput from '@/components/common/DatePickerVuetifyInput.vue';

export default {
    name: 'PaymentDialog',
    components: {
        DatePickerVuetifyInput
    },
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        purchase: {
            type: Object,
            default: null
        },
        saving: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue', 'save', 'cancel'],
    data() {
        return {
            localPaymentAmount: 0,
            localPaymentMethod: 'cash',
            localPaymentDate: new Date().toISOString().split('T')[0],
            localPaymentNotes: '',
            paymentMethods: [
                { title: 'Cash', value: 'cash' },
                { title: 'Card', value: 'card' },
                { title: 'Mobile Banking', value: 'mobile_banking' },
                { title: 'Bank Transfer', value: 'bank_transfer' },
                { title: 'Cheque', value: 'cheque' },
            ]
        };
    },
    computed: {
        rules() {
            return {
                required: v => !!v || 'This field is required',
                maxDue: v => {
                    if (!this.purchase) return true;
                    const due = parseFloat(this.purchase.balance_amount || 0);
                    return v <= due || `Cannot exceed balance amount (${this.formatCurrency(due)})`;
                }
            };
        }
    },
    watch: {
        modelValue(newVal) {
            if (newVal && this.purchase) {
                this.localPaymentAmount = parseFloat(this.purchase.balance_amount || 0);
                this.localPaymentDate = new Date().toISOString().split('T')[0];
                this.localPaymentMethod = 'cash';
                this.localPaymentNotes = '';
            }
        },
        purchase(newVal) {
            if (newVal && this.modelValue) {
                this.localPaymentAmount = parseFloat(newVal.balance_amount || 0);
                this.localPaymentDate = new Date().toISOString().split('T')[0];
                this.localPaymentMethod = 'cash';
                this.localPaymentNotes = '';
            }
        }
    },
    methods: {
        formatCurrency(value) {
            if (value === null || value === undefined) return '৳0.00';
            return '৳' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },
        async handleSave() {
            if (this.$refs.paymentForm) {
                const { valid } = await this.$refs.paymentForm.validate();
                if (!valid) return;
            }

            this.$emit('save', {
                amount: this.localPaymentAmount,
                payment_method: this.localPaymentMethod,
                payment_date: this.localPaymentDate,
                notes: this.localPaymentNotes
            });
        },
        handleCancel() {
            if (this.$refs.paymentForm) {
                this.$refs.paymentForm.resetValidation();
            }
            this.$emit('cancel');
        }
    }
};
</script>

