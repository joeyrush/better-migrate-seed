<?php

namespace JoeyRush\BetterMigrateSeed;

use Illuminate\Support\ServiceProvider;
use JoeyRush\BetterMigrateSeed\Commands\BetterMigrateSeed;
use JoeyRush\BetterMigrateSeed\Commands\GenerateSeedersFromDatabase;

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
            ]);
        }
    }
}
