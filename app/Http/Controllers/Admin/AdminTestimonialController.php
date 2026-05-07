<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()
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
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story created.'));
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $this->validateTestimonial($request);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('status', __('Success story updated.'));
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story deleted.'));
    }

    public function visibility(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update([
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.testimonials.index')->with('status', __('Success story visibility updated.'));
    }

    private function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'quote' => ['required', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'result_label' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);
    }
}
