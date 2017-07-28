<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetIssueStatusCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('set-status')
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
        if ($result == 'OK') {
            $message = "Status changed to '<info>$new_status</info>' on issue #$issue_id";
            $output->writeln($message);
            return 0;
        } else {
            $output->writeln("<error>$result</error>");
            return 1;
        }
    }
}
