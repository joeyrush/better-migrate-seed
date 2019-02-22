<?php
namespace JoeyRush\BetterMigrateSeed\SeedStrategies;

interface SeedStrategyContract
{
    public function execute();

    public function outputBuffer();
}
