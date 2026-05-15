<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminModuleController extends Controller
{
    public function store(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'client_key' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $module = $course->modules()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_published' => $validated['is_published'] ?? true,
            'sort_order' => $validated['sort_order'] ?? ($course->modules()->max('sort_order') + 1),
        ]);

        return response()->json([
            'id' => $module->id,
            'client_key' => $validated['client_key'],
            'title' => $module->title,
            'description' => $module->description,
            'is_published' => $module->is_published,
            'sort_order' => $module->sort_order,
        ], 201);
    }

    public function update(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        abort_unless($module->course_id === $course->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $module->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? $module->description,
            'is_published' => array_key_exists('is_published', $validated)
                ? (bool) $validated['is_published']
                : $module->is_published,
            'sort_order' => $validated['sort_order'] ?? $module->sort_order,
        ]);

        return response()->json([
            'id' => $module->id,
            'title' => $module->title,
            'description' => $module->description,
            'is_published' => $module->is_published,
            'sort_order' => $module->sort_order,
        ]);
    }

    public function destroy(Course $course, CourseModule $module): JsonResponse
    {
        abort_unless($module->course_id === $course->id, 404);

        // Unassign lessons rather than deleting them — they stay in the course.
        $course->lessons()->where('course_module_id', $module->id)
            ->update(['course_module_id' => null]);

        $module->delete();

        return response()->json(['deleted' => true]);
    }

    public function reorder(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => [
                'required',
                'integer',
                Rule::exists('course_modules', 'id')
                    ->where(fn ($q) => $q->where('course_id', $course->id)),
            ],
        ]);

        foreach ($validated['order'] as $position => $moduleId) {
            $course->modules()->whereKey($moduleId)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['reordered' => true]);
    }
}
