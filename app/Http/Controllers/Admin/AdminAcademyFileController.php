<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAcademyFileController extends Controller
{
    public function show(Request $request): StreamedResponse
    {
        $path = $this->safeAcademyPath($request->query('path'));

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function safeAcademyPath(mixed $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path), '/');

        if ($path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
            return null;
        }

        return str_starts_with($path, 'academy/') ? $path : null;
    }
}
