<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstructorController extends Controller
{
    /**
     * List all instructors for the authenticated user's school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $instructors = Instructor::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $instructors]);
    }

    /**
     * Create a new instructor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:instructors,email,' . $request->user()->school_id . ',school_id',
            'phone' => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $instructor = Instructor::create([
            'school_id' => $request->user()->school_id,
            ...$validated,
        ]);

        return response()->json($instructor, Response::HTTP_CREATED);
    }

    /**
     * Get a specific instructor.
     */
    public function show(Instructor $instructor)
    {
        return response()->json($instructor);
    }

    /**
     * Update an instructor.
     */
    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:instructors,email,' . $instructor->id,
            'phone' => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $instructor->update($validated);
        return response()->json($instructor);
    }

    /**
     * Delete an instructor.
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
