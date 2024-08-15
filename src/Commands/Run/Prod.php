<?php

namespace Devdot\Cli\Builder\Commands\Run;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Exceptions\CommandFailedException;

class Prod extends RunCommand
{
    protected function configure(): void
    {
        $this->ignoreValidationErrors();
        $this->setDescription('Run the project application in production mode. Use run:build to build the container before running the application in production mode.');
    }

    protected function getBinPath(): string
    {
        if (file_exists($this->project->rootDirectory . '/bin/prod')) {
            return 'bin/prod';
        }

        // attempt to find the path
        $bins = $this->project->composer->getContent()['bin'] ?? [];
        $bin = array_shift($bins);

        if (!is_string($bin)) {
            throw new CommandFailedException('Cannot locate production binary. Perhaps you need to add it to composer.json?');
        }

        return $bin;
    }
}
