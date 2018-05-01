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

use ArrayIterator;
use CallbackFilterIterator;
use Eventum\Console\Formatter\AnnotateFormatter;
use Eventum\Console\Formatter\FormatterInterface;
use Eventum\Console\Formatter\PhpdocFormatter;
use Eventum_RPC;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpMethodsCommand extends Command
{
    const COMMAND_NAME = 'system:dump-methods';

    const FORMAT_PHPDOC = 'phpdoc';
    const FORMAT_ANNOTATE = 'annotate';

    /**
     * @var Eventum_RPC
     */
    private $client;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setAliases(array('dump-methods'))
            ->setDescription('Dump available XMLRPC methods from Eventum')
            ->addArgument(
                'method',
                InputArgument::OPTIONAL,
                'method name'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Set format',
                self::FORMAT_PHPDOC
            )
            ->setHelp(
                <<<EOT
                <info>%command.full_name% method_name</info>

Get info about available XMLRPC methods or specific one.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = $this->getClient();
        $formatter = $this->getFormatter($output, $input->getOption('format'));

        foreach ($this->getMethods($input) as $method) {
            $help = $this->getMethodHelp($method);
            $signature = $this->getMethodSignature($method);
            $formatter->format($method, $signature, $help);
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $format
     * @return FormatterInterface
     */
    private function getFormatter(OutputInterface $output, $format)
    {
        switch ($format) {
            case self::FORMAT_PHPDOC:
                return new PhpdocFormatter($output);
            case self::FORMAT_ANNOTATE:
                return new AnnotateFormatter($output);
            default:
                throw new InvalidArgumentException("Unknown format: $format");
        }
    }

    /**
     * Get methods to display.
     *
     * @param InputInterface $input
     * @return CallbackFilterIterator
     */
    private function getMethods(InputInterface $input)
    {
        $method = $input->getArgument('method');

        // get sorted list of methods
        $it = new ArrayIterator($this->client->__call('system.listMethods', array()));
        $it->asort();

        $length = strlen($method);
        $accept = function ($value) use ($method, $length) {
            // exclude system methods
            if (strpos($value, 'system.') === 0) {
                return false;
            }

            // filter by name prefix
            if ($length) {
                return substr($value, 0, $length) == $method;
            }

            // accept anything else
            return true;
        };

        return new CallbackFilterIterator($it, $accept);
    }

    /**
     * Get available documentation for $method.
     *
     * @param string $method
     * @return string
     */
    private function getMethodHelp($method)
    {
        return $this->client->__call('system.methodHelp', array($method));
    }

    /**
     * Get method signature: return value and argument types.
     *
     * @param string $method
     * @return array
     */
    private function getMethodSignature($method)
    {
        $signature = $this->client->__call('system.methodSignature', array($method));

        return current($signature);
    }
}
