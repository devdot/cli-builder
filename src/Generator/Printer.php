<?php

namespace Devdot\Cli\Builder\Generator;

use Nette\PhpGenerator\Printer as NettePrinter;

class Printer extends NettePrinter
{
    public string $indentation = '    ';
    public int $linesBetweenMethods = 1;
}
