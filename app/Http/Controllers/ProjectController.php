<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProjectResource::collection(Project::all());
    }

    /**
     * Store a newly created resource in storage.
     */
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

        // رفع ملف PDF داخل `public/pdfs`
        if ($request->hasFile('price_offer')) {
            $file = $request->file('price_offer');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pdfs'), $filename);
            $data['price_offer'] = 'pdfs/' . $filename;
        }

        $project = Project::create($data);

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

         return new ProjectResource($project);
     }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
