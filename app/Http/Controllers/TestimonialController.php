<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __invoke(): View
    {
        $testimonials = Schema::hasTable('testimonials')
            ? Testimonial::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get()
            : Collection::make();

        return view('marketing.success-stories', compact('testimonials'));
    }
}
