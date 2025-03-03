<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name_ar' => 'nullable|string|max:255',
            'display_name_en' => 'nullable|string|max:255',
            'permissions' => 'required|array', // يجب أن يكون مصفوفة من IDs
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name_ar' => $request->display_name_ar,
            'display_name_en' => $request->display_name_en,
        ]);

        // إسناد الصلاحيات للدور الجديد
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ], 201);
    }

    // عرض جميع الأدوار
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    // جلب جميع الصلاحيات للاستخدام في Select
    public function getPermissions()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }
}
