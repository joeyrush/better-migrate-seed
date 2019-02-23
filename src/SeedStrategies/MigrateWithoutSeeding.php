<?php
namespace JoeyRush\BetterMigrateSeed\SeedStrategies;

use Illuminate\Support\Facades\Artisan;

class MigrateWithoutSeeding implements SeedStrategyContract
{
    private $output;

    public function __construct($buffer)
    {
        $this->output = $buffer;
    }

    public function execute(string $migrationCommandType)
    {
        Artisan::call("migrate:$migrationCommandType", [], $this->outputBuffer());
    }

    public function outputBuffer()
    {
        return $this->output;
    }
}
