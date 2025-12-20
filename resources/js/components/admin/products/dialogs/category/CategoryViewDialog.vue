<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="700px"
        persistent scrollable>
        <v-card class="category-view-dialog">
            <!-- Header -->
            <v-card-title class="dialog-header d-flex justify-space-between align-center pa-3">
                <span class="text-h6 font-weight-medium">Category Details</span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="close" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <div v-if="loading" class="text-center py-8">
                    <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                    <div class="mt-3 text-body-2">Loading category details...</div>
                </div>
                <div v-else-if="!displayCategory" class="text-center py-8">
                    <v-alert type="error" variant="tonal" class="ma-3" density="compact">
                        Failed to load category details
                    </v-alert>
                </div>
                <div v-else>
                    <!-- Basic Information Section -->
                    <div class="text-subtitle-2 font-weight-medium mb-2">Category Information</div>
                    <v-card variant="outlined" class="mb-2">
                        <v-card-text class="pa-2">
                            <v-row dense class="ma-0">
                                <v-col cols="12" sm="3" class="pa-2" v-if="displayCategory.image">
                                    <div class="text-center">
                                        <v-img :src="displayCategory.image" max-width="120" max-height="120" class="mx-auto"
                                            style="border-radius: 8px;" cover></v-img>
                                    </div>
                                </v-col>
                                <v-col :cols="displayCategory.image ? 12 : 12" :sm="displayCategory.image ? 9 : 12" class="pa-2">
                                    <v-row dense class="ma-0">
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">ID:</span>
                                                <span class="text-body-2 font-weight-medium ml-1">{{ displayCategory.id || '-'
                                                    }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Status:</span>
                                                <v-chip size="x-small" :color="displayCategory.is_active ? 'success' : 'error'"
                                                    variant="tonal" class="ml-1">
                                                    {{ displayCategory.is_active ? 'Active' : 'Inactive' }}
                                                </v-chip>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Order:</span>
                                                <span class="text-body-2 ml-1">{{ displayCategory.order || 0 }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Slug:</span>
                                                <span class="text-body-2 ml-1">{{ displayCategory.slug || '-' }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" class="pa-2">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Name:</span>
                                                <span class="text-body-2 font-weight-medium ml-1">{{ displayCategory.name || '-'
                                                    }}</span>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" class="pa-2" v-if="displayCategory.description">
                                            <div class="info-item-compact">
                                                <span class="text-caption text-grey">Description:</span>
                                                <span class="text-body-2 ml-1">{{ displayCategory.description }}</span>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Created & Updated Information Section -->
                    <v-divider class="my-2" />
                    <div class="text-subtitle-2 font-weight-medium mb-2">Audit Information</div>
                    <v-card variant="outlined" class="mb-2">
                        <v-card-text class="pa-2">
                            <v-row dense class="ma-0">
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Created By:</span>
                                        <span class="text-body-2 ml-1">{{ displayCategory.created_by?.name || '-' }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Created At:</span>
                                        <span class="text-body-2 ml-1">{{ formatDateShort(displayCategory.created_at) || '-'
                                            }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Updated By:</span>
                                        <span class="text-body-2 ml-1">{{ displayCategory.updated_by?.name || '-' }}</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="6" class="pa-2">
                                    <div class="info-item-compact">
                                        <span class="text-caption text-grey">Updated At:</span>
                                        <span class="text-body-2 ml-1">{{ formatDateShort(displayCategory.updated_at) || '-'
                                            }}</span>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
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

export default {
    name: 'CategoryViewDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        category: { type: Object, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            loading: false,
            categoryData: null,
        };
    },
    computed: {
        displayCategory() {
            return this.categoryData || this.category;
        },
    },
    watch: {
        modelValue(newVal) {
            if (newVal && this.category && this.category.id) {
                this.loadCategoryDetails();
            } else {
                this.categoryData = null;
            }
        },
        category: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal.id && this.modelValue) {
                    this.loadCategoryDetails();
                } else {
                    this.categoryData = null;
                }
            },
        },
    },
    methods: {
        async loadCategoryDetails() {
            if (!this.category?.id) return;

            this.loading = true;
            try {
                const token = localStorage.getItem('admin_token');
                const response = await axios.get(`/api/v1/categories/${this.category.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.categoryData = response.data;
            } catch (error) {
                console.error('Failed to load category details', error);
                this.$toast?.error('Failed to load category details');
            } finally {
                this.loading = false;
            }
        },
        formatDateShort(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            const formattedHours = String(hours).padStart(2, '0');

            return `${day}/${month}/${year} ${formattedHours}:${minutes} ${ampm}`;
        },
        close() {
            this.$emit('update:modelValue', false);
        },
    },
};
</script>

<style scoped>
.category-view-dialog {
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
</style>

