<?php

namespace Eventum\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption('url', null, InputOption::VALUE_REQUIRED, 'Specify location of Eventum')
        );
        $definition->addOption(
            new InputOption('username', 'u', InputOption::VALUE_REQUIRED, 'If specified, use the given username.')
        );
        $definition->addOption(
            new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'If specified, use the given password.')
        );

        return $definition;
    }

    /**
     * Initializes all the composer commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\ListIssuesCommand();
        $commands[] = new Command\ViewIssueCommand();
        $commands[] = new Command\WeeklyReportCommand();
        $commands[] = new Command\CreateIssueCommand();
        $commands[] = new Command\AddAttachmentCommand();
        $commands[] = new Command\DumpMethodsCommand();
        $commands[] = new Command\ConfigCommand();

        return $commands;
    }
} 