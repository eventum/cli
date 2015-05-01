<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $client = $this->getClient();

        // FIXME: this used to do:
//        $details = self::checkIssuePermissions($client, $auth, $issue_id);
        $details = $client->getIssueDetails($issue_id);

        if (!empty($details["quarantine"]["iqu_status"])) {
            $output->write("        WARNING: Issue is currently quarantined!");
            if (!empty($details["quarantine"]["iqu_expiration"])) {
                $output->write(" Quarantine expires in " . $details["quarantine"]["time_till_expiration"]);
            }
            $output->writeln("");
        }
        $output->writeln("        Issue #: $issue_id");
        $output->writeln("        Summary: " . $details['iss_summary']);
        $output->writeln("         Status: " . $details['sta_title']);
        $output->writeln("     Assignment: " . $details['assignments']);
        $output->writeln(" Auth. Repliers: " . implode(', ', $details['authorized_names']));
        $output->writeln("       Reporter: " . $details['reporter']);

        if (isset($details['customer'])) {
            $output->writeln("       Customer: " . @$details['customer']['name']);
            $output->writeln("  Support Level: " . @$details['contract']['support_level']);
            $output->writeln("Support Options: " . @$details['contract']['options_display']);
            $output->writeln("          Phone: " . $details['iss_contact_phone']);
            $output->writeln("       Timezone: " . $details['iss_contact_timezone']);
            $output->writeln("Account Manager: " . @$details['customer']['account_manager_name']);
        }
        $output->writeln("  Last Response: " . $details['iss_last_response_date']);
        $output->writeln("   Last Updated: " . $details['iss_updated_date']);
    }
} 