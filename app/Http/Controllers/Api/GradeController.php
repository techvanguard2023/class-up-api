<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        return response()->json(Grade::with(['enrollment', 'subject'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'subject_id' => 'required|exists:subjects,id',
            'period' => 'required|string',
            'value' => 'required|numeric',
            'weight' => 'numeric',
        ]);

        $grade = Grade::create($validated);
        return response()->json($grade, 201);
    }

    public function show(Grade $grade)
    {
        return response()->json($grade->load(['enrollment', 'subject']));
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'enrollment_id' => 'exists:enrollments,id',
            'subject_id' => 'exists:subjects,id',
            'period' => 'string',
            'value' => 'numeric',
            'weight' => 'numeric',
        ]);

        $grade->update($validated);
        return response()->json($grade);
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return response()->json(null, 204);
    }
}
