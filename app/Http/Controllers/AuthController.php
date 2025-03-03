<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Exception;

class AuthController extends Controller
{
    // تسجيل مستخدم جديد (فقط super_admin يمكنه إنشاء مستخدمين)
    public function register(Request $request)
    {
        try {
            // تحقق من أن المستخدم الحالي هو super_admin
            if (auth()->user()->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // التحقق من البيانات المدخلة
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role' => 'required|in:super_admin,team_lead,hr,user',
            ]);

            // إنشاء المستخدم
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // تعيين الدور إذا كان موجودًا
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            return response()->json(['message' => 'User registered successfully'], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error in user registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials.'],
                ]);
            }

            return response()->json([
                'token' => $user->createToken('authToken')->plainTextToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // تسجيل الخروج
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error in logout process',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
