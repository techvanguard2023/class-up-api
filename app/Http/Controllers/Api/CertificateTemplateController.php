<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        return response()->json(CertificateTemplate::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'background_url' => 'nullable|url',
            'content_json' => 'required|array',
            'is_default' => 'boolean',
        ]);

        $template = CertificateTemplate::create($validated);
        return response()->json($template, Response::HTTP_CREATED);
    }

    public function show(CertificateTemplate $template)
    {
        return response()->json($template);
    }

    public function update(Request $request, CertificateTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'background_url' => 'nullable|url',
            'content_json' => 'array',
            'is_default' => 'boolean',
        ]);

        $template->update($validated);
        return response()->json($template);
    }

    public function destroy(CertificateTemplate $template)
    {
        $template->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
