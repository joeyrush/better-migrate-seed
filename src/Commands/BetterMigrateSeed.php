<?php

namespace JoeyRush\BetterMigrateSeed\Commands;

use Illuminate\Console\Command;
use JoeyRush\BetterMigrateSeed\SeedOptions;
use JoeyRush\BetterMigrateSeed\SeedStrategies\DefaultMigrateSeed;
use JoeyRush\BetterMigrateSeed\SeedStrategies\MigrateGroupSeed;
use JoeyRush\BetterMigrateSeed\SeedStrategies\MigrateIndividualSeed;
use JoeyRush\BetterMigrateSeed\SeedStrategies\MigrateWithoutSeeding;
use JoeyRush\BetterMigrateSeed\SeedStrategies\SeedStrategyContract;

class BetterMigrateSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:migrate {--refresh : use migrate:refresh instead of migrate:fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a set of seeders from existing database and choose which set of seeders to run';

    protected $baseSeedFolder;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseSeedFolder = config('iseed::config.path');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $message = 'Do you want to generate a set of seeders from your current data before doing a fresh migrate?';
        if ($this->confirm($message)) {
            $this->call('seed:generate');
        }

        $seedOptions = SeedOptions::get($this->baseSeedFolder);
        $choice = $this->choice('Which scenario would you like to seed?', $seedOptions->toArray(), 0);
        
        $migrationCommand = $this->option('refresh') ? 'refresh' : 'fresh';
        $this->getSeedStrategy($choice, $seedOptions)->execute($migrationCommand);
    }

    /**
     * Determine which artisan commands to run based on the users chosen seed option.
     * @param  string      $choice
     * @param  SeedOptions $seedOptions
     * @return SeedStrategyContract
     */
    public function getSeedStrategy(string $choice, SeedOptions $seedOptions) : SeedStrategyContract
    {
        if ($choice == $seedOptions->getNone()) {
            return new MigrateWithoutSeeding($this->output);
        }

        if ($choice == $seedOptions->getDefault()) {
            return new DefaultMigrateSeed($this->output);
        }

        return $choice == $seedOptions->getOther()
            ? new MigrateIndividualSeed($this->output, $this->baseSeedFolder, function ($options) {
                return $this->choice('Pick a seeder?', $options, 0);
            })
            : new MigrateGroupSeed($this->output, $choice);
    }
}
