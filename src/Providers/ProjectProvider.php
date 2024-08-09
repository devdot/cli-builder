<?php

namespace Devdot\Cli\Builder\Providers;

use Devdot\Cli\Builder\Project\Project;
use Devdot\Cli\Container\ServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProjectProvider extends ServiceProvider
{
    public function booting(ContainerBuilder $container): void
    {
        parent::booting($container);

        $container->autowire(Project::class, Project::class)->setFactory([Project::class, 'make']);
    }
}
