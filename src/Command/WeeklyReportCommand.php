<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WeeklyReportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('weekly-report')
            ->setAliases(array('wr'))
            ->setDescription('Show weekly reports')
            ->addArgument(
                'start',
                InputArgument::OPTIONAL,
                'Week number or Start date'
            )
            ->addArgument(
                'end',
                InputArgument::OPTIONAL,
                'End date'
            )
            ->addOption(
                'separate-closed',
                '',
                InputArgument::OPTIONAL,
                'Separate closed issues',
                false
            )
            ->setHelp(<<<EOT
<info>%command.full_name% [<week>] [--separate-closed]</info>
<info>%command.full_name% [<start>] [<end>] [--separate-closed]</info>

Fetches the weekly report. Week is specified as an integer with 0 representing
the current week, -1 the previous week and so on. If the week is omitted it 
defaults to the current week. Alternately, a date range can be set. Dates 
should be in the format 'YYYY-MM-DD'.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $week = 0;
        $start_date = (string)$input->getArgument('start');
        $end_date = (string)$input->getArgument('end');

        // FIXME: preserve old behavior (which is quite complex to translate to current form)
        if ($end_date) {
        }

        $separate_closed = $input->getOption('separate-closed');

        $output->write($this->getClient()->getWeeklyReport($week, $start_date, $end_date, $separate_closed));
    }
}
