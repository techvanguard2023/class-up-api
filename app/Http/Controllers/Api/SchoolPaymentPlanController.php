<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolPaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SchoolPaymentPlanController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        $plans = SchoolPaymentPlan::where('school_id', $schoolId)
            ->get();

        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'due_day' => 'required|integer|min:1|max:31',
            'active' => 'boolean',
        ]);

        $schoolId = Auth::user()->school_id;
        $validated['school_id'] = $schoolId;

        $plan = SchoolPaymentPlan::create($validated);

        return response()->json($plan, Response::HTTP_CREATED);
    }

    public function show(SchoolPaymentPlan $schoolPaymentPlan)
    {
        return response()->json($schoolPaymentPlan);
    }

    public function update(Request $request, SchoolPaymentPlan $schoolPaymentPlan)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0.01',
            'due_day' => 'integer|min:1|max:31',
            'active' => 'boolean',
        ]);

        $schoolPaymentPlan->update($validated);

        return response()->json($schoolPaymentPlan);
    }

    public function destroy(SchoolPaymentPlan $schoolPaymentPlan)
    {
        $schoolPaymentPlan->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
