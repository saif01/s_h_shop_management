<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="500px">
        <v-card v-if="warehouse">
            <v-card-title class="pa-3 d-flex justify-space-between align-center bg-primary">
                <span class="text-white text-h6">Warehouse Details</span>
                <v-btn icon="mdi-close" variant="text" size="small" class="text-white" 
                    @click="$emit('update:modelValue', false)" />
            </v-card-title>
            <v-card-text class="pa-3">
                <v-list density="compact" class="pa-0">
                    <v-list-item class="px-0">
                        <v-list-item-title class="text-caption font-weight-bold text-grey-darken-1">Name</v-list-item-title>
                        <v-list-item-subtitle class="text-body-2 mt-1">{{ warehouse.name }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item v-if="warehouse.code" class="px-0">
                        <v-list-item-title class="text-caption font-weight-bold text-grey-darken-1">Code</v-list-item-title>
                        <v-list-item-subtitle class="text-body-2 mt-1">{{ warehouse.code }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item v-if="warehouse.phone" class="px-0">
                        <v-list-item-title class="text-caption font-weight-bold text-grey-darken-1">Phone</v-list-item-title>
                        <v-list-item-subtitle class="text-body-2 mt-1">{{ warehouse.phone }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item v-if="warehouse.email" class="px-0">
                        <v-list-item-title class="text-caption font-weight-bold text-grey-darken-1">Email</v-list-item-title>
                        <v-list-item-subtitle class="text-body-2 mt-1">{{ warehouse.email }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item v-if="warehouse.address" class="px-0">
                        <v-list-item-title class="text-caption font-weight-bold text-grey-darken-1">Address</v-list-item-title>
                        <v-list-item-subtitle class="text-body-2 mt-1">
                            {{ warehouse.address }}<br v-if="warehouse.city || warehouse.state || warehouse.postal_code">
                            <template v-if="warehouse.city || warehouse.state || warehouse.postal_code">
                                {{ [warehouse.city, warehouse.state, warehouse.postal_code].filter(Boolean).join(', ') }}<br>
                            </template>
                            {{ warehouse.country }}
                        </v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item class="px-0">
                        <v-list-item-title class="text-caption font-weight-bold text-grey-darken-1">Status</v-list-item-title>
                        <v-list-item-subtitle class="mt-1">
                            <v-chip :color="warehouse.is_active ? 'success' : 'error'" size="small" density="compact">
                                {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                            </v-chip>
                        </v-list-item-subtitle>
                    </v-list-item>
                </v-list>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
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
    emits: ['update:modelValue']
};
</script>

