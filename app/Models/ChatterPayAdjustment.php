<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterPayAdjustment extends Model
{
    use HasFactory;

    protected $fillable = ['chatter_timesheet_id', 'created_by', 'amount_pence', 'label', 'note'];

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(ChatterTimesheet::class, 'chatter_timesheet_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
