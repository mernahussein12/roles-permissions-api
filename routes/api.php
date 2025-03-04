<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // السماح فقط لـ Super Admin بإنشاء المستخدمين
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        // Route::post('/roles', [RoleController::class, 'store']); // إضافة دور جديد
        // Route::get('/roles', [RoleController::class, 'index']); // عرض جميع الأدوار
        Route::apiResource('/roles', RoleController::class);
        Route::get('/permissions', [RoleController::class, 'getPermissions']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/role', [UserController::class, 'getRoles']); // جلب الأدوار للسيلكت
      Route::get('/users', [UserController::class, 'index']);
      Route::get('/users/{id}', [UserController::class, 'show']);


    });
});
