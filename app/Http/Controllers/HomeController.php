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
        try {
            $testimonials = Schema::hasTable('testimonials')
                ? Testimonial::query()
                    ->with('submitter:id,name,profile_photo_path')
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('created_at')
                    ->take(8)
                    ->get()
                : Collection::make();
        } catch (\Exception $e) {
            $testimonials = Collection::make();
        }

        return view('home', compact('testimonials'));
    }
}
