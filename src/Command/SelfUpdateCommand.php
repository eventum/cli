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

use KevinGH\Amend;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Amend\Command
{
    const MANIFEST_FILE = 'https://raw.githubusercontent.com/eventum/cli/dist/manifest.json';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->set(new Amend\Helper());
        $this->setManifestUri(self::MANIFEST_FILE);

        $this
            ->setName('self-update')
            ->setDescription('Updates eventum.phar to the latest version');

        parent::execute($input, $output);
    }
}
