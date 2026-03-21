<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index()
    {
        return response()->json(Certificate::with(['student', 'template'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'template_id' => 'required|exists:certificate_templates,id',
            'course_name' => 'required|string',
            'instructor_name' => 'required|string',
            'issue_date' => 'required|date',
        ]);

        $validated['code'] = Str::upper(Str::random(10));

        $certificate = Certificate::create($validated);
        return response()->json($certificate, Response::HTTP_CREATED);
    }

    public function show(Certificate $certificate)
    {
        return response()->json($certificate->load(['student', 'template']));
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
