<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preset'       => ['nullable', 'string', 'max:32'],
            'mode'         => ['required', 'in:light,dark'],
            'primary'      => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'primaryLight' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        SiteSetting::set('theme', $validated);

        return response()->json(['status' => 'saved']);
    }
}
