<template>
    <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="900"
        scrollable persistent>
        <v-card>
            <!-- Dialog Header -->
            <v-card-title class="d-flex align-center justify-space-between">
                <span class="text-h6 font-weight-medium">
                    {{ dialogTitle }}
                </span>
                <v-btn icon="mdi-close" variant="text" size="small" @click="handleClose" />
            </v-card-title>

            <v-divider />

            <!-- Dialog Content -->
            <v-card-text class="pa-0">
                <v-form ref="formRef" @submit.prevent="handleSave">
                    <v-tabs v-model="activeTab" bg-color="grey-lighten-5" color="primary" slider-color="primary"
                        class="user-dialog-tabs">
                        <v-tab value="basic">
                            <v-icon start size="small">mdi-account</v-icon>
                            <span class="ml-1">Basic Information</span>
                        </v-tab>
                        <v-tab value="profile">
                            <v-icon start size="small">mdi-account-details</v-icon>
                            <span class="ml-1">Profile Information</span>
                        </v-tab>
                        <v-tab value="security">
                            <v-icon start size="small">mdi-lock</v-icon>
                            <span class="ml-1">Security</span>
                        </v-tab>
                    </v-tabs>

                    <v-window v-model="activeTab">
                        <!-- Basic Information Tab -->
                        <v-window-item value="basic">
                            <div class="pa-6">
                                <v-text-field v-model="form.name" placeholder="Enter full name (e.g., John Doe)"
                                    variant="outlined" density="comfortable" :rules="[validationRules.required]"
                                    required prepend-inner-icon="mdi-account"
                                    hint="Enter the user's full name as it should appear in the system" persistent-hint
                                    hide-details="auto" class="mb-4">
                                    <template #label>
                                        Full Name <span class="text-error"
                                            style="font-size: 1.2em; font-weight: bold;">*</span>
                                    </template>
                                </v-text-field>

                                <v-text-field v-model="form.email" type="email"
                                    placeholder="Enter email address (e.g., user@example.com)" variant="outlined"
                                    density="comfortable" :rules="[validationRules.required, validationRules.email]"
                                    required prepend-inner-icon="mdi-email"
                                    hint="Enter a valid email address. This will be used for login and notifications"
                                    persistent-hint hide-details="auto" class="mb-4">
                                    <template #label>
                                        Email <span class="text-error"
                                            style="font-size: 1.2em; font-weight: bold;">*</span>
                                    </template>
                                </v-text-field>

                                <v-select v-model="form.role_ids" :items="roles" item-title="label" item-value="value"
                                    placeholder="Select one or more roles" variant="outlined" density="comfortable"
                                    :rules="[validationRules.required]" required multiple chips
                                    prepend-inner-icon="mdi-account-group"
                                    hint="Select at least one role to define user permissions. You can select multiple roles"
                                    persistent-hint hide-details="auto" class="mb-4">
                                    <template #label>
                                        Roles <span class="text-error"
                                            style="font-size: 1.2em; font-weight: bold;">*</span>
                                    </template>
                                    <template #item="{ props, item }">
                                        <v-list-item v-bind="props">
                                            <template #title>
                                                <div class="d-flex align-center">
                                                    {{ item.raw.label }}
                                                    <v-chip v-if="item.raw.is_system" size="x-small" color="warning"
                                                        class="ml-2">
                                                        System
                                                    </v-chip>
                                                </div>
                                            </template>
                                            <template #subtitle>
                                                {{ item.raw.description }}
                                            </template>
                                        </v-list-item>
                                    </template>
                                    <template #selection="{ item, index }">
                                        <v-chip v-if="index < 2" size="small" class="mr-1">
                                            {{ item.raw.label }}
                                        </v-chip>
                                        <span v-if="index === 2" class="text-grey text-caption align-self-center">
                                            (+{{ form.role_ids.length - 2 }} others)
                                        </span>
                                    </template>
                                </v-select>

                                <!-- Avatar Upload Section -->
                                <div class="mb-4">
                                    <div class="text-subtitle-2 font-weight-medium mb-3">
                                        <v-icon size="small" class="mr-1">mdi-image</v-icon>
                                        Avatar
                                    </div>

                                    <!-- Avatar Preview -->
                                    <div v-if="form.avatar" class="mb-3 text-center">
                                        <v-avatar size="100" class="mb-3">
                                            <v-img :src="avatarUrl" :alt="`${form.name || 'User'} Avatar`" cover />
                                        </v-avatar>
                                        <div>
                                            <v-btn size="small" variant="text" color="error" prepend-icon="mdi-delete"
                                                @click="handleClearAvatar">
                                                Remove Avatar
                                            </v-btn>
                                        </div>
                                    </div>

                                    <!-- File Upload -->
                                    <v-file-input v-model="avatarFile" label="Upload Avatar" variant="outlined"
                                        density="comfortable" color="primary" accept="image/*" prepend-icon="mdi-image"
                                        :hint="avatarUploadHint" persistent-hint show-size
                                        @update:model-value="handleAvatarUpload">
                                        <template #append-inner v-if="uploadingAvatar">
                                            <v-progress-circular indeterminate size="20" color="primary" />
                                        </template>
                                    </v-file-input>
                                </div>
                            </div>
                        </v-window-item>

                        <!-- Profile Information Tab -->
                        <v-window-item value="profile">
                            <div class="pa-6">
                                <v-row dense>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="form.phone" label="Phone Number"
                                            placeholder="Enter phone number (e.g., +8801707080401)" variant="outlined"
                                            density="comfortable" prepend-inner-icon="mdi-phone"
                                            hint="Include country code. Format: +[country code][number]" persistent-hint
                                            hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <DatePickerVuetifyInput field-label="Date of Birth" variant="outlined"
                                            :initial-date="form.date_of_birth"
                                            @trigerInputValue="form.date_of_birth = $event" />
                                        <div class="text-caption text-grey mt-1 ml-2">
                                            Select the user's date of birth
                                        </div>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-select v-model="form.gender" :items="genderOptions" label="Gender"
                                            placeholder="Select gender" variant="outlined" density="comfortable"
                                            prepend-inner-icon="mdi-gender-male-female" clearable
                                            hint="Optional: Select user's gender" persistent-hint hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12">
                                        <v-textarea v-model="form.address" label="Address"
                                            placeholder="Enter street address, building number, etc." variant="outlined"
                                            density="comfortable" rows="2" prepend-inner-icon="mdi-map-marker"
                                            hint="Enter the complete street address" persistent-hint
                                            hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model="form.city" label="City" placeholder="Enter city name"
                                            variant="outlined" density="comfortable" prepend-inner-icon="mdi-city"
                                            hint="Optional: City name" persistent-hint hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model="form.state" label="State/Province"
                                            placeholder="Enter state or province" variant="outlined"
                                            density="comfortable" prepend-inner-icon="mdi-map"
                                            hint="Optional: State or province name" persistent-hint
                                            hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model="form.country" label="Country"
                                            placeholder="Enter country name" variant="outlined" density="comfortable"
                                            prepend-inner-icon="mdi-earth" hint="Optional: Country name" persistent-hint
                                            hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="form.postal_code" label="Postal Code"
                                            placeholder="Enter postal or zip code" variant="outlined"
                                            density="comfortable" prepend-inner-icon="mdi-mailbox"
                                            hint="Optional: Postal or ZIP code" persistent-hint hide-details="auto" />
                                    </v-col>
                                    <v-col cols="12">
                                        <v-textarea v-model="form.bio" label="Bio/Description"
                                            placeholder="Enter a brief description about the user (optional)"
                                            variant="outlined" density="comfortable" rows="4"
                                            prepend-inner-icon="mdi-text"
                                            hint="Optional: Brief description about the user. Maximum 1000 characters"
                                            persistent-hint :counter="1000" hide-details="auto" />
                                    </v-col>
                                </v-row>
                            </div>
                        </v-window-item>

                        <!-- Security Tab -->
                        <v-window-item value="security">
                            <div class="pa-6">
                                <v-text-field v-model="form.password"
                                    :placeholder="isEditing ? 'Enter new password (leave blank to keep current)' : 'Enter password (minimum 8 characters)'"
                                    variant="outlined" density="comfortable" :type="showPassword ? 'text' : 'password'"
                                    :rules="passwordRules" :required="!isEditing"
                                    :hint="isEditing ? 'Leave blank to keep current password. If changing, enter a new password with at least 8 characters' : 'Password must be at least 8 characters long'"
                                    :persistent-hint="true" prepend-inner-icon="mdi-lock" hide-details="auto"
                                    class="mb-4">
                                    <template #label>
                                        Password <span v-if="!isEditing" class="text-error"
                                            style="font-size: 1.2em; font-weight: bold;">*</span>
                                    </template>
                                    <template #append-inner>
                                        <v-btn icon variant="text" size="small" @click="showPassword = !showPassword">
                                            <v-icon>
                                                {{ showPassword ? 'mdi-eye-off' : 'mdi-eye' }}
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                </v-text-field>

                                <v-text-field v-if="shouldShowPasswordConfirmation" v-model="form.password_confirmation"
                                    placeholder="Re-enter the password to confirm" variant="outlined"
                                    density="comfortable" :type="showPasswordConfirmation ? 'text' : 'password'"
                                    :rules="passwordConfirmationRules" :required="!!form.password"
                                    prepend-inner-icon="mdi-lock-check"
                                    hint="Re-enter the password to confirm it matches" persistent-hint
                                    hide-details="auto">
                                    <template #label>
                                        Confirm Password <span v-if="form.password" class="text-error"
                                            style="font-size: 1.2em; font-weight: bold;">*</span>
                                    </template>
                                    <template #append-inner>
                                        <v-btn icon variant="text" size="small"
                                            @click="showPasswordConfirmation = !showPasswordConfirmation">
                                            <v-icon>
                                                {{ showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye' }}
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                </v-text-field>
                            </div>
                        </v-window-item>
                    </v-window>
                </v-form>
            </v-card-text>

            <!-- Dialog Actions -->
            <v-divider />
            <v-card-actions class="pa-4">
                <v-spacer />
                <v-btn variant="text" @click="handleClose" :disabled="saving">
                    Cancel
                </v-btn>
                <v-btn color="primary" variant="elevated" :loading="saving" @click="handleSave">
                    {{ isEditing ? 'Update' : 'Create' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import commonMixin from '../../../../mixins/commonMixin';
import { normalizeUploadPath, resolveUploadUrl } from '../../../../utils/uploads';
import DatePickerVuetifyInput from '../../../common/DatePickerVuetifyInput.vue';

/**
 * UserDialog Component
 * 
 * A dialog component for creating and editing users with three tabs:
 * - Basic Information: Name, email, roles, avatar
 * - Profile Information: Contact details, address, bio
 * - Security: Password management
 * 
 * @component
 */
export default {
    name: 'UserDialog',
    components: {
        DatePickerVuetifyInput
    },
    mixins: [commonMixin],

    props: {
        /**
         * Controls dialog visibility (v-model)
         */
        modelValue: {
            type: Boolean,
            required: true
        },
        /**
         * User object to edit (null for new user)
         */
        user: {
            type: Object,
            default: null
        },
        /**
         * Available roles for selection
         */
        roles: {
            type: Array,
            default: () => []
        }
    },

    emits: ['update:modelValue', 'save', 'error'],

    data() {
        return {
            // Form data
            form: {
                name: '',
                email: '',
                role_ids: [],
                password: '',
                password_confirmation: '',
                avatar: '',
                phone: '',
                date_of_birth: '',
                gender: null,
                address: '',
                city: '',
                state: '',
                country: '',
                postal_code: '',
                bio: ''
            },

            // UI state
            activeTab: 'basic',
            showPassword: false,
            showPasswordConfirmation: false,
            avatarFile: null,
            uploadingAvatar: false,
            saving: false,

            // Constants
            AVATAR_MAX_SIZE: 5 * 1024 * 1024, // 5MB
            AVATAR_FOLDER: 'users',
            MIN_PASSWORD_LENGTH: 8
        };
    },

    computed: {
        /**
         * Whether we're editing an existing user
         */
        isEditing() {
            return !!this.user;
        },

        /**
         * Dialog title based on mode
         */
        dialogTitle() {
            return this.isEditing ? 'Edit User' : 'Add New User';
        },

        /**
         * Gender options for select
         */
        genderOptions() {
            return [
                { title: 'Male', value: 'male' },
                { title: 'Female', value: 'female' },
                { title: 'Other', value: 'other' }
            ];
        },

        /**
         * Validation rules for form fields
         */
        validationRules() {
            return {
                required: (value) => {
                    if (Array.isArray(value)) {
                        return value.length > 0 || 'At least one role is required';
                    }
                    return !!value || 'This field is required';
                },
                email: (value) => {
                    if (!value) return true; // Skip if empty (handled by required)
                    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return pattern.test(value) || 'Invalid email address';
                },
                minLength: (value) => {
                    if (!value) return true; // Skip if empty (handled by required)
                    return value.length >= this.MIN_PASSWORD_LENGTH
                        || `Password must be at least ${this.MIN_PASSWORD_LENGTH} characters`;
                }
            };
        },

        /**
         * Password field validation rules
         */
        passwordRules() {
            if (this.isEditing) {
                // Optional for editing
                return [];
            }
            // Required for new users
            return [
                this.validationRules.required,
                this.validationRules.minLength
            ];
        },

        /**
         * Password confirmation validation rules
         */
        passwordConfirmationRules() {
            if (!this.form.password) {
                return [];
            }
            return [
                () => !!this.form.password_confirmation || 'Please confirm your password',
                () => this.form.password_confirmation === this.form.password || 'Passwords do not match'
            ];
        },

        /**
         * Whether to show password confirmation field
         */
        shouldShowPasswordConfirmation() {
            return !this.isEditing || !!this.form.password;
        },

        /**
         * Resolved avatar URL for display
         */
        avatarUrl() {
            return this.form.avatar ? this.resolveImageUrl(this.form.avatar) : '';
        },

        /**
         * Avatar upload hint text
         */
        avatarUploadHint() {
            return 'Upload an avatar image (JPG, PNG, GIF, WebP - Max 5MB). Recommended size: 200x200px';
        }
    },

    watch: {
        user: {
            handler(newUser) {
                if (newUser) {
                    this.loadUserData(newUser);
                } else {
                    this.resetForm();
                }
            },
            immediate: true
        },

        modelValue(newValue) {
            if (!newValue) {
                this.resetForm();
            } else {
                this.activeTab = 'basic';
            }
        }
    },

    methods: {
        /**
         * Load user data into form
         * @param {Object} user - User object to load
         */
        loadUserData(user) {
            const roleIds = user.roles ? user.roles.map(role => role.id) : [];
            const avatarPath = this.normalizeImageInput(user.avatar || '');

            this.form = {
                name: user.name || '',
                email: user.email || '',
                role_ids: roleIds,
                password: '',
                password_confirmation: '',
                avatar: avatarPath,
                phone: user.phone || '',
                date_of_birth: user.date_of_birth || '',
                gender: user.gender || null,
                address: user.address || '',
                city: user.city || '',
                state: user.state || '',
                country: user.country || '',
                postal_code: user.postal_code || '',
                bio: user.bio || ''
            };
        },

        /**
         * Reset form to initial state
         */
        resetForm() {
            const defaultRoleId = this.roles.length > 0 ? this.roles[0].value : null;

            this.form = {
                name: '',
                email: '',
                role_ids: defaultRoleId ? [defaultRoleId] : [],
                password: '',
                password_confirmation: '',
                avatar: '',
                phone: '',
                date_of_birth: '',
                gender: null,
                address: '',
                city: '',
                state: '',
                country: '',
                postal_code: '',
                bio: ''
            };

            this.showPassword = false;
            this.showPasswordConfirmation = false;
            this.avatarFile = null;
            this.activeTab = 'basic';

            if (this.$refs.formRef) {
                this.$refs.formRef.resetValidation();
            }
        },

        /**
         * Normalize image input path
         * @param {string} imageValue - Image path or URL
         * @returns {string} Normalized path
         */
        normalizeImageInput(imageValue) {
            return normalizeUploadPath(imageValue);
        },

        /**
         * Resolve image URL for display
         * @param {string} value - Image path
         * @returns {string} Full URL
         */
        resolveImageUrl(value) {
            return resolveUploadUrl(value);
        },

        /**
         * Handle avatar file upload
         */
        async handleAvatarUpload() {
            if (!this.avatarFile) {
                return;
            }

            const fileToUpload = Array.isArray(this.avatarFile)
                ? this.avatarFile[0]
                : this.avatarFile;

            if (!fileToUpload) {
                return;
            }

            // Validate file type
            if (!fileToUpload.type.startsWith('image/')) {
                this.showError('Please select a valid image file');
                this.avatarFile = null;
                return;
            }

            // Validate file size
            if (fileToUpload.size > this.AVATAR_MAX_SIZE) {
                this.showError('File size must be less than 5MB');
                this.avatarFile = null;
                return;
            }

            this.uploadingAvatar = true;

            try {
                const formData = new FormData();
                formData.append('image', fileToUpload);
                formData.append('folder', this.AVATAR_FOLDER);

                // Add user name as prefix if available
                if (this.form.name) {
                    formData.append('prefix', this.form.name);
                }

                const token = this.getAuthToken();
                const response = await this.$axios.post('/api/v1/upload/image', formData, {
                    headers: {
                        ...this.getAuthHeaders(),
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.success) {
                    const uploadedPath = this.normalizeImageInput(
                        response.data.path || response.data.url
                    );
                    this.form.avatar = uploadedPath;
                    this.avatarFile = null;
                    this.showSuccess('Avatar uploaded successfully');
                } else {
                    throw new Error(response.data.message || 'Failed to upload avatar');
                }
            } catch (error) {
                console.error('Error uploading avatar:', error);
                const errorMessage = this.extractErrorMessage(
                    error,
                    'Failed to upload avatar'
                );
                this.showError(errorMessage);
                this.avatarFile = null;
            } finally {
                this.uploadingAvatar = false;
            }
        },

        /**
         * Clear avatar from form
         */
        handleClearAvatar() {
            this.form.avatar = '';
            this.avatarFile = null;
        },

        /**
         * Extract error message from error object
         * @param {Error} error - Error object
         * @param {string} defaultMessage - Default error message
         * @returns {string} Error message
         */
        extractErrorMessage(error, defaultMessage) {
            if (error.response?.data?.message) {
                return error.response.data.message;
            }
            if (error.message) {
                return error.message;
            }
            return defaultMessage;
        },

        /**
         * Validate password fields
         * @returns {boolean} True if valid
         */
        validatePasswords() {
            // Check if passwords match
            if (this.form.password &&
                this.form.password !== this.form.password_confirmation) {
                this.showError('Passwords do not match');
                return false;
            }

            // Password required for new users
            if (!this.isEditing && !this.form.password) {
                this.showError('Password is required for new users');
                return false;
            }

            // Confirmation required if password is provided
            if (this.isEditing &&
                this.form.password &&
                !this.form.password_confirmation) {
                this.showError('Please confirm the password');
                return false;
            }

            return true;
        },

        /**
         * Prepare form data for API submission
         * @returns {Object} Prepared data object
         */
        prepareFormData() {
            const data = { ...this.form };

            // Ensure role_ids is an array
            if (data.role_ids) {
                data.role_ids = Array.isArray(data.role_ids)
                    ? data.role_ids
                    : [data.role_ids];
            }

            // Handle password fields
            if (this.form.password) {
                data.password_confirmation = this.form.password_confirmation;
            } else {
                // Remove password fields if not changing
                delete data.password;
                delete data.password_confirmation;
            }

            // Normalize avatar path
            data.avatar = this.normalizeImageInput(data.avatar);

            // Remove legacy fields
            delete data.role;

            return data;
        },

        /**
         * Handle form save
         */
        async handleSave() {
            // Validate passwords
            if (!this.validatePasswords()) {
                return;
            }

            // Validate form
            if (!this.$refs.formRef.validate()) {
                return;
            }

            this.saving = true;

            try {
                const token = this.getAuthToken();
                const url = this.isEditing
                    ? `/api/v1/users/${this.user.id}`
                    : '/api/v1/users';
                const method = this.isEditing ? 'put' : 'post';
                const data = this.prepareFormData();

                await this.$axios[method](url, data, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess(
                    this.isEditing
                        ? 'User updated successfully'
                        : 'User created successfully'
                );

                this.$emit('save', data);
                this.handleClose();
            } catch (error) {
                console.error('Error saving user:', error);
                this.handleSaveError(error);
            } finally {
                this.saving = false;
            }
        },

        /**
         * Handle save error
         * @param {Error} error - Error object
         */
        handleSaveError(error) {
            let message = 'Error saving user';

            if (error.response?.data?.errors) {
                // Handle validation errors
                const errors = error.response.data.errors;
                const errorMessages = [];
                Object.keys(errors).forEach(key => {
                    if (Array.isArray(errors[key]) && errors[key].length > 0) {
                        errorMessages.push(errors[key][0]);
                    }
                });
                message = errorMessages.length > 0
                    ? errorMessages.join(', ')
                    : message;
            } else if (error.response?.data?.message) {
                message = error.response.data.message;
            }

            this.handleApiError(error, message);
            this.$emit('error', error);
        },

        /**
         * Handle dialog close
         */
        handleClose() {
            this.$emit('update:modelValue', false);
        }
    }
};
</script>

<style scoped>
/* User Dialog Tabs Styling */
.user-dialog-tabs :deep(.v-tab) {
    text-transform: none;
    font-weight: 500;
    letter-spacing: 0.02em;
    transition: all 0.3s ease;
    min-height: 48px;
}

.user-dialog-tabs :deep(.v-tab:hover) {
    background-color: rgba(var(--v-theme-primary), 0.08);
}

.user-dialog-tabs :deep(.v-tab--selected) {
    background-color: rgba(var(--v-theme-primary), 0.12);
    color: rgb(var(--v-theme-primary));
    font-weight: 600;
}

.user-dialog-tabs :deep(.v-tab--selected .v-icon) {
    color: rgb(var(--v-theme-primary));
}

.user-dialog-tabs :deep(.v-tabs-slider) {
    height: 3px;
    border-radius: 2px 2px 0 0;
}

.user-dialog-tabs :deep(.v-tab__content) {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>