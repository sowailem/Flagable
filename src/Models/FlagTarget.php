<?php

namespace Sowailem\Flagable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlagTarget extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function links(): HasMany
    {
        return $this->hasMany(FlagLink::class);
    }
}