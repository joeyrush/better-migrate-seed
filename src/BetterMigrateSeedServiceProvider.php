<?php

namespace JoeyRush\BetterMigrateSeed;

use Illuminate\Support\ServiceProvider;
use JoeyRush\BetterMigrateSeed\Commands\BetterMigrateSeed;
use JoeyRush\BetterMigrateSeed\Commands\DeleteSeedGroups;
use JoeyRush\BetterMigrateSeed\Commands\GenerateSeedersFromDatabase;
use JoeyRush\BetterMigrateSeed\Commands\RenameSeedGroups;

class BetterMigrateSeedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSeedersFromDatabase::class,
                BetterMigrateSeed::class,
                DeleteSeedGroups::class,
                RenameSeedGroups::class,
            ]);
        }
    }
}
