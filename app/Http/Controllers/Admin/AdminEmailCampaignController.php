<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\User;
use App\Services\EmailCampaignDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminEmailCampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = EmailCampaign::query()
            ->with(['creator:id,name', 'latestRun'])
            ->latest()
            ->paginate(15);

        return view('admin.email-campaigns.index', [
            'campaigns' => $campaigns,
            'allModelsCount' => User::query()
                ->where('role', 'model')
                ->whereNull('marketing_unsubscribed_at')
                ->count(),
            'onboardedModelsCount' => User::query()
                ->where('role', 'model')
                ->whereNull('marketing_unsubscribed_at')
                ->whereHas('modelProfile', fn ($query) => $query->whereNotNull('community_role_assigned_at'))
                ->count(),
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
        ]);
    }

    public function store(Request $request, EmailCampaignDispatcher $dispatcher): RedirectResponse
    {
        $validated = $request->validate($this->campaignRules(includeDeliveryMode: true));

        $campaign = EmailCampaign::create([
            ...$this->contentAttributes($validated),
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
        $validated = $request->validate([
            'scheduled_for' => ['required', 'date', 'after:now'],
        ]);

        $emailCampaign->update([
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => $validated['scheduled_for'],
        ]);

        return back()->with('status', __('Campaign scheduled.'));
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
            'repeat_every_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];

        if ($includeDeliveryMode) {
            $rules['delivery_mode'] = ['required', Rule::in(['draft', 'send_now', 'schedule'])];
            $rules['scheduled_for'] = ['nullable', 'required_if:delivery_mode,schedule', 'date', 'after:now'];
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
            'repeat_every_days' => filled($validated['repeat_every_days'] ?? null)
                ? (int) $validated['repeat_every_days']
                : null,
        ];
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
}
