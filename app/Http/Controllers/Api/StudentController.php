<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function index()
    {
        return response()->json(Student::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'modality' => 'required|string',
            'level' => 'required|string',
            'status' => 'in:active,inactive,suspended',
            'birth_date' => 'nullable|date',
            'guardian_name' => 'nullable|string',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string',
            'health_info' => 'nullable|array',
        ]);

        $student = Student::create($validated);
        return response()->json($student, Response::HTTP_CREATED);
    }

    public function show(Student $student)
    {
        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:students,email,' . $student->id,
            'modality' => 'string',
            'level' => 'string',
            'status' => 'in:active,inactive,suspended',
            'birth_date' => 'nullable|date',
            'guardian_name' => 'nullable|string',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string',
            'health_info' => 'nullable|array',
        ]);

        $student->update($validated);
        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
