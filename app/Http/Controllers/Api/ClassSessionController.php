<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClassSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassSession::with('instructor');

        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->query('classroom_id'));
        }

        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->query('instructor_id'));
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->query('subject_id'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days' => 'required|array',
            'modality' => 'required|string',
            'color' => 'nullable|string',
        ]);

        $classSession = ClassSession::create($validated);
        return response()->json($classSession, Response::HTTP_CREATED);
    }

    public function show(ClassSession $classSession)
    {
        return response()->json($classSession);
    }

    public function update(Request $request, ClassSession $classSession)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i|after:start_time',
            'days' => 'array',
            'modality' => 'string',
            'color' => 'nullable|string',
        ]);

        $classSession->update($validated);
        return response()->json($classSession);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $class = ClassSession::find($id);

        if (!$class) {
            return response()->json(['message' => 'Aula não encontrada'], 404);
        }

        if ($class->school_id !== $user->school_id) {
            return response()->json(['message' => 'Você não tem permissão para excluir esta aula'], 403);
        }

        $class->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);


    }
}
