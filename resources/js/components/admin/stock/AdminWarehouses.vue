<template>
    <v-container fluid>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <div class="d-flex align-center gap-2">
                    <v-icon>mdi-warehouse</v-icon>
                    <span class="text-h5">Warehouses</span>
                </div>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog">Add Warehouse</v-btn>
            </v-card-title>
            <v-divider />
            
            <v-card-text>
                <v-text-field v-model="search" label="Search Warehouses" 
                    prepend-inner-icon="mdi-magnify" clearable />
            </v-card-text>

            <v-data-table :headers="headers" :items="filteredWarehouses" :loading="loading" 
                :items-per-page="15">
                <template #item.is_active="{ item }">
                    <v-chip :color="item.is_active ? 'success' : 'error'" size="small">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </v-chip>
                </template>
                <template #item.created_at="{ item }">
                    {{ formatDate(item.created_at) }}
                </template>
                <template #item.actions="{ item }">
                    <v-btn icon="mdi-eye" size="small" variant="text" @click="viewWarehouse(item)" />
                    <v-btn icon="mdi-pencil" size="small" variant="text" @click="editWarehouse(item)" />
                    <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDelete(item)" />
                </template>
            </v-data-table>
        </v-card>

        <!-- Warehouse Dialog -->
        <v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    <span>{{ isEdit ? 'Edit Warehouse' : 'Add Warehouse' }}</span>
                    <v-btn icon="mdi-close" variant="text" @click="closeDialog" />
                </v-card-title>
                <v-divider />
                <v-card-text>
                    <v-form ref="formRef" v-model="formValid">
                        <v-row dense>
                            <v-col cols="12" sm="8">
                                <v-text-field v-model="form.name" label="Warehouse Name *" 
                                    :rules="[rules.required]" />
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-switch v-model="form.is_active" label="Active" color="primary" />
                            </v-col>
                            <v-col cols="12">
                                <v-text-field v-model="form.code" label="Warehouse Code" 
                                    hint="Unique identifier" />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field v-model="form.phone" label="Phone" />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field v-model="form.email" label="Email" type="email" />
                            </v-col>
                            <v-col cols="12">
                                <v-textarea v-model="form.address" label="Address" rows="2" />
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="form.city" label="City" />
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="form.state" label="State/Province" />
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="form.postal_code" label="Postal Code" />
                            </v-col>
                            <v-col cols="12">
                                <v-text-field v-model="form.country" label="Country" />
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-divider />
                <v-card-actions class="justify-end">
                    <v-btn variant="text" @click="closeDialog">Cancel</v-btn>
                    <v-btn color="primary" :loading="saving" @click="save">
                        {{ isEdit ? 'Update' : 'Save' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- View Warehouse Dialog -->
        <v-dialog v-model="viewDialog" max-width="600px">
            <v-card v-if="selectedWarehouse">
                <v-card-title class="d-flex justify-space-between align-center bg-primary">
                    <span class="text-white">Warehouse Details</span>
                    <v-btn icon="mdi-close" variant="text" class="text-white" @click="viewDialog = false" />
                </v-card-title>
                <v-card-text class="pa-4">
                    <v-list density="compact">
                        <v-list-item>
                            <v-list-item-title class="font-weight-bold">Name</v-list-item-title>
                            <v-list-item-subtitle>{{ selectedWarehouse.name }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item v-if="selectedWarehouse.code">
                            <v-list-item-title class="font-weight-bold">Code</v-list-item-title>
                            <v-list-item-subtitle>{{ selectedWarehouse.code }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item v-if="selectedWarehouse.phone">
                            <v-list-item-title class="font-weight-bold">Phone</v-list-item-title>
                            <v-list-item-subtitle>{{ selectedWarehouse.phone }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item v-if="selectedWarehouse.email">
                            <v-list-item-title class="font-weight-bold">Email</v-list-item-title>
                            <v-list-item-subtitle>{{ selectedWarehouse.email }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item v-if="selectedWarehouse.address">
                            <v-list-item-title class="font-weight-bold">Address</v-list-item-title>
                            <v-list-item-subtitle>
                                {{ selectedWarehouse.address }}<br>
                                {{ selectedWarehouse.city }}, {{ selectedWarehouse.state }} {{ selectedWarehouse.postal_code }}<br>
                                {{ selectedWarehouse.country }}
                            </v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title class="font-weight-bold">Status</v-list-item-title>
                            <v-list-item-subtitle>
                                <v-chip :color="selectedWarehouse.is_active ? 'success' : 'error'" size="small">
                                    {{ selectedWarehouse.is_active ? 'Active' : 'Inactive' }}
                                </v-chip>
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="400">
            <v-card>
                <v-card-title>Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this warehouse?</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="deleteDialog = false">Cancel</v-btn>
                    <v-btn color="error" :loading="deleting" @click="deleteWarehouse">Delete</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'AdminWarehouses',
    data() {
        return {
            warehouses: [],
            search: '',
            loading: false,
            dialog: false,
            viewDialog: false,
            deleteDialog: false,
            saving: false,
            deleting: false,
            formValid: false,
            form: this.getEmptyForm(),
            selectedWarehouse: null,
            headers: [
                { title: 'Name', key: 'name' },
                { title: 'Code', key: 'code' },
                { title: 'City', key: 'city' },
                { title: 'Phone', key: 'phone' },
                { title: 'Status', key: 'is_active' },
                { title: 'Created', key: 'created_at' },
                { title: 'Actions', key: 'actions', sortable: false, align: 'center' },
            ],
            rules: {
                required: v => !!v || 'Required',
            },
        };
    },
    computed: {
        filteredWarehouses() {
            if (!this.search) return this.warehouses;
            const searchLower = this.search.toLowerCase();
            return this.warehouses.filter(warehouse => 
                warehouse.name?.toLowerCase().includes(searchLower) ||
                warehouse.code?.toLowerCase().includes(searchLower) ||
                warehouse.city?.toLowerCase().includes(searchLower)
            );
        },
        isEdit() {
            return !!this.form.id;
        },
    },
    mounted() {
        this.fetchWarehouses();
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
        async fetchWarehouses() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/warehouses');
                this.warehouses = data.data || data.warehouses || [];
            } catch (error) {
                console.error('Failed to fetch warehouses', error);
                this.$toast?.error('Failed to load warehouses');
            } finally {
                this.loading = false;
            }
        },
        openDialog() {
            this.form = this.getEmptyForm();
            this.dialog = true;
        },
        editWarehouse(warehouse) {
            this.form = { ...warehouse };
            this.dialog = true;
        },
        viewWarehouse(warehouse) {
            this.selectedWarehouse = warehouse;
            this.viewDialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.form = this.getEmptyForm();
        },
        async save() {
            const valid = await this.$refs.formRef.validate();
            if (!valid.valid) return;

            this.saving = true;
            try {
                if (this.isEdit) {
                    await axios.put(`/api/v1/warehouses/${this.form.id}`, this.form);
                    this.$toast?.success('Warehouse updated successfully');
                } else {
                    await axios.post('/api/v1/warehouses', this.form);
                    this.$toast?.success('Warehouse created successfully');
                }
                this.closeDialog();
                this.fetchWarehouses();
            } catch (error) {
                console.error('Failed to save warehouse', error);
                this.$toast?.error('Failed to save warehouse');
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(warehouse) {
            this.form = warehouse;
            this.deleteDialog = true;
        },
        async deleteWarehouse() {
            this.deleting = true;
            try {
                await axios.delete(`/api/v1/warehouses/${this.form.id}`);
                this.$toast?.success('Warehouse deleted successfully');
                this.deleteDialog = false;
                this.fetchWarehouses();
            } catch (error) {
                console.error('Failed to delete warehouse', error);
                this.$toast?.error('Failed to delete warehouse');
            } finally {
                this.deleting = false;
            }
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
        },
    },
};
</script>

