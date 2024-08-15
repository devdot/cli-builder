<?php

namespace Devdot\Cli\Builder\Commands;

use Devdot\Cli\Builder\Project\Project;
use Devdot\Cli\Command as CliCommand;

abstract class Command extends CliCommand
{
    public function __construct(
        protected Project $project,
    ) {
        parent::__construct();
    }
}
