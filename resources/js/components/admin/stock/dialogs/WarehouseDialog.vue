<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="600px" persistent>
        <v-card>
            <v-card-title class="pa-3 d-flex justify-space-between align-center">
                <span class="text-h6">{{ isEdit ? 'Edit Warehouse' : 'Add Warehouse' }}</span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="$emit('update:modelValue', false)" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <v-form ref="formRef" v-model="formValid">
                    <v-row dense class="ma-0">
                        <v-col cols="12" sm="8" class="pa-2">
                            <v-text-field v-model="localForm.name" label="Warehouse Name *" 
                                density="compact" variant="outlined" hide-details="auto"
                                :rules="[rules.required]" />
                        </v-col>
                        <v-col cols="12" sm="4" class="pa-2">
                            <v-switch v-model="localForm.is_active" label="Active" color="primary" 
                                density="compact" hide-details class="mt-2" />
                        </v-col>
                        <v-col cols="12" class="pa-2">
                            <v-text-field v-model="localForm.code" label="Warehouse Code" 
                                density="compact" variant="outlined" hide-details="auto"
                                hint="Unique identifier" persistent-hint />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="localForm.phone" label="Phone" 
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="6" class="pa-2">
                            <v-text-field v-model="localForm.email" label="Email" type="email" 
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" class="pa-2">
                            <v-textarea v-model="localForm.address" label="Address" 
                                density="compact" variant="outlined" rows="2" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="4" class="pa-2">
                            <v-text-field v-model="localForm.city" label="City" 
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="4" class="pa-2">
                            <v-text-field v-model="localForm.state" label="State/Province" 
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" sm="4" class="pa-2">
                            <v-text-field v-model="localForm.postal_code" label="Postal Code" 
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                        <v-col cols="12" class="pa-2">
                            <v-text-field v-model="localForm.country" label="Country" 
                                density="compact" variant="outlined" hide-details="auto" />
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-3">
                <v-spacer />
                <v-btn variant="text" density="compact" @click="$emit('update:modelValue', false)">Cancel</v-btn>
                <v-btn color="primary" :loading="saving" density="compact" @click="handleSave">
                    {{ isEdit ? 'Update' : 'Save' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'WarehouseDialog',
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        warehouse: {
            type: Object,
            default: null
        },
        saving: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue', 'save'],
    data() {
        return {
            formValid: false,
            localForm: this.getEmptyForm(),
            rules: {
                required: v => !!v || 'Required',
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.localForm.id;
        },
    },
    watch: {
        modelValue(newVal) {
            if (newVal) {
                if (this.warehouse) {
                    this.localForm = { ...this.warehouse };
                } else {
                    this.localForm = this.getEmptyForm();
                }
            }
        },
        warehouse: {
            handler(newVal) {
                if (newVal && this.modelValue) {
                    this.localForm = { ...newVal };
                }
            },
            deep: true
        }
    },
    methods: {
        getEmptyForm() {
            return {
                id: null,
                name: '',
                code: '',
                phone: '',
                email: '',
                address: '',
                city: '',
                state: '',
                postal_code: '',
                country: '',
                is_active: true,
            };
        },
        async handleSave() {
            const valid = await this.$refs.formRef.validate();
            if (!valid.valid) return;
            this.$emit('save', { ...this.localForm });
        },
    },
};
</script>

