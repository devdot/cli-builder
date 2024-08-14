<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

/**
 * @mixin Command
 */
trait ClassFromNameTrait
{
    use NameTrait;

    private ClassType $class;
    private PhpNamespace $namespace;

    protected function handle(): int
    {
        $this->getClass();
        $this->getNamespace();

        $this->handleBuildClass();

        $this->writeClass($this->getClass(), $this->getNamespace());

        return self::SUCCESS;
    }

    abstract protected function handleBuildClass(): void;

    private function getNamespace(): PhpNamespace
    {
        if (!isset($this->namespace)) {
            $path = dirname($this->getMakeName());

            if (str_starts_with($path, '/')) {
                $path = substr($path, 1);
            }

            $path = str_replace('/', '\\', $path);

            $namespace = $this->project->namespace . '\\' . $this->getMakeNamespace() . $path;

            if (str_ends_with($namespace, '\\.')) {
                $namespace = substr($namespace, 0, -2);
            }

            $this->namespace = new PhpNamespace($namespace);
        }

        return $this->namespace;
    }

    private function getClass(): ClassType
    {
        return $this->class ??= new ClassType(basename($this->getMakeName()));
    }

    abstract protected function getMakeNamespace(): string;
}
