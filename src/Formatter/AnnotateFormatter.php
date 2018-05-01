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

class AnnotateFormatter implements FormatterInterface
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
        $doc = $this->parseBlockComment($docstring);
        $arguments = isset($doc['param']) ? $this->buildArguments($doc['param']) : '';
        $this->output->writeln(" * @method $return $function($arguments)");
    }

    private function buildArguments($params)
    {
        $result = array();
        foreach ($params as $param) {
            list($type, $name) = $param;
            $result[] = "{$type} {$name}";
        }

        return implode(', ', $result);
    }

    /**
     * Parse PHP Doc block and return array for each tag
     *
     * @param string $doc
     * @return array
     */
    private function parseBlockComment($doc)
    {
        $doc = preg_replace('#/+|\t+|\*+#', '', $doc);

        $tags = array();
        foreach (explode("\n", $doc) as $line) {
            $line = trim($line);
            $line = preg_replace('/\s+/', ' ', $line);

            if (empty($line) || $line[0] !== '@') {
                continue;
            }

            $tokens = explode(' ', $line);
            if (empty($tokens)) {
                continue;
            }

            $name = str_replace('@', '', array_shift($tokens));

            if (!isset($tags[$name])) {
                $tags[$name] = array();
            }
            $tags[$name][] = $tokens;
        }

        return $tags;
    }
}
