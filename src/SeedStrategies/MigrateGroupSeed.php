<?php
namespace JoeyRush\BetterMigrateSeed\SeedStrategies;

use Illuminate\Support\Facades\Artisan;

class MigrateGroupSeed implements SeedStrategyContract
{
    private $output;

    public function __construct($buffer, $choice)
    {
        $this->output = $buffer;
        $this->choice = $choice;
    }

    public function execute(string $migrationCommandType)
    {
        Artisan::call("migrate:$migrationCommandType", [], $this->outputBuffer());
        Artisan::call('db:seed', ['--class' => $this->choice . 'DatabaseSeeder'], $this->outputBuffer());
    }

    public function outputBuffer()
    {
        return $this->output;
    }
}
