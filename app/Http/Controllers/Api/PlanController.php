<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::where('active', true)
            ->with('features')
            ->get()
            ->map(function ($plan) {
            return [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'price' => $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'color' => $plan->color,
            'features' => ($plan->features ?? collect())->map(fn($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'description' => $f->description,
            'limit' => $f->pivot->limit,
            ])->values(),
            ];
        });

        return response()->json(['data' => $plans]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'billing_cycle' => 'required|in:monthly,annual',
            'features' => 'nullable|array',
            'active' => 'boolean',
            'color' => 'nullable|string',
        ]);

        $plan = Plan::create($validated);
        return response()->json($plan, Response::HTTP_CREATED);
    }

    public function show(Plan $plan)
    {
        return response()->json($plan);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric',
            'billing_cycle' => 'in:monthly,annual',
            'features' => 'nullable|array',
            'active' => 'boolean',
            'color' => 'nullable|string',
        ]);

        $plan->update($validated);
        return response()->json($plan);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
