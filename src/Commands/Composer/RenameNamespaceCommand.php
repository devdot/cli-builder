<?php

namespace Devdot\Cli\Builder\Commands\Composer;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Exceptions\CommandFailedException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RenameNamespaceCommand extends Command
{
    private string $source;
    private string $target;

    protected function configure(): void
    {
        $this->setDescription('Rename the namespace for this application.');
        $this->addArgument('namespace', InputArgument::OPTIONAL);
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write any files.');
    }

    private function getNamespaceInput(): string
    {
        $input = $this->input->getArgument('namespace');
        if (!is_string($input)) {
            $input = $this->style->ask('Namespace') ?? '';
            assert(is_string($input));
        }

        if (str_starts_with($input, '\\')) {
            throw new CommandFailedException('Invalid namespace format "' . $input . '", no leading \\ allowed!');
        }

        return $input;
    }

    protected function handle(): int
    {
        $this->source = $this->project->namespace;
        $this->target = $this->getNamespaceInput();

        if (!$this->input->getOption('dry-run') && !$this->style->confirm('Rename namespace from ' . $this->source . ' to ' . $this->target)) {
            return self::FAILURE;
        }

        $this->handleBins();
        $this->handleSrc();
        $this->handleComposer();

        $this->style->success('Done');

        return self::SUCCESS;
    }

    private function handleFile(string $path, callable $callback): void
    {
        $this->output->write('<fg=gray>--</> ' . $this->project->rootDirectory->makeRelative($path));

        $original = file_get_contents($path) ?: '';
        $originalLines = explode(PHP_EOL, $original);
        $changedLines = array_map($callback, $originalLines);
        $changed = implode(PHP_EOL, $changedLines);

        // output diff
        $changes = 0;
        $diff = '';
        foreach ($originalLines as $line => $old) {
            $new = $changedLines[$line];
            if ($old !== $new) {
                $changes++;
                $diff .= sprintf('    <fg=gray>@%s</>  <fg=red>%s</>', $line, $old);
                $diff .= PHP_EOL;
                $diff .= sprintf('    <fg=gray>@%s</>  <fg=green>%s</>', $line, $new);
                $diff .= PHP_EOL;
            }
        }

        $this->output->writeln('<fg=gray> -- ' . $changes . ' --</>');
        if (!empty($diff)) {
            $this->output->write($diff);
        }

        if (!$this->input->getOption('dry-run')) {
            file_put_contents($path, $changed);
        }
    }

    /**
     * @return string[]
     */
    private function getAllFilePaths(string $base): array
    {
        $files = scandir($base) ?: [];
        $files = array_filter($files, fn(string $file) => $file !== '.' && $file !== '..');
        $files = array_map(fn(string $file) => $base . DIRECTORY_SEPARATOR . $file, $files);

        $dirs = array_filter($files, is_dir(...));
        $files = array_filter($files, is_file(...));

        return array_merge($files, ...array_map($this->getAllFilePaths(...), $dirs));
    }

    private function handleBins(): void
    {
        $files = $this->getAllFilePaths($this->project->rootDirectory->makeAbsolute('bin'));
        $files = array_filter($files, fn(string $file) => !str_ends_with($file, '.phar'));

        $search = $this->source . '\\Kernel::';
        $replace = $this->target . '\\Kernel::';
        foreach ($files as $file) {
            $this->handleFile($file, fn (string $line): string => str_replace($search, $replace, $line));
        }
    }

    private function handleSrc(): void
    {
        $files = $this->getAllFilePaths($this->project->srcDirectory->get());
        $files = array_filter($files, fn(string $file) => str_ends_with($file, '.php'));

        foreach ($files as $file) {
            $this->handleFile($file, fn(string $line): string => match (explode(' ', $line, 2)[0]) {
                'namespace' => str_replace('namespace ' . $this->source, 'namespace ' . $this->target, $line),
                'use' => str_replace('use ' . $this->source, 'use ' . $this->target, $line),
                default => str_replace('\\' . $this->source, '\\' . $this->target, $line),
            });
        }
    }

    private function handleComposer(): void
    {
        $this->handleFile($this->project->rootDirectory->makeAbsolute('composer.json'), function (string $line): string {
            $src = $this->project->rootDirectory->makeRelative($this->project->srcDirectory->get());
            $format = '            "%s\\\\": "%s/"';
            $search = sprintf($format, addslashes($this->source), $src);
            $replace = sprintf($format, addslashes($this->target), $src);
            return str_replace($search, $replace, $line);
        });
    }
}
