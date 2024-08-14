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
                $this->updateLiteralsArray($class, 'services');
                $this->updateLiteralsArray($class, 'providers');
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

        $this->writeClass($class, $class->getNamespace(), true);

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

    private function updateLiteralsArray(ClassType $class, string $property): void
    {
        $property = $class->getProperty($property);
        $namespace = $class->getNamespace();

        $values = [];
        foreach ($property->getValue() as $key => $value) {
            if (class_exists($value)) {
                $values[$key] = new Literal($namespace->simplifyName('\\' . $value) . '::class');
            } else {
                $values[$key] = $value;
            }
        }

        $property->setValue($values);
    }
}
