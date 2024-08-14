<?php

namespace Devdot\Cli\Builder\Commands;

use Devdot\Cli\Traits\RunProcessTrait;

class Build extends Command
{
    use RunProcessTrait;

    protected function configure(): void
    {
        $this->setDescription('Build a standalone executable phar for this project');
    }

    protected function handle(): int
    {
        $this->output->writeln('Start building application.');

        $this->buildPath = getcwd() . '/tmp';

        $this->handleBuildTmp();
        $this->handleBuildContainer();
        $this->handleBuildPhar();

        $this->style->success('Done');

        return self::SUCCESS;
    }

    private function handleBuildContainer(): void
    {
        $this->style->section('Build production container');
        $this->runProcess(['bin/build'], false, $this->buildPath);

        $this->output->writeln('');
    }

    private function handleBuildTmp(): void
    {
        $this->style->section('Build temporary project');

        if (!is_dir($this->buildPath)) {
            // $this->runProcess(['rm', '-rf', $this->buildPath]);
            mkdir($this->buildPath, 0777, true);
            $this->runProcess(['git', 'clone', '.', $this->buildPath]);
        } else {
            $this->runProcess(['git', 'pull'], false, $this->buildPath);
        }

        $this->runProcess(['cp', 'composer.lock', $this->buildPath . '/composer.lock']);
        $this->runProcess(['composer', 'install', '--no-dev'], false, $this->buildPath, ['COMPOSER_MIRROR_PATH_REPOS' => '1']);

        $this->output->writeln('');
    }

    private function handleBuildPhar(): void
    {
        $this->style->section('Build executable phar');

        $pharComposerPath = realpath(__DIR__ . '/../../bin/phar-composer.phar');

        $this->runProcess([$pharComposerPath, 'build', $this->buildPath]);

        $this->output->writeln('');
    }
}
