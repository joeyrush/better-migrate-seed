<?php

namespace JoeyRush\BetterMigrateSeed;

class SeedGroup
{
    public $baseSeedFolder;

    public $folder;

    public $folderAbsolutePath;

    private $name;

    public function __construct(string $name, string $baseSeedFolder)
    {
        $this->baseSeedFolder = $baseSeedFolder;
        $this->name = $this->normalizeName($name);

        $this->folder = "{$this->baseSeedFolder}/{$this->name}";
        $this->folderAbsolutePath = base_path() . $this->folder;

        // Override the directory for where the new set of seeders get generated which allows us to group them
        // We'll also prefix them when we actually generate them, so it'll look like:
        // /database/seeds/SomeName/SomeNameUsersTableSeeder.php
        // /database/seeds/SomeName/SomeNamePostsTableSeeder.php
        config(['iseed::config.path' => $this->folder]);
    }

    private function normalizeName($name)
    {
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }

    public function name()
    {
        return $this->name;
    }
}
