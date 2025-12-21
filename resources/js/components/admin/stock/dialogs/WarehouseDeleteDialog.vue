<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="400px">
        <v-card>
            <v-card-title class="pa-3 text-h6">Confirm Delete</v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <p class="text-body-2 mb-0">Are you sure you want to delete this warehouse?</p>
                <p v-if="warehouse" class="text-body-2 font-weight-medium mt-2 text-grey-darken-1">
                    {{ warehouse.name }}
                </p>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-3">
                <v-spacer />
                <v-btn variant="text" density="compact" @click="$emit('update:modelValue', false)">Cancel</v-btn>
                <v-btn color="error" :loading="deleting" density="compact" @click="$emit('confirm')">Delete</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'WarehouseDeleteDialog',
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        warehouse: {
            type: Object,
            default: null
        },
        deleting: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue', 'confirm']
};
</script>

