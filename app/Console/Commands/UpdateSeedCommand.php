<?php

namespace App\Console\Commands;

use App\Actions\UpdateFakerSeedAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('update:seed')]
#[Description('Change the faker seed and publish a pollsockets refresh event')]
class UpdateSeedCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UpdateFakerSeedAction $action): int
    {
        $action();

        return self::SUCCESS;
    }
}
