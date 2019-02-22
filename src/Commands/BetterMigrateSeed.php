<?php

namespace JoeyRush\BetterMigrateSeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JoeyRush\BetterMigrateSeed\SeedGroup;
use JoeyRush\BetterMigrateSeed\SeedOptions;

class BetterMigrateSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a set of seeders from existing database and choose which set of seeders to run';

    /**
     * @var Connection
     */
    protected $connection;

    protected $baseSeedFolder;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->connection = DB::getDoctrineConnection();
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
            $directory = $this->ask('Give the seeders a name (defaults to the current timestamp)') ?? time();
            $this->seedGroup = new SeedGroup($directory, $this->baseSeedFolder);

            // Make sure the directory exists
            if (! File::isDirectory($this->seedGroup->folderAbsolutePath)) {
                $this->line("Creating {$this->seedGroup->folder} directory");
                File::makeDirectory($this->seedGroup->folderAbsolutePath);
            }

            $this->createBaseSeeder();
            $this->createSeedersFromExistingData();
            $this->prefixBaseSeeder();

            // We need to regenerate the autoloader since we've generated non-namespaced files
            // but the file won't be loaded within this script, so we'll need to manually include it :hide:
            shell_exec('composer dumpautoload');
            foreach (glob($this->seedGroup->folderAbsolutePath . '/*') as $seedClass) {
                require_once($seedClass);
            }
        }

        $seedOptions = SeedOptions::get($this->baseSeedFolder);
        $choice = $this->choice('Which scenario would you like to seed?', $seedOptions->toArray(), 0);
        
        if ($choice == $seedOptions->getDefault()) {
            Artisan::call('migrate:fresh', ['--seed' => true], $this->output);
        } else {
            Artisan::call('migrate:fresh', [], $this->output);
            Artisan::call('db:seed', ['--class' => $choice . 'DatabaseSeeder'], $this->output);
        }
    }

    protected function createSeedersFromExistingData()
    {
        $tableNames = $this->getTableNames();
        $tableNames = array_diff($tableNames, ['migrations']);

        $this->line("Setting up seeders from your database");
        Artisan::call('iseed', [
            'tables' => implode(',', $tableNames),
            '--classnameprefix' => $this->seedGroup->name(),
            '--force' => true,
        ], $this->output);
    }

    public function createBaseSeeder()
    {
        // Make sure theres a base seeder in the specified directory.
        $this->line("Creating base seeder (DatabaseSeeder.php) for the {$this->seedGroup->folder} directory");
        Artisan::call("make:seeder", ['name' => "{$this->seedGroup->name()}/DatabaseSeeder"], $this->output);
    }

    public function prefixBaseSeeder()
    {
        // Fix the class name and put it in the correct filename.
        $baseSeederClassName = $this->seedGroup->name() . 'DatabaseSeeder';
        $databaseSeederContents = File::get($this->seedGroup->folderAbsolutePath . "/DatabaseSeeder.php");
        $this->line("Prefixing the base DatabaseSeeder class to match the name you provided to avoid class collisions");
        File::put(
            $this->seedGroup->folderAbsolutePath . '/' . $baseSeederClassName . '.php',
            str_replace("{$this->seedGroup->name()}/DatabaseSeeder", $baseSeederClassName, $databaseSeederContents)
        );

        // Remove the incorrectly named one to avoid collisions with the default database seeder class
        File::delete($this->seedGroup->folderAbsolutePath . '/DatabaseSeeder.php');
    }

    protected function getTableNames(): array
    {
        $sm = $this->connection->getSchemaManager();
        $databaseName = $this->connection->getDatabase();

        $fullSchema = $sm->createSchema();
        $tableNames = $fullSchema->getTableNames();

        $shortTableNames = str_replace("$databaseName.", '', $tableNames);

        return $shortTableNames;
    }
}
