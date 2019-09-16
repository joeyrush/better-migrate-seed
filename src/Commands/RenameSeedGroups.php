<?php

namespace JoeyRush\BetterMigrateSeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JoeyRush\BetterMigrateSeed\SeedGroup;
use JoeyRush\BetterMigrateSeed\SeedOptions;

class RenameSeedGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:rename';

    protected $baseSeedFolder;

    protected $absoluteBaseFolder;

    private $renameFrom;

    private $renameTo;

    private $originalFolder;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseSeedFolder = config('iseed::config.path');
        $this->absoluteBaseFolder = base_path() . $this->baseSeedFolder . '/';
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

        $name = $this->choice('Which scenario would you like to rename?', $seedOptions->toArray());
        $newName = $this->ask('What will you rename "' . $name . '" to?');
        $this->rename($name, $newName);
    }

    /**
     * Renames a named directory of seeders including:
     *  1. The filenames
     *  2. The classnames
     *  3. The references to those classnames inside the base seeder
     *
     * @param $name
     * @param $to
     *
     * @return void
     */
    public function rename($name, $to)
    {
        $this->renameFrom = $name;
        $this->renameTo = (new SeedGroup($to, $this->baseSeedFolder))->name();
        $this->originalFolder = $this->absoluteBaseFolder . $this->renameFrom . '/';

        $this->renameFiles()
            ->updateReferences()
            ->renameDirectory()
            ->dumpAutoload();
    }

    public function extractFileData($file)
    {
        return array_values([
            'filename' => $filename = $file->getFilename(),
            'classname' => $classname = $file->getFilenameWithoutExtension(),
            'newFilename' => Str::replaceFirst($this->renameFrom, $this->renameTo, $filename),
            'newClassname' => Str::replaceFirst($this->renameFrom, $this->renameTo, $classname)
        ]);
    }

    protected function renameFiles()
    {
        $files = File::allFiles($this->originalFolder);
        foreach ($files as $file) {
            list($filename, $classname, $newFilename, $newClassname) = $this->extractFileData($file);
            $this->info("Renaming $filename to $newFilename");

            $updatedFileContents = str_replace(
                "class $classname",
                "class $newClassname",
                File::get($this->originalFolder . $filename)
            );

            // First update the php class with the new classname, and then rename the file itself.
            File::put($this->originalFolder . $filename, $updatedFileContents);
            File::move($this->originalFolder . $filename, $this->originalFolder . $newFilename);
        }

        return $this;
    }

    protected function updateReferences()
    {
        $defaultSeederName = $this->renameTo . 'DatabaseSeeder.php';
        $this->info("Updating the references in $defaultSeederName");
        $defaultSeederContents = str_replace($this->renameFrom, $this->renameTo, File::get($this->originalFolder . $defaultSeederName));
        File::put($this->originalFolder . $defaultSeederName, $defaultSeederContents);

        return $this;
    }

    protected function renameDirectory()
    {
        $this->info("Renaming the seed group directory");
        File::moveDirectory($this->originalFolder, $this->absoluteBaseFolder . $this->renameTo);

        return $this;
    }

    public function dumpAutoload()
    {
        shell_exec('composer dumpautoload');

        return $this;
    }

}
