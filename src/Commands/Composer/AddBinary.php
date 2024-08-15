<?php

namespace Devdot\Cli\Builder\Commands\Composer;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Exceptions\CommandFailedException;
use Devdot\Cli\Traits\RunProcessTrait;
use Symfony\Component\Console\Input\InputOption;

class AddBinary extends Command
{
    use RunProcessTrait;

    protected function configure(): void
    {
        $this->setDescription('Add the project production binary to composer.json.');
        $this->addOption('rename', null, InputOption::VALUE_REQUIRED, 'Rename the to this name before adding it to composer', 'bin/' . basename($this->project->rootDirectory));
    }

    protected function handle(): int
    {
        $this->setRunProcessThrowErrors(true);

        // check if bin/prod is still around
        $binPath = $this->project->rootDirectory . '/bin/prod';
        if (!file_exists($binPath)) {
            throw new CommandFailedException('Cannot locate bin/prod');
        }

        $rename = $this->input->getOption('rename');
        assert(is_string($rename));
        $newPath = $this->project->rootDirectory . '/' . $rename;

        $this->runProcess(['mv', $binPath, $newPath]);

        // add the binary to composer.json
        $this->output->writeln('Add ' . $rename . ' to composer.json');
        $composer = $this->project->composer->getContent();
        $composer['bin'] ??= [];
        $composer['bin'][] = $rename;
        $this->project->composer->writeContent($composer);

        $this->style->success('Done');

        return self::SUCCESS;
    }
}
