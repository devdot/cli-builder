<?php

use Devdot\Cli\Application;
use Devdot\Cli\Builder\Kernel;

require $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

return Kernel::getInstance()->getContainer()->get(Application::class);
