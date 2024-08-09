<?php

namespace Devdot\Cli\Builder\Generator;

use Nette\PhpGenerator\Printer as NettePrinter;

class Printer extends NettePrinter
{
    public string $indentation = '    ';
    public int $linesBetweenMethods = 1;
    public int $linesBetweenUseTypes = 1;

    protected function isBraceOnNextLine(bool $multiLine, bool $hasReturnType): bool
    {
        return !$multiLine;
    }
}
