<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAccessRequestFile extends Model
{
    protected $fillable = [
        'course_access_request_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function accessRequest(): BelongsTo
    {
        return $this->belongsTo(CourseAccessRequest::class, 'course_access_request_id');
    }

    public function displaySize(): string
    {
        if (! $this->size) {
            return '';
        }

        if ($this->size < 1024 * 1024) {
            return round($this->size / 1024, 1).' KB';
        }

        return round($this->size / (1024 * 1024), 1).' MB';
    }
}
