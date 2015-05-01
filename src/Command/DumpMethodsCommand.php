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
        $client = $this->getClient();
        foreach ($this->getMethods($client, $input) as $method) {
            $help = $this->getMethodHelp($client, $method);
            $signature = $this->getMethodSignature($client, $method);
            $output->writeln("");
            $output->writeln("    <comment>$help</comment>");
            $output->writeln("    function <info>$method</info>($signature)");
        }
    }

    /**
     * Get methods to display.
     *
     * @param Eventum_RPC $client
     * @param InputInterface $input
     * @return array List of methods to inspect
     */
    private function getMethods($client, $input)
    {
        $method = $input->getArgument('method');
        if ($method) {
            return (array)$method;
        }

        // get sorted list of methods
        $it = new ArrayIterator($client->__call('system.listMethods', array()));
        $it->asort();

        // exclude system methods
        $valid = function ($value) {
            return substr($value, 0, 7) != 'system.';
        };
        $filter = new CallbackFilterIterator($it, $valid);

        return $filter;
    }

    /**
     * Get available documentation for $method.
     *
     * @param Eventum_RPC $client
     * @param string $method
     * @return string
     */
    private function getMethodHelp($client, $method)
    {
        return $client->__call('system.methodHelp', array($method));
    }

    /**
     * Get method parameter types.
     *
     * @param Eventum_RPC $client
     * @param string $method
     * @return string
     */
    private function getMethodSignature($client, $method)
    {
        $signature = $client->__call('system.methodSignature', array($method));
        return join(', ', current($signature));
    }
}