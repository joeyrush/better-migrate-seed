<?php
namespace JoeyRush\BetterMigrateSeed;

class SeedOptions
{
    private $default;

    private $other;

    private $list;

    private function __construct($seedDir)
    {
        $subDirectories = glob(base_path() . "$seedDir/*", GLOB_ONLYDIR);

        $this->default = 'Default Seeder (DatabaseSeeder.php)';
        $this->other = 'Other (run a specific seeder)';

        $this->list = collect($subDirectories)->map(function ($dirname) {
            $parts = explode('/', $dirname);
            return array_pop($parts);
        })->prepend($this->other)->prepend($this->default);
    }

    public static function get($seedDir)
    {
        return new self($seedDir);
    }

    public function toArray()
    {
        return $this->list->toArray();
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getOther()
    {
        return $this->other;
    }
}
