<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Models\SchoolPaymentPlan;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $query = Payment::whereHas('student', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->with(['student', 'schoolPaymentPlan', 'paymentMethod']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->query('student_id'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('due_date', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('due_date', '<=', $request->query('end_date'));
        }

        $payments = $query->get();

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_payment_plan_id' => 'nullable|exists:school_payment_plans,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'paid_date' => 'nullable|date',
            'status' => 'in:pending,paid,late,canceled',
            'description' => 'nullable|string',
        ]);

        $schoolId = Auth::user()->school_id;

        // Verify student belongs to school
        $student = Student::where('id', $validated['student_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Verify payment plan belongs to school (if provided)
        if ($validated['school_payment_plan_id']) {
            SchoolPaymentPlan::where('id', $validated['school_payment_plan_id'])
                ->where('school_id', $schoolId)
                ->firstOrFail();
        }

        // Verify payment method belongs to school (if provided)
        if ($validated['payment_method_id']) {
            PaymentMethod::where('id', $validated['payment_method_id'])
                ->where('school_id', $schoolId)
                ->firstOrFail();
        }

        $payment = Payment::create($validated);

        return response()->json($payment, Response::HTTP_CREATED);
    }

    public function show(Payment $payment)
    {
        return response()->json($payment);
    }

    public function update(Request $request, Payment $payment)
    {
        // Cannot edit if already paid
        if ($payment->status === 'paid') {
            return response()->json([
                'message' => 'Cannot edit a paid payment',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'school_payment_plan_id' => 'nullable|exists:school_payment_plans,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'numeric|min:0.01',
            'due_date' => 'date',
            'status' => 'in:pending,paid,late,canceled',
            'description' => 'nullable|string',
        ]);

        $schoolId = Auth::user()->school_id;

        // Verify payment plan belongs to school (if provided)
        if ($validated['school_payment_plan_id'] ?? null) {
            SchoolPaymentPlan::where('id', $validated['school_payment_plan_id'])
                ->where('school_id', $schoolId)
                ->firstOrFail();
        }

        // Verify payment method belongs to school (if provided)
        if ($validated['payment_method_id'] ?? null) {
            PaymentMethod::where('id', $validated['payment_method_id'])
                ->where('school_id', $schoolId)
                ->firstOrFail();
        }

        $payment->update($validated);

        return response()->json($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function markAsPaid(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'paid_date' => 'nullable|date',
        ]);

        $paidDate = $validated['paid_date'] ?? null;
        $payment->markAsPaid($paidDate);

        return response()->json($payment);
    }
}
