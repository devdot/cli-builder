<?php

namespace Devdot\Cli\Builder\Commands;

use Devdot\Cli\Builder\Project\Project;
use Devdot\Cli\Command as CliCommand;
use Psr\Container\ContainerInterface;

abstract class Command extends CliCommand
{
    public function __construct(
        ContainerInterface $container,
        protected Project $project,
    ) {
        parent::__construct($container);
    }
}
