<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Set config options')
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'List configuration settings'
            )
            ->setHelp(<<<EOT
This command allows you to edit some basic settings in the Eventum config file.

To get a list of configuration values in the file:

    <comment>%command.full_name% --list</comment>

EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // List the configuration of the file settings
        if ($input->getOption('list')) {
            $output->writeln("Listing configuration options");
            return;
        }
    }
}