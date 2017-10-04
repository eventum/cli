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

use Herrera\Version\Parser;
use KevinGH\Amend;
use KevinGH\Amend\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Amend\Command
{
    const MANIFEST_FILE = 'https://eventum.github.io/cli/manifest.json';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->set(new Amend\Helper());
        $this->setManifestUri(self::MANIFEST_FILE);

        // Allow pre-release updates for pre-releases themselves
        $version = Parser::toVersion($this->getApplication()->getVersion());
        if (!$version->isStable()) {
            $input->setOption('pre', true);
            $output->writeln('Set pre-release to <info>true</info>', OutputInterface::VERBOSITY_VERBOSE);
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->dumpUpdates($output);
        }

        parent::execute($input, $output);
    }

    public function dumpUpdates(OutputInterface $output)
    {
        /** @var Helper $amend */
        $amend = $this->getHelper('amend');
        $manager = $amend->getManager(self::MANIFEST_FILE);

        $output->writeln('Available versions (<info>*</info> indicates pre-release):');

        $manifest = $manager->getManifest();
        foreach ($manifest->getUpdates() as $update) {
            $version = $update->getVersion();
            $preReleaseFlag = $version->isStable() ? ' ' : '*';
            $output->writeln("- <info>{$version}</info>{$preReleaseFlag} - {$update->getUrl()}");
        }
    }
}
