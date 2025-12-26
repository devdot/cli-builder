<?php

use Devdot\Cli\Application;

require $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

return Devdot\Cli\Builder\Kernel::getInstance()->getContainer()->get(Application::class);
