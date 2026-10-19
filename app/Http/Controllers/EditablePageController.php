<?php

namespace App\Http\Controllers;

use App\Support\MarketingContent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EditablePageController extends Controller
{
    public function show(string $page): Response
    {
        $this->validatePage($page);
        abort_unless(MarketingContent::value("{$page}.published") === true, 404);

        return response()->view('marketing.editable-page', compact('page'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function preview(string $page): Response
    {
        $this->validatePage($page);

        return response()->view('marketing.editable-page', compact('page'))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store');
    }

    public function asset(Request $request, string $page, string $file): BinaryFileResponse
    {
        $this->validatePage($page);
        abort_unless(MarketingContent::value("{$page}.published") === true || $request->user()?->role === 'admin', 404);
        $path = "marketing/drafts/{$page}/{$file}";
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function validatePage(string $page): void
    {
        abort_unless(in_array($page, MarketingContent::blankPageKeys(), true), 404);
    }
}
