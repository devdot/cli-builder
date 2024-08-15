<?php

namespace Devdot\Cli\Builder\Commands\Build;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Exceptions\CommandFailedException;
use Devdot\Cli\Traits\RunProcessTrait;

class Readme extends Command
{
    use RunProcessTrait;

    protected function configure(): void
    {
        $this->setDescription('Make or update a README.md and fill it with the content of list.');
    }

    protected function handle(): int
    {
        $this->setRunProcessThrowErrors(true);

        $path = $this->project->rootDirectory . '/README.md';

        // get the content
        $this->runProcess(['bin/dev', 'list'], true);

        $content = $this->getLastProcess()->getOutput();
        $name = basename($this->project->rootDirectory);
        $marker = '```' . $name . PHP_EOL;

        if (file_exists($path)) {
            // find the right place
            $file = file_get_contents($path) ?: '';
            $posStart = strpos($file, $marker);

            if ($posStart === false) {
                throw new CommandFailedException('Cannot find marker "' . $marker . '" in existing README.md');
            }

            $textPre = substr($file, 0, $posStart);
            $remaining = substr($file, $posStart + strlen($marker));
            $posEnd = strpos($remaining, '```');

            if ($posEnd === false) {
                throw new CommandFailedException('Cannot find end marker "```" in existing README.md');
            }

            $textPost = substr($remaining, $posEnd);

            $this->output->writeln('Edit README.md');

            file_put_contents($path, $textPre . $marker . $content . $textPost);
        } else {
            $this->output->writeln('Create new README.md');
            $content = $name . PHP_EOL . str_pad('', strlen($name), '=') . PHP_EOL . PHP_EOL . $marker . $content . '```' . PHP_EOL;
            file_put_contents($path, $content);
        }

        $this->style->success('Done');

        return self::SUCCESS;
    }
}
