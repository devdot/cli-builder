<?php

namespace Devdot\Cli\Builder\Commands\Run;

class Dev extends RunCommand
{
    protected function configure(): void
    {
        $this->ignoreValidationErrors();
        $this->setDescription('Run the project application in development mode.');
    }

    protected function getBinPath(): string
    {
        return 'bin/dev';
    }
}
