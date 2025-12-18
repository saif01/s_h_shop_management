<template>
    <v-container fluid>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <div class="d-flex align-center gap-2">
                    <v-icon>mdi-weight-kilogram</v-icon>
                    <span class="text-h5">Units</span>
                </div>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog">Add Unit</v-btn>
            </v-card-title>
            <v-divider />
            
            <v-card-text>
                <v-text-field v-model="search" label="Search Units" 
                    prepend-inner-icon="mdi-magnify" clearable />
            </v-card-text>

            <v-data-table :headers="headers" :items="filteredUnits" :loading="loading" 
                :items-per-page="15">
                <template #item.code="{ item }">
                    <v-chip size="small">{{ item.code }}</v-chip>
                </template>
                <template #item.is_active="{ item }">
                    <v-chip :color="item.is_active ? 'success' : 'error'" size="small">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </v-chip>
                </template>
                <template #item.created_at="{ item }">
                    {{ formatDate(item.created_at) }}
                </template>
                <template #item.actions="{ item }">
                    <v-btn icon="mdi-pencil" size="small" variant="text" @click="editUnit(item)" />
                    <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDelete(item)" />
                </template>
            </v-data-table>
        </v-card>

        <!-- Unit Dialog -->
        <v-dialog v-model="dialog" max-width="500px" persistent>
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    <span>{{ isEdit ? 'Edit Unit' : 'Add Unit' }}</span>
                    <v-btn icon="mdi-close" variant="text" @click="closeDialog" />
                </v-card-title>
                <v-divider />
                <v-card-text>
                    <v-form ref="formRef" v-model="formValid">
                        <v-text-field v-model="form.name" label="Unit Name *" 
                            :rules="[rules.required]" />
                        <v-text-field v-model="form.code" label="Code *" 
                            :rules="[rules.required]" hint="e.g., KG, PCS, LTR" />
                        <v-textarea v-model="form.description" label="Description" rows="2" />
                        <v-switch v-model="form.is_active" label="Active" color="primary" />
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

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="400">
            <v-card>
                <v-card-title>Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this unit?</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="deleteDialog = false">Cancel</v-btn>
                    <v-btn color="error" :loading="deleting" @click="deleteUnit">Delete</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import axios from '@/utils/axios.config';

export default {
    name: 'AdminUnits',
    data() {
        return {
            units: [],
            search: '',
            loading: false,
            dialog: false,
            deleteDialog: false,
            saving: false,
            deleting: false,
            formValid: false,
            form: this.getEmptyForm(),
            headers: [
                { title: 'Name', key: 'name' },
                { title: 'Code', key: 'code' },
                { title: 'Description', key: 'description' },
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
        filteredUnits() {
            if (!this.search) return this.units;
            const searchLower = this.search.toLowerCase();
            return this.units.filter(unit => 
                unit.name?.toLowerCase().includes(searchLower) ||
                unit.code?.toLowerCase().includes(searchLower)
            );
        },
        isEdit() {
            return !!this.form.id;
        },
    },
    mounted() {
        this.fetchUnits();
    },
    methods: {
        getEmptyForm() {
            return {
                id: null,
                name: '',
                code: '',
                description: '',
                is_active: true,
            };
        },
        async fetchUnits() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/units');
                this.units = data.data || data.units || [];
            } catch (error) {
                console.error('Failed to fetch units', error);
                this.$toast?.error('Failed to load units');
            } finally {
                this.loading = false;
            }
        },
        openDialog() {
            this.form = this.getEmptyForm();
            this.dialog = true;
        },
        editUnit(unit) {
            this.form = { ...unit };
            this.dialog = true;
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
                    await axios.put(`/api/v1/units/${this.form.id}`, this.form);
                    this.$toast?.success('Unit updated successfully');
                } else {
                    await axios.post('/api/v1/units', this.form);
                    this.$toast?.success('Unit created successfully');
                }
                this.closeDialog();
                this.fetchUnits();
            } catch (error) {
                console.error('Failed to save unit', error);
                this.$toast?.error('Failed to save unit');
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(unit) {
            this.form = unit;
            this.deleteDialog = true;
        },
        async deleteUnit() {
            this.deleting = true;
            try {
                await axios.delete(`/api/v1/units/${this.form.id}`);
                this.$toast?.success('Unit deleted successfully');
                this.deleteDialog = false;
                this.fetchUnits();
            } catch (error) {
                console.error('Failed to delete unit', error);
                this.$toast?.error('Failed to delete unit');
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

