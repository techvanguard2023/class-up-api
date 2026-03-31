<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json(Banner::where('school_id', $user->school_id)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'image_url' => 'required|string|url',
            'status' => 'required|string|in:active,inactive',
            'position' => 'required|string|max:100',
            'link' => 'nullable|string|url',
        ]);

        $validated['school_id'] = $request->user()->school_id;
        $banner = Banner::create($validated);
        return response()->json($banner, Response::HTTP_CREATED);
    }

    public function show(Banner $banner)
    {
        return response()->json($banner);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string|max:500',
            'image_url' => 'string|url',
            'status' => 'string|in:active,inactive',
            'position' => 'string|max:100',
            'link' => 'nullable|string|url',
        ]);

        $banner->update($validated);
        return response()->json($banner);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
