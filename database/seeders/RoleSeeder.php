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
            'view users'
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
    }
}
