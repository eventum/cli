<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;

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
                'project',
                null,
                InputArgument::OPTIONAL,
                'Project Id'
            )
            ->addOption(
                'separate-closed',
                null,
                InputArgument::OPTIONAL,
                'Separate closed issues',
                false
            )
            ->setHelp(
                <<<EOT

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
        $start = (string)$input->getArgument('start');
        $end = (string)$input->getArgument('end');
        $options = array(
            'separate_closed' => $input->getOption('separate-closed'),
        );

        // take current week
        $start = new DateTime("Last Monday");
        $end = new DateTime("Next Monday");
        // TODO: handle parameters and week option

        $prj_id = $this->getProjectId();
        $data = $this->getClient()->getWeeklyReportData($prj_id, $start, $end, $options);

        $group_name = $data['group_name'] ? "[{$data['group_name']}]" : "";
        $output->writeln(
            "{$data['user']['usr_full_name']}{$group_name} Weekly Report {$data['start']} - {$data['end']}"
        );
        $output->writeln("");

        $output->writeln("Issues worked on:");
        if ($data['issues']['other']) {
            foreach ($data['issues']['other'] as $type => $issue) {
                $iss_id = str_pad($issue['iss_id'], 5, ' ', STR_PAD_LEFT);
                $output->writeln("{$iss_id} {$issue['iss_summary']}");
            }
        } else {
            $output->writeln("No issues touched this time period");
        }
        // TODO: handle separate closed, etc view options

        $output->writeln("");
        $output->writeln("New Issues Assigned:  {$data['new_assigned_count']}");

        // iterate over issue statuses
        foreach ($data['status_counts'] as $status) {
            $title = str_pad($status['sta_title'], 22);
            $output->writeln("$title {$status['total']}");
        }
        $total = count($data['issues']['other']) + count($data['issues']['closed']);
        $output->writeln("Total Issues: $total");

        $output->writeln("");
        $output->writeln("Eventum Emails:       {$data['email_count']['associated']}");
        $output->writeln("Other Emails:         {$data['email_count']['other']}");
        $output->writeln("Total Phone Calls:    {$data['phone_count']}");
        $output->writeln("Total Notes:          {$data['note_count']}");

        $output->writeln("");
        $time_spent = @$data['time_tracking']['Telephone_Discussion']['formatted_time'] ?: "00h 00m";
        $output->writeln("Phone Time Spent:     $time_spent");
        $time_spent = @$data['time_tracking']['Email_Discussion']['formatted_time'] ?: "00h 00m";
        $output->writeln("Email Time Spent:     $time_spent");
        $time_spent = @$data['time_tracking']['Login_Work']['formatted_time'] ?: "00h 00m";
        $output->writeln("Login Time Spent:     $time_spent");

        $output->writeln("Total Time Spent:     {$data['total_time']}");
    }
}
