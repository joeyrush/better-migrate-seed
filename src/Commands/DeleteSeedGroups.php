<?php

namespace JoeyRush\BetterMigrateSeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use JoeyRush\BetterMigrateSeed\SeedOptions;

class DeleteSeedGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a set of seeders generated with the seed:generate command';

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
        $seedOptions = SeedOptions::getGroupsOnly($this->baseSeedFolder);

        if (!$seedOptions->count()) {
            return $this->info('No seed groups available. Use the seed:generate or seed:migrate command to generate one');
        }

        $choice = $this->choice('Which scenario would you like to delete?', $seedOptions->toArray());
        $this->delete($choice);
    }

    public function delete($name)
    {
        $toDelete = $this->baseSeedFolder . '/' . $name;

        $this->info("Deleting $toDelete");
        if (File::deleteDirectory(base_path() . $toDelete)) {
            $this->info("Seeder deleted successfully");
        } else {
            $this->info("Unable to delete $toDelete, check that it exists and the permissions are correct.");
        }
    }
}
