<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function index()
    {
        return response()->json(Guardian::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string',
            'cpf' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $guardian = Guardian::create($validated);
        return response()->json($guardian, 201);
    }

    public function show(Guardian $guardian)
    {
        return response()->json($guardian);
    }

    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'cpf' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $guardian->update($validated);
        return response()->json($guardian);
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();
        return response()->json(null, 204);
    }
}
