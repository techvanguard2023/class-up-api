<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ModalityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Modality::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $modality = Modality::create($validated);

        return response()->json($modality, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Modality $modality)
    {
        return response()->json($modality);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Modality $modality)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $modality->update($validated);

        return response()->json($modality);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modality $modality)
    {
        $modality->delete();

        return response()->json(null, 204);
    }
}
