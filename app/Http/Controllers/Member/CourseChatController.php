<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChatMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseChatController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        CourseChatMessage::create([
            'course_id' => $course->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->back()->withFragment('course-chat')->with('status', __('Message posted.'));
    }
}
