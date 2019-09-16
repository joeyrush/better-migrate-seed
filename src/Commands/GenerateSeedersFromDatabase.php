<?php

namespace JoeyRush\BetterMigrateSeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JoeyRush\BetterMigrateSeed\SeedGroup;

class GenerateSeedersFromDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:generate {--include-empty-tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a set of seeders from existing database';

    /**
     * @var Connection
     */
    protected $connection;

    protected $baseSeedFolder;

    protected $tasks = [
        'verifyDirectoryExists',
        'createBaseSeeder',
        'createSeedersFromExistingData',
        'prefixBaseSeeder',
        'dumpAutoload',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Prevent an exception being thrown if one of the DB tables contains an 'enum'.
        // We don't care about the column types, it's just to get a list of table names!
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

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
        $directory = $this->ask('Give the seeders a name (defaults to the current timestamp)') ?? $this->generatedName();
        $this->seedGroup = new SeedGroup($directory, $this->baseSeedFolder);

        foreach ($this->tasks as $task) {
            $this->$task();
        }
    }

    /**
     * Generate a *unique* name for the seeders for when a user doesnt want to supply a custom one
     * @return string
     */
    public function generatedName() : string
    {
        // note: classes cannot start with a number
        return 'DB_' . time();
    }

    /**
     * Use iseed to generate a folder of seeders based off of the existing data in the default database connection
     * @return void
     */
    private function createSeedersFromExistingData() : void
    {
        $tableNames = $this->getTableNames($this->option('include-empty-tables'));
        $tableNames = array_diff($tableNames, ['migrations']);

        $this->line("Setting up seeders from your database");
        Artisan::call('iseed', [
            'tables' => implode(',', $tableNames),
            '--classnameprefix' => $this->seedGroup->name(),
            '--force' => true,
        ], $this->output);
    }

    /**
     * Create a "DatabaseSeeder.php" file inside a subdirectory named after the users input.
     * This is where iseed puts the code to call all of the newly generated seeders
     * This will be renamed to XDatabaseSeeder after we've got the new seeders (where X = user supplied name)
     * @return void
     */
    private function createBaseSeeder() : void
    {
        // Make sure theres a base seeder in the specified directory.
        $this->line("Creating base seeder (DatabaseSeeder.php) in {$this->seedGroup->folder}");
        Artisan::call("make:seeder", ['name' => "{$this->seedGroup->name()}/DatabaseSeeder"], $this->output);
    }

    /**
     * Rename X/DatabaseSeeder.php to X/XDatabaseSeeder.php to prevent class collisions since seeders aren't namespaced.
     * (where X = user supplied name)
     * @return void
     */
    private function prefixBaseSeeder() : void
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

    private function verifyDirectoryExists()
    {
        // Make sure the user supplied seeder name sub-directory exists
        if (! File::isDirectory($this->seedGroup->folderAbsolutePath)) {
            $this->line("Creating {$this->seedGroup->folder} directory");
            File::makeDirectory($this->seedGroup->folderAbsolutePath);
        }
    }

    private function getTableNames($includeEmptyTables = false) : array
    {
        $sm = $this->connection->getSchemaManager();
        $databaseName = $this->connection->getDatabase();

        $fullSchema = $sm->createSchema();
        $tableNames = $fullSchema->getTableNames();

        $shortTableNames = str_replace("$databaseName.", '', $tableNames);

        return $includeEmptyTables
            ? $shortTableNames
            : collect($shortTableNames)->filter(function ($tableName) {
                return DB::table($tableName)->count();
            })->toArray();
    }

    private function dumpAutoload()
    {
        // We need to regenerate the autoloader since we've generated non-namespaced files
        // but the file won't be loaded within this script, so we'll need to manually include it :hide:
        shell_exec('composer dumpautoload');
        foreach (glob($this->seedGroup->folderAbsolutePath . '/*') as $seedClass) {
            require_once($seedClass);
        }
    }
}
