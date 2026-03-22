<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'update users',
            'delete users',

            // Student management
            'view students',
            'create students',
            'update students',
            'delete students',
            'export students',

            // Branch management
            'view branches',
            'create branches',
            'update branches',
            'delete branches',

            // School year management
            'view school years',
            'create school years',
            'update school years',
            'delete school years',
            'activate school year',

            // Grade level management
            'view grade levels',
            'create grade levels',
            'update grade levels',
            'delete grade levels',

            // Section management
            'view sections',
            'create sections',
            'update sections',
            'delete sections',

            // Subject management
            'view subjects',
            'create subjects',
            'update subjects',
            'delete subjects',

            // Enrollment management
            'view enrollments',
            'create enrollments',
            'update enrollments',
            'delete enrollments',
            'approve enrollments',
            'reject enrollments',

            // Enrollment application management
            'view enrollment applications',
            'create enrollment applications',
            'update enrollment applications',
            'delete enrollment applications',
            'review enrollment applications',

            // Grade management
            'view grades',
            'create grades',
            'update grades',
            'delete grades',
            'import grades',
            'export grades',

            // Payment management
            'view payments',
            'create payments',
            'update payments',
            'delete payments',
            'export payments',
            'view receipts',
            'print receipts',

            // Payment utility management
            'view payment utilities',
            'create payment utilities',
            'update payment utilities',
            'delete payment utilities',

            // Requirement management
            'view requirements',
            'create requirements',
            'update requirements',
            'delete requirements',
            'verify requirements',

            // Account management
            'view accounts',
            'update accounts',

            // Report generation
            'generate report cards',
            'generate financial reports',
            'generate enrollment reports',

            // Activity log
            'view activity log',

            // Settings
            'manage settings',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Administrator - full access
        $admin = Role::firstOrCreate(['name' => 'administrator']);
        $admin->givePermissionTo(Permission::all());

        // Staff - access to students, enrollments, grades, and payments
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'view students', 'create students', 'update students', 'export students',
            'view enrollments', 'create enrollments', 'update enrollments',
            'view enrollment applications', 'update enrollment applications', 'review enrollment applications',
            'view grades', 'create grades', 'update grades', 'import grades', 'export grades',
            'view payments', 'create payments', 'view receipts', 'print receipts',
            'view payment utilities',
            'view requirements', 'verify requirements',
            'view accounts',
            'view sections', 'view subjects', 'view grade levels',
            'generate report cards',
        ]);

        // Branch Manager - limited to their branch
        $branchManager = Role::firstOrCreate(['name' => 'branch_manager']);
        $branchManager->givePermissionTo([
            'view students', 'create students', 'update students', 'export students',
            'view enrollments', 'create enrollments', 'update enrollments',
            'view enrollment applications', 'review enrollment applications',
            'view grades', 'create grades', 'update grades',
            'view payments', 'create payments', 'view receipts', 'print receipts',
            'view payment utilities',
            'view requirements', 'verify requirements',
            'view accounts',
            'view sections', 'view subjects', 'view grade levels',
            'generate report cards', 'generate financial reports', 'generate enrollment reports',
        ]);

        // Student - very limited access (portal only)
        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'view grades',
            'view payments',
            'view requirements',
        ]);
    }
}
