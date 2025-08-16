<?php

namespace Sowailem\Flagable\Traits;

use Illuminate\Database\Eloquent\Model;
use Sowailem\Flagable\Facades\Flag;

trait Flagable
{
    public function isFlaggedBy(Model $flagger, ?string $flagTypeName = null): bool
    {
        return Flag::isFlaggedBy($this, $flagger, $flagTypeName);
    }

    public function flagCount(?string $flagTypeName = null): int
    {
        return Flag::getFlagCount($this, $flagTypeName);
    }

    public function flaggers(string $flagTypeName, string $flaggerModel)
    {
        return Flag::getFlaggers($this, $flagTypeName, $flaggerModel);
    }

    public function flags()
    {
        return $this->hasManyThrough(
            \Sowailem\Flagable\Models\Flag::class,
            \Sowailem\Flagable\Models\FlagLink::class,
            'flag_target_id',
            'flag_link_id'
        )->whereHas('link.target', function ($query) {
            $query->where('name', get_class($this));
        });
    }
}