<?php

namespace Devdot\Cli\Builder\Project;

use Devdot\Cli\DirectoryProject\WorkingDirectory;
use Devdot\Cli\DirectoryProject\WorkingDirectoryInterface;
use Nadar\PhpComposerReader\AutoloadSection;
use Nadar\PhpComposerReader\ComposerReader;

class Project
{
    public readonly string $namespace;
    public readonly string $name;

    public function __construct(
        public readonly ComposerReader $composer,
        public readonly WorkingDirectoryInterface $rootDirectory,
        public readonly WorkingDirectoryInterface $srcDirectory,
    ) {
        $this->setNameFromComposer();
        $this->setNamespaceFromComposer();
    }

    public static function make(WorkingDirectoryInterface $root): self
    {
        return new self(
            new ComposerReader($root->makeAbsolute('composer.json')),
            $root,
            new WorkingDirectory($root->makeAbsolute('src')),
        );
    }

    private function setNameFromComposer(): void
    {
        $name = $this->composer->getContent()['name'] ?? $this->rootDirectory;
        $explode = explode('/', $name);
        $this->name = $explode[count($explode) - 1];
    }

    private function setNamespaceFromComposer(): void
    {
        $section = new AutoloadSection($this->composer, AutoloadSection::TYPE_PSR4);
        /** @var \Nadar\PhpComposerReader\Autoload $autoload */
        foreach ($section as $autoload) {
            $dir = $this->rootDirectory . '/' . substr($autoload->source, 0, -1);
            if ($dir === (string) $this->srcDirectory) {
                $this->namespace = substr($autoload->namespace, 0, -1);
                return;
            }
        }

        $this->namespace = 'App';
    }
}
