<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('testimonials')) {
            return;
        }

        $demoTestimonials = [
            ['New Paradise Dolls member', 'From nervous beginner to confident online earner'],
            ['Boss Doll Blueprint student', 'The system made the platforms less overwhelming'],
            ['Paradise Dolls community member', 'I finally felt like I had a team behind me'],
        ];

        DB::table('testimonials')
            ->whereNull('submitted_by')
            ->where(function ($query) use ($demoTestimonials) {
                foreach ($demoTestimonials as [$name, $headline]) {
                    $query->orWhere(function ($testimonial) use ($name, $headline) {
                        $testimonial->where('name', $name)->where('headline', $headline);
                    });
                }
            })
            ->delete();
    }

    public function down(): void
    {
        // Removed demo testimonials are intentionally not restored.
    }
};
