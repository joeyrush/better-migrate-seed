<?php

namespace JoeyRush\BetterMigrateSeed;

class SeedGroup
{
    public $baseSeedFolder = '/database/seeds';

    public $folder;

    public $folderAbsolutePath;

    private $name;

    public function __construct($name)
    {
        $this->name = $this->normalizeName($name);

        // Override the directory for where the seeders get generated (todo: normalize filename to lowercase/underscore)
        $this->folder = $this->baseSeedFolder . "/$directory";
        $this->folderAbsolutePath = base_path() . $this->folder;
        config(['iseed::config.path' => $this->folder]);
    }

    private function normalizeName($name)
    {
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }
}
