<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class Kernel extends MakeCommand
{
    protected function configure(): void
    {
        $this->setDescription('Generate a fresh Kernel instance. If a Kernel already exists, this will carefully transform the old Kernel.');
    }

    protected function handle(): int
    {
        $classname = $this->project->namespace . '\\Kernel';
        $class = null;
        $namespace = new PhpNamespace($this->project->namespace);

        if (class_exists($classname)) {
            $this->style->note($classname . ' exists already!');
            $this->style->writeln('This command is carefully designed to upgrade old Kernels into new ones. However, it is recommended to have a backup before proceeding.');
            if ($this->input->getOption('force') || $this->style->confirm('Proceed anyways?', false)) {
                // load the namespace from the file
                $file = PhpFile::fromCode(file_get_contents($this->makePathFromNamespace($classname)) ?: '');
                $loadedNamespace = $file->getNamespaces()[$this->project->namespace] ?? null;
                if ($loadedNamespace) {
                    $loadedNamespace->removeClass('Kernel');
                    $namespace = $loadedNamespace;
                }

                // load the class from a real object
                $class = ClassType::from($classname, true);
                assert($class instanceof ClassType);
                $this->updateConstructor($class);
                $this->updateLiteralsArray($class, $namespace, 'services');
                $this->updateLiteralsArray($class, $namespace, 'providers');
            } else {
                return self::FAILURE;
            }
        } else {
            $class = new ClassType('Kernel', $namespace);
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

        $namespace->addUse(\Devdot\Cli\Kernel::class, 'BaseKernel');

        $this->writeClass($class, $namespace, true);

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

    private function updateLiteralsArray(ClassType $class, PhpNamespace $namespace, string $property): void
    {
        $property = $class->getProperty($property);

        $values = [];
        $currentValues = $property->getValue();
        assert(is_array($currentValues));

        foreach ($currentValues as $key => $value) {
            if (class_exists($value)) {
                $values[$key] = new Literal($namespace->simplifyName('\\' . $value) . '::class');
            } else {
                $values[$key] = $value;
            }
        }

        $property->setValue($values);
    }
}
