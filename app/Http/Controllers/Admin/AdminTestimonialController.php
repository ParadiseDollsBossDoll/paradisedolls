<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminTestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()
            ->with(['submitter:id,name,email,profile_photo_path', 'approver:id,name'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTestimonial($request);
        $validated = [
            ...$validated,
            ...$this->approvalAttributes($request->boolean('is_published')),
        ];
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated = $this->withUploadedPhoto($request, $validated);

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story created.'));
    }

    public function edit(Testimonial $testimonial): View
    {
        $testimonial->loadMissing('submitter:id,name');

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $this->validateTestimonial($request);
        $validated = [
            ...$validated,
            ...$this->approvalAttributes($request->boolean('is_published'), $testimonial),
        ];
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated = $this->withUploadedPhoto($request, $validated, $testimonial);

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('status', __('Success story updated.'));
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->image_path) {
            Storage::disk('public')->delete($testimonial->image_path);
        }

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story deleted.'));
    }

    public function approve(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->approvalAttributes(true));

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story approved and published.'));
    }

    public function visibility(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->approvalAttributes($request->boolean('is_published'), $testimonial));

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story visibility updated.'));
    }

    private function validateTestimonial(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_handle' => ['required', 'string', 'max:30', 'regex:/^@?(?=.*[A-Za-z0-9])[A-Za-z0-9_.]+$/'],
            'quote' => ['required', 'string', 'max:700'],
            'result_label' => ['required', 'string', 'max:80'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ], [
            'display_handle.regex' => __('Use only letters, numbers, underscores, or periods for the handle.'),
        ]);

        unset($validated['photo']);

        $validated['display_handle'] = $this->normalizeHandle($validated['display_handle']);
        $validated['headline'] = $this->headlineFor($validated);

        return $validated;
    }

    private function withUploadedPhoto(Request $request, array $attributes, ?Testimonial $testimonial = null): array
    {
        if (! $request->hasFile('photo')) {
            return $attributes;
        }

        if ($testimonial?->image_path) {
            Storage::disk('public')->delete($testimonial->image_path);
        }

        $attributes['image_path'] = $request->file('photo')->store('testimonials', 'public');
        $attributes['image_url'] = null;

        return $attributes;
    }

    private function approvalAttributes(bool $approved, ?Testimonial $testimonial = null): array
    {
        if (! $approved) {
            return [
                'is_published' => false,
                'approved_by' => null,
                'approved_at' => null,
            ];
        }

        return [
            'is_published' => true,
            'approved_by' => $testimonial?->approved_by ?? auth()->id(),
            'approved_at' => $testimonial?->approved_at ?? now(),
        ];
    }

    private function normalizeHandle(?string $handle): ?string
    {
        if (blank($handle)) {
            return null;
        }

        $normalized = Str::of($handle)
            ->lower()
            ->replaceMatches('/^@+/', '')
            ->replaceMatches('/[^a-z0-9_.]+/', '')
            ->trim('.')
            ->limit(30, '')
            ->toString();

        return $normalized !== '' ? $normalized : null;
    }

    private function headlineFor(array $validated): string
    {
        $label = trim((string) ($validated['result_label'] ?? ''));

        return $label !== ''
            ? Str::of($label)->replaceMatches('/\s+/', ' ')->title()->limit(255)->toString()
            : Str::of($validated['quote'])->replaceMatches('/\s+/', ' ')->limit(255)->toString();
    }
}
