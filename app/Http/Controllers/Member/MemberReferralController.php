<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ModelReferral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberReferralController extends Controller
{
    public function index(Request $request): View
    {
        $member = $request->user();
        $member->ensureReferralCode();

        $referrals = $member->modelReferrals()
            ->with(['application:id,status,user_id', 'application.user:id,name'])
            ->latest()
            ->paginate(10);

        return view('member.referrals.index', [
            'member' => $member,
            'referralLink' => route('apply', ['ref' => $member->referral_code]),
            'referrals' => $referrals,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'candidate_name' => ['required', 'string', 'max:255'],
            'candidate_email' => ['nullable', 'email', 'max:255'],
            'candidate_phone' => ['nullable', 'string', 'max:64'],
            'candidate_social_handle' => ['nullable', 'string', 'max:255'],
            'experience_level' => ['required', 'string', Rule::in(['none', 'beginner', '1-2', '3+'])],
            'note' => ['nullable', 'string', 'max:2000'],
            'photos' => ['required', 'array', 'min:1', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'consent_confirmed' => ['accepted'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            if (
                blank($request->input('candidate_email'))
                && blank($request->input('candidate_phone'))
                && blank($request->input('candidate_social_handle'))
            ) {
                $validator->errors()->add(
                    'candidate_email',
                    __('Provide at least one contact method: email, phone, or social handle.')
                );
            }
        });

        $validated = $validator->validate();

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = $photo->store('referrals/photos', 'local');
        }

        $request->user()->modelReferrals()->create([
            'candidate_name' => $validated['candidate_name'],
            'candidate_email' => $validated['candidate_email'] ?? null,
            'candidate_phone' => $validated['candidate_phone'] ?? null,
            'candidate_social_handle' => $validated['candidate_social_handle'] ?? null,
            'experience_level' => $validated['experience_level'],
            'note' => $validated['note'] ?? null,
            'photo_paths' => $photoPaths,
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_MEMBER_FORM,
            'status' => ModelReferral::STATUS_REFERRED,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        return redirect()
            ->route('member.referrals.index')
            ->with('status', __('Referral submitted. The admin team can now review it from Applications.'));
    }
}
