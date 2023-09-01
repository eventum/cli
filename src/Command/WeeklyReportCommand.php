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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
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

        // TODO: handle separate closed, etc view options
        $this->formatIssuesTable($data['issues']['other']);
        $this->formatIssueStatuses($data['status_counts']);
        $this->formatTimeTracking($data['time_tracking'], $data['total_time']);
        $this->formatIssueStats($data);
    }

    private function formatIssuesTable(array $issues)
    {
        $this->output->writeln('Issues worked on:');
        if (!$issues) {
            $this->output->writeln('No issues touched this time period');

            return;
        }

        $table = new Table($this->output);
        $rightAligned = new TableStyle();
        $rightAligned->setPadType(STR_PAD_LEFT);
        $table->setColumnStyle(0, $rightAligned);
        $table->setHeaders(array('issue id', 'issue summary'));

        $issueWidth = 0;
        foreach ($issues as $type => $issue) {
            $issueId = $this->renderIssueLink($issue['iss_id']);
            $table->addRow(array($issueId, $issue['iss_summary']));
        }
        $table->setColumnWidth(0, 10);

        $table->setColumnWidths(array(10, 0, 30));

        $table->render();
    }

    /**
     * @see https://gist.github.com/egmontkob/eb114294efbcd5adb1944c9f3cb5feda#the-escape-sequence
     * @see https://github.com/symfony/symfony/pull/29668
     * @see https://github.com/symfony/console/commit/20e4894521056ff6aa059015143bb971d475469a#diff-5871b25b12684413fc82089739d41411 symfony 4.2+
     * @see https://github.com/symfony/console/commit/4f04cf84d6b0ce4beae6d1ed7767043636d7bfab#diff-5871b25b12684413fc82089739d41411 4.3.0
     * @see https://youtrack.jetbrains.com/issue/IDEA-204536
     */
    private function renderIssueLink($issue_id, $issue_link1 = '')
    {
        $issue_link = 'http://example.com';
        return "\e]8;;{$issue_link}\e\\{$issue_id}\e]8;;\e\\";
    }

    private function formatIssueStatuses(array $data)
    {
        $this->output->writeln('Issues by status:');
        $table = new Table($this->output);
        $table->setHeaders(array('status', 'count'));

        foreach ($data as $status) {
            $table->addRow(array($status['sta_title'], $status['total']));
        }
        $table->render();
    }

    private function formatIssueStats(array $data)
    {
        $table = new Table($this->output);
        $table->addRow(array('New Issues Assigned', $data['new_assigned_count']));
        $table->addRow(array('Total Issues', count($data['issues']['other']) + count($data['issues']['closed'])));

        $table->addRow(array('Eventum Emails', $data['email_count']['associated']));
        $table->addRow(array('Other Emails', $data['email_count']['other']));
        $table->addRow(array('Total Phone Calls', $data['phone_count']));
        $table->addRow(array('Total Notes', $data['note_count']));
        $table->render();
    }

    private function formatTimeTracking(array $time_tracking, $total_time)
    {
        $table = new Table($this->output);
        $table->setHeaders(array('Time Spent', ''));

        $time_spent = @$time_tracking['Telephone_Discussion']['formatted_time'] ?: '00h 00m';
        $table->addRow(array('Phone Time Spent', $time_spent));

        $time_spent = @$time_tracking['Email_Discussion']['formatted_time'] ?: '00h 00m';
        $table->addRow(array('Email Time Spent', $time_spent));

        $time_spent = @$time_tracking['Login_Work']['formatted_time'] ?: '00h 00m';
        $table->addRow(array('Login Time Spent', $time_spent));

        $table->addRow(array('Total Time Spent', $total_time));
        $table->render();
    }

    /**
     * @return array
     */
    private function getWeeklyReportData()
    {
        list($start, $end) = $this->getDateRange();
        $prj_id = $this->getProjectId();
        $options = array(
            'separate_closed' => $this->input->getOption('separate-closed'),
        );

        /** @var array $data */
        $data = $this->getClient()->getWeeklyReportData($prj_id, $start, $end, $options);

        return $data;
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
