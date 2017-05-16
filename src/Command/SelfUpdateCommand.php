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

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use KevinGH\Version\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    const MANIFEST_FILE = 'https://raw.githubusercontent.com/eventum/cli/dist/manifest.json';

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates eventum.phar to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $version = Version::create($this->getApplication()->getVersion());

        $update = $manager->getManifest()->findRecent($version, true, true);
        if ($update) {
            $output->writeln("<info>Updating to version {$update->getVersion()}.</info>");
            $manager->update($version, true, true);
        } else {
            $output->writeln("<info>You are already using composer version {$version}.</info>");
        }
    }
}
