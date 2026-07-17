<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRun;
use App\Models\User;
use App\Services\EmailCampaignDispatcher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminEmailCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $filter = in_array($request->query('filter'), ['sent', 'scheduled', 'draft'], true)
            ? $request->query('filter')
            : 'all';

        $campaignQuery = EmailCampaign::query()
            ->with(['creator:id,name', 'latestRun'])
            ->withSum('runs as delivered_count', 'sent_count')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($filter === 'sent', fn ($query) => $query->whereHas(
                'runs',
                fn ($runQuery) => $runQuery->where('sent_count', '>', 0)
            ))
            ->when($filter === 'scheduled', fn ($query) => $query->whereIn('status', [
                EmailCampaign::STATUS_SCHEDULED,
                EmailCampaign::STATUS_ACTIVE,
            ]))
            ->when($filter === 'draft', fn ($query) => $query->where('status', EmailCampaign::STATUS_DRAFT))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $audienceCounts = $this->audienceCounts();

        return view('admin.email-campaigns.index', [
            'campaigns' => $campaignQuery,
            'search' => $search,
            'filter' => $filter,
            'filterCounts' => [
                'all' => EmailCampaign::query()->count(),
                'sent' => EmailCampaign::query()
                    ->whereHas('runs', fn ($query) => $query->where('sent_count', '>', 0))
                    ->count(),
                'scheduled' => EmailCampaign::query()
                    ->whereIn('status', [EmailCampaign::STATUS_SCHEDULED, EmailCampaign::STATUS_ACTIVE])
                    ->count(),
                'draft' => EmailCampaign::query()
                    ->where('status', EmailCampaign::STATUS_DRAFT)
                    ->count(),
            ],
            'allModelsCount' => $audienceCounts[EmailCampaign::AUDIENCE_ALL_MODELS],
            'onboardedModelsCount' => $audienceCounts[EmailCampaign::AUDIENCE_ONBOARDED_MODELS],
            'emailsSentCount' => EmailCampaignRun::query()->sum('sent_count'),
            'unsubscribedCount' => User::query()
                ->where('role', 'model')
                ->whereNotNull('marketing_unsubscribed_at')
                ->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.email-campaigns.create', [
            'campaign' => new EmailCampaign([
                'audience' => EmailCampaign::AUDIENCE_ALL_MODELS,
            ]),
            'audienceCounts' => $this->audienceCounts(),
        ]);
    }

    public function store(Request $request, EmailCampaignDispatcher $dispatcher): RedirectResponse
    {
        $this->mergeScheduledFor($request);

        $validated = $request->validate($this->campaignRules(includeDeliveryMode: true));

        $campaign = EmailCampaign::create([
            ...$this->contentAttributes($validated),
            'repeat_every_days' => $this->repeatEveryDays($validated),
            'created_by' => $request->user()->id,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $this->applyDeliveryMode($campaign, $validated);

        if ($validated['delivery_mode'] === 'send_now') {
            $dispatcher->dispatch($campaign->fresh());
        }

        return redirect()
            ->route('admin.email-campaigns.edit', $campaign)
            ->with('status', __('Email campaign created.'));
    }

    public function edit(EmailCampaign $emailCampaign): View
    {
        return view('admin.email-campaigns.edit', [
            'campaign' => $emailCampaign,
            'runs' => $emailCampaign->runs()->latest()->paginate(10),
            'audienceCounts' => $this->audienceCounts(),
        ]);
    }

    public function update(Request $request, EmailCampaign $emailCampaign): RedirectResponse
    {
        $validated = $request->validate($this->campaignRules());
        $emailCampaign->update($this->contentAttributes($validated));

        return back()->with('status', __('Email campaign updated.'));
    }

    public function sendNow(EmailCampaign $emailCampaign, EmailCampaignDispatcher $dispatcher): RedirectResponse
    {
        $emailCampaign->update([
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => now(),
        ]);

        $run = $dispatcher->dispatch($emailCampaign->fresh());

        return back()->with('status', $run
            ? __('Campaign queued for :count recipients.', ['count' => $run->recipient_count])
            : __('Campaign could not be queued.'));
    }

    public function schedule(Request $request, EmailCampaign $emailCampaign): RedirectResponse
    {
        $this->mergeScheduledFor($request);

        $validated = $request->validate([
            'schedule_date' => ['nullable', 'date_format:Y-m-d'],
            'schedule_time' => ['nullable', 'date_format:H:i'],
            'scheduled_for' => ['required', 'date', 'after:now'],
            'repeat_preset' => ['nullable', Rule::in(array_keys(EmailCampaign::repeatPresetOptions()))],
            'repeat_every_days' => ['nullable', 'required_if:repeat_preset,custom', 'integer', 'min:1', 'max:365'],
        ]);

        $emailCampaign->update([
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => $validated['scheduled_for'],
            'repeat_every_days' => $this->repeatEveryDays($validated),
        ]);

        return back()->with('status', $emailCampaign->fresh()->repeats()
            ? __('Campaign scheduled and will repeat automatically.')
            : __('Campaign scheduled.'));
    }

    public function pause(EmailCampaign $emailCampaign): RedirectResponse
    {
        $emailCampaign->update(['status' => EmailCampaign::STATUS_PAUSED]);

        return back()->with('status', __('Campaign paused.'));
    }

    public function resume(EmailCampaign $emailCampaign): RedirectResponse
    {
        if ($emailCampaign->status !== EmailCampaign::STATUS_PAUSED) {
            return back();
        }

        $emailCampaign->update([
            'status' => $emailCampaign->repeats()
                ? EmailCampaign::STATUS_ACTIVE
                : EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => $emailCampaign->next_send_at?->isFuture()
                ? $emailCampaign->next_send_at
                : now(),
        ]);

        return back()->with('status', __('Campaign resumed.'));
    }

    public function destroy(EmailCampaign $emailCampaign): RedirectResponse
    {
        if ($emailCampaign->runs()->exists()) {
            return back()->withErrors([
                'campaign' => __('Campaigns with delivery history cannot be deleted. Pause them instead.'),
            ]);
        }

        $emailCampaign->delete();

        return redirect()
            ->route('admin.email-campaigns.index')
            ->with('status', __('Email campaign deleted.'));
    }

    private function campaignRules(bool $includeDeliveryMode = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'action_label' => ['nullable', 'string', 'max:80', 'required_with:action_url'],
            'action_url' => ['nullable', 'url:http,https', 'max:2000', 'required_with:action_label'],
            'audience' => ['required', Rule::in(array_keys(EmailCampaign::audienceOptions()))],
        ];

        if ($includeDeliveryMode) {
            $rules['delivery_mode'] = ['required', Rule::in(['draft', 'send_now', 'schedule'])];
            $rules['schedule_date'] = ['nullable', 'date_format:Y-m-d'];
            $rules['schedule_time'] = ['nullable', 'date_format:H:i'];
            $rules['scheduled_for'] = ['nullable', 'required_if:delivery_mode,schedule', 'date', 'after:now'];
            $rules['repeat_preset'] = ['nullable', Rule::in(array_keys(EmailCampaign::repeatPresetOptions()))];
            $rules['repeat_every_days'] = ['nullable', 'required_if:repeat_preset,custom', 'integer', 'min:1', 'max:365'];
        }

        return $rules;
    }

    private function contentAttributes(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'subject' => trim($validated['subject']),
            'body' => trim($validated['body']),
            'action_label' => filled($validated['action_label'] ?? null) ? trim($validated['action_label']) : null,
            'action_url' => filled($validated['action_url'] ?? null) ? trim($validated['action_url']) : null,
            'audience' => $validated['audience'],
        ];
    }

    private function repeatEveryDays(array $validated): ?int
    {
        return match ($validated['repeat_preset'] ?? 'none') {
            'daily' => 1,
            'weekly' => 7,
            'fortnightly' => 14,
            'monthly' => 30,
            'custom' => filled($validated['repeat_every_days'] ?? null)
                ? (int) $validated['repeat_every_days']
                : null,
            default => null,
        };
    }

    private function applyDeliveryMode(EmailCampaign $campaign, array $validated): void
    {
        if ($validated['delivery_mode'] === 'draft') {
            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => $validated['delivery_mode'] === 'send_now'
                ? now()
                : $validated['scheduled_for'],
        ]);
    }

    private function mergeScheduledFor(Request $request): void
    {
        $date = trim((string) $request->input('schedule_date'));
        $time = trim((string) $request->input('schedule_time'));

        if ($date !== '' && $time !== '') {
            try {
                $scheduledFor = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    "{$date} {$time}",
                    EmailCampaign::schedulingTimezone()
                );

                $request->merge([
                    'scheduled_for' => $scheduledFor->utc()->format('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable) {
                // Field validation below presents the relevant error to the admin.
            }
        }
    }

    private function audienceCounts(): array
    {
        return [
            EmailCampaign::AUDIENCE_ALL_MODELS => User::query()
                ->where('role', 'model')
                ->whereNull('marketing_unsubscribed_at')
                ->count(),
            EmailCampaign::AUDIENCE_ONBOARDED_MODELS => User::query()
                ->where('role', 'model')
                ->whereNull('marketing_unsubscribed_at')
                ->whereHas('modelProfile', fn ($query) => $query->whereNotNull('community_role_assigned_at'))
                ->count(),
        ];
    }
}
