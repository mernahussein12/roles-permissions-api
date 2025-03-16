<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Exception;

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

    public function getTeamByDepartment(Request $request): JsonResponse
{
    try {
        // التحقق من وجود قسم في الطلب
        if (!$request->has('department')) {
            return response()->json([
                'message' => 'يرجى تحديد القسم المطلوب'
            ], 400);
        }

        $department = $request->department;

        // جلب جميع المستخدمين الذين ينتمون إلى القسم المحدد
        $team = User::where('department', $department)
            ->select('id as value', 'name as label')
            ->get();

        return response()->json([
            'message' => "تم جلب الفريق بنجاح للقسم: $department",
            'team' => $team
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء جلب الفريق',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getTeamLeaders(Request $request)
{
    $department = $request->query('department');

    if (!$department) {
        return response()->json([
            'message' => 'يجب تحديد القسم المطلوب',
            'team_leaders' => []
        ], 400);
    }

    $teamLeaders = User::where('department', $department)
        ->whereIn('role', ['team_lead', 'Leader'])
        ->select('id as value', 'name as label', 'role')
        ->get()
        ->map(function ($user) {
            return [
                'value' => $user->value,
                'label' => $user->label,
                'role' => $user->role
            ];
        });

    return response()->json([
        'message' => "تم جلب القادة بنجاح للقسم: $department",
        'team_leaders' => $teamLeaders
    ], 200);
}



}

