<?php
namespace JoeyRush\BetterMigrateSeed\SeedStrategies;

use Illuminate\Support\Facades\Artisan;

class DefaultMigrateSeed implements SeedStrategyContract
{
    private $output;

    public function __construct($buffer)
    {
        $this->output = $buffer;
    }

    public function execute()
    {
        Artisan::call('migrate:fresh', ['--seed' => true], $this->outputBuffer());
    }

    public function outputBuffer()
    {
        return $this->output;
    }
}
