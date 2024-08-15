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
        $this->setRunProcessShowInternalCommand(true);

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
            $this->output->writeln('.gitattributes.dist does not exist anymore', $this->output::VERBOSITY_VERBOSE);

            $this->createFileLines('.gitattributes', [
                'bin/build export-ignore',
                'bin/dev export-ignore',
                'bin/prod export-ignore',
                '',
                'tests export-ignore',
            ]);
        }

        $this->createFileLines('.gitignore', [
            '# exclude the generated cache',
            'src/ProductionContainer.php',
            '',
            'vendor',
        ]);

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
        $this->runProcess([Make\BaseCommand::class, '--no-interaction']);

        if (!is_dir($this->project->rootDirectory . '/bin')) {
            mkdir($this->project->rootDirectory . '/bin', 0777, true);
        }

        $this->writeBin('dev', $this->project->namespace . '\Kernel::run(true);');
        $this->writeBin('prod', $this->project->namespace . '\Kernel::run(false);');
        $this->writeBin('build', $this->project->namespace . '\Kernel::cacheContainer();');

        $this->runProcess([Make\Command::class, 'Example', '--no-interaction']);

        $this->output->writeln('');
    }

    private function writeBin(string $file, string $code, bool $overwrite = false): void
    {
        $filepath = $this->project->rootDirectory . '/bin/' . $file;

        if (file_exists($filepath) && !$overwrite) {
            $this->output->writeln('bin/' . $file . ' already exists');
            return;
        } else {
            $this->output->writeln('Create bin/' . $file);
            $header = '#!/usr/bin/env php' . PHP_EOL
                . '<?php' . PHP_EOL
                . PHP_EOL
                . 'require $_composer_autoload_path ?? __DIR__ . \'/../vendor/autoload.php\';' . PHP_EOL
                . PHP_EOL;
            file_put_contents($filepath, $header . trim($code) . PHP_EOL);

            $this->runProcess(['chmod', '+x', $filepath], true);
        }
    }

    private function createFile(string $filename, string $content): void
    {
        $path = $this->project->rootDirectory . '/' . $filename;
        $force = $this->input->getOption('force');
        assert(is_bool($force));

        if (file_exists($path)) {
            if ($force || $this->style->confirm('Overwrite ' . $filename, $this->input->isInteractive())) {
                $this->output->writeln('Overwrite ' . $filename);
                file_put_contents($path, $content);
            }
        } else {
            $this->output->writeln('Write ' . $filename);
            file_put_contents($path, $content);
        }
    }

    /**
     * @param string[] $lines
     */
    private function createFileLines(string $filename, array $lines): void
    {
        $this->createFile($filename, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
