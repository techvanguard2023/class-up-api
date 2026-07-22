<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClassroomController extends Controller
{
    public function index()
    {
        return response()->json(Classroom::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'instructor_id' => 'nullable|exists:instructors,id',
            'year' => 'nullable|integer',
            'shift' => 'nullable|string',
            'level' => 'nullable|string',
        ]);

        $this->assertInstructorInSchool($validated['instructor_id'] ?? null);

        // school_id is filled automatically by the BelongsToSchool trait.
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
            'name' => 'string',
            'capacity' => 'integer|min:1',
            'instructor_id' => 'nullable|exists:instructors,id',
            'year' => 'integer',
            'shift' => 'string',
            'level' => 'string',
        ]);

        if (array_key_exists('instructor_id', $validated)) {
            $this->assertInstructorInSchool($validated['instructor_id']);
        }

        $classroom->update($validated);
        return response()->json($classroom);
    }

    /**
     * Ensure the instructor (when provided) belongs to the caller's school.
     */
    private function assertInstructorInSchool(?int $instructorId): void
    {
        if ($instructorId === null) {
            return;
        }

        // Global scope limits this to the caller's school.
        if (!Instructor::find($instructorId)) {
            throw ValidationException::withMessages([
                'instructor_id' => ['O instrutor não pertence à sua escola.'],
            ]);
        }
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return response()->json(null, 204);
    }
}
