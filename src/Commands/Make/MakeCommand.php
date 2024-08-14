<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Builder\Generator\Printer;
use Devdot\Cli\Builder\Project\Project;
use Devdot\Cli\Exceptions\CommandFailedException;
use Devdot\Cli\Traits\ForceTrait;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Container\ContainerInterface;

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

        if (file_exists($path)) {
            if ($overwrite || $this->input->getOption('force')) {
                $this->output->writeln('Overwrite ' . $path);
            } else {
                $this->style->warning($path . ' exists already!');
                if (!$this->style->confirm('Do you want to overwrite this file', $this->input->isInteractive())) {
                    throw new CommandFailedException($path . ' exists already');
                }
                $this->output->writeln('Overwrite ' . $path);
            }
        } else {
            $this->output->writeln('Write to ' . $path);
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
        $rel = substr($namespace->getName(), strlen($this->project->namespace));
        $rel = str_replace('\\', '/', $rel);

        if (!str_ends_with($rel, '/')) {
            $rel .= '/';
        }

        return $this->project->srcDirectory . $rel . $class->getName() . '.php';
    }
}
