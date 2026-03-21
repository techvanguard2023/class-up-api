<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return response()->json(Attendance::with(['classSession', 'student'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_session_id' => 'required|exists:class_sessions,id',
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        $attendance = Attendance::create($validated);
        return response()->json($attendance, 201);
    }

    public function show(Attendance $attendance)
    {
        return response()->json($attendance->load(['classSession', 'student']));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'class_session_id' => 'exists:class_sessions,id',
            'student_id' => 'exists:students,id',
            'date' => 'date',
            'status' => 'string',
        ]);

        $attendance->update($validated);
        return response()->json($attendance);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(null, 204);
    }
}
