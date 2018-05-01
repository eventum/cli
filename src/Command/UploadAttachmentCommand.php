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

use Eventum\RPC\XmlRpcException;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadAttachmentCommand extends Command
{
    const COMMAND_NAME = 'attachment:upload';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setAliases(array('add-attachment'))
            ->setDescription('Upload attachment to an issue')
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
        $contents = $this->getFileContents($file);
        $filename = $input->getOption('filename') ?: basename($file);
        $mimetype = $input->getOption('mimetype') ?: $this->util->getFileMimeType($file);
        $file_description = $input->getOption('description') ?: $this->getFileDescription($file);
        $internal_only = $input->getOption('internal');

        $client = $this->getClient();

        $binary = $client->encodeBinary($contents);
        try {
            $res = $client->addFile($issue_id, $filename, $mimetype, $binary, $file_description, $internal_only);
        } catch (XmlRpcException $e) {
            if ($e->getMessage() === 'XML error: Invalid document end at line 1') {
                $this->checkFileSize(strlen($contents));
            }
            throw $e;
        }

        $baseurl = $this->getEventumUrl();
        $dl_url = "{$baseurl}/download.php?cat=attachment&id={$res['iaf_id']}";
        $issue_url = "{$baseurl}/view.php?id=$issue_id";

        if ($internal_only) {
            $status = '<fg=red>internal</fg=red>';
        } else {
            $status = '<fg=yellow>public</fg=yellow>';
        }
        $filesize = $this->util->formatMemory(strlen($contents), 2);
        $output->writeln("Uploaded '$filename' ($filesize) to issue $issue_url");
        $output->writeln("Status: $status");
        $output->writeln("Description: $file_description");
        $output->writeln("MIME-Type: $mimetype");
        $output->writeln("<comment>To view</comment>: $dl_url&force_inline=1");
        $output->writeln("<comment>To download</comment>: $dl_url");
    }

    /**
     * @param string $fileName
     * @throws RuntimeException
     * @return string
     */
    private function getFileContents($fileName)
    {
        if (!file_exists($fileName)) {
            throw new RuntimeException("File does not exist: $fileName");
        }
        if (!is_file($fileName)) {
            throw new RuntimeException("Not a file: $fileName");
        }
        if (!is_readable($fileName)) {
            throw new RuntimeException("File not readable: $fileName");
        }

        $contents = file_get_contents($fileName);
        if ($contents === false) {
            throw new RuntimeException("Unable to read $fileName");
        }

        return $contents;
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

        return "File {$file} uploaded from {$hostname} via CLI";
    }

    /**
     * @param int $fileSize
     * @throws InvalidArgumentException
     */
    private function checkFileSize($fileSize)
    {
        $max = $this->getMaxFileSize();
        if ($fileSize <= $max) {
            return;
        }

        $max = $this->util->formatMemory($max, 2);
        $printable = $this->util->formatMemory($fileSize, 2);
        throw new InvalidArgumentException("Uploaded file too big: $printable, max filesize $max");
    }

    /**
     * Get Eventum server upload_max_filesize parameter
     *
     * @return int
     */
    private function getMaxFileSize()
    {
        return (int)$this->getClient()->getServerParameter('upload_max_filesize');
    }
}
