<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // السماح فقط لـ Super Admin بإنشاء المستخدمين
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/roles', [RoleController::class, 'store']); // إضافة دور جديد
        Route::get('/roles', [RoleController::class, 'index']); // عرض جميع الأدوار
        Route::get('/permissions', [RoleController::class, 'getPermissions']);
    });
});
