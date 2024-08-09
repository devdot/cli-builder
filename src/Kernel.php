<?php

namespace Devdot\Cli\Builder;

use Devdot\Cli\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /** @var class-string[] */
    protected array $services = [
        Generator\Printer::class,
    ];

    /** @var class-string<\Devdot\Cli\Container\ServiceProvider>[] */
    protected array $providers = [
        Providers\ProjectProvider::class,
    ];

    public function __construct(string $dir = __DIR__, string $namespace = __NAMESPACE__)
    {
        parent::__construct($dir, $namespace);
    }
}
