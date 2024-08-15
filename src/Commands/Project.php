<?php

namespace Devdot\Cli\Builder\Commands;

class Project extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Show information about the current project.');
    }

    protected function handle(): int
    {
        $this->style->definitionList(
            ['Name' => $this->project->name],
            ['Root Directory' => $this->project->rootDirectory],
            ['Src Directory' => $this->project->srcDirectory],
            ['Namespace' => $this->project->namespace],
        );

        return self::SUCCESS;
    }
}
