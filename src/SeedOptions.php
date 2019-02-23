<?php
namespace JoeyRush\BetterMigrateSeed;

class SeedOptions
{
    private $default;

    private $none;

    private $other;

    private $list;

    private function __construct($seedDir, $onlyIncludeGroups = false)
    {
        $subDirectories = glob(base_path() . "$seedDir/*", GLOB_ONLYDIR);

        $this->default = 'Default Seeder (DatabaseSeeder.php)';
        $this->none = 'None (migrate without seeding)';
        $this->other = 'Other (run a specific seeder)';

        $this->list = collect($subDirectories)->map(function ($dirname) {
            $parts = explode('/', $dirname);
            return array_pop($parts);
        });

        if (! $onlyIncludeGroups) {
            $this->list->prepend([$this->default, $this->none, $this->other]);
        }
    }

    public static function getGroupsOnly($seedDir)
    {
        return new self($seedDir, $onlyIncludeGroups = true);
    }

    public static function get($seedDir)
    {
        return new self($seedDir);
    }

    public function toArray()
    {
        return $this->list->flatten()->toArray();
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getOther()
    {
        return $this->other;
    }

    public function getNone()
    {
        return $this->none;
    }

    public function __call($method, $args)
    {
        return $this->list->$method($args);
    }
}
