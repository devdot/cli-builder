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
        $this->name = $this->getNameFromComposer();
        $this->namespace = $this->getNamespaceFromComposer();
    }

    public static function make(WorkingDirectoryInterface $root): self
    {
        return new self(
            new ComposerReader($root->makeAbsolute('composer.json')),
            $root,
            new WorkingDirectory($root->makeAbsolute('src')),
        );
    }

    private function getNameFromComposer(): string
    {
        $name = $this->composer->getContent()['name'] ?? $this->rootDirectory;
        $explode = explode('/', $name);
        return $explode[count($explode) - 1];
    }

    private function getNamespaceFromComposer(): string
    {
        $section = new AutoloadSection($this->composer, AutoloadSection::TYPE_PSR4);
        /** @var \Nadar\PhpComposerReader\Autoload $autoload */
        foreach ($section as $autoload) {
            $dir = $this->rootDirectory . '/' . substr($autoload->source, 0, -1);
            if ($dir === (string) $this->srcDirectory) {
                return substr($autoload->namespace, 0, -1);
            }
        }

        return 'App';
    }
}
