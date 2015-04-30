<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Eventum_RPC_Exception;

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
        $file_description = $input->getOption('description') ?: $this->getFileDescription($file);
        $internal_only = $input->getOption('internal');

        $client = $this->getClient();

        $binary = $client->encodeBinary($contents);
        try {
            $res = $client->addFile($issue_id, $filename, $mimetype, $binary, $file_description, $internal_only);
        } catch (Eventum_RPC_Exception $e) {
            if ($e->getMessage() == "XML error: Invalid document end at line 1") {
                $this->checkFilesize(strlen($contents));
            }
            throw $e;
        }

        $baseurl = $this->getEventumUrl();
        $dl_url = "{$baseurl}/download.php?cat=attachment&id={$res['iaf_id']}";
        $issue_url = "{$baseurl}/view.php?id=$issue_id";

        if ($internal_only) {
            $status = "<fg=red>internal</fg=red>";
        } else {
            $status = "<fg=yellow>public</fg=yellow>";
        }
        $filesize = $this->util->formatMemory(strlen($contents), 2);
        $output->writeln("Uploaded '$filename' ($filesize) to issue $issue_url");
        $output->writeln("Status: $status");
        $output->writeln("Description: $file_description");
        $output->writeln("<comment>To view</comment>: $dl_url&force_inline=1");
        $output->writeln("<comment>To download</comment>: $dl_url");
    }

    /**
     * Generate automatic description for file
     *
     * @param string $file
     * @return string
     */
    private function getFileDescription($file)
    {
        $hostname = gethostname();
        $file = realpath($file);
        $description = "File {$file} uploaded from {$hostname} via CLI";

        return $description;
    }

    private function checkFilesize($filesize)
    {
        $max = $this->getMaxFileSize();
        if ($filesize <= $max) {
            return;
        }

        $max = $this->util->formatMemory($max, 2);
        $filesize = $this->util->formatMemory($filesize, 2);
        throw new \InvalidArgumentException("Uploaded file too big: $filesize, max filesize $max");
    }

    /**
     * Get Eventum server upload_max_filesize parameter
     *
     * @return int
     */
    private function getMaxFileSize()
    {
        return $this->getClient()->getServerParameter('upload_max_filesize');
    }
}