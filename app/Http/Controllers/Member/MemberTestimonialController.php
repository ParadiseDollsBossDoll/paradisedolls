<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberTestimonialController extends Controller
{
    public function create(Request $request): View
    {
        $testimonials = $request->user()
            ->testimonials()
            ->latest()
            ->get();

        return view('member.testimonials.create', compact('testimonials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_handle' => ['required', 'string', 'max:30', 'regex:/^@?(?=.*[A-Za-z0-9])[A-Za-z0-9_.]+$/'],
            'quote' => ['required', 'string', 'max:700'],
            'result_label' => ['required', 'string', 'max:80'],
        ], [
            'display_handle.regex' => __('Use only letters, numbers, underscores, or periods for your handle.'),
        ]);

        $validated['display_handle'] = $this->normalizeHandle($validated['display_handle']);

        Testimonial::create([
            ...$validated,
            'headline' => $this->headlineFor($validated),
            'submitted_by' => $request->user()->id,
            'is_published' => false,
            'sort_order' => 0,
        ]);

        return redirect()
            ->route('member.testimonials.create')
            ->with('status', __('Your testimonial was submitted for admin approval.'));
    }

    private function normalizeHandle(string $handle): string
    {
        return Str::of($handle)
            ->lower()
            ->replaceMatches('/^@+/', '')
            ->replaceMatches('/[^a-z0-9_.]+/', '')
            ->trim('.')
            ->limit(30, '')
            ->toString();
    }

    private function headlineFor(array $validated): string
    {
        $label = trim((string) ($validated['result_label'] ?? ''));

        return $label !== ''
            ? Str::of($label)->replaceMatches('/\s+/', ' ')->title()->limit(255)->toString()
            : Str::of($validated['quote'])->replaceMatches('/\s+/', ' ')->limit(255)->toString();
    }
}
