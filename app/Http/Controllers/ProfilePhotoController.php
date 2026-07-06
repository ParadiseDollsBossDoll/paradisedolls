<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePhotoController extends Controller
{
    public function __invoke(User $user): StreamedResponse
    {
        $path = $user->profile_photo_path;

        abort_if(blank($path) || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
