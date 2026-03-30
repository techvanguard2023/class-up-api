<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json(Expense::where('school_id', $user->school_id)->with(['user'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = $request->user()->school_id;
        $expense = Expense::create($validated);
        return response()->json($expense->load(['user']), Response::HTTP_CREATED);
    }

    public function show(Expense $expense)
    {
        return response()->json($expense->load(['user']));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'category' => 'string|max:255',
            'date' => 'date',
            'payment_method' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);
        return response()->json($expense->load(['user']));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}