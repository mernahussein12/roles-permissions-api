<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // إنشاء الصلاحيات
        $permissions = [
            'create users',
            'edit users',
            'delete users',
            'view users',
            'manage projects',
            'assign tasks',
            'approve requests',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // إنشاء الأدوار
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $teamLead = Role::firstOrCreate(['name' => 'team_lead']);
        $hr = Role::firstOrCreate(['name' => 'hr']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // إعطاء Super Admin كافة الصلاحيات
        $superAdmin->givePermissionTo(Permission::all());

        // إعطاء Team Lead صلاحيات معينة
        $teamLead->givePermissionTo(['view users', 'assign tasks']);

        // إعطاء HR صلاحيات معينة
        $hr->givePermissionTo(['view users', 'approve requests']);

        // إعطاء User صلاحية محددة
        $user->givePermissionTo(['view users']);
    }
}

