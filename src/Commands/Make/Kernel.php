<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;

class Kernel extends MakeCommand
{
    protected function configure(): void
    {
        $this->setDescription('Generate a fresh Kernel instance');
    }

    protected function handle(): int
    {
        $classname = $this->project->namespace . '\\Kernel';
        $class = null;
        if (class_exists($classname)) {
            $this->style->warning($classname . ' exists already!');
            if ($this->input->getOption('force') || $this->style->confirm('Proceed anyways?', false)) {
                $class = ClassType::from($classname);
                $this->updateConstructor($class);
            } else {
                return self::FAILURE;
            }
        } else {
            $class = new ClassType('Kernel', new PhpNamespace($this->project->namespace));
            $class
                ->setExtends(\Devdot\Cli\Kernel::class)
                ->setFinal(true)
                ;
            $class->addProperty('services', [])
                ->setVisibility('protected')
                ->setType('array')
                ->setComment('@var class-string[]')
            ;
            $class->addProperty('providers', [])
                ->setVisibility('protected')
                ->setType('array')
                ->setComment('@var class-string<\Devdot\Cli\Container\ServiceProvider>[]')
            ;
            $this->updateConstructor($class);
        }

        $class->getNamespace()->addUse(\Devdot\Cli\Kernel::class, 'BaseKernel');

        $this->writeClass($class);

        return self::SUCCESS;
    }

    private function updateConstructor(ClassType $class): void
    {
        $dir = new Literal('__DIR__');
        $namespace = new Literal('__NAMESPACE__');

        $constructor = $class->addMethod('__construct', true);
        $constructor->addParameter('dir', $dir)->setType('string');
        $constructor->addParameter('namespace', $namespace)->setType('string');
        $constructor->addBody('parent::__construct($dir, $namespace);');
    }
}
