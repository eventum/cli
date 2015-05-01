<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Eventum_RPC_Exception;

class ViewIssueCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('view-issue')
            ->setDescription('Display Issue details')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue id'
            )
            ->setHelp(
                <<<EOT
                <info>%command.full_name% 123</info>

View general details of an existing issue.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $issue_id = (int )$input->getArgument('issue');

        $details = $this->getClient()->getIssueDetails($issue_id);

        if (!empty($details["quarantine"]["iqu_status"])) {
            $output->write("<info>WARNING</info>: Issue is currently quarantined!");
            if (!empty($details["quarantine"]["iqu_expiration"])) {
                $output->write(" Quarantine expires in " . $details["quarantine"]["time_till_expiration"]);
            }
            $output->writeln("");
        }

        $table = new Table($output);
        $table->addRow(array('Issue #', $issue_id));
        $table->addRow(array('Summary', $details['iss_summary']));
        $table->addRow(array('Status', $details['sta_title']));
        $table->addRow(array('Assignment', $details['assignments']));
        $table->addRow(array('Auth. Repliers', implode(', ', $details['authorized_names'])));
        $table->addRow(array('Reporter', $details['reporter']));

        if (isset($details['customer'])) {
            $table->addRow(array('Customer', $details['customer']['name']));
            $table->addRow(array('Support Level', $details['contract']['support_level']));
            $table->addRow(array('Support Options', $details['contract']['options_display']));
            $table->addRow(array('Phone', $details['iss_contact_phone']));
            $table->addRow(array('Timezone', $details['iss_contact_timezone']));
            $table->addRow(array('Account Manager', $details['customer']['account_manager_name']));
        }

        $table->addRow(array('Last Response', $details['iss_last_response_date']));
        $table->addRow(array('Last Updated', $details['iss_updated_date']));
        $table->render();

        $this->renderFilelist($output, $issue_id);
    }

    private function renderFilelist($output, $issue_id)
    {
        try {
            $filelist = $this->getClient()->getFileList($issue_id);
        } catch (Eventum_RPC_Exception $e) {
            // may throw "No files could be found"
            return;
        }

        $table = new Table($output);
        $table->setHeaders(array("Attachments"));

        $i = 1;
        foreach ($filelist as $attachment) {
            if ($i > 1) {
                $table->addRow(new TableSeparator());
            }

            $table->addRow(
                array("Attachment sent by {$attachment['usr_full_name']} on {$attachment['iat_created_date']}")
            );

            if ($attachment['iat_description']) {
                $table->addRow(array("Description: {$attachment['iat_description']}"));
            }

            foreach ($attachment['files'] as $file) {
                $table->addRow(array("[$i]. {$file['iaf_filename']} ({$file['iaf_filesize']})"));
                $i++;
            }
        }
        $table->render();
    }
}