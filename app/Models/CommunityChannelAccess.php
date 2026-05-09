<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityChannelAccess extends Model
{
    protected $fillable = [
        'community_channel_id',
        'user_id',
        'invited_by',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(CommunityChannel::class, 'community_channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
