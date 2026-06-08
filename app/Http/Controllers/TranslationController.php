<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TranslationController extends Controller
{
    public function languages(TranslationService $translator): JsonResponse
    {
        return response()->json([
            'enabled' => $translator->isActive(),
            'source' => 'en',
            'priority' => $translator->priorityCodes(),
            'languages' => $translator->languages(),
        ]);
    }

    public function translate(Request $request, TranslationService $translator): JsonResponse
    {
        $validated = $request->validate([
            'target' => ['required', 'string', 'max:12', 'regex:/^[a-z]{2,3}(-[A-Za-z]{2,4})?$/'],
            'texts' => ['required', 'array', 'min:1', 'max:80'],
            'texts.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $target = (string) $validated['target'];

        if (! $translator->isSupportedLanguage($target)) {
            throw ValidationException::withMessages([
                'target' => __('Choose a supported language.'),
            ]);
        }

        $texts = array_values(array_map(
            fn ($text) => is_string($text) ? $text : '',
            $validated['texts']
        ));

        return response()->json([
            'enabled' => $translator->isActive(),
            'source' => 'en',
            'target' => $target,
            'translations' => $translator->translateBatch($texts, $target),
        ]);
    }
}
