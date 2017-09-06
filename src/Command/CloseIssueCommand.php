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

class CloseIssueCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('close')
            ->setDescription('Marks an issue as closed')
            ->addArgument(
                'issue_id',
                InputArgument::REQUIRED,
                'Issue id'
            )
            ->addOption(
                'status',
                's',
                InputOption::VALUE_REQUIRED,
                'New status for issue'
            )
            ->addOption(
                'resolution',
                'r',
                InputOption::VALUE_REQUIRED,
                'Resolution for issue'
            )
            ->addOption(
                'notify',
                null,
                InputOption::VALUE_NONE,
                'Send notification about issue close',
                null
            )
            ->addOption(
                'no-notify',
                null,
                InputOption::VALUE_NONE,
                'Do not send notification about issue close',
                null
            )
            ->addOption(
                'message',
                'm',
                InputOption::VALUE_REQUIRED,
                'Note message'
            )
            // project_id required when retrieving statuses
            // should really figure that out by asking via issue_id instead
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'Project Id'
            )
            ->setHelp(
                <<<EOT
                <info>%command.full_name% 123</info>

Marks an issue as closed.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $issue_id = (int )$input->getArgument('issue_id');
        $status = $this->getStatus();
        $resolution_id = $this->getResolutionId();
        $send_notification = $this->getSendNotification();
        $note = $this->getMessage();

        $client = $this->getClient();
        $result = $client->closeIssue($issue_id, $status, $resolution_id, $send_notification, $note);

        $message = "OK: issue #$issue_id successfully closed.";
        $output->writeln($message);

        if ($result === 'INCIDENT') {
            $message = 'WARNING: This customer has incidents.';
            $message .= " Please redeem incidents by running 'eventum $issue_id redeem'";
            $output->writeln($message);
        }
    }

    /**
     * Return issue status title.
     *
     * @return string
     */
    private function getStatus()
    {
        $status = $this->input->getOption('status');
        if ($status) {
            return $status;
        }

        $list = $this->getClient()->getClosedAbbreviationAssocList($this->getProjectId());
        $prompt = 'Which status do you want to use in this action?';
        $errorMessage = "Entered status doesn't match any in the list available to you";

        return $this->askChoices($prompt, $list, $errorMessage);
    }

    /**
     * @return int
     */
    private function getResolutionId()
    {
        $resolution = $this->input->getOption('resolution');
        if ($resolution) {
            return (int)$resolution;
        }

        $list = $this->getClient()->getResolutionAssocList();
        $prompt = 'Which resolution do you want to use in this action?';
        $errorMessage = "Entered resolution doesn't match any in the list available to you";

        return (int)$this->askChoices($prompt, $list, $errorMessage);
    }

    /**
     * @return bool
     */
    private function getSendNotification()
    {
        $notify = $this->input->getOption('notify');
        $no_notify = $this->input->getOption('no-notify');
        if ($notify || $no_notify) {
            return $notify || !$no_notify;
        }

        $question = 'Would you like to send a notification email about this issue being closed? [Y/n]: ';

        return $this->io->askConfirmation($question, 'y');
    }

    /**
     * Get issue close message from commandline option or prompt from user.
     *
     * @return string
     */
    private function getMessage()
    {
        $message = $this->input->getOption('message');
        if ($message) {
            return $message;
        }

        $question = 'Please enter a reason for closing this issue (one line only): ';

        return $this->io->ask($question);
    }

    /**
     * ask choices, but return the key not value from the list.
     */
    private function askChoices($prompt, $list, $errorMessage)
    {
        // avoid asking if answer is known
        switch (count($list)) {
            case 0:
                return 0;
            case 1:
                return key($list);
        }

        $answer = $this->io->askChoices($prompt, $list, $errorMessage);

        return array_search($answer, $list);
    }
}
