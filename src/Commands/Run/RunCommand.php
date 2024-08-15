<?php

namespace Devdot\Cli\Builder\Commands\Run;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Traits\RunProcessTrait;

abstract class RunCommand extends Command
{
    use RunProcessTrait;

    protected function handle(): int
    {
        $command = $_SERVER['argv'];
        $name = $this->getName();

        while ($command[0] !== $name) {
            array_shift($command);
        }

        $command[0] = $this->getBinPath();

        return $this->runProcess($command, false, $this->project->rootDirectory);
    }

    abstract protected function getBinPath(): string;
}
