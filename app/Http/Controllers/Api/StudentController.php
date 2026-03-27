<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->school_id) {
            return response()->json(['message' => 'Usuário não está vinculado a nenhuma escola.'], 403);
        }

        $students = Student::where('school_id', $user->school_id)->get();
        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'modality' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'guardian_relationship' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Ensure user belongs to a school
        if (!$user->school_id) {
            return response()->json(['message' => 'Usuário não está vinculado a nenhuma escola.'], 403);
        }

        return DB::transaction(function () use ($request, $user) {
            // 1. Create Student
            $student = Student::create([
                'name' => $request->name,
                'modality' => $request->modality,
                'level' => $request->level,
                'birth_date' => $request->birth_date,
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'status' => 'active',
            ]);

            // 2. Create or get Guardian for the authenticated user
            $guardian = Guardian::firstOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $user->school_id,
            ],
            [
                'name' => $user->name . ' ' . $user->last_name,
                'phone' => $user->phone ?? null,
            ]
            );

            // 3. Link Guardian to Student
            $guardian->students()->attach($student->id, [
                'relationship' => $request->guardian_relationship ?? 'responsável',
            ]);

            return response()->json([
                'message' => 'Aluno cadastrado com sucesso.',
                'student' => $student->load('guardians'),
            ], Response::HTTP_CREATED);
        });
    }

    public function show(Request $request, Student $student)
    {
        $user = $request->user();

        // Check if user is guardian of this student or if user is the student
        $isGuardian = $student->guardians()->where('user_id', $user->id)->exists();
        $isStudent = $student->user_id === $user->id;

        if (!$isGuardian && !$isStudent) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return response()->json($student->load('guardians'));
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
