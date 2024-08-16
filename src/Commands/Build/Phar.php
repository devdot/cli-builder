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
        $this->setDescription('Build a standalone executable PHAR for this project.');
        $this->addOption('build-version', null, InputOption::VALUE_REQUIRED, 'Version to be used in container build.');
        $this->addOption('exclude', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Exclude files from the PHAR archive.');
        $this->addOption('binary', null, InputOption::VALUE_REQUIRED, 'Use this binary instead of the default binary as PHAR entrypoint.');
        $this->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Use this git branch as base for the PHAR branch.', 'master');
    }

    protected function handle(): int
    {
        $this->output->writeln('Start building application.');

        $this->buildPath = getcwd() . '/tmp';

        $this->handleBuildTmp();
        $this->handleBuildContainer();
        $this->handleExcludes();
        $this->handleChangeBinary();
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

        $branch = $this->input->getOption('branch');
        assert(is_string($branch));

        if (!is_dir($this->buildPath)) {
            // $this->runProcess(['rm', '-rf', $this->buildPath]);
            mkdir($this->buildPath, 0777, true);
            $this->runProcess(['git', 'clone', '.', $this->buildPath]);
            $this->runProcess(['git', 'checkout', $branch], false, $this->buildPath);
        } else {
            $this->runProcess(['git', 'checkout', $branch], false, $this->buildPath);
            $this->runProcess(['git', 'reset', 'HEAD', '--hard'], false, $this->buildPath);
            $this->runProcess(['git', 'pull', '--rebase', '--force'], false, $this->buildPath);
        }

        $this->runProcess(['cp', 'composer.lock', $this->buildPath . '/composer.lock']);
        $this->runProcess(['composer', 'install', '--no-dev'], false, $this->buildPath, ['COMPOSER_MIRROR_PATH_REPOS' => '1']);

        $this->output->writeln('');
    }

    private function handleExcludes(): void
    {
        $excludes = $this->input->getOption('exclude');
        if ($excludes) {
            assert(is_array($excludes));

            $this->style->section('Remove excluded files');

            foreach ($excludes as $file) {
                $path = realpath($this->buildPath . '/' . $file);

                if ($path === false) {
                    $this->style->warning('File could not be found: ' . $this->buildPath . '/' . $file);
                } else {
                    $this->runProcess(['rm', $path]);
                }
            }

            $this->output->writeln('');
        }
    }

    private function handleChangeBinary(): void
    {
        $binary = $this->input->getOption('binary');
        if ($binary) {
            assert(is_string($binary));

            $this->style->section('Change deploy binary');

            $path = realpath($this->buildPath . '/' . $binary);

            if ($path === false) {
                throw new CommandFailedException('Cannot locate binary ' . $binary . ' in ' . $this->buildPath);
            }

            // change the composer bin
            $this->output->writeln('Change bin in composer.json to ' . $binary);
            $composerPath = $this->buildPath . '/composer.json';
            $composer = json_decode(file_get_contents($composerPath) ?: '', null, 512, JSON_THROW_ON_ERROR);
            assert($composer instanceof \stdClass);
            $composer->bin = [$binary];
            file_put_contents($composerPath, json_encode($composer, JSON_THROW_ON_ERROR));

            $this->output->writeln('');
        }
    }

    private function handleBuildPhar(): void
    {
        $this->style->section('Build executable PHAR');

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
