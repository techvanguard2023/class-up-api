<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        return response()->json(Classroom::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'year' => 'nullable|integer',
            'shift' => 'nullable|string',
            'level' => 'nullable|string',
        ]);

        $classroom = Classroom::create($validated);
        return response()->json($classroom, 201);
    }

    public function show(Classroom $classroom)
    {
        return response()->json($classroom);
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'school_id' => 'exists:schools,id',
            'name' => 'string',
            'capacity' => 'integer|min:1',
            'year' => 'integer',
            'shift' => 'string',
            'level' => 'string',
        ]);

        $classroom->update($validated);
        return response()->json($classroom);
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return response()->json(null, 204);
    }
}
