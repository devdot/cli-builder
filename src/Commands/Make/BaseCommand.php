<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Command;

class BaseCommand extends MakeCommand
{
    use ClassFromNameTrait;

    protected function configure(): void
    {
        $this->setDescription('Generate a command base class. If located at the default location, it will be used as default parent for make:command.');
    }

    protected function getDefaultMakeName(): ?string
    {
        return 'Command';
    }

    protected function handleBuildClass(): void
    {
        $namespace = $this->getNamespace();
        $namespace->addUse(Command::class, 'CliCommand');

        $class = $this->getClass();
        $class
            ->setExtends(Command::class)
            ->setAbstract()
        ;
        $constructor = $class->addMethod('__construct');
        $constructor->setBody('parent::__construct();');
    }

    protected function getMakeNamespace(): string
    {
        return 'Commands\\';
    }
}
