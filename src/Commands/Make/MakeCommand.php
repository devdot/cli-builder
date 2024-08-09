<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Builder\Commands\Command;
use Devdot\Cli\Builder\Generator\Printer;
use Devdot\Cli\Builder\Project\Project;
use Devdot\Cli\Traits\ForceTrait;
use Nette\PhpGenerator\ClassType;
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

    protected function writeClass(ClassType $class): void
    {
        $path = $this->getPathFromNamespace($class);

        $this->output->writeln('Write to ' . $path);

        $str = self::PHP_HEADER
            . $this->printer->printNamespace($class->getNamespace())
            . $this->printer->printClass($class, $class->getNamespace())
        ;

        file_put_contents($path, $str);
    }

    protected function getPathFromNamespace(ClassType $class): string
    {
        $rel = substr($class->getNamespace()->getName(), strlen($this->project->namespace));
        $rel = str_replace($rel, '\\', '/');

        if (!str_ends_with($rel, '/')) {
            $rel .= '/';
        }

        return $this->project->srcDirectory . $rel . $class->getName() . '.php';
    }
}
