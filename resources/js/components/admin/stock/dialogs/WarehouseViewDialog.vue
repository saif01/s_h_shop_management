<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="700px"
        persistent scrollable>
        <v-card class="warehouse-view-dialog">
            <!-- Header -->
            <v-card-title class="dialog-header d-flex justify-space-between align-center pa-3">
                <div class="d-flex align-center gap-2">
                    <v-icon color="primary">mdi-warehouse</v-icon>
                    <span class="text-h6 font-weight-medium">Warehouse Details</span>
                </div>
                <v-btn icon="mdi-close" variant="text" size="small" @click="close" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <div v-if="loading" class="text-center py-8">
                    <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                    <div class="mt-3 text-body-2">Loading warehouse details...</div>
                </div>
                <div v-else-if="!displayWarehouse" class="text-center py-8">
                    <v-alert type="error" variant="tonal" class="ma-3" density="compact">
                        Failed to load warehouse details
                    </v-alert>
                </div>
                <div v-else>
                    <!-- Basic Information Section -->
                    <div class="text-subtitle-2 font-weight-medium mb-2 d-flex align-center">
                        <v-icon size="small" class="mr-1">mdi-information</v-icon>
                        Basic Information
                    </div>
                    <v-card variant="outlined" class="mb-3">
                        <v-card-text class="pa-3">
                            <v-row dense class="ma-0">
                                <v-col cols="12" sm="6" md="4" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">ID:</span>
                                        <span class="text-body-2 font-weight-medium ml-1">{{ displayWarehouse.id || '-' }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" md="4" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Status:</span>
                                        <v-chip size="x-small" :color="displayWarehouse.is_active ? 'success' : 'error'"
                                            variant="tonal" class="ml-1">
                                            {{ displayWarehouse.is_active ? 'Active' : 'Inactive' }}
                                        </v-chip>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" md="4" class="pa-2" v-if="displayWarehouse.code">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Code:</span>
                                        <span class="text-body-2 font-weight-medium ml-1">{{ displayWarehouse.code }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Name:</span>
                                        <span class="text-body-2 font-weight-medium ml-1">{{ displayWarehouse.name || '-' }}</span>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Contact Information Section -->
                    <div class="text-subtitle-2 font-weight-medium mb-2 d-flex align-center">
                        <v-icon size="small" class="mr-1">mdi-phone</v-icon>
                        Contact Information
                    </div>
                    <v-card variant="outlined" class="mb-3">
                        <v-card-text class="pa-3">
                            <v-row dense class="ma-0">
                                <v-col cols="12" sm="6" class="pa-2" v-if="displayWarehouse.phone">
                                    <div class="info-item-compact">
                                        <v-icon size="small" color="primary" class="mr-1">mdi-phone</v-icon>
                                        <span class="text-caption text-grey">Phone:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.phone }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2" v-if="displayWarehouse.email">
                                    <div class="info-item-compact">
                                        <v-icon size="small" color="primary" class="mr-1">mdi-email</v-icon>
                                        <span class="text-caption text-grey">Email:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.email }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" class="pa-2" v-if="!displayWarehouse.phone && !displayWarehouse.email">
                                    <div class="text-body-2 text-grey">No contact information available</div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Address Information Section -->
                    <div class="text-subtitle-2 font-weight-medium mb-2 d-flex align-center">
                        <v-icon size="small" class="mr-1">mdi-map-marker</v-icon>
                        Address Information
                    </div>
                    <v-card variant="outlined" class="mb-3">
                        <v-card-text class="pa-3">
                            <v-row dense class="ma-0">
                                <v-col cols="12" class="pa-2" v-if="displayWarehouse.address">
                                    <div class="info-item-compact">
                                        <v-icon size="small" color="primary" class="mr-1">mdi-home</v-icon>
                                        <span class="text-caption text-grey">Address:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.address }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" md="4" class="pa-2" v-if="displayWarehouse.city">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">City:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.city }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" md="4" class="pa-2" v-if="displayWarehouse.state">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">State/Province:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.state }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" md="4" class="pa-2" v-if="displayWarehouse.postal_code">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Postal Code:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.postal_code }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" class="pa-2" v-if="displayWarehouse.country">
                                    <div class="info-item-compact">
                                        <v-icon size="small" color="primary" class="mr-1">mdi-earth</v-icon>
                                        <span class="text-caption text-grey">Country:</span>
                                        <span class="text-body-2 ml-1">{{ displayWarehouse.country }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" class="pa-2" v-if="!hasAddressInfo">
                                    <div class="text-body-2 text-grey">No address information available</div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- System Information Section (if available) -->
                    <template v-if="hasSystemInfo">
                        <v-divider class="my-2" />
                        <div class="text-subtitle-2 font-weight-medium mb-2 d-flex align-center">
                            <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon>
                            System Information
                        </div>
                        <v-card variant="outlined" class="mb-2">
                            <v-card-text class="pa-3">
                                <v-row dense class="ma-0">
                                    <v-col cols="12" sm="6" class="pa-2" v-if="displayWarehouse.created_by">
                                        <div class="info-item-compact">
                                            <span class="text-caption text-grey">Created By:</span>
                                            <span class="text-body-2 ml-1">{{ displayWarehouse.created_by?.name || '-' }}</span>
                                        </div>
                                    </v-col>
                                    <v-col cols="12" sm="6" class="pa-2" v-if="displayWarehouse.created_at">
                                        <div class="info-item-compact">
                                            <span class="text-caption text-grey">Created At:</span>
                                            <span class="text-body-2 ml-1">{{ formatDateShort(displayWarehouse.created_at) || '-' }}</span>
                                        </div>
                                    </v-col>
                                    <v-col cols="12" sm="6" class="pa-2" v-if="displayWarehouse.updated_by">
                                        <div class="info-item-compact">
                                            <span class="text-caption text-grey">Updated By:</span>
                                            <span class="text-body-2 ml-1">{{ displayWarehouse.updated_by?.name || '-' }}</span>
                                        </div>
                                    </v-col>
                                    <v-col cols="12" sm="6" class="pa-2" v-if="displayWarehouse.updated_at">
                                        <div class="info-item-compact">
                                            <span class="text-caption text-grey">Updated At:</span>
                                            <span class="text-body-2 ml-1">{{ formatDateShort(displayWarehouse.updated_at) || '-' }}</span>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </template>
                </div>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-2 justify-end">
                <v-btn variant="text" size="small" @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';
import { formatDateShort } from '@/utils/formatters';

export default {
    name: 'WarehouseViewDialog',
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        warehouse: {
            type: Object,
            default: null
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            loading: false,
            warehouseData: null,
        };
    },
    computed: {
        displayWarehouse() {
            return this.warehouseData || this.warehouse;
        },
        hasAddressInfo() {
            if (!this.displayWarehouse) return false;
            return !!(this.displayWarehouse.address || 
                     this.displayWarehouse.city || 
                     this.displayWarehouse.state || 
                     this.displayWarehouse.postal_code || 
                     this.displayWarehouse.country);
        },
        hasSystemInfo() {
            if (!this.displayWarehouse) return false;
            return !!(this.displayWarehouse.created_at || 
                     this.displayWarehouse.updated_at ||
                     this.displayWarehouse.created_by ||
                     this.displayWarehouse.updated_by);
        },
    },
    watch: {
        modelValue(newVal) {
            if (newVal && this.warehouse && this.warehouse.id) {
                this.loadWarehouseDetails();
            } else {
                this.warehouseData = null;
            }
        },
        warehouse: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal.id && this.modelValue) {
                    this.loadWarehouseDetails();
                } else {
                    this.warehouseData = null;
                }
            },
        },
    },
    methods: {
        async loadWarehouseDetails() {
            if (!this.warehouse?.id) return;

            this.loading = true;
            try {
                const token = localStorage.getItem('admin_token');
                const response = await axios.get(`/api/v1/warehouses/${this.warehouse.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.warehouseData = response.data.warehouse || response.data;
            } catch (error) {
                console.error('Failed to load warehouse details', error);
                this.$toast?.error('Failed to load warehouse details');
            } finally {
                this.loading = false;
            }
        },
        formatDateShort,
        close() {
            this.$emit('update:modelValue', false);
        },
    },
};
</script>

<style scoped>
.warehouse-view-dialog {
    max-height: 90vh;
}

.dialog-header {
    background-color: rgba(0, 0, 0, 0.02);
}

.info-item-compact {
    display: flex;
    align-items: center;
    min-height: 32px;
}

.gap-2 {
    gap: 8px;
}
</style>
