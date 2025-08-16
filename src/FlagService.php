<?php

namespace Sowailem\Flagable;

use Sowailem\Flagable\Models\Flag;
use Sowailem\Flagable\Models\FlagLink;
use Sowailem\Flagable\Models\FlagType;
use Sowailem\Flagable\Models\FlagTarget;
use Illuminate\Database\Eloquent\Model;

class FlagService
{
    public function addFlagType(string $name): FlagType
    {
        return FlagType::firstOrCreate(['name' => $name]);
    }

    public function removeFlagType(string $name): bool
    {
        return FlagType::where('name', $name)->delete() > 0;
    }

    public function flag(Model $flagger, Model $flagable, string $flagTypeName): Flag
    {
        $flagType = $this->addFlagType($flagTypeName);

        $flagTarget = FlagTarget::firstOrCreate([
            'name' => get_class($flagable)
        ]);

        $flagLink = FlagLink::firstOrCreate([
            'flag_type_id' => $flagType->id,
            'flag_target_id' => $flagTarget->id
        ]);

        return Flag::firstOrCreate([
            'flag_link_id' => $flagLink->id,
            'flagger_type' => get_class($flagger),
            'flagger_id' => $flagger->getKey(),
        ]);
    }

    public function unflag(Model $flagger, Model $flagable, string $flagTypeName): bool
    {
        $flagType = FlagType::where('name', $flagTypeName)->first();

        if (!$flagType) {
            return false;
        }

        $flagTarget = FlagTarget::where('name', get_class($flagable))->first();

        if (!$flagTarget) {
            return false;
        }

        $flagLink = FlagLink::where('flag_type_id', $flagType->id)
            ->where('flag_target_id', $flagTarget->id)
            ->first();

        if (!$flagLink) {
            return false;
        }

        return Flag::where('flag_link_id', $flagLink->id)
                ->where('flagger_type', get_class($flagger))
                ->where('flagger_id', $flagger->getKey())
                ->delete() > 0;
    }

    public function isFlaggedBy(Model $flagable, Model $flagger, ?string $flagTypeName = null): bool
    {
        $query = Flag::query()
            ->whereHas('link.target', function ($query) use ($flagable) {
                $query->where('name', get_class($flagable));
            })
            ->where('flagger_type', get_class($flagger))
            ->where('flagger_id', $flagger->getKey());

        if ($flagTypeName) {
            $query->whereHas('link.type', function ($query) use ($flagTypeName) {
                $query->where('name', $flagTypeName);
            });
        }

        return $query->exists();
    }

    public function getFlaggers(Model $flagable, string $flagTypeName, string $flaggerModel): \Illuminate\Database\Eloquent\Collection
    {
        return $flaggerModel::whereHas('flags', function ($query) use ($flagable, $flagTypeName) {
            $query->whereHas('link', function ($query) use ($flagable, $flagTypeName) {
                $query->whereHas('target', function ($query) use ($flagable) {
                    $query->where('name', get_class($flagable));
                })->whereHas('type', function ($query) use ($flagTypeName) {
                    $query->where('name', $flagTypeName);
                });
            });
        })->get();
    }

    public function getFlagCount(Model $flagable, ?string $flagTypeName = null): int
    {
        $query = Flag::query()
            ->whereHas('link.target', function ($query) use ($flagable) {
                $query->where('name', get_class($flagable));
            });

        if ($flagTypeName) {
            $query->whereHas('link.type', function ($query) use ($flagTypeName) {
                $query->where('name', $flagTypeName);
            });
        }

        return $query->count();
    }
}