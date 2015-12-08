<?php
namespace Eventum\Console\Command;

use Eventum_RPC;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddTimeEntryCommand extends Command
{
    /**
     * @var \RemoteApi|Eventum_RPC
     */
    private $client;

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
            ->addOption(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Time Entry Category'
            )
            ->addOption(
                'summary',
                null,
                InputOption::VALUE_REQUIRED,
                'Summary'
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
        $this->client = $this->getClient();

        $issue_id = (int )$input->getArgument('issue_id');
        $time_spent = (int)$input->getArgument('time-spent');
        $category = $this->getCategory($issue_id);
        $summary = $input->getOption('summary') ?: 'Time entry added from CLI';
        $this->client->recordTimeWorked($issue_id, $category, $summary, $time_spent);
        $output->writeln("Added time entry to #$issue_id: $time_spent minutes ($summary)");
    }

    /**
     * @return int
     */
    private function getCategory($issue_id)
    {
        $category = $this->input->getOption('category');
        // numeric category requires no lookup
        if ($category && is_numeric($category)) {
            return (int)$category;
        }

        $categories = $this->client->getTimeTrackingCategories($issue_id);

        if ($category && ($category_id = array_search($category, $categories))) {
            return (int)$category_id;
        }

        $category = $this->io->askChoices('Time Category:', $categories, 'Category-Id %s is invalid.');
        $category_id = array_search($category, $categories);

        return (int)$category_id;
    }
}
