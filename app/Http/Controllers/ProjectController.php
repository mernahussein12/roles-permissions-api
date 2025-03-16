<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{

    public function index()
    {
        return ProjectResource::collection(Project::all());
    }


    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'date' => 'required|date',
    //         'employee' => 'required|string',
    //         'owner_name' => 'required|string',
    //         'owner_number' => 'required|string',
    //         'owner_country' => 'required|string',
    //         'project_name' => 'required|string',
    //         'project_type' => 'required|string',
    //         'price_offer' => 'nullable|file|mimes:pdf|max:5120',
    //         'cost' => 'required|numeric',
    //         'initial_payment' => 'required|numeric',
    //         'profit_margin' => 'required|numeric',
    //         'hosting' => 'nullable|string',
    //         'technical_support' => 'nullable|string',
    //     ]);

    //     // تعيين الحالة الافتراضية إلى "pending"
    //     $data['status'] = 'pending';

    //     // رفع ملف PDF داخل `public/pdfs`
    //     if ($request->hasFile('price_offer')) {
    //         $file = $request->file('price_offer');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('pdfs'), $filename);
    //         $data['price_offer'] = 'pdfs/' . $filename;
    //     }

    //     $project = Project::create($data);

    //     return response()->json($project, 201);
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'employee' => 'required|string',
            'owner_name' => 'required|string',
            'owner_number' => 'required|string',
            'owner_country' => 'required|string',
            'project_name' => 'required|string',
            'project_type' => 'required|string',
            'price_offer' => 'nullable|file|mimes:pdf|max:5120',
            'cost' => 'required|numeric',
            'initial_payment' => 'required|numeric',
            'profit_margin' => 'required|numeric',
            'hosting' => 'nullable|string',
            'technical_support' => 'nullable|string',
        ]);

        $status = 'pending';

        if ($request->hasFile('price_offer')) {
            $file = $request->file('price_offer');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pdfs'), $filename);
            $data['price_offer'] = 'pdfs/' . $filename;
        }

        $project = Project::create($data);

        $project->status = $status;

        return response()->json($project, 201);
    }



    /**
     * Display the specified resource.
     */


    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'المشروع غير موجود'], 404);
        }

        // تعيين الحالة إلى "pending" إذا لم تكن موجودة
        if (!$project->status) {
            $project->status = 'pending';
        }

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);

        $oldStatus = $project->status;
        $newStatus = $request->input('status');

        $project->update($request->all());

        // تسجيل التغيير في السجل
        ProjectHistory::create([
            'project_id' => $project->id,
            'status' => $oldStatus,
            'changed_by' => auth()->user()->name ?? 'System',
        ]);

        return response()->json(['message' => 'تم تحديث المشروع بنجاح', 'project' => $project]);
    }

    public function generateProjectPDF($id)
{
    $user = Auth::user();

    // السماح فقط للسوبر أدمن أو التيم ليدر بتنزيل PDF
    if (!$user || !in_array($user->role, ['super_admin', 'team_lead'])) {
        return response()->json(['message' => 'غير مصرح لك بتنزيل هذا الملف'], 403);
    }

    $project = Project::findOrFail($id);

    // توليد PDF باستخدام DomPDF
    $pdf = Pdf::loadView('pdf.project', compact('project'));

    // تحديد مسار الحفظ في مجلد public
    $fileName = "project_{$project->id}.pdf";
    $filePath = public_path($fileName);

    // حفظ الملف في public مباشرة
    $pdf->save($filePath);

    // إنشاء رابط مباشر للتحميل
    $fileUrl = asset($fileName);

    return response()->json([
        'message' => 'تم إنشاء ملف PDF بنجاح',
        'download_url' => $fileUrl
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
