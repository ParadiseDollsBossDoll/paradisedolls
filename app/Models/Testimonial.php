<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    protected $fillable = [
        'submitted_by',
        'name',
        'display_handle',
        'headline',
        'quote',
        'location',
        'result_label',
        'image_url',
        'image_path',
        'is_published',
        'approved_by',
        'approved_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function displayImage(): string
    {
        if (filled($this->image_path) && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        return $this->image_url ?: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=85&w=900';
    }

    public function displayAvatar(): string
    {
        return $this->submitter?->profilePhotoUrl() ?: $this->displayImage();
    }

    public function displayHandle(): string
    {
        $source = filled($this->display_handle) ? $this->display_handle : $this->name;

        $handle = Str::of($source ?: 'member')
            ->lower()
            ->replaceMatches('/^@+/', '')
            ->replaceMatches('/[^a-z0-9_.]+/', '')
            ->trim('.')
            ->substr(0, 30)
            ->toString();

        return '@'.($handle !== '' ? $handle : 'member');
    }

    public function displayHashtag(): ?string
    {
        $source = $this->result_label ?: $this->headline;

        if (blank($source)) {
            return null;
        }

        $tag = Str::of($source)
            ->title()
            ->replaceMatches('/[^A-Za-z0-9]+/', '')
            ->substr(0, 32)
            ->toString();

        return $tag !== '' ? '#'.$tag : null;
    }

    public function statusLabel(): string
    {
        if ($this->is_published) {
            return __('Approved');
        }

        return $this->submitted_by ? __('Pending review') : __('Draft');
    }
}
