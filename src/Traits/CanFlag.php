<?php

namespace Sowailem\Flagable\Traits;

use Illuminate\Database\Eloquent\Model;
use Sowailem\Flagable\Facades\Flag;

trait CanFlag
{
    public function flag(Model $flagable, string $flagTypeName)
    {
        return Flag::flag($this, $flagable, $flagTypeName);
    }

    public function unflag(Model $flagable, string $flagTypeName)
    {
        return Flag::unflag($this, $flagable, $flagTypeName);
    }

    public function hasFlagged(Model $flagable, ?string $flagTypeName = null): bool
    {
        return Flag::isFlaggedBy($flagable, $this, $flagTypeName);
    }

    public function flags(?string $flagTypeName = null)
    {
        if ($flagTypeName) {
            return Flag::getFlags($this, $flagTypeName);
        }
        
        return Flag::getFlags($this);
    }

    public function flagsRelation()
    {
        return $this->morphMany(\Sowailem\Flagable\Models\Flag::class, 'flagger');
    }
}