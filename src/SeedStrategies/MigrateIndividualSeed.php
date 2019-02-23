<?php
namespace JoeyRush\BetterMigrateSeed\SeedStrategies;

use Illuminate\Support\Facades\Artisan;

class MigrateIndividualSeed implements SeedStrategyContract
{
    private $output;

    public function __construct($buffer, $baseSeedFolder, $getChoiceCallback)
    {
        $this->output = $buffer;
        $this->baseSeedFolder = $baseSeedFolder;
        $this->getChoiceCallback = $getChoiceCallback;
    }

    public function execute(string $migrationCommandType)
    {
        $files = $this->getSeedListFromDirectoryContents(
            $this->getDirContents(base_path() . $this->baseSeedFolder)
        );

        $choice = ($this->getChoiceCallback)($files->toArray());

        Artisan::call("migrate:$migrationCommandType", [], $this->outputBuffer());
        Artisan::call('db:seed', ['--class' => $choice], $this->outputBuffer());
    }

    public function outputBuffer()
    {
        return $this->output;
    }

    private function getSeedListFromDirectoryContents($files)
    {
        return collect($files)->map(function ($file) {
            $parts = explode('/', $file);
            return array_last($parts);
        })->filter(function ($file) {
            return ends_with($file, '.php');
        })->map(function ($file) {
            return substr($file, 0, -4);
        });
    }

    private function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }
}
