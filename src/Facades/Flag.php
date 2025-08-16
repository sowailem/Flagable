<?php

namespace Sowailem\Flagable\Facades;

use Illuminate\Support\Facades\Facade;

class Flag extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'flag';
    }
}