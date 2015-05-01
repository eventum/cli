<?php

namespace Eventum\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Eventum_RPC;
use CallbackFilterIterator;
use ArrayIterator;

class DumpMethodsCommand extends Command
{
    /**
     * @var \RemoteApi|Eventum_RPC
     */
    private $client;

    protected function configure()
    {
        $this
            ->setName('dump-methods')
            ->setDescription('Dump available XMLRPC methods from Eventum')
            ->addArgument(
                'method',
                InputArgument::OPTIONAL,
                'method name'
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

        foreach ($this->getMethods($input) as $method) {
            $help = $this->getMethodHelp($method);
            $signature = $this->getMethodSignature($method);
            $output->writeln("");
            $output->writeln("    <comment>$help</comment>");
            $output->writeln("    function <info>$method</info>($signature)");
        }
    }

    /**
     * Get methods to display.
     *
     * @param InputInterface $input
     * @return array List of methods to inspect
     */
    private function getMethods($input)
    {
        $method = $input->getArgument('method');

        // get sorted list of methods
        $it = new ArrayIterator($this->client->__call('system.listMethods', array()));
        $it->asort();

        $length = strlen($method);
        $accept = function ($value) use ($method, $length) {
            // exclude system methods
            if (substr($value, 0, 7) == 'system.') {
                return false;
            }

            // filter by name prefix
            if ($length) {
                return substr($value, 0, $length) == $method;
            }

            // accept anything else
            return true;
        };
        $filter = new CallbackFilterIterator($it, $accept);

        return $filter;
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
     * Get method parameter types.
     *
     * @param string $method
     * @return string
     */
    private function getMethodSignature($method)
    {
        $signature = $this->client->__call('system.methodSignature', array($method));
        return join(', ', current($signature));
    }
}