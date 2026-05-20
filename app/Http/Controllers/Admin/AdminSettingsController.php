<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\MarketingContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function editMarketingContent(Request $request): View
    {
        $pages = MarketingContent::pages();
        $activePage = (string) $request->query('page', array_key_first($pages));

        if (! isset($pages[$activePage])) {
            $activePage = array_key_first($pages);
        }

        return view('admin.site-editor.edit', [
            'pages' => $pages,
            'activePage' => $activePage,
            'activeDefinition' => $pages[$activePage],
        ]);
    }

    public function updateMarketingContent(Request $request): RedirectResponse
    {
        $pages = MarketingContent::pages();
        $activePage = (string) $request->input('_page', array_key_first($pages));

        abort_unless(isset($pages[$activePage]), 404);

        $request->validate([
            'content' => ['nullable', 'array'],
            'image_files' => ['nullable', 'array'],
            'image_files.*.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'card_image_files' => ['nullable', 'array'],
            'card_image_files.*.*.*.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $errors = [];
        $settings = SiteSetting::get(MarketingContent::SETTINGS_KEY, []);
        $pageInput = $request->input("content.{$activePage}", []);

        foreach ($pages[$activePage]['fields'] as $field) {
            $this->saveMarketingField($request, $settings, $activePage, $field, $pageInput, $errors);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        SiteSetting::set(MarketingContent::SETTINGS_KEY, $settings);

        return redirect()
            ->route('admin.site-editor.edit', ['page' => $activePage])
            ->with('status', __('Site content saved.'));
    }

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

    private function saveMarketingField(Request $request, array &$settings, string $pageKey, array $field, array $pageInput, array &$errors): void
    {
        $fieldId = MarketingContent::fieldId($field['key']);
        $fullKey = "{$pageKey}.{$field['key']}";
        $inputKey = "content.{$pageKey}.{$fieldId}";
        $type = $field['type'];

        if ($type === 'cards') {
            $cards = [];
            $submittedCards = is_array($pageInput[$fieldId] ?? null) ? $pageInput[$fieldId] : [];
            $defaults = MarketingContent::items($fullKey);
            $cardFields = $field['card_fields'] ?? [];
            $rowCount = max(count($defaults), count($submittedCards));

            for ($index = 0; $index < $rowCount; $index++) {
                $row = [];
                $submittedRow = is_array($submittedCards[$index] ?? null) ? $submittedCards[$index] : [];

                foreach ($cardFields as $subKey => $subType) {
                    if ($subType === 'image') {
                        $file = $request->file("card_image_files.{$pageKey}.{$fieldId}.{$index}.{$subKey}");
                        $value = $file
                            ? $file->store('marketing/site-editor', 'public')
                            : trim((string) ($submittedRow[$subKey] ?? data_get($defaults, "{$index}.{$subKey}", '')));
                    } else {
                        $value = trim((string) ($submittedRow[$subKey] ?? ''));
                        $limit = $subType === 'textarea' ? 4000 : 300;

                        if (mb_strlen($value) > $limit) {
                            $errors["content.{$pageKey}.{$fieldId}.{$index}.{$subKey}"] = __('This field is too long.');
                        }
                    }

                    $row[$subKey] = $value;
                }

                if ($this->rowHasContent($row)) {
                    $cards[] = $row;
                }
            }

            data_set($settings, $fullKey, $cards);

            return;
        }

        if ($type === 'image') {
            $file = $request->file("image_files.{$pageKey}.{$fieldId}");
            $value = $file
                ? $file->store('marketing/site-editor', 'public')
                : trim((string) ($pageInput[$fieldId] ?? MarketingContent::text($fullKey)));

            data_set($settings, $fullKey, $value);

            return;
        }

        $rawValue = trim((string) ($pageInput[$fieldId] ?? ''));

        if ($type === 'list') {
            data_set($settings, $fullKey, MarketingContent::splitLines($rawValue));

            return;
        }

        if ($type === 'paragraphs') {
            data_set($settings, $fullKey, MarketingContent::splitParagraphs($rawValue));

            return;
        }

        if ($type === 'url' && ! MarketingContent::allowedLink($rawValue)) {
            $errors[$inputKey] = __('Use an internal link starting with / or #, or a valid http/https URL.');
        }

        $limit = $type === 'textarea' ? 5000 : 300;
        if (mb_strlen($rawValue) > $limit) {
            $errors[$inputKey] = __('This field is too long.');
        }

        data_set($settings, $fullKey, $rawValue);
    }

    private function rowHasContent(array $row): bool
    {
        foreach ($row as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}
