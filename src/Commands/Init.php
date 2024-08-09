<?php

namespace Devdot\Cli\Builder\Commands;

use Devdot\Cli\Traits\ForceTrait;
use Devdot\Cli\Traits\RunProcessTrait;

class Init extends Command
{
    use ForceTrait;
    use RunProcessTrait;

    protected function configure(): void
    {
        $this->setDescription('Initialize a new CLI project.');
    }

    protected function handle(): int
    {
        $this->setRunProcessDefaultCwd($this->project->rootDirectory);
        $this->setRunProcessThrowErrors(true);

        $this->output->writeln('Initialize a new CLI project at: ' . $this->project->rootDirectory . '');
        $this->output->writeln('');

        $this->handleDistFiles();
        $this->handleUpdateComposer();
        $this->handleInitGit();
        $this->handleGenerateBoilerplate();

        $this->style->success('Done');

        return self::SUCCESS;
    }

    protected function handleDistFiles(): void
    {
        $this->style->section('Move dist files');

        if (file_exists($this->project->rootDirectory . '/.gitattributes.dist')) {
            $this->runProcess(['mv', '.gitattributes.dist', '.gitattributes']);
        } else {
            $this->output->writeln('.gitattributes.dist does not exist anymore');
        }

        $this->output->writeln('');
    }

    protected function handleUpdateComposer(): void
    {
        $this->style->section('Update package information');

        $data = $this->project->composer->getContent();

        $defaultName = $data['name'];
        if ($data['name'] === 'devdot/cli-project') {
            $defaultName = ($_SERVER['COMPOSER_DEFAULT_VENDOR'] ?? 'devdot') . '/' . basename($this->project->rootDirectory);
        }

        $name = $this->style->ask('Package name', $defaultName);
        $this->runProcess(['composer', 'config', 'name', $name], true);

        $description = $this->style->ask('Description', $data['description'] ?? '');
        $this->runProcess(['composer', 'config', 'description', $description], true);

        $license = $this->style->ask('License', $data['license'] ?? 'MIT');
        $this->runProcess(['composer', 'config', 'license', $license], true);

        $this->output->writeln('');
    }

    protected function handleInitGit(): void
    {
        $this->style->section('Initialize Git');

        if (!is_dir($this->project->rootDirectory . '/.git')) {
            $this->runProcess(['git', 'init']);
        } else {
            $this->output->writeln('Git is already initialized.');
        }

        $this->output->writeln('');
    }

    protected function handleGenerateBoilerplate(): void
    {
        $this->style->section('Generate Boilerplate Code');
        $this->runProcess([Make\Kernel::class, '-f', '--no-interaction']);
        $this->output->writeln('');
    }
}
