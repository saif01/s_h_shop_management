<template>
    <v-dialog v-model="dialogModel" max-width="800" scrollable>
        <v-card v-if="customer">
            <v-card-title>
                Customer Details
            </v-card-title>
            <v-card-text>
                <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                    <v-tab value="basic">Basic Information</v-tab>
                    <v-tab value="contact">Contact Details</v-tab>
                    <v-tab value="financial">Financial</v-tab>
                    <v-tab value="audit">System Information</v-tab>
                </v-tabs>

                <v-window v-model="activeTab">
                    <!-- Basic Information Tab -->
                    <v-window-item value="basic">
                        <div class="pa-4">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Name:</strong>
                                        <div class="mt-1">{{ customer.name || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Code:</strong>
                                        <div class="mt-1">{{ customer.code || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Company Name:</strong>
                                        <div class="mt-1">{{ customer.company_name || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Tax ID:</strong>
                                        <div class="mt-1">{{ customer.tax_id || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mb-4">
                                <strong class="text-grey-darken-1">Status:</strong>
                                <div class="mt-1">
                                    <v-chip :color="customer.is_active ? 'success' : 'error'" size="small">
                                        {{ customer.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </div>
                            </div>
                        </div>
                    </v-window-item>

                    <!-- Contact Details Tab -->
                    <v-window-item value="contact">
                        <div class="pa-4">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Email:</strong>
                                        <div class="mt-1">{{ customer.email || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Phone:</strong>
                                        <div class="mt-1">{{ customer.phone || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Mobile:</strong>
                                        <div class="mt-1">{{ customer.mobile || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mb-4">
                                <strong class="text-grey-darken-1">Address:</strong>
                                <div class="mt-1">{{ customer.address || '-' }}</div>
                            </div>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">City:</strong>
                                        <div class="mt-1">{{ customer.city || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">State:</strong>
                                        <div class="mt-1">{{ customer.state || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Country:</strong>
                                        <div class="mt-1">{{ customer.country || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Postal Code:</strong>
                                        <div class="mt-1">{{ customer.postal_code || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>
                        </div>
                    </v-window-item>

                    <!-- Financial Tab -->
                    <v-window-item value="financial">
                        <div class="pa-4">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Opening Balance:</strong>
                                        <div class="mt-1">{{ formatCurrency(customer.opening_balance) }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Current Balance:</strong>
                                        <div class="mt-1">
                                            <v-chip :color="customer.current_balance >= 0 ? 'success' : 'error'"
                                                size="small">
                                                {{ formatCurrency(customer.current_balance) }}
                                            </v-chip>
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mb-4">
                                <strong class="text-grey-darken-1">Notes:</strong>
                                <div class="mt-1">{{ customer.notes || '-' }}</div>
                            </div>
                        </div>
                    </v-window-item>

                    <!-- System Information Tab -->
                    <v-window-item value="audit">
                        <div class="pa-4">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Created By:</strong>
                                        <div class="mt-1">
                                            {{ customer.created_by?.name || customer.created_by?.email || '-' }}
                                        </div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Created At:</strong>
                                        <div class="mt-1">{{ formatDateShort(customer.created_at) }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Updated By:</strong>
                                        <div class="mt-1">
                                            {{ customer.updated_by?.name || customer.updated_by?.email || '-' }}
                                        </div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Updated At:</strong>
                                        <div class="mt-1">{{ formatDateShort(customer.updated_at) }}</div>
                                    </div>
                                </v-col>
                            </v-row>
                        </div>
                    </v-window-item>
                </v-window>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="dialogModel = false" variant="text">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import commonMixin from '../../../../mixins/commonMixin';

export default {
    name: 'CustomerViewDialog',
    mixins: [commonMixin],
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        customer: {
            type: Object,
            default: null
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            activeTab: 'basic'
        };
    },
    computed: {
        dialogModel: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },
    watch: {
        modelValue(newValue) {
            if (newValue) {
                this.activeTab = 'basic';
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
        }
    }
};
</script>

<style scoped>
/* Add any dialog-specific styles here */
</style>
