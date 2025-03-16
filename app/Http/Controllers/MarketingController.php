<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeveloperRequest; // تأكد من استخدام الطلب الصحيح
use App\Http\Resources\MarketingResource;
use App\Models\Marketing;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MarketingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // تأكد من أنك مسجل الدخول
    }

    public function index(): JsonResponse
    {
        try {
            $marketingProjects = Marketing::all();

            if ($marketingProjects->isEmpty()) {
                return response()->json(['message' => 'لا توجد مشاريع تسويقية متاحة'], 404);
            }

            return response()->json([
                'message' => 'تم جلب المشاريع التسويقية بنجاح',
                'data' => MarketingResource::collection($marketingProjects)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشاريع التسويقية', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(DeveloperRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // رفع ملف الـ summary إذا كان موجودًا
            if ($request->hasFile('summary')) {
                $data['summary'] = $request->file('summary')->store('summary', 'public');
            }

            $marketing = Marketing::create($data);

            // إضافة الفريق عبر الجدول الوسيط
            if ($request->has('team')) {
                $marketing->users()->sync($request->team);
            }

            // جلب المستخدمين الذين ينتمون إلى المشروع بصيغة label, value فقط
            $team = $marketing->users()
                ->select('users.id as value', 'users.name as label') // ✅ تحديد الأعمدة المطلوبة فقط
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => $user->value,
                        'label' => $user->label
                    ]; // ✅ تأكيد إزالة أي بيانات إضافية مثل `roles`
                });

            return response()->json([
                'message' => 'تم إنشاء المشروع التسويقي بنجاح',
                'data' => new MarketingResource($marketing),
                'team' => $team, // ✅ الآن ستكون فقط `value` و `label`
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المشروع التسويقي',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Marketing $marketing): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'تم جلب المشروع التسويقي بنجاح',
                'data' => new MarketingResource($marketing)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشروع التسويقي', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(DeveloperRequest $request, Marketing $marketing): JsonResponse
    {
        try {
            $data = $request->validated();

            // رفع ملف الـ summary إذا كان موجودًا
            if ($request->hasFile('summary')) {
                $data['summary'] = $request->file('summary')->store('summary', 'public');
            }

            $marketing->update($data);

            // تحديث الفريق
            if ($request->has('team')) {
                $marketing->users()->sync($request->team);
            }

            return response()->json([
                'message' => 'تم تحديث المشروع التسويقي بنجاح',
                'data' => new MarketingResource($marketing)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء تحديث المشروع التسويقي', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Marketing $marketing): JsonResponse
    {
        try {
            $marketing->delete();

            return response()->json(['message' => 'تم حذف المشروع التسويقي بنجاح'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء حذف المشروع التسويقي', 'error' => $e->getMessage()], 500);
        }
    }

   
}
