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
        $name = $this->input->getArgument('name');
        assert(is_string($name));
        return $this->makeName ??= str_replace('\\', '/', $name);
    }

    abstract protected function getDefaultMakeName(): ?string;
}
