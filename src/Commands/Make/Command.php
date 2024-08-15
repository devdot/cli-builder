<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Exceptions\CommandFailedException;
use Symfony\Component\Console\Input\InputOption;

class Command extends MakeCommand
{
    use ClassFromNameTrait;

    const DEFAULT_BASE_COMMAND = 'Command';

    protected function configure(): void
    {
        $this->addOption('extends', null, InputOption::VALUE_REQUIRED, 'The base Command class of your project', $this->getExtendsDefault());
    }

    protected function getMakeNamespace(): string
    {
        return 'Commands\\';
    }

    protected function getDefaultMakeName(): ?string
    {
        return null;
    }

    protected function handleBuildClass(): void
    {
        // find the project root base command
        $extends = $this->input->getOption('extends');
        assert(is_string($extends));
        if (!class_exists($extends)) {
            throw new CommandFailedException('Base class ' . $extends . ' does not exist!');
        }

        $class = $this->getClass();
        $class->setExtends($extends);

        $class->addMethod('handle')
            ->setProtected()
            ->setReturnType('int')
            ->setBody('//' . PHP_EOL . PHP_EOL . 'return self::SUCCESS;')
        ;

        $this->getNamespace()->addUse($extends);
    }

    private function getExtendsDefault(): string
    {
        $extends = $this->project->namespace . '\\' . $this->getMakeNamespace() . self::DEFAULT_BASE_COMMAND;
        return class_exists($extends) ? $extends : \Devdot\Cli\Command::class;
    }
}
