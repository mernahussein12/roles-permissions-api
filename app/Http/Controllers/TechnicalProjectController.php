<?php

namespace App\Http\Controllers;

use App\Models\TechnicalProject;
use Illuminate\Http\Request;

class TechnicalProjectController extends Controller
{
    // عرض جميع المشاريع
    public function index()
    {
        $projects = TechnicalProject::all();
        return response()->json([
            'status' => true,
            'message' => 'تم جلب المشاريع بنجاح',
            'data' => $projects
        ]);
    }

    // تخزين مشروع جديد
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'summary' => 'nullable|file|mimes:pdf|max:5120', // السماح فقط بملفات PDF بحجم أقصى 5MB
            'cost' => 'required|numeric',
        ]);

        $data = $request->all();

        if ($request->hasFile('summary')) {
            $data['summary'] = $request->file('summary')->store('summary', 'public');
        }

        $project = TechnicalProject::create($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة المشروع بنجاح',
            'data' => $project
        ], 201);
    }

    // عرض مشروع واحد
    public function show($id)
    {
        $project = TechnicalProject::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'المشروع غير موجود'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم جلب بيانات المشروع بنجاح',
            'data' => $project
        ]);
    }

    public function update(Request $request, $id)
    {
        $project = TechnicalProject::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'المشروع غير موجود'
            ], 404);
        }

        $request->validate([
            'name_en' => 'sometimes|string',
            'name_ar' => 'sometimes|string',
            'type' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'summary' => 'nullable|file|mimes:pdf|max:5120',
            'cost' => 'sometimes|numeric',
        ]);

        $data = array_filter($request->except('summary')); // استبعاد القيم الفارغة

        if ($request->hasFile('summary')) {
            $data['summary'] = $request->file('summary')->store('summary', 'public');
        }

        $project->update($data);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث المشروع بنجاح',
            'data' => $project
        ], 200);
    }


    // حذف المشروع
    public function destroy($id)
    {
        $project = TechnicalProject::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'المشروع غير موجود'
            ], 404);
        }

        $project->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف المشروع بنجاح'
        ]);
    }
}
