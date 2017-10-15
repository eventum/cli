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

use Eventum\Console\AuthHelper;
use Eventum\Console\Config;
use Eventum\Console\IO;
use Eventum\Console\Util;
use Eventum\RPC\RemoteApi;
use Eventum_RPC;
use Eventum_RPC_Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends BaseCommand
{
    /**
     * URI path to RPC endpoint in Eventum server
     */
    const RPC_PATH = '/rpc/xmlrpc.php';

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var IO
     */
    protected $io;

    /**
     * @var Util
     */
    protected $util;

    /**
     * @var AuthHelper
     */
    protected $auth;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var RemoteApi|Eventum_RPC
     */
    private $client;

    /**
     * Initializes the command just after the input has been validated.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new IO($input, $output, $this->getHelperSet());
        $this->util = new Util();

        // Load config and override with local config/auth config
        $this->config = new Config();

        // load auth configs into the IO instance
        $this->auth = new AuthHelper();
        $this->auth->loadConfiguration($this->config);
    }

    private function getUserAgent()
    {
        $version = $this->getApplication()->getVersion();
        if ($version[0] === '@') {
            $version = 'git';
        }
        return 'EventumCLI/' . $version;
    }

    /**
     * @return RemoteApi|Eventum_RPC
     */
    protected function getClient()
    {
        if (!$this->client) {
            $url = $this->getUrl();
            $this->client = new Eventum_RPC($url);
            $this->client->addUserAgent($this->getUserAgent());

            // set debug if verbosity debug
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                $this->client->setDebug(1);
            }

            if ($this->auth->hasAuthentication($url)) {
                $auth = $this->auth->getAuthentication($url);
            } else {
                $auth = $this->askAuthentication($url);
            }

            $this->client->setCredentials($auth['username'], $auth['password']);
        }

        return $this->client;
    }

    /**
     * Ask authentication credentials, retry several times
     *
     * @param string $url
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return array
     */
    private function askAuthentication($url, $retry = 3)
    {
        $defaultUsername = null;
        for ($i = 0; $i < $retry; $i++) {
            $this->output->writeln("    Authentication required (<info>{$url}</info>):");
            $auth = array(
                'username' => $this->io->ask('      Username' . ($defaultUsername ? " [$defaultUsername]: " : ': '), $defaultUsername),
                'password' => $this->io->askHidden('      Password: '),
            );
            $defaultUsername = $auth['username'];

            $this->client->setCredentials($auth['username'], $auth['password']);

            try {
                $this->client->checkAuthentication();
                $this->auth->setAuthentication($url, $auth['username'], $auth['password']);

                $storeAuth = $this->config->get('store-auths');
                $this->storeAuth($url, $storeAuth);

                return $auth;
            } catch (Eventum_RPC_Exception $e) {
                $this->output->writeln("<error>ERROR: {$e->getMessage()}</error>");
            }
            $this->output->writeln('');
        }

        throw new RuntimeException('Unable to authenticate');
    }

    /**
     * @return string URL to Eventum frontpage
     */
    protected function getEventumUrl()
    {
        $url = $this->getUrl();
        $url = substr($url, 0, -strlen(self::RPC_PATH));

        return $url;
    }

    private function getUrl()
    {
        $url = $this->input->getOption('url') ?: $this->config->get('url');

        if (!$url) {
            $url = $this->io->ask('    Eventum URL: ');
            if (!$url) {
                throw new InvalidArgumentException('URL must be provided');
            }
        }

        // allow user input url with trailing slash
        $url = rtrim($url, '/');

        // append rpc path
        if (substr($url, -strlen(self::RPC_PATH)) != self::RPC_PATH) {
            $url .= self::RPC_PATH;
        }

        return $url;
    }

    private function storeAuth($url, $storeAuth)
    {
        $store = false;
        if ($storeAuth === true) {
            $store = true;
        } elseif ($storeAuth === 'prompt') {
            $answer = $this->io->askAndValidate(
                'Do you want to store credentials for ' . $url . ' ? [Yn] ',
                function ($value) {
                    $input = strtolower(substr(trim($value), 0, 1));
                    if (in_array($input, array('y', 'n'))) {
                        return $input;
                    }
                    throw new RuntimeException('Please answer (y)es or (n)o');
                },
                false,
                'y'
            );

            if ($answer === 'y') {
                $store = true;
            }
        }

        if ($store) {
            $this->config->set('url', $url);
            $this->config->set('http-basic.' . $url, $this->auth->getAuthentication($url));
            $this->config->save();
        }
    }

    /**
     * If user has not specified project id via commandline,
     * ask it from user, unless user belongs to exactly one project.
     *
     * @throws InvalidArgumentException
     * @return int
     */
    protected function getProjectId()
    {
        $project_id = $this->input->getOption('project');
        if ($project_id) {
            return (int)$project_id;
        }

        $res = $this->getClient()->getUserAssignedProjects(false);
        if (!$res) {
            throw new InvalidArgumentException('User has no projects');
        }

        // if user has only one project. return that
        if (count($res) === 1) {
            $project = current($res);

            return (int)$project['id'];
        }

        // convert to sane array
        $projects = array();
        foreach ($res as $i => $project) {
            $projects[$project['id']] = $project['title'];
        }

        $project = $this->io->askChoices('Project:', $projects, 'Project %s is invalid.');
        $project_id = array_search($project, $projects);

        return (int)$project_id;
    }
}
