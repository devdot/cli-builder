<?php

namespace Devdot\Cli\Builder\Commands\Make;

use Symfony\Component\Console\Input\InputArgument;

/**
 * @mixin Command
 */
trait NameTrait
{
    private string $makeName;

    public function __constructNameTrait(): void
    {
        $default = $this->getDefaultMakeName();
        if ($default) {
            $this->addArgument('name', InputArgument::OPTIONAL, 'Name of the new object, can be a path too', $default);
        } else {
            $this->addArgument('name', InputArgument::REQUIRED, 'Name of the new object, can be a path too');
        }
    }

    protected function getMakeName(): string
    {
        return $this->makeName ??= str_replace('\\', '/', $this->input->getArgument('name'));
    }

    abstract protected function getDefaultMakeName(): ?string;
}
