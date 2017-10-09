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
use Symfony\Component\Console\Output\OutputInterface;

class SetIssueStatusCommand extends Command
{
    const COMMAND_NAME = 'set-status';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setAliases(array('ss'))
            ->setDescription('Set Issue status')
            ->addArgument(
                'issue_id',
                InputArgument::REQUIRED,
                'Issue id'
            )
            ->addArgument(
                'status',
                InputArgument::REQUIRED,
                'New status for issue'
            )
            ->setHelp(
                <<<EOT
                <info>%command.full_name% 123 new</info>

Set issue status.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $issue_id = (int )$input->getArgument('issue_id');
        $new_status = $input->getArgument('status');
        $client = $this->getClient();

        $result = $client->setIssueStatus($issue_id, $new_status);
        if ($result === 'OK') {
            $message = "Status changed to '<info>$new_status</info>' on issue #$issue_id";
            $output->writeln($message);

            return 0;
        }
        $output->writeln("<error>$result</error>");

        return 1;
    }
}
