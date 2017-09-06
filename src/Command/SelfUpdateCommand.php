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
use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Strategy\ShaStrategy;
use KevinGH\Version\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Humbug\SelfUpdate\Updater;

class SelfUpdateCommand extends Command
{
    /** for github strategy: stable releases */
    const PackageName = 'eventum/cli';
    const PharFile = 'eventum.phar';

    /** for sha1 strategy: snapshot releases */
    const PharFileUrl = 'https://raw.githubusercontent.com/eventum/cli/dist/eventum.phar';
    const VersionFileUrl = 'https://raw.githubusercontent.com/eventum/cli/dist/versions.json';

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates eventum.phar to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = $this->getUpdater();

        $result = $updater->update();
        if (!$result) {
            $version = $updater->getNewVersion();
            $output->writeln("<info>You are already using version {$version}.</info>");
            return 0;
        }

        $new = $updater->getNewVersion();
        $old = $updater->getOldVersion();
        $output->writeln("<info>Updated from {$old} to {$new}.</info>");

        return 0;
    }

    /**
     * Get updater based whether we are on stable or unstable channel.
     *
     * @return Updater
     */
    private function getUpdater()
    {
        $appVersion = $this->getApplication()->getVersion();
        $version = Version::create($appVersion);

        $updater = new Updater(null, false);

        if ($version->isStable()) {
            $updater->setStrategy(Updater::STRATEGY_GITHUB);

            /** @var GithubStrategy $strategy */
            $strategy = $updater->getStrategy();

            $strategy->setPackageName(self::PackageName);
            $strategy->setPharName(self::PharFile);
            $strategy->setCurrentLocalVersion($appVersion);
        } else {
            /** @var ShaStrategy $strategy */
            $strategy = $updater->getStrategy();
            $strategy->setPharUrl(self::PharFileUrl);
            $strategy->setVersionUrl(self::VersionFileUrl);
        }

        return $updater;
    }
}
