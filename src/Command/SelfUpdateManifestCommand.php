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

use RuntimeException;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateManifestCommand extends Command
{
    const DIST_URL = 'https://raw.githubusercontent.com/eventum/cli/dist/eventum.phar';

    protected function configure()
    {
        $this
            ->setName('create-manifest')
            ->setDescription('Creates manifest.json for self-update command')
            ->addArgument(
                'phar-file',
                InputArgument::REQUIRED,
                'PHAR file'
            )
            ->addOption(
                'output-file',
                'o',
                InputOption::VALUE_REQUIRED,
                'Filename to save output to'
            )
            ->setHelp(
                <<<EOT
<info>%command.full_name% -o manifest.json ./eventum.phar</info>

Create manifest.json file for self update command.

This is internal command and won't be included in eventum.phar itself.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pharFile = $input->getArgument('phar-file');

        $version = new stdClass();
        $version->name = basename($pharFile);
        $version->sha1 = sha1_file($pharFile);
        $version->url = self::DIST_URL;
        $version->version = $this->getPharFileVersion($pharFile);

        $manifest = array(
            $version,
        );

        $result = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        if ($outputFile = $input->getOption('output-file')) {
            file_put_contents($outputFile, $result);
        } else {
            echo $result;
        }
    }

    /**
     * Extract version from $pharFile
     *
     * @param string $pharFile
     * @param $pharFile
     * @return string
     * @throws RuntimeException
     */
    private function getPharFileVersion($pharFile)
    {
        $command = "$pharFile --version --no-ansi";
        $out = exec($command, $discard, $rc);
        if ($rc !== 0) {
            throw new RuntimeException("$command failed with rc=$rc");
        }
        $parts = explode(' ', $out);

        return end($parts);
    }
}
