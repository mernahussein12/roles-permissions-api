<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Project;
use App\Models\TeamLeadRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    // إرسال طلب جديد من قسم المبيعات
    public function store(Request $request)
    {
        $request->validate([
            'team_lead_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $user = auth()->user();

        if (!($user->role === 'team_lead' && $user->department === 'sales')) {
            return response()->json(['message' => 'غير مصرح لك بإرسال الطلبات'], 403);
        }

        $newRequest = TeamLeadRequest::create([
            'sales_id' => $user->id,
            'team_lead_id' => $request->team_lead_id,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'تم إرسال الطلب بنجاح', 'request' => $newRequest], 201);
    }

    // public function index()
    // {
    //     $user = auth()->user();
    //     $requests = TeamLeadRequest::with('approver')->where('team_lead_id', $user->id)->get();

    //     return response()->json([
    //         'message' => 'تم جلب الطلبات بنجاح',
    //         'requests' => $requests->map(function ($request) {
    //             return [
    //                 'id' => $request->id,
    //                 'sales_id' => $request->sales_id,
    //                 'team_lead_id' => $request->team_lead_id,
    //                 'status' => $request->status,
    //                 'approved_by' => $request->approver ? $request->approver->name : 'لم يتم القبول بعد',
    //             ];
    //         }),
    //     ], 200);
    // }


    public function index()
{
    $user = auth()->user();

    // إذا كان المستخدم سوبر أدمن، يحصل على كل الطلبات، وإلا يحصل على طلباته فقط
    $requests = $user->role === 'super_admin'
        ? TeamLeadRequest::with('approver')->get()
        : TeamLeadRequest::with('approver')->where('team_lead_id', $user->id)->get();

    return response()->json([
        'message' => 'تم جلب الطلبات بنجاح',
        'requests' => $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'sales_id' => $request->sales_id,
                'team_lead_id' => $request->team_lead_id,
                'status' => $request->status,
                'approved_by' => $request->approver ? $request->approver->name : 'لم يتم القبول بعد',
            ];
        }),
    ], 200);
}

    public function update(Request $request, TeamLeadRequest $teamLeadRequest)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        // التحقق من تسجيل الدخول
        if (!auth()->check()) {
            return response()->json(['message' => 'يجب تسجيل الدخول'], 401);
        }

        $user = auth()->user();

        // التأكد أن المستخدم له صلاحية التعديل
        if ($user->id !== $teamLeadRequest->team_lead_id) {
            return response()->json(['message' => 'غير مصرح لك بتحديث هذا الطلب'], 403);
        }

        // تحديث حالة الطلب وتسجيل من قام بالموافقة
        $teamLeadRequest->update([
            'status' => $request->status,
            'approved_by' => $user->id,
        ]);

        // تسجيل التغيير في سجل التاريخ
        History::create([
            'request_id' => $teamLeadRequest->id,
            'user_id' => $user->id,
            'action' => $request->status,
        ]);

        // البحث عن المشروع المرتبط بالسيلز
        $project = Project::where('owner_number', $teamLeadRequest->sales_id)->first();

        if ($project) {
            $oldStatus = $project->status;
            $project->update(['status' => $request->status]);

            // تسجيل التغيير في تاريخ المشروع
            History::create([
                'project_id' => $project->id,
                'status' => $oldStatus,
                'changed_by' => filled($user->name) ? $user->name : 'System',
            ]);
        }

        return response()->json([
            'message' => 'تم تحديث حالة الطلب بنجاح، وتم تحديث المشروع المرتبط بالسيلز',
            'request' => $teamLeadRequest,
        ]);
    }


    // حذف الطلب
    public function destroy(TeamLeadRequest $teamLeadRequest)
    {
        $user = auth()->user();

        if ($user->id !== $teamLeadRequest->team_lead_id) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الطلب'], 403);
        }

        $teamLeadRequest->delete();

        return response()->json(['message' => 'تم حذف الطلب بنجاح']);
    }

    // public function salesReports()
    // {
    //     $user = auth()->user();

    //     if (!($user->role === 'team_lead' && $user->department === 'sales')) {
    //         return response()->json(['message' => 'غير مصرح لك بعرض التقارير'], 403);
    //     }

    //     $reports = TeamLeadRequest::where('sales_id', $user->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json(['message' => 'تم جلب التقارير بنجاح', 'requests' => $reports]);
    // }

    public function salesReports()
{
    $user = auth()->user();

    if (!($user->role === 'team_lead' && $user->department === 'sales')) {
        return response()->json(['message' => 'غير مصرح لك بعرض التقارير'], 403);
    }

    $reports = TeamLeadRequest::where('sales_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($report) {
            return [
                'id' => $report->id,
                'sales_name' => $report->sales->name ?? null, // اسم السيلز
                'team_lead_name' => $report->teamLead->name ?? null, // اسم التيم ليد
                'message' => $report->message, // الرسالة
                'status' => $report->status, // الحالة
                'approved_by' => $report->approved_by, // الشخص الذي وافق عليه
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
            ];
        });

    return response()->json(['message' => 'تم جلب التقارير بنجاح', 'requests' => $reports]);
}

    public function requestHistory($requestId)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'غير مصرح لك بعرض السجل'], 403);
        }

        $history = History::where('request_id', $requestId)->orderBy('created_at', 'desc')->get();

        if ($history->isEmpty()) {
            return response()->json(['message' => 'لا يوجد سجل لهذا الطلب'], 404);
        }

        return response()->json([
            'message' => 'تم جلب السجل بنجاح',
            'history' => $history
        ]);
    }

    public function downloadHistoryPdf($requestId)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'team_lead'])) {
            return response()->json(['message' => 'غير مصرح لك بتنزيل السجل'], 403);
        }

        $history = History::where('request_id', $requestId)->orderBy('created_at', 'desc')->get();

        if ($history->isEmpty()) {
            return response()->json(['message' => 'لا يوجد سجل لهذا الطلب'], 404);
        }

        // تحميل الـ View وتحويله إلى PDF
        $pdf = PDF::loadView('pdf.history', compact('history'));

        // تحديد مسار الحفظ في مجلد public
        $fileName = "request_history_{$requestId}.pdf";
        $filePath = public_path($fileName);

        // حفظ الملف في مجلد public
        $pdf->save($filePath);

        // إنشاء رابط للملف
        $fileUrl = asset($fileName);

        return response()->json([
            'message' => 'تم إنشاء ملف PDF بنجاح',
            'pdf_url' => $fileUrl
        ]);
    }


}
