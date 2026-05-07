<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $testimonials = Schema::hasTable('testimonials')
            ? Testimonial::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->take(3)
                ->get()
            : Collection::make();

        return view('home', compact('testimonials'));
    }
}
