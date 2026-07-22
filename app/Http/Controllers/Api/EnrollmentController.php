<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['student', 'classroom']);

        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->query('classroom_id'));
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->query('student_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'status' => 'string',
            'year' => 'required|integer',
        ]);

        // Resolve within the current school (global scope hides other schools).
        $student = Student::find($validated['student_id']);
        $classroom = Classroom::find($validated['classroom_id']);

        if (!$student || !$classroom) {
            throw ValidationException::withMessages([
                'student_id' => ['Aluno ou turma não pertence à sua escola.'],
            ]);
        }

        // Prevent enrolling the same student twice in the same classroom.
        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('classroom_id', $classroom->id)
            ->exists();

        if ($alreadyEnrolled) {
            throw ValidationException::withMessages([
                'student_id' => ['Este aluno já está matriculado nesta turma.'],
            ]);
        }

        // Enforce classroom capacity.
        if ($classroom->enrolled >= $classroom->capacity) {
            throw ValidationException::withMessages([
                'classroom_id' => ['A turma atingiu a capacidade máxima.'],
            ]);
        }

        $enrollment = Enrollment::create($validated);
        return response()->json($enrollment, 201);
    }

    public function show(Enrollment $enrollment)
    {
        return response()->json($enrollment->load(['student', 'classroom', 'grades']));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'student_id' => 'exists:students,id',
            'classroom_id' => 'exists:classrooms,id',
            'status' => 'string',
            'year' => 'integer',
        ]);

        $enrollment->update($validated);
        return response()->json($enrollment);
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return response()->json(null, 204);
    }
}
