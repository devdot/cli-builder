<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Builder\Generator\Printer;
use Devdot\Cli\Builder\Project\Project;
use Devdot\Cli\Contracts\ContainerInterface;
use Devdot\Cli\Exceptions\CommandFailedException;
use Devdot\Cli\Traits\ForceTrait;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

abstract class MakeCommand extends Command
{
    use ForceTrait;

    const PHP_HEADER = '<?php' . PHP_EOL . PHP_EOL;

    public function __construct(
        ContainerInterface $container,
        Project $project,
        private Printer $printer,
    ) {
        parent::__construct($container, $project);
    }

    protected function writeClass(ClassType $class, PhpNamespace $namespace, bool $overwrite = false): void
    {
        $path = $this->getClassPathFromNamespace($class, $namespace);
        $relativePath = $this->makeRelativePath($path);

        if (file_exists($path)) {
            if (!($overwrite || $this->input->getOption('force'))) {
                if ($this->input->isInteractive()) {
                    $this->style->warning($relativePath . ' exists already!');
                }

                if (!$this->style->confirm('Do you want to overwrite this file', $this->input->isInteractive())) {
                    throw new CommandFailedException($relativePath . ' exists already');
                }
            }

            $this->output->writeln('Overwrite ' . $relativePath);
        } else {
            $this->output->writeln('Write to ' . $relativePath);
        }


        $str = self::PHP_HEADER
            . $this->printer->printNamespace($namespace)
            . $this->printer->printClass($class, $namespace)
        ;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $str);
    }

    protected function getClassPathFromNamespace(ClassType $class, PhpNamespace $namespace): string
    {
        return $this->makePathFromNamespace($namespace->getName() . '\\' . $class->getName());
    }

    protected function makePathFromNamespace(string $namespace, string $extension = '.php'): string
    {
        $rel = substr($namespace, strlen($this->project->namespace));
        $rel = str_replace('\\', '/', $rel);

        if (str_ends_with($rel, '/')) {
            $rel = substr($rel, 0, -1);
        }

        return $this->project->srcDirectory . $rel . $extension;
    }

    protected function makeRelativePath(string $path): string
    {
        $path = realpath($path) ?: $path;

        if (str_starts_with($path, $this->project->rootDirectory)) {
            $path = '.' . substr($path, strlen($this->project->rootDirectory));
        }

        return $path;
    }
}
