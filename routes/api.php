<?php

use App\Http\Controllers\FinanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\HostingProjectController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TechnicalProjectController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // السماح فقط لـ Super Admin بإنشاء المستخدمين
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::apiResource('/roles', RoleController::class);
        Route::get('/permissions', [RoleController::class, 'getPermissions']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/role', [UserController::class, 'getRoles']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::apiResource('technical-projects', TechnicalProjectController::class);
        Route::apiResource('hosting-projects', HostingProjectController::class);
        Route::get('/requests/{id}/history', [RequestController::class, 'requestHistory']);
        Route::get('/requests/{id}/history/download', [RequestController::class, 'downloadRequestHistory']);
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::get('/developer', [DeveloperController::class, 'index']);
        Route::get('/developer/{developer}', [DeveloperController::class, 'show']);
        Route::get('/marketing', [MarketingController::class, 'index']);
        Route::get('/marketing/{marketing}', [MarketingController::class, 'show']);
        Route::get('/finance', [FinanceController::class, 'index']);
        Route::post('/projects/finance', [FinanceController::class, 'storeProject']);
        Route::post('/expenses', [FinanceController::class, 'storeExpense']);

    });
    Route::middleware(['auth:sanctum', 'check_team_lead_department:sales'])->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::get('sales/reports', [RequestController::class, 'salesReports']);
        Route::post('/requests', [RequestController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'check_team_lead_department:developer'])->group(function () {
        Route::apiResource('developer', DeveloperController::class);
    });
    Route::middleware(['auth:sanctum', 'check_team_lead_department:marketing'])->group(function () {
        Route::apiResource('marketing', MarketingController::class);
    });

    Route::get('projects/{id}/history', [ProjectController::class, 'history']);

    // عرض الطلبات الخاصة بالتيم ليدر
    Route::get('/requests', [RequestController::class, 'index']);

    //  // قبول أو رفض الطلب
    Route::put('/requests/{teamLeadRequest}', [RequestController::class, 'update']);
    Route::delete('/requests/{teamLeadRequest}', [RequestController::class, 'destroy']);

    Route::get('/team-by-department', [UserController::class, 'getTeamByDepartment']);
    Route::get('/team-leaders', [UserController::class, 'getTeamLeaders']);
    Route::get('/projects/{id}/download', [ProjectController::class, 'generateProjectPDF']);
    Route::get('/requests/{id}/history/pdf', [RequestController::class, 'downloadHistoryPdf']);


});
