<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeveloperRequest;
use App\Http\Resources\DeveloperResource;
use Illuminate\Http\Request;
use App\Models\Developer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Exception;

class DeveloperController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // تأكد من أنك مسجل الدخول
    }

    public function index(): JsonResponse
    {
        try {
            $developers = Developer::all();

            if ($developers->isEmpty()) {
                return response()->json(['message' => 'لا توجد مشاريع متاحة'], 404);
            }

            return response()->json([
                'message' => 'تم جلب المشاريع بنجاح',
                'data' => DeveloperResource::collection($developers)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشاريع', 'error' => $e->getMessage()], 500);
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

            $developer = Developer::create($data);

            $developer->users()->sync($request->team);

            $team = $developer->users()
                ->select('users.id as value', 'users.name as label')
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => $user->value,
                        'label' => $user->label
                    ];
                });

            return response()->json([
                'message' => 'تم إنشاء المشروع بنجاح',
                'data' => new DeveloperResource($developer),
                'team' => $team, // ✅ الآن ستكون فقط `value` و `label`
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المشروع',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Developer $developer): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'تم جلب المشروع بنجاح',
                'data' => new DeveloperResource($developer)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشروع', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Developer $developer): JsonResponse
    {
        try {
            $developer->delete();
            return response()->json(['message' => 'تم حذف المشروع بنجاح'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء حذف المشروع', 'error' => $e->getMessage()], 500);
        }
    }


}
