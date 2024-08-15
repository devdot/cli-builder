<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Command;
use Devdot\Cli\Contracts\ContainerInterface;

class BaseCommand extends MakeCommand
{
    use ClassFromNameTrait;

    protected function getDefaultMakeName(): ?string
    {
        return 'Command';
    }

    protected function handleBuildClass(): void
    {
        $namespace = $this->getNamespace();
        $namespace->addUse(Command::class, 'CliCommand');
        $namespace->addUse(ContainerInterface::class);

        $class = $this->getClass();
        $class
            ->setExtends(Command::class)
            ->setAbstract()
        ;
        $constructor = $class->addMethod('__construct');
        $constructor->addParameter('container')->setType(ContainerInterface::class);
        $constructor->setBody('parent::__construct($container);');
    }

    protected function getMakeNamespace(): string
    {
        return 'Commands\\';
    }
}
