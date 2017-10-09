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

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListIssuesCommand extends Command
{
    const COMMAND_NAME = 'open-issues';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('List open issues')
            ->addOption(
                'status',
                '',
                InputArgument::OPTIONAL,
                'Optional status'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'Project Id'
            )
            ->addOption(
                'my',
                '',
                InputOption::VALUE_NONE,
                'List only issues assigned to you'
            )
            ->setHelp(<<<EOT
<info>%command.full_name% [--my] [--status=<status>]</info>

List all issues that are not set to a status with a 'closed' context. Use
optional argument <info>--my</info> if you just wish to see issues assigned to you.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = (string)$input->getOption('status');
        $show_all_issues = $input->getOption('my') == null;
        $project_id = $this->getProjectId();

        $issues = $this->getClient()->getOpenIssues((int)$project_id, $show_all_issues, $status);

        if ($status) {
            $output->writeln("The following issues are set to status '<comment>$status</comment>':");
        } else {
            $output->writeln('The following issues are still open:');
        }

        foreach ($issues as $issue) {
            $output->write("- <comment>#{$issue['issue_id']}</comment> - <info>{$issue['summary']}</info> ({$issue['status']})");
            if (!empty($issue['assigned_users'])) {
                $output->writeln(" - ({$issue['assigned_users']})");
            } else {
                $output->writeln(' - (unassigned)');
            }
        }
    }
}
