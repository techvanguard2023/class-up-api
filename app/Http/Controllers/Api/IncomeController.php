<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json(Income::where('school_id', $user->school_id)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'received_from' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = $request->user()->school_id;
        $income = Income::create($validated);
        return response()->json($income, Response::HTTP_CREATED);
    }

    public function show(Income $income)
    {
        return response()->json($income);
    }

    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'description' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'category' => 'string|max:255',
            'date' => 'date',
            'payment_method' => 'nullable|string|max:255',
            'received_from' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $income->update($validated);
        return response()->json($income);
    }

    public function destroy(Income $income)
    {
        $income->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}