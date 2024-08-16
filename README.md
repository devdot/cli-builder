devdot/cli-builder
==================

*Tools for development of devdot/cli.*

See documentation on [https://github.com/devdot/cli](GitHub).


```cli-builder
cli-builder 1.0.1

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion           Dump the shell completion script
  help                 Display help for a command
  init                 Initialize a new CLI project.
  list                 List commands
  project              Show information about the current project.
 build
  build:phar           Build a standalone executable PHAR for this project.
  build:readme         Make or update a README.md and fill it with the content of list.
 composer
  composer:add-binary  Add the project production binary to composer.json.
 make
  make:base-command    Generate a command base class. If located at the default location, it will be used as default parent for make:command.
  make:command         Make a new command class. Will be named and registered automatically base on the namespace path.
  make:kernel          Generate a fresh Kernel instance. If a Kernel already exists, this will carefully transform the old Kernel.
  make:provider        Make a new service provider. Service Providers may register complex services into the container.
 run
  run:build            Build this projects production application container.
  run:dev              Run the project application in development mode.
  run:prod             Run the project application in production mode. Use run:build to build the container before running the application in production mode.
```
