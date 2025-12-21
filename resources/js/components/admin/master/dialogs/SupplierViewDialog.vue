<template>
    <v-dialog v-model="dialogModel" max-width="800" scrollable>
        <v-card v-if="supplier">
            <v-card-title>
                Supplier Details
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
                                        <div class="mt-1">{{ supplier.name || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Code:</strong>
                                        <div class="mt-1">{{ supplier.code || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Company Name:</strong>
                                        <div class="mt-1">{{ supplier.company_name || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Tax ID:</strong>
                                        <div class="mt-1">{{ supplier.tax_id || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mb-4">
                                <strong class="text-grey-darken-1">Status:</strong>
                                <div class="mt-1">
                                    <v-chip :color="supplier.is_active ? 'success' : 'error'" size="small">
                                        {{ supplier.is_active ? 'Active' : 'Inactive' }}
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
                                        <div class="mt-1">{{ supplier.email || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Phone:</strong>
                                        <div class="mt-1">{{ supplier.phone || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Mobile:</strong>
                                        <div class="mt-1">{{ supplier.mobile || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mb-4">
                                <strong class="text-grey-darken-1">Address:</strong>
                                <div class="mt-1">{{ supplier.address || '-' }}</div>
                            </div>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">City:</strong>
                                        <div class="mt-1">{{ supplier.city || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">State:</strong>
                                        <div class="mt-1">{{ supplier.state || '-' }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Country:</strong>
                                        <div class="mt-1">{{ supplier.country || '-' }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Postal Code:</strong>
                                        <div class="mt-1">{{ supplier.postal_code || '-' }}</div>
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
                                        <div class="mt-1">{{ formatCurrency(supplier.opening_balance) }}</div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Current Balance:</strong>
                                        <div class="mt-1">
                                            <v-chip :color="supplier.current_balance >= 0 ? 'success' : 'error'"
                                                size="small">
                                                {{ formatCurrency(supplier.current_balance) }}
                                            </v-chip>
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mb-4">
                                <strong class="text-grey-darken-1">Notes:</strong>
                                <div class="mt-1">{{ supplier.notes || '-' }}</div>
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
                                            {{ supplier.created_by?.name || supplier.created_by?.email || '-' }}
                                        </div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Created At:</strong>
                                        <div class="mt-1">{{ formatDateShort(supplier.created_at) }}</div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Updated By:</strong>
                                        <div class="mt-1">
                                            {{ supplier.updated_by?.name || supplier.updated_by?.email || '-' }}
                                        </div>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div class="mb-4">
                                        <strong class="text-grey-darken-1">Updated At:</strong>
                                        <div class="mt-1">{{ formatDateShort(supplier.updated_at) }}</div>
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
import { formatCurrency, formatDateShort } from '@/utils/formatters';

export default {
    name: 'SupplierViewDialog',
    mixins: [commonMixin],
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        supplier: {
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
        formatCurrency,
        formatDateShort
    }
};
</script>

<style scoped>
/* Add any dialog-specific styles here */
</style>
