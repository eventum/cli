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

use DateTime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WeeklyReportCommand extends Command
{
    const COMMAND_NAME = 'report:weekly';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setAliases(array('wr', 'weekly-report'))
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
                InputOption::VALUE_REQUIRED,
                'Project Id'
            )
            ->addOption(
                'separate-closed',
                null,
                InputOption::VALUE_NONE,
                'Separate closed issues'
            )
            ->setHelp(
                <<<EOT

<info>%command.full_name% [<start_date>] [<end_date>] [--separate-closed]</info>

Shows the weekly report.

Dates should be in the format 'YYYY-MM-DD'.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->getWeeklyReportData();

        $group_name = isset($data['group_name']) ? "[{$data['group_name']}]" : '';
        $output->writeln(
            "{$data['user']['usr_full_name']}{$group_name} Weekly Report {$data['start']} - {$data['end']}"
        );
        $output->writeln('');

        $output->writeln('Issues worked on:');
        if ($data['issues']['other']) {
            foreach ($data['issues']['other'] as $type => $issue) {
                $iss_id = str_pad($issue['iss_id'], 5, ' ', STR_PAD_LEFT);
                $output->writeln("{$iss_id} {$issue['iss_summary']}");
            }
        } else {
            $output->writeln('No issues touched this time period');
        }
        // TODO: handle separate closed, etc view options

        $output->writeln('');
        $output->writeln("New Issues Assigned:  {$data['new_assigned_count']}");

        // iterate over issue statuses
        foreach ($data['status_counts'] as $status) {
            $title = str_pad($status['sta_title'], 22);
            $output->writeln("$title {$status['total']}");
        }
        $total = count($data['issues']['other']) + count($data['issues']['closed']);
        $output->writeln("Total Issues: $total");

        $output->writeln('');
        $output->writeln("Eventum Emails:       {$data['email_count']['associated']}");
        $output->writeln("Other Emails:         {$data['email_count']['other']}");
        $output->writeln("Total Phone Calls:    {$data['phone_count']}");
        $output->writeln("Total Notes:          {$data['note_count']}");

        $output->writeln('');
        $time_spent = @$data['time_tracking']['Telephone_Discussion']['formatted_time'] ?: '00h 00m';
        $output->writeln("Phone Time Spent:     $time_spent");
        $time_spent = @$data['time_tracking']['Email_Discussion']['formatted_time'] ?: '00h 00m';
        $output->writeln("Email Time Spent:     $time_spent");
        $time_spent = @$data['time_tracking']['Login_Work']['formatted_time'] ?: '00h 00m';
        $output->writeln("Login Time Spent:     $time_spent");

        $output->writeln("Total Time Spent:     {$data['total_time']}");
    }

    private function getWeeklyReportData()
    {
        list($start, $end) = $this->getDateRange();
        $prj_id = $this->getProjectId();
        $options = array(
            'separate_closed' => $this->input->getOption('separate-closed'),
        );

        return $this->getClient()->getWeeklyReportData($prj_id, $start, $end, $options);
    }

    private function getDateRange()
    {
        $start = $this->input->getArgument('start') ?: 'Last Monday';
        $end = $this->input->getArgument('end') ?: 'Next Monday';

        return array(
            new DateTime($start),
            new DateTime($end),
        );
    }
}
