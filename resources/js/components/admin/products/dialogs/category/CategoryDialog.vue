<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="700px"
        persistent scrollable>
        <v-card class="category-dialog">
            <!-- Header -->
            <v-card-title class="dialog-header d-flex justify-space-between align-center pa-3">
                <span class="text-h6 font-weight-medium">{{ isEdit ? 'Edit Category' : 'Add New Category' }}</span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="close" />
            </v-card-title>
            <v-divider />
            <v-card-text class="pa-3">
                <v-form ref="formRef" v-model="formValid" lazy-validation>
                    <v-text-field v-model="form.name" placeholder="Enter category name (e.g., Electronics, Clothing)"
                        :rules="[rules.required]" required variant="outlined" density="comfortable" hide-details="auto"
                        hint="Enter the category name as it should appear in the system" persistent-hint class="mb-4">
                        <template v-slot:label>
                            Category Name <span class="text-error" style="font-size: 1.2em; font-weight: bold;">*</span>
                        </template>
                    </v-text-field>

                    <v-text-field v-model="form.slug" label="Slug" placeholder="Auto-generated from category name"
                        hint="Auto-generated from category name (read-only)" persistent-hint variant="outlined"
                        density="comfortable" hide-details="auto" readonly class="mb-4" />

                    <v-text-field v-model.number="form.order" label="Display Order" type="number" min="0"
                        placeholder="Enter display order (e.g., 0, 1, 2)"
                        hint="Lower numbers appear first in the category list" persistent-hint variant="outlined"
                        density="comfortable" hide-details="auto" class="mb-4" />

                    <v-textarea v-model="form.description" label="Description"
                        placeholder="Enter a brief description about the category (optional)" variant="outlined"
                        density="comfortable" rows="3" hint="Optional: Brief description about the category"
                        persistent-hint hide-details="auto" class="mb-4" />

                    <!-- Image Upload Section -->
                    <div class="mb-4">
                        <div class="text-subtitle-2 font-weight-medium mb-2">Category Image</div>

                        <!-- Image Preview -->
                        <div v-if="form.image" class="mb-3 text-center">
                            <v-avatar size="120" class="mb-2">
                                <v-img :src="form.image ? resolveImageUrl(form.image) : ''"
                                    alt="Category Preview"></v-img>
                            </v-avatar>
                            <div>
                                <v-btn size="small" variant="text" color="error" prepend-icon="mdi-delete"
                                    @click="clearImage">Remove
                                    Image</v-btn>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <v-file-input v-model="imageFile" label="Upload Image" variant="outlined" density="comfortable"
                            color="primary" accept="image/*" prepend-icon="mdi-image"
                            hint="Upload a category image (JPG, PNG, GIF, WebP - Max 5MB)" persistent-hint show-size
                            @update:model-value="handleImageUpload">
                            <template v-slot:append-inner v-if="uploadingImage">
                                <v-progress-circular indeterminate size="20" color="primary"></v-progress-circular>
                            </template>
                        </v-file-input>
                    </div>

                    <v-switch v-model="form.is_active" label="Active Category" color="success" class="mb-4"></v-switch>
                </v-form>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-2 justify-end">
                <v-btn variant="text" size="small" @click="close">Cancel</v-btn>
                <v-btn color="primary" size="small" :loading="saving" @click="save">
                    {{ isEdit ? 'Update' : 'Create' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from '@/utils/axios.config';
import { normalizeUploadPath, resolveUploadUrl } from '@/utils/uploads';

export default {
    name: 'CategoryDialog',
    props: {
        modelValue: { type: Boolean, required: true },
        category: { type: Object, default: null },
    },
    emits: ['update:modelValue', 'saved'],
    data() {
        return {
            form: this.getEmptyForm(),
            formValid: false,
            saving: false,
            imageFile: null,
            uploadingImage: false,
            rules: {
                required: value => !!value || 'This field is required',
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.form.id;
        },
    },
    watch: {
        category: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    const imagePath = this.normalizeImageInput(newVal.image || '');
                    this.form = {
                        id: newVal.id,
                        name: newVal.name || '',
                        slug: newVal.slug || '',
                        description: newVal.description || '',
                        image: imagePath,
                        order: newVal.order || 0,
                        is_active: newVal.is_active !== undefined ? newVal.is_active : true,
                    };
                } else {
                    this.form = this.getEmptyForm();
                }
                this.imageFile = null;
            },
        },
        'form.name'(newName) {
            // Auto-generate slug from name in real-time
            if (newName) {
                this.form.slug = this.generateSlug(newName);
            } else {
                this.form.slug = '';
            }
        },
    },
    methods: {
        getEmptyForm() {
            return {
                id: null,
                name: '',
                slug: '',
                description: '',
                image: '',
                order: 0,
                is_active: true,
            };
        },
        generateSlug(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        },
        normalizeImageInput(imageValue) {
            return normalizeUploadPath(imageValue);
        },
        resolveImageUrl(value) {
            return resolveUploadUrl(value);
        },
        async handleImageUpload() {
            if (!this.imageFile) {
                return;
            }

            const fileToUpload = Array.isArray(this.imageFile) ? this.imageFile[0] : this.imageFile;
            if (!fileToUpload) {
                return;
            }

            if (!fileToUpload.type.startsWith('image/')) {
                this.$toast?.error('Please select a valid image file');
                this.imageFile = null;
                return;
            }

            const maxSize = 5 * 1024 * 1024;
            if (fileToUpload.size > maxSize) {
                this.$toast?.error('File size must be less than 5MB');
                this.imageFile = null;
                return;
            }

            this.uploadingImage = true;
            try {
                const formData = new FormData();
                formData.append('image', fileToUpload);
                formData.append('folder', 'products/categories');
                if (this.form.name) {
                    formData.append('prefix', this.form.name);
                }

                const token = localStorage.getItem('admin_token');
                const response = await axios.post('/api/v1/upload/image', formData, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.success) {
                    const uploadedPath = this.normalizeImageInput(response.data.path || response.data.url);
                    this.form.image = uploadedPath;
                    this.imageFile = null;
                    this.$toast?.success('Image uploaded successfully');
                } else {
                    throw new Error(response.data.message || 'Failed to upload image');
                }
            } catch (error) {
                console.error('Error uploading image:', error);
                let errorMessage = 'Failed to upload image';
                if (error.response) {
                    errorMessage = error.response.data?.message || error.response.statusText || errorMessage;
                } else if (error.message) {
                    errorMessage = error.message;
                }
                this.$toast?.error(errorMessage);
                this.imageFile = null;
            } finally {
                this.uploadingImage = false;
            }
        },
        clearImage() {
            this.form.image = '';
            this.imageFile = null;
        },
        close() {
            this.$emit('update:modelValue', false);
        },
        async save() {
            const valid = await this.$refs.formRef.validate();
            if (!valid) return;

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.isEdit
                    ? `/api/v1/categories/${this.form.id}`
                    : '/api/v1/categories';

                const data = { ...this.form };
                data.image = this.normalizeImageInput(data.image);

                const method = this.isEdit ? 'put' : 'post';

                await axios[method](url, data, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.$toast?.success(
                    this.isEdit ? 'Category updated successfully' : 'Category created successfully'
                );
                this.$emit('saved');
                this.close();
            } catch (error) {
                console.error('Error saving category:', error);
                let message = 'Error saving category';

                if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    const errorMessages = [];
                    Object.keys(errors).forEach(key => {
                        errorMessages.push(errors[key][0]);
                    });
                    message = errorMessages.join(', ');
                } else if (error.response?.data?.message) {
                    message = error.response.data.message;
                }

                this.$toast?.error(message);
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
.category-dialog {
    max-height: 90vh;
}

.dialog-header {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>
