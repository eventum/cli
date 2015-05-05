<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddTimeEntryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('add-time')
            ->setDescription('Add time-tracking entry to an issue')
            ->addArgument(
                'issue_id',
                InputArgument::REQUIRED,
                'Issue id'
            )
            ->addArgument(
                'time-spent',
                InputArgument::REQUIRED,
                'Time spent'
            )
            ->setHelp(
                <<<EOT
<info>%command.full_name% 123 20</info>

Add time tracking entry to issue, with time spent 20 minutes.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $issue_id = (int )$input->getArgument('issue_id');
        $time_spent = $input->getArgument('time-spent');
        $output->writeln("Adding time entry to $issue_id: $time_spent");
    }
}