<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\SchoolType;

class SchoolTypeController extends Controller
{
    public function index()
    {
        return response()->json(SchoolType::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $schoolType = SchoolType::create($validated);

        return response()->json($schoolType, 201);
    }

    public function show(SchoolType $schoolType)
    {
        return response()->json($schoolType);
    }

    public function update(Request $request, SchoolType $schoolType)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
        ]);

        $schoolType->update($validated);

        return response()->json($schoolType);
    }

    public function destroy(SchoolType $schoolType)
    {
        $schoolType->delete();

        return response()->json(null, 204);
    }
}
