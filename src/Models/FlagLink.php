<?php

namespace Sowailem\Flagable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlagLink extends Model
{
    protected $fillable = ['flag_type_id', 'flag_target_id'];

    public $timestamps = false;

    public function type(): BelongsTo
    {
        return $this->belongsTo(FlagType::class, 'flag_type_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(FlagTarget::class, 'flag_target_id');
    }

    public function flags(): HasMany
    {
        return $this->hasMany(Flag::class);
    }
}