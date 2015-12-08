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

class CreateIssueCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create-issue')
            ->setDescription('Create issue')
            ->addOption(
                'summary',
                's',
                InputArgument::OPTIONAL,
                'Issue summary'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'Project Id'
            )
            ->setHelp(
                <<<EOT
                <info>%command.full_name% -s summary</info>

Create new issue.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project_id = $this->getProjectId();
        $client = $this->getClient();

        $summary = $this->getSummary();
    }

    /**
     * Get issue summary from option or ask from user
     * @return string
     */
    private function getSummary()
    {
        $summary = $this->input->getOption('summary');
        if ($summary) {
            return $summary;
        }

        return $this->io->ask('Summary: ');
    }
}
