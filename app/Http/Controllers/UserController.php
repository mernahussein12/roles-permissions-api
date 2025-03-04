<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
        ]);

        // إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role_id' => $request->role_id,
        ]);

        // إسناد الدور للمستخدم
        $role = Role::find($request->role_id);
        if ($role) {
            $user->assignRole($role->name);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->getRoleNames()->first() // استرجاع اسم الدور
            ],
        ], 201);
    }

    public function index()
    {
        // جلب جميع المستخدمين مع أدوارهم، باستثناء "super_admin"
        $users = User::with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            })
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }

    public function show($id)
    {
        // جلب المستخدم مع أدواره
        $user = User::with('roles')->find($id);

        // التحقق مما إذا كان المستخدم موجودًا
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'user' => $user
        ]);
    }


    // جلب جميع الأدوار لاستخدامها في السيلكت
    public function getRoles()
    {
        $roles = Role::all(['id', 'name']);
        return response()->json($roles);
    }
}

