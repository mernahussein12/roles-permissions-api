<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // إنشاء دور جديد مع الصلاحيات
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
            'guard_name' => 'sanctum', // ✅ تأكد من استخدام `sanctum`
        ]);

        // إسناد الصلاحيات للدور الجديد
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ], 201);
    }

    // عرض جميع الأدوار مع الصلاحيات
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

    // تعديل الدور مع تحديث الصلاحيات
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'display_name_ar' => 'nullable|string|max:255',
            'display_name_en' => 'nullable|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'display_name_ar' => $request->display_name_ar,
            'display_name_en' => $request->display_name_en,
        ]);

        // تحديث الصلاحيات المرتبطة بالدور
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role
        ]);
    }

    public function destroy($roleId)
    {
        // جلب الدور مع الصلاحيات
        $role = Role::with('permissions')->findOrFail($roleId);

        // إزالة الصلاحيات المرتبطة بالدور قبل حذفه
        $role->syncPermissions([]);

        // حذف الدور
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }




}
