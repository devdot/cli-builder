<?php

namespace Devdot\Cli\Builder\Commands\Run;

class Build extends RunCommand
{
    protected function configure(): void
    {
        $this->setDescription('Build this projects production application container.');
    }

    protected function getBinPath(): string
    {
        return 'bin/build';
    }
}
