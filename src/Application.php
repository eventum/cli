<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the LICENSE and AUTHORS files
 * that were distributed with this source code.
 */

namespace Eventum\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
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
        $commands[] = new Command\AddTimeEntryCommand();
        $commands[] = new Command\SetIssueStatusCommand();

        if ('phar:' === substr(__FILE__, 0, 5)) {
            $commands[] = new Command\SelfUpdateCommand();
        }

        if (class_exists('Eventum\Console\Command\SelfUpdateManifestCommand')) {
            $commands[] = new Command\SelfUpdateManifestCommand();
        }

        return $commands;
    }
}
