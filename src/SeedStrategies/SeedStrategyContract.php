<?php
namespace JoeyRush\BetterMigrateSeed\SeedStrategies;

interface SeedStrategyContract
{
    public function execute(string $migrationCommandType);

    public function outputBuffer();
}
