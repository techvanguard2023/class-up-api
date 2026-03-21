<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClassSessionController extends Controller
{
    public function index()
    {
        return response()->json(ClassSession::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days' => 'required|array',
            'capacity' => 'required|integer',
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
            'capacity' => 'integer',
            'modality' => 'string',
            'color' => 'nullable|string',
        ]);

        $classSession->update($validated);
        return response()->json($classSession);
    }

    public function destroy(ClassSession $classSession)
    {
        $classSession->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
