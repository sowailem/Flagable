<?php

namespace Sowailem\Flagable\Database\Seeders;

use Illuminate\Database\Seeder;
use Sowailem\Flagable\Models\FlagType;

class FlagTypeSeeder extends Seeder
{
    public function run()
    {
        $defaultTypes = [
            'like',
            'follow',
            'favorite',
            'bookmark',
            'upvote',
            'downvote'
        ];

        foreach ($defaultTypes as $type) {
            FlagType::firstOrCreate(['name' => $type]);
        }
    }
}