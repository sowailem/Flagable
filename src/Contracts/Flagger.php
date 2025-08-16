<?php

namespace Sowailem\Flagable\Contracts;

interface Flagger
{
    public function flag($flagger, $flagable, string $flagTypeName);

    public function unflag($flagger, $flagable, string $flagTypeName);

    public function isFlaggedBy($flagable, $flagger, ?string $flagTypeName = null): bool;

    public function getFlaggers($flagable, string $flagTypeName, string $flaggerModel);

    public function getFlagCount($flagable, ?string $flagTypeName = null): int;
}