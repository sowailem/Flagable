<?php

namespace Sowailem\Flagable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Flag extends Model
{
    protected $fillable = ['flag_link_id', 'flagger_type', 'flagger_id'];

    public $timestamps = true;

    public function link(): BelongsTo
    {
        return $this->belongsTo(FlagLink::class, 'flag_link_id');
    }

    public function flagger(): MorphTo
    {
        return $this->morphTo();
    }
}