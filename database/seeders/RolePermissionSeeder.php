<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // General Permissions
            ['name' => 'Access Dashboard', 'slug' => 'access-dashboard', 'group' => 'general', 'description' => 'Access to admin dashboard'],
            
            // Product Management Permissions
            ['name' => 'View Products', 'slug' => 'view-products', 'group' => 'products', 'description' => 'View product list'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'group' => 'products', 'description' => 'Create new products'],
            ['name' => 'Edit Products', 'slug' => 'edit-products', 'group' => 'products', 'description' => 'Edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'group' => 'products', 'description' => 'Delete products'],
            ['name' => 'Manage Categories', 'slug' => 'manage-categories', 'group' => 'products', 'description' => 'Manage product categories'],
            ['name' => 'Manage Units', 'slug' => 'manage-units', 'group' => 'products', 'description' => 'Manage product units'],

            // Warehouse / Stock
            ['name' => 'View Warehouses', 'slug' => 'view-warehouses', 'group' => 'warehouses', 'description' => 'View warehouse list'],
            ['name' => 'Manage Warehouses', 'slug' => 'manage-warehouses', 'group' => 'warehouses', 'description' => 'Create, edit, delete warehouses'],
            ['name' => 'View Stock Levels', 'slug' => 'view-stock-levels', 'group' => 'warehouses', 'description' => 'View current stock levels'],
            ['name' => 'View Stock Ledger', 'slug' => 'view-stock-ledger', 'group' => 'reports', 'description' => 'View complete stock movement history'],

            // Purchase (stock in)
            ['name' => 'View Purchases', 'slug' => 'view-purchases', 'group' => 'purchases', 'description' => 'View purchase records'],
            ['name' => 'Create Purchase', 'slug' => 'create-purchase', 'group' => 'purchases', 'description' => 'Create purchase invoices'],
            ['name' => 'Manage Suppliers', 'slug' => 'manage-suppliers', 'group' => 'purchases', 'description' => 'Manage supplier information'],

            // Sales (POS)
            ['name' => 'View Sales', 'slug' => 'view-sales', 'group' => 'sales', 'description' => 'View sales records'],
            ['name' => 'Create Sale', 'slug' => 'create-sale', 'group' => 'sales', 'description' => 'Create sales invoices'],
            ['name' => 'Manage Customers', 'slug' => 'manage-customers', 'group' => 'sales', 'description' => 'Manage customer information'],

            // Payments / dues
            ['name' => 'View Payments', 'slug' => 'view-payments', 'group' => 'payments', 'description' => 'View payment records'],
            ['name' => 'Record Payment', 'slug' => 'record-payment', 'group' => 'payments', 'description' => 'Record customer/supplier payments'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'view-reports', 'group' => 'reports', 'description' => 'View reports'],
            ['name' => 'Export Reports', 'slug' => 'export-reports', 'group' => 'reports', 'description' => 'Export reports to various formats'],

            // User & Role Permissions
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'group' => 'users', 'description' => 'Create, edit, delete users'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'group' => 'users', 'description' => 'Create, edit, delete roles and permissions'],

            // Settings Permissions
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group' => 'settings', 'description' => 'Manage system settings'],

            // Upload Permissions
            ['name' => 'Upload Files', 'slug' => 'upload-files', 'group' => 'general', 'description' => 'Upload images and files'],

            // System Permissions
            ['name' => 'View Login Logs', 'slug' => 'view-login-logs', 'group' => 'system', 'description' => 'View and manage login logs'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['slug']] = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full system access - manage users, roles, warehouse access, and configure master data',
                'is_system' => true,
                'is_active' => true,
                'order' => 1,
                'permissions' => array_keys($createdPermissions), // All permissions
            ],
            [
                'name' => 'Cashier',
                'slug' => 'cashier',
                'description' => 'Handle sales and customer payments',
                'is_system' => true,
                'is_active' => true,
                'order' => 2,
                'permissions' => [
                    'access-dashboard',
                    'view-products',
                    'view-warehouses',
                    'view-sales',
                    'create-sale',
                    'manage-customers',
                    'view-payments',
                    'record-payment',
                    'view-reports',
                    'upload-files',
                ],
            ],
            [
                'name' => 'Storekeeper',
                'slug' => 'storekeeper',
                'description' => 'Manage stock, purchases, and products',
                'is_system' => true,
                'is_active' => true,
                'order' => 3,
                'permissions' => [
                    'access-dashboard',
                    'view-products',
                    'create-products',
                    'edit-products',
                    'delete-products',
                    'manage-categories',
                    'manage-units',
                    'view-warehouses',
                    'manage-warehouses',
                    'view-stock-levels',
                    'view-stock-ledger',
                    'view-purchases',
                    'create-purchase',
                    'manage-suppliers',
                    'view-payments',
                    'record-payment',
                    'view-reports',
                    'upload-files',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Attach permissions
            $permissionIds = [];
            foreach ($permissions as $permissionSlug) {
                if (isset($createdPermissions[$permissionSlug])) {
                    $permissionIds[] = $createdPermissions[$permissionSlug]->id;
                }
            }
            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
