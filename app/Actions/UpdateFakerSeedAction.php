<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;
use Pollsockets\Pollsockets;

class UpdateFakerSeedAction
{
    public function __invoke(): void
    {
        Cache::forever('faker-seed', rand());
        Pollsockets::channel('pollsockets')->publish('refresh');
    }
}
