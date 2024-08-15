<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Devdot\Cli\Container\ServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Provider extends MakeCommand
{
    use ClassFromNameTrait;

    protected function configure(): void
    {
        $this->setDescription('Make a new service provider. Service Providers may register complex services into the container.');
    }

    protected function getMakeNamespace(): string
    {
        return 'Providers\\';
    }

    protected function getDefaultMakeName(): ?string
    {
        return null;
    }

    protected function handleBuildClass(): void
    {
        $class = $this->getClass();
        $class->setExtends(ServiceProvider::class);
        $booting = $class->addMethod('booting');
        $booting->setReturnType('void');
        $booting->addParameter('container')->setType(ContainerBuilder::class);
        $booting->addBody('parent::booting($container);' . PHP_EOL . PHP_EOL . '//');

        ($class->addProperty('services'))
            ->setType('array')
            ->setValue([])
            ->setProtected()
            ->addComment('@var class-string[]')
        ;

        $namespace = $this->getNamespace();
        $namespace
            ->addUse(ServiceProvider::class)
            ->addUse(ContainerBuilder::class)
        ;

        // TODO: find a way to add it automatically to the kernel
        $this->style->info('Make sure to add the provider to your Kernel!');
    }
}
