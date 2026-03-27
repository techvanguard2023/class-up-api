<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentPaymentPlan;
use App\Models\Student;
use App\Models\SchoolPaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class StudentPaymentPlanController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        $studentPaymentPlans = StudentPaymentPlan::whereHas('student', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->with(['student', 'schoolPaymentPlan'])->get();

        return response()->json($studentPaymentPlans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_payment_plan_id' => 'required|exists:school_payment_plans,id',
            'due_day' => 'nullable|integer|between:1,31',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'active' => 'boolean',
        ]);

        $schoolId = Auth::user()->school_id;

        // Verify student belongs to school
        $student = Student::where('id', $validated['student_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Verify payment plan belongs to school
        $plan = SchoolPaymentPlan::where('id', $validated['school_payment_plan_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Check if student already has an active plan (max 1 active per student)
        if ($validated['active'] ?? false) {
            $existingActive = StudentPaymentPlan::where('student_id', $student->id)
                ->where('active', true)
                ->exists();

            if ($existingActive) {
                return response()->json([
                    'message' => 'Student already has an active payment plan',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $studentPaymentPlan = StudentPaymentPlan::create($validated);

        return response()->json($studentPaymentPlan, Response::HTTP_CREATED);
    }

    public function show(StudentPaymentPlan $studentPaymentPlan)
    {
        return response()->json($studentPaymentPlan);
    }

    public function update(Request $request, StudentPaymentPlan $studentPaymentPlan)
    {
        $validated = $request->validate([
            'due_day' => 'nullable|integer|between:1,31',
            'active' => 'boolean',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // If activating, check max 1 active rule
        if ($validated['active'] ?? false) {
            $existingActive = StudentPaymentPlan::where('student_id', $studentPaymentPlan->student_id)
                ->where('id', '!=', $studentPaymentPlan->id)
                ->where('active', true)
                ->exists();

            if ($existingActive) {
                return response()->json([
                    'message' => 'Student already has an active payment plan',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $studentPaymentPlan->update($validated);

        return response()->json($studentPaymentPlan);
    }

    public function destroy(StudentPaymentPlan $studentPaymentPlan)
    {
        $studentPaymentPlan->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
