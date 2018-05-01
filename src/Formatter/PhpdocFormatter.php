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

namespace Eventum\Console\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

class PhpdocFormatter implements FormatterInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function format($function, $signature, $docstring)
    {
        $return = array_shift($signature);
        $arguments = implode(', ', $signature);

        $this->output->writeln('');
        $this->output->writeln("    <comment>$docstring</comment>");
        $this->output->writeln("    function <info>$function</info>($arguments): $return");
    }
}
