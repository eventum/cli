<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IssueDetailsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('issue')
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

        $client = $this->getClient();

        // FIXME: this used to do:
//        $details = self::checkIssuePermissions($client, $auth, $issue_id);
        $details = $client->getIssueDetails($issue_id);

        $msg = '';
        if (!empty($details["quarantine"]["iqu_status"])) {
            $msg .= "        WARNING: Issue is currently quarantined!";
            if (!empty($details["quarantine"]["iqu_expiration"])) {
                $msg .= " Quarantine expires in " . $details["quarantine"]["time_till_expiration"];
            }
            $msg .= "\n";
        }
        $msg .= "        Issue #: $issue_id
        Summary: " . $details['iss_summary'] . "
         Status: " . $details['sta_title'] . "
     Assignment: " . $details['assignments'] . "
 Auth. Repliers: " . @implode(', ', $details['authorized_names']) . "
       Reporter: " . $details['reporter'];
        if (@isset($details['customer'])) {
            $msg
                .= "
       Customer: " . @$details['customer']['name'] . "
  Support Level: " . @$details['contract']['support_level'] . "
Support Options: " . @$details['contract']['options_display'] . "
          Phone: " . $details['iss_contact_phone'] . "
       Timezone: " . $details['iss_contact_timezone'] . "
Account Manager: " . @$details['customer']['account_manager_name'];
        }
        $msg
            .= "
  Last Response: " . $details['iss_last_response_date'] . "
   Last Updated: " . $details['iss_updated_date'];
        $output->writeln($msg);
    }
} 