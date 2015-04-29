<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddAttachmentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('add-attachment')
            ->setDescription('Add attachment to issue')
            ->addArgument(
                'issue_id',
                InputArgument::REQUIRED,
                'Issue id'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'File to upload'
            )
            ->addOption(
                'filename',
                'f',
                InputOption::VALUE_REQUIRED,
                'Override filename'
            )
            ->addOption(
                'mimetype',
                'm',
                InputOption::VALUE_REQUIRED,
                'Override mimetype'
            )
            ->addOption(
                'description',
                'd',
                InputOption::VALUE_REQUIRED,
                'Add description to the file'
            )
            ->addOption(
                'internal',
                'i',
                InputOption::VALUE_NONE,
                'Set file visibility status to Internal'
            )
            ->setHelp(
                <<<EOT
                <info>%command.full_name% 123 file.txt</info>

Upload file.txt to issue 123.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $issue_id = (int )$input->getArgument('issue_id');
        $file = $input->getArgument('file');
        $filename = $input->getOption('filename') ?: basename($file);
        $mimetype = $input->getOption('mimetype') ?: 'application/octet-stream';
        $contents = file_get_contents($file);
        $file_description = $input->getOption('description') ?: '';
        $internal_only = $input->getOption('internal');

        $client = $this->getClient();
        $res = $client->addFile($issue_id, $filename, $mimetype, $contents, $file_description, $internal_only);

        $output->writeln("Uploaded <info>OK</info>, id={$res['iaf_id']}");
    }
}