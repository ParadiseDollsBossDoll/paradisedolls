<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WeeklyModelDraw;
use App\Models\WeeklyModelDrawEntry;
use App\Models\WeeklyModelDrawPrize;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminWeeklyModelDrawController extends Controller
{
    public function index(): View
    {
        $draws = WeeklyModelDraw::query()
            ->with(['winnerEntry', 'winningPrize', 'creator'])
            ->withCount([
                'entries',
                'prizes',
                'entries as qualified_entries_count' => fn ($query) => $query->where('is_qualified', true),
            ])
            ->latest('week_start')
            ->latest()
            ->paginate(12);

        return view('admin.weekly-model-draws.index', [
            'draws' => $draws,
            'defaultWeekStart' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'defaultWeekEnd' => CarbonImmutable::now()->endOfWeek()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'qualification_threshold' => ['nullable', 'numeric', 'between:0,1000000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $draw = WeeklyModelDraw::create([
            'title' => $validated['title'],
            'week_start' => $validated['week_start'],
            'week_end' => $validated['week_end'],
            'qualification_threshold_cents' => $this->nullableMoneyCents($validated['qualification_threshold'] ?? null),
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.weekly-draws.show', $draw)
            ->with('status', __('Weekly model draw created.'));
    }

    public function show(WeeklyModelDraw $draw): View
    {
        $draw->load([
            'entries.user:id,name,email,profile_photo_path',
            'prizes',
            'winnerEntry',
            'winningPrize',
            'creator',
            'completedBy',
        ]);

        $draw->setRelation('entries', $draw->entries
            ->sortBy([
                ['is_qualified', 'desc'],
                ['earnings_cents', 'desc'],
                ['model_name', 'asc'],
            ])
            ->values());
        $draw->setRelation('prizes', $draw->prizes->sortBy('sort_order')->values());

        $modelOptions = User::query()
            ->where('role', 'model')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $qualifiedNames = implode("\n", $draw->qualifiedNames());

        return view('admin.weekly-model-draws.show', [
            'draw' => $draw,
            'modelOptions' => $modelOptions,
            'qualifiedNames' => $qualifiedNames,
            'stats' => [
                'entries' => $draw->entries->count(),
                'qualified' => $draw->entries->where('is_qualified', true)->count(),
                'earnings_cents' => (int) $draw->entries->sum('earnings_cents'),
                'qualified_earnings_cents' => (int) $draw->entries->where('is_qualified', true)->sum('earnings_cents'),
            ],
        ]);
    }

    public function update(Request $request, WeeklyModelDraw $draw): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'status' => ['required', Rule::in([WeeklyModelDraw::STATUS_DRAFT, WeeklyModelDraw::STATUS_READY, WeeklyModelDraw::STATUS_COMPLETED])],
            'qualification_threshold' => ['nullable', 'numeric', 'between:0,1000000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'recording_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $draw->update([
            'title' => $validated['title'],
            'week_start' => $validated['week_start'],
            'week_end' => $validated['week_end'],
            'status' => $validated['status'],
            'qualification_threshold_cents' => $this->nullableMoneyCents($validated['qualification_threshold'] ?? null),
            'notes' => $validated['notes'] ?? null,
            'recording_url' => $validated['recording_url'] ?? null,
        ]);

        return back()->with('status', __('Draw details saved.'));
    }

    public function storeEntry(Request $request, WeeklyModelDraw $draw): RedirectResponse
    {
        $validated = $this->validateEntry($request);
        $model = $this->selectedModel($validated['model_id'] ?? null);
        $entry = $this->entryPayload($validated, $model);

        if ($model) {
            WeeklyModelDrawEntry::query()->updateOrCreate(
                ['weekly_model_draw_id' => $draw->id, 'user_id' => $model->id],
                $entry + ['weekly_model_draw_id' => $draw->id],
            );
        } else {
            $draw->entries()->create($entry);
        }

        return back()->with('status', __('Model earnings saved for this draw.'));
    }

    public function updateEntry(Request $request, WeeklyModelDraw $draw, WeeklyModelDrawEntry $entry): RedirectResponse
    {
        $this->assertEntryBelongsToDraw($draw, $entry);
        $validated = $this->validateEntry($request);
        $model = $this->selectedModel($validated['model_id'] ?? null);

        $entry->update($this->entryPayload($validated, $model));

        return back()->with('status', __('Model draw entry updated.'));
    }

    public function destroyEntry(WeeklyModelDraw $draw, WeeklyModelDrawEntry $entry): RedirectResponse
    {
        $this->assertEntryBelongsToDraw($draw, $entry);

        DB::transaction(function () use ($draw, $entry): void {
            if ((int) $draw->winner_entry_id === $entry->id) {
                $draw->forceFill([
                    'winner_entry_id' => null,
                    'winning_prize_id' => null,
                    'status' => WeeklyModelDraw::STATUS_READY,
                    'completed_by' => null,
                    'drawn_at' => null,
                ])->save();
            }

            $entry->delete();
        });

        return back()->with('status', __('Model entry removed from this draw.'));
    }

    public function importEntries(Request $request, WeeklyModelDraw $draw): RedirectResponse
    {
        $validated = $request->validate([
            'import_text' => ['required', 'string', 'max:50000'],
        ]);

        [$rows, $errors] = $this->parseImportRows($validated['import_text'], $draw);

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'import_text' => implode(' ', $errors),
            ]);
        }

        DB::transaction(function () use ($draw, $rows): void {
            foreach ($rows as $row) {
                $match = ['weekly_model_draw_id' => $draw->id];
                if ($row['user_id']) {
                    $match['user_id'] = $row['user_id'];
                } elseif ($row['model_email']) {
                    $match['model_email'] = $row['model_email'];
                } else {
                    $match['model_name'] = $row['model_name'];
                }

                WeeklyModelDrawEntry::query()->updateOrCreate($match, $row + ['weekly_model_draw_id' => $draw->id]);
            }
        });

        return back()->with('status', trans_choice(':count model earning imported.|:count model earnings imported.', count($rows), ['count' => count($rows)]));
    }

    public function storePrize(Request $request, WeeklyModelDraw $draw): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'value' => ['nullable', 'numeric', 'between:0,1000000'],
            'sort_order' => ['nullable', 'integer', 'between:0,65535'],
        ]);

        $draw->prizes()->create([
            'name' => $validated['name'],
            'value_cents' => $this->nullableMoneyCents($validated['value'] ?? null),
            'sort_order' => $validated['sort_order'] ?? (((int) $draw->prizes()->max('sort_order')) + 10),
        ]);

        return back()->with('status', __('Prize added to this draw.'));
    }

    public function updatePrize(Request $request, WeeklyModelDraw $draw, WeeklyModelDrawPrize $prize): RedirectResponse
    {
        $this->assertPrizeBelongsToDraw($draw, $prize);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'value' => ['nullable', 'numeric', 'between:0,1000000'],
            'sort_order' => ['required', 'integer', 'between:0,65535'],
        ]);

        $prize->update([
            'name' => $validated['name'],
            'value_cents' => $this->nullableMoneyCents($validated['value'] ?? null),
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('status', __('Prize updated.'));
    }

    public function destroyPrize(WeeklyModelDraw $draw, WeeklyModelDrawPrize $prize): RedirectResponse
    {
        $this->assertPrizeBelongsToDraw($draw, $prize);

        DB::transaction(function () use ($draw, $prize): void {
            if ((int) $draw->winning_prize_id === $prize->id) {
                $draw->forceFill([
                    'winning_prize_id' => null,
                    'status' => WeeklyModelDraw::STATUS_READY,
                    'completed_by' => null,
                    'drawn_at' => null,
                ])->save();
            }

            $prize->delete();
        });

        return back()->with('status', __('Prize removed from this draw.'));
    }

    public function complete(Request $request, WeeklyModelDraw $draw): RedirectResponse
    {
        $validated = $request->validate([
            'winner_entry_id' => [
                'required',
                Rule::exists(WeeklyModelDrawEntry::class, 'id')
                    ->where('weekly_model_draw_id', $draw->id)
                    ->where('is_qualified', true),
            ],
            'winning_prize_id' => [
                'nullable',
                Rule::exists(WeeklyModelDrawPrize::class, 'id')->where('weekly_model_draw_id', $draw->id),
            ],
            'recording_url' => ['nullable', 'url', 'max:2048'],
            'drawn_at' => ['nullable', 'date'],
        ]);

        $draw->update([
            'status' => WeeklyModelDraw::STATUS_COMPLETED,
            'winner_entry_id' => (int) $validated['winner_entry_id'],
            'winning_prize_id' => $validated['winning_prize_id'] ?? null,
            'recording_url' => $validated['recording_url'] ?? $draw->recording_url,
            'drawn_at' => $validated['drawn_at'] ?? now(),
            'completed_by' => $request->user()->id,
        ]);

        return back()->with('status', __('Winner and draw recording saved.'));
    }

    public function exportQualified(WeeklyModelDraw $draw): Response
    {
        $draw->load(['entries' => fn ($query) => $query->where('is_qualified', true)->orderBy('model_name')]);
        $names = implode("\n", $draw->qualifiedNames());

        return response($names."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="weekly-model-draw-'.$draw->week_start->format('Y-m-d').'-qualified.txt"',
        ]);
    }

    private function validateEntry(Request $request): array
    {
        return $request->validate([
            'model_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('role', 'model')],
            'model_name' => ['required_without:model_id', 'nullable', 'string', 'max:255'],
            'model_email' => ['nullable', 'email', 'max:255'],
            'earnings' => ['required', 'numeric', 'between:0,1000000'],
            'is_qualified' => ['required', 'boolean'],
            'qualification_note' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function entryPayload(array $validated, ?User $model): array
    {
        return [
            'user_id' => $model?->id,
            'model_name' => $model?->name ?? trim((string) $validated['model_name']),
            'model_email' => $model?->email ?? Str::lower(trim((string) ($validated['model_email'] ?? ''))) ?: null,
            'earnings_cents' => $this->moneyCents($validated['earnings']),
            'is_qualified' => (bool) $validated['is_qualified'],
            'qualification_note' => $validated['qualification_note'] ?? null,
        ];
    }

    private function selectedModel(mixed $modelId): ?User
    {
        if (! $modelId) {
            return null;
        }

        return User::query()
            ->where('role', 'model')
            ->findOrFail((int) $modelId);
    }

    private function parseImportRows(string $text, WeeklyModelDraw $draw): array
    {
        $rows = [];
        $errors = [];

        foreach (preg_split('/\R/', $text) ?: [] as $lineNumber => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $columns = array_map(fn ($value) => trim((string) $value), str_getcsv($line));
            if ($lineNumber === 0 && $this->looksLikeImportHeader($columns)) {
                continue;
            }

            $name = $columns[0] ?? '';
            $email = Str::lower($columns[1] ?? '');
            $earnings = $columns[2] ?? '';
            $qualified = $columns[3] ?? null;
            $note = $columns[4] ?? null;

            if ($name === '' || $earnings === '' || ! is_numeric($this->normalizeMoney($earnings))) {
                $errors[] = __('Line :line must include model name, optional email, and earnings.', ['line' => $lineNumber + 1]);
                continue;
            }

            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = __('Line :line has an invalid email address.', ['line' => $lineNumber + 1]);
                continue;
            }

            $model = $email !== ''
                ? User::query()->where('role', 'model')->where('email', $email)->first(['id', 'name', 'email'])
                : null;
            $earningsCents = $this->moneyCents($earnings);

            $rows[] = [
                'user_id' => $model?->id,
                'model_name' => $model?->name ?? $name,
                'model_email' => $model?->email ?? ($email ?: null),
                'earnings_cents' => $earningsCents,
                'is_qualified' => $qualified === null || $qualified === ''
                    ? ($draw->qualification_threshold_cents !== null && $earningsCents >= $draw->qualification_threshold_cents)
                    : $this->truthyImportValue($qualified),
                'qualification_note' => $note ?: null,
            ];
        }

        if ($rows === [] && $errors === []) {
            $errors[] = __('Paste at least one model earnings row.');
        }

        return [$rows, $errors];
    }

    private function looksLikeImportHeader(array $columns): bool
    {
        $firstLine = Str::lower(implode(' ', $columns));

        return str_contains($firstLine, 'name') && str_contains($firstLine, 'earning');
    }

    private function truthyImportValue(string $value): bool
    {
        return in_array(Str::lower(trim($value)), ['1', 'yes', 'y', 'true', 'qualified', 'q'], true);
    }

    private function nullableMoneyCents(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->moneyCents($value);
    }

    private function moneyCents(mixed $value): int
    {
        return (int) round(((float) $this->normalizeMoney($value)) * 100);
    }

    private function normalizeMoney(mixed $value): string
    {
        return preg_replace('/[^\d.\-]/', '', (string) $value) ?: '0';
    }

    private function assertEntryBelongsToDraw(WeeklyModelDraw $draw, WeeklyModelDrawEntry $entry): void
    {
        abort_unless((int) $entry->weekly_model_draw_id === $draw->id, 404);
    }

    private function assertPrizeBelongsToDraw(WeeklyModelDraw $draw, WeeklyModelDrawPrize $prize): void
    {
        abort_unless((int) $prize->weekly_model_draw_id === $draw->id, 404);
    }
}
