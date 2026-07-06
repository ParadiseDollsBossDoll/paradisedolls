<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardingFormDefinition
{
    public const SETTINGS_KEY = 'member_onboarding_form';

    private const DEFAULT_VERSION = 'default-2026-07-05';

    public static function get(): array
    {
        $saved = SiteSetting::get(self::SETTINGS_KEY, []);

        return self::normalize(is_array($saved) ? $saved : []);
    }

    public static function saveFromRequest(Request $request): array
    {
        $current = self::get();
        $input = $request->input('form', []);
        $input = is_array($input) ? $input : [];

        $definition = self::normalize([
            'version' => now()->format('YmdHis'),
            'sections' => self::sectionsFromInput($input['sections'] ?? [], $current['sections']),
            'option_groups' => self::optionGroupsFromInput($input['option_groups'] ?? [], $current['option_groups']),
            'fetish_sections' => self::fetishSectionsFromInput($input['fetish_sections'] ?? [], $current['fetish_sections']),
            'custom_fields' => self::customFieldsFromInput($input['custom_fields'] ?? [], $current['custom_fields']),
        ]);

        SiteSetting::set(self::SETTINGS_KEY, $definition);

        return $definition;
    }

    public static function platformGroupsForMember(array $definition, array $selected = []): array
    {
        return [
            'platforms_cam',
            'platforms_fan',
            'platforms_ai',
        ];
    }

    public static function optionGroup(array $definition, string $key): array
    {
        return $definition['option_groups'][$key] ?? [
            'label' => Str::headline($key),
            'help' => '',
            'options' => [],
            'archived' => [],
        ];
    }

    public static function memberOptions(array $definition, string $key, array $selected = []): array
    {
        $group = self::optionGroup($definition, $key);

        return array_values(array_unique([
            ...($group['options'] ?? []),
            ...array_values(array_filter($selected, fn ($value) => is_string($value) && $value !== '')),
        ]));
    }

    public static function validationOptions(array $definition, string $key, array $saved = []): array
    {
        $group = self::optionGroup($definition, $key);

        return array_values(array_unique([
            ...($group['options'] ?? []),
            ...($group['archived'] ?? []),
            ...array_values(array_filter($saved, fn ($value) => is_string($value) && $value !== '')),
        ]));
    }

    public static function allPlatformOptions(array $definition, array $saved = []): array
    {
        return array_values(array_unique([
            ...self::validationOptions($definition, 'platforms_cam', $saved),
            ...self::validationOptions($definition, 'platforms_fan', $saved),
            ...self::validationOptions($definition, 'platforms_ai', $saved),
        ]));
    }

    public static function fetishSectionsForMember(array $definition): array
    {
        return collect($definition['fetish_sections'] ?? [])
            ->reject(fn (array $section) => (bool) ($section['archived'] ?? false))
            ->map(fn (array $section) => [
                'title' => $section['title'],
                'note' => $section['note'] ?? '',
                'items' => $section['items'] ?? [],
            ])
            ->filter(fn (array $section) => $section['title'] !== '' && $section['items'] !== [])
            ->values()
            ->all();
    }

    public static function allFetishItems(array $definition, array $saved = []): array
    {
        $configured = collect($definition['fetish_sections'] ?? [])
            ->flatMap(fn (array $section) => [
                ...($section['items'] ?? []),
                ...($section['archived_items'] ?? []),
            ])
            ->values()
            ->all();

        return array_values(array_unique([
            ...$configured,
            ...array_keys($saved),
        ]));
    }

    public static function activeCustomFields(array $definition): array
    {
        return collect($definition['custom_fields'] ?? [])
            ->reject(fn (array $field) => (bool) ($field['archived'] ?? false))
            ->filter(fn (array $field) => ($field['label'] ?? '') !== '')
            ->values()
            ->all();
    }

    public static function allCustomFields(array $definition): array
    {
        return collect($definition['custom_fields'] ?? [])
            ->filter(fn (array $field) => ($field['label'] ?? '') !== '')
            ->values()
            ->all();
    }

    public static function customAnswersFromRequest(Request $request, array $definition, array $previous = []): array
    {
        $input = $request->input('custom_onboarding', []);
        $input = is_array($input) ? $input : [];
        $answers = is_array($previous) ? $previous : [];

        foreach (self::activeCustomFields($definition) as $field) {
            $id = $field['id'];
            $type = $field['type'];

            if ($type === 'section') {
                continue;
            }

            if ($type === 'checkbox') {
                $value = $input[$id] ?? [];
                $value = is_array($value) ? self::cleanLines($value) : [];
            } else {
                $value = trim((string) ($input[$id] ?? ''));
            }

            if ($value === '' || $value === []) {
                unset($answers[$id]);
            } else {
                $answers[$id] = $value;
            }
        }

        return $answers;
    }

    public static function customAnswersForDisplay(array $definition, array $answers): array
    {
        $fieldsById = collect(self::allCustomFields($definition))->keyBy('id');

        return collect($answers)
            ->map(function (mixed $answer, string $id) use ($fieldsById): array {
                $field = $fieldsById->get($id, [
                    'label' => Str::headline(str_replace(['custom_', '_'], ['', ' '], $id)),
                    'archived' => true,
                ]);

                return [
                    'id' => $id,
                    'label' => $field['label'],
                    'answer' => is_array($answer) ? implode(', ', $answer) : (string) $answer,
                    'archived' => (bool) ($field['archived'] ?? false),
                ];
            })
            ->filter(fn (array $row) => trim($row['answer']) !== '')
            ->values()
            ->all();
    }

    public static function normalize(array $definition): array
    {
        $defaults = self::defaults();

        return [
            'version' => trim((string) ($definition['version'] ?? $defaults['version'])) ?: self::DEFAULT_VERSION,
            'sections' => self::mergeSections($definition['sections'] ?? [], $defaults['sections']),
            'option_groups' => self::mergeOptionGroups($definition['option_groups'] ?? [], $defaults['option_groups']),
            'fetish_sections' => self::normalizeFetishSections($definition['fetish_sections'] ?? $defaults['fetish_sections']),
            'custom_fields' => self::normalizeCustomFields($definition['custom_fields'] ?? []),
        ];
    }

    public static function defaults(): array
    {
        return [
            'version' => self::DEFAULT_VERSION,
            'sections' => [
                'platforms' => [
                    'eyebrow' => 'Step 2',
                    'title' => 'Platforms & Setup',
                    'help' => '',
                ],
                'fetishes' => [
                    'title' => 'General Fetishes & Kinks Checklist',
                    'help' => 'Please answer Yes / No / Sometimes for each item.',
                ],
                'work_preferences' => [
                    'title' => 'Work Preferences',
                    'help' => '',
                ],
                'schedule' => [
                    'title' => 'Schedule Details',
                    'help' => '',
                ],
                'appearance' => [
                    'eyebrow' => 'Step 3',
                    'title' => 'Appearance & Style',
                    'help' => '',
                ],
                'payout' => [
                    'eyebrow' => 'Step 4',
                    'title' => 'Payout Information',
                    'help' => '',
                ],
                'extra' => [
                    'eyebrow' => 'Step 5',
                    'title' => 'Extra Details',
                    'help' => '',
                ],
                'custom' => [
                    'eyebrow' => 'Step 6',
                    'title' => 'Additional Questions',
                    'help' => '',
                ],
                'discord' => [
                    'eyebrow' => 'Step 7',
                    'title' => 'Discord',
                    'help' => '',
                ],
            ],
            'option_groups' => [
                'platforms_cam' => [
                    'label' => 'Cam Sites',
                    'help' => '',
                    'options' => ['AdultWork', 'Babestation', 'BongaCams', 'CAM4', 'CamSoda', 'Chaturbate', 'Flirt4Free', 'LiveJasmin', 'MyFreeCams', 'Streamate', 'Stripchat', 'Susi.live', 'XLoveCam'],
                    'archived' => [],
                ],
                'platforms_fan' => [
                    'label' => 'Fan Sites',
                    'help' => '',
                    'options' => ['Clips4Sale', 'FanCentro', 'Fansly', 'Fanzi', 'LoyalFans', 'ManyVids', 'OnlyFans', 'OnlyPPV', 'Playboy Fans', 'Supermodels.fans'],
                    'archived' => [],
                ],
                'platforms_ai' => [
                    'label' => 'AI',
                    'help' => '',
                    'options' => ['OhChatAI'],
                    'archived' => [],
                ],
                'work_interests' => [
                    'label' => 'Work interests',
                    'help' => 'What type of content are you interested in?',
                    'options' => ['OnlyFans Content', 'Webcam Premium Shows', 'Freemium Webcam', 'All Types'],
                    'archived' => [],
                ],
                'comfort_levels' => [
                    'label' => 'Comfort levels',
                    'help' => 'What are you comfortable performing?',
                    'options' => ['Lingerie', 'Tease / Lingerie', 'Topless', 'Nude', 'Toys (Solo)', 'Girl/Girl', 'Fetish', 'Anal (Solo)', 'Domination / Roleplay'],
                    'archived' => [],
                ],
                'equipment' => [
                    'label' => 'Available equipment',
                    'help' => '',
                    'options' => ['Phone', 'Laptop', 'Desktop PC', 'Webcam', 'Ring light', 'Softbox lighting', 'Microphone', 'Stable internet', 'Private room'],
                    'archived' => [],
                ],
                'payout_methods' => [
                    'label' => 'Preferred payout methods',
                    'help' => 'Tick all methods you are able to receive payment via.',
                    'options' => ['Revolut', 'Wise', 'Bank Transfer', 'Crypto', 'Other'],
                    'archived' => [],
                ],
            ],
            'fetish_sections' => self::defaultFetishSections(),
            'custom_fields' => [],
        ];
    }

    private static function defaultFetishSections(): array
    {
        return [
            ['id' => 'lingerie_tease', 'title' => 'Lingerie / Tease Shows', 'note' => '', 'items' => ['Lingerie', 'Topless', 'Fully Nude', 'Pussy Play / Fingering', 'Toys (Solo)', 'Anal (Solo)', 'Squirting', 'Girl/Girl (G/G)', 'Boy/Girl (B/G) - must be verified', 'Couples Shows', 'Group Shows', 'Shower/Bath Shows', 'Oil Shows', 'Outdoor Shows', 'Public Shows (must be safe/legal)'], 'archived_items' => [], 'archived' => false],
            ['id' => 'fetish_kink', 'title' => 'Fetish / Kink', 'note' => '', 'items' => ['Foot Fetish (showing soles, toes, heels)', 'JOI (Jerk Off Instruction)', 'SPH (Small Penis Humiliation)', 'CEI (Cum Eating Instruction)', 'Domination (light)', 'Domination (hardcore)', 'Submissive (obedient, brat, etc.)', 'Findom (Financial Domination)', 'Roleplay (student/teacher, nurse, etc.)', 'Cosplay (costumes, fantasy characters)', 'Dirty Talk / Verbal Tease', 'Humiliation (light)', 'Humiliation (extreme)', 'Cuckold / Cuckquean Play', 'Giantess / Shrinking Fetish', 'Sissy Training', 'Chastity / Keyholding', 'Fetish Outfit Requests (latex, leather, socks, heels, etc.)'], 'archived_items' => [], 'archived' => false],
            ['id' => 'bodily_sensation', 'title' => 'Bodily / Sensation Fetishes', 'note' => '', 'items' => ['Oil / Lotion Play', 'Shower / Bath Shows', 'Wet & Messy (e.g. food play, cream, etc.)', 'Squirting', 'Spitting (on self or POV)', 'Sweaty / Gym Content', 'Twerking / Ass Play', 'Nipple Play', 'Pussy Play / Fingering', 'Anal Play (solo)', 'Gaping'], 'archived_items' => [], 'archived' => false],
            ['id' => 'feet_legs_stockings', 'title' => 'Feet, Legs, & Stockings', 'note' => '', 'items' => ['Foot Close-Ups', 'Shoeplay / Heels', 'Socks / Dirty Socks', 'Stockings / Pantyhose', 'Toe Curling / Wrinkled Soles'], 'archived_items' => [], 'archived' => false],
            ['id' => 'dom_sub_taboo', 'title' => 'Dom/Sub Kinks & Taboo Play', 'note' => 'Only if legal and site-approved', 'items' => ['Pet Play (Kitten, Puppy)', 'Age Play (18+ only)', 'Degradation', 'Collar / Leash Content', 'Role Reversal / Power Swap', 'Impact Play (light spanking)'], 'archived_items' => [], 'archived' => false],
            ['id' => 'clean_fetish_extras', 'title' => 'Clean Fetish Extras', 'note' => '', 'items' => ['Shaving (legs, pussy, etc.)', 'Hair Brushing / Hair Play', 'Nail Fetish (polish, filing, close-ups)'], 'archived_items' => [], 'archived' => false],
        ];
    }

    private static function sectionsFromInput(mixed $input, array $current): array
    {
        $input = is_array($input) ? $input : [];

        return collect($current)
            ->map(function (array $section, string $key) use ($input): array {
                $submitted = is_array($input[$key] ?? null) ? $input[$key] : [];

                return [
                    'eyebrow' => self::cleanString($submitted['eyebrow'] ?? $section['eyebrow'] ?? '', 80),
                    'title' => self::cleanString($submitted['title'] ?? $section['title'] ?? Str::headline($key), 120),
                    'help' => self::cleanString($submitted['help'] ?? $section['help'] ?? '', 500),
                ];
            })
            ->all();
    }

    private static function optionGroupsFromInput(mixed $input, array $current): array
    {
        $input = is_array($input) ? $input : [];
        $groups = [];

        foreach ($current as $key => $group) {
            $submitted = is_array($input[$key] ?? null) ? $input[$key] : [];
            $submittedOptions = self::parseLines($submitted['options'] ?? ($group['options'] ?? []));
            $submittedArchived = self::parseLines($submitted['archived'] ?? ($group['archived'] ?? []));
            $removed = array_values(array_diff($group['options'] ?? [], $submittedOptions));
            $archived = array_values(array_unique([...$submittedArchived, ...$removed]));
            $archived = array_values(array_diff($archived, $submittedOptions));

            $groups[$key] = [
                'label' => self::cleanString($submitted['label'] ?? $group['label'] ?? Str::headline($key), 100),
                'help' => self::cleanString($submitted['help'] ?? $group['help'] ?? '', 500),
                'options' => $submittedOptions,
                'archived' => $archived,
            ];
        }

        return $groups;
    }

    private static function fetishSectionsFromInput(mixed $input, array $current): array
    {
        $input = is_array($input) ? $input : [];
        $currentById = collect($current)->keyBy('id');
        $sections = [];

        foreach ($input as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = self::cleanString($row['title'] ?? '', 120);
            $items = self::parseLines($row['items'] ?? []);
            $archivedItems = self::parseLines($row['archived_items'] ?? []);

            if ($title === '' && $items === [] && $archivedItems === []) {
                continue;
            }

            $id = self::fieldId($row['id'] ?? '', $title ?: 'fetish_section', $sections);
            $previous = $currentById->get($id, []);
            $removed = array_values(array_diff($previous['items'] ?? [], $items));
            $archivedItems = array_values(array_unique([...$archivedItems, ...$removed]));
            $archivedItems = array_values(array_diff($archivedItems, $items));

            $sections[] = [
                'id' => $id,
                'title' => $title,
                'note' => self::cleanString($row['note'] ?? '', 300),
                'items' => $items,
                'archived_items' => $archivedItems,
                'archived' => filter_var($row['archived'] ?? false, FILTER_VALIDATE_BOOL),
            ];
        }

        return $sections !== [] ? $sections : $current;
    }

    private static function customFieldsFromInput(mixed $input, array $current): array
    {
        $input = is_array($input) ? $input : [];
        $fields = [];

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = self::cleanString($row['label'] ?? '', 140);
            $options = self::parseLines($row['options'] ?? []);

            if ($label === '' && $options === []) {
                continue;
            }

            $type = (string) ($row['type'] ?? 'text');
            if (! in_array($type, ['text', 'textarea', 'select', 'radio', 'checkbox', 'yes_no_maybe', 'section'], true)) {
                $type = 'text';
            }

            $id = self::fieldId($row['id'] ?? '', $label ?: 'custom_field', $fields);

            $fields[] = [
                'id' => $id,
                'label' => $label,
                'type' => $type,
                'help' => self::cleanString($row['help'] ?? '', 500),
                'required' => filter_var($row['required'] ?? false, FILTER_VALIDATE_BOOL),
                'options' => $type === 'yes_no_maybe' ? ['Yes', 'No', 'Maybe'] : $options,
                'archived' => filter_var($row['archived'] ?? false, FILTER_VALIDATE_BOOL),
            ];
        }

        return $fields;
    }

    private static function mergeSections(mixed $saved, array $defaults): array
    {
        $saved = is_array($saved) ? $saved : [];

        foreach ($defaults as $key => $default) {
            $submitted = is_array($saved[$key] ?? null) ? $saved[$key] : [];
            $defaults[$key] = [
                'eyebrow' => self::cleanString($submitted['eyebrow'] ?? $default['eyebrow'] ?? '', 80),
                'title' => self::cleanString($submitted['title'] ?? $default['title'] ?? Str::headline($key), 120),
                'help' => self::cleanString($submitted['help'] ?? $default['help'] ?? '', 500),
            ];
        }

        return $defaults;
    }

    private static function mergeOptionGroups(mixed $saved, array $defaults): array
    {
        $saved = is_array($saved) ? $saved : [];

        foreach ($defaults as $key => $default) {
            $group = is_array($saved[$key] ?? null) ? $saved[$key] : [];
            $defaults[$key] = [
                'label' => self::cleanString($group['label'] ?? $default['label'], 100),
                'help' => self::cleanString($group['help'] ?? $default['help'] ?? '', 500),
                'options' => self::parseLines($group['options'] ?? $default['options']),
                'archived' => self::parseLines($group['archived'] ?? []),
            ];
        }

        return $defaults;
    }

    private static function normalizeFetishSections(mixed $sections): array
    {
        $sections = is_array($sections) ? $sections : [];
        $normalized = [];

        foreach ($sections as $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = self::cleanString($row['title'] ?? '', 120);
            $items = self::parseLines($row['items'] ?? []);
            $archivedItems = self::parseLines($row['archived_items'] ?? []);

            if ($title === '' || ($items === [] && $archivedItems === [])) {
                continue;
            }

            $normalized[] = [
                'id' => self::fieldId($row['id'] ?? '', $title, $normalized),
                'title' => $title,
                'note' => self::cleanString($row['note'] ?? '', 300),
                'items' => $items,
                'archived_items' => $archivedItems,
                'archived' => (bool) ($row['archived'] ?? false),
            ];
        }

        return $normalized;
    }

    private static function normalizeCustomFields(mixed $fields): array
    {
        $fields = is_array($fields) ? $fields : [];
        $normalized = [];

        foreach ($fields as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = self::cleanString($row['label'] ?? '', 140);
            if ($label === '') {
                continue;
            }

            $type = (string) ($row['type'] ?? 'text');
            if (! in_array($type, ['text', 'textarea', 'select', 'radio', 'checkbox', 'yes_no_maybe', 'section'], true)) {
                $type = 'text';
            }

            $normalized[] = [
                'id' => self::fieldId($row['id'] ?? '', $label, $normalized),
                'label' => $label,
                'type' => $type,
                'help' => self::cleanString($row['help'] ?? '', 500),
                'required' => (bool) ($row['required'] ?? false),
                'options' => $type === 'yes_no_maybe' ? ['Yes', 'No', 'Maybe'] : self::parseLines($row['options'] ?? []),
                'archived' => (bool) ($row['archived'] ?? false),
            ];
        }

        return $normalized;
    }

    private static function parseLines(mixed $value): array
    {
        if (is_array($value)) {
            return self::cleanLines($value);
        }

        return self::cleanLines(preg_split('/\R/', (string) $value) ?: []);
    }

    private static function cleanLines(array $lines): array
    {
        return collect($lines)
            ->map(fn ($line) => self::cleanString($line, 140))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function cleanString(mixed $value, int $limit): string
    {
        return Str::limit(trim((string) $value), $limit, '');
    }

    private static function fieldId(mixed $candidate, string $label, array $existing): string
    {
        $candidate = trim((string) $candidate);
        $base = $candidate !== '' ? $candidate : 'custom_'.Str::slug($label, '_');
        $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base) ?: 'custom_field';
        $base = Str::limit($base, 64, '');
        $id = $base;
        $existingIds = collect($existing)->pluck('id')->filter()->all();
        $counter = 2;

        while (in_array($id, $existingIds, true)) {
            $id = Str::limit($base, 56, '').'_'.$counter;
            $counter++;
        }

        return $id;
    }
}
