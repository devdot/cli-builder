<?php

namespace Devdot\Cli\Builder\Commands\Build;

use Phar as PhpPhar;
use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Exceptions\CommandFailedException;
use Devdot\Cli\Traits\RunProcessTrait;
use Symfony\Component\Console\Input\InputOption;

class Phar extends Command
{
    use RunProcessTrait;

    private string $buildPath;

    protected function configure(): void
    {
        $this->setDescription('Build a standalone executable phar for this project.');
        $this->addOption('build-version', null, InputOption::VALUE_REQUIRED, 'Version to be used in container build.');
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

        $command = ['bin/build'];
        $version = $this->input->getOption('build-version');
        if ($version) {
            assert(is_string($version));
            $command[] = $version;
        }

        $this->runProcess($command, false, $this->buildPath);

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
            $this->runProcess(['git', 'pull', '--rebase', '--force'], false, $this->buildPath);
        }

        $this->runProcess(['cp', 'composer.lock', $this->buildPath . '/composer.lock']);
        $this->runProcess(['composer', 'install', '--no-dev'], false, $this->buildPath, ['COMPOSER_MIRROR_PATH_REPOS' => '1']);

        $this->output->writeln('');
    }

    private function handleBuildPhar(): void
    {
        $this->style->section('Build executable phar');

        $pharSearchPath = __DIR__ . '/../../../bin/phar-composer.phar';
        $pharComposerPath = realpath($pharSearchPath);

        if ($pharComposerPath === false) {
            // we might be inside a phar, check for that
            if (class_exists(PhpPhar::class) && $pharPath = PhpPhar::running(false)) {
                $pharSearchPath = dirname($pharPath) . '/phar-composer.phar';
                $pharComposerPath = realpath($pharSearchPath);
            }

            if ($pharComposerPath === false) {
                throw new CommandFailedException('Could not locate phar-composer.phar! Searching at ' . $pharSearchPath);
            }
        }

        $this->runProcess([$pharComposerPath, 'build', $this->buildPath]);

        $this->output->writeln('');
    }
}
