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

namespace Eventum\Console;

class AuthHelper
{
    protected $authentications = array();

    /**
     * Verify if the repository has a authentication information.
     *
     * @param string $url The unique name of repository
     *
     * @return boolean
     */
    public function hasAuthentication($url)
    {
        return isset($this->authentications[$url]);
    }

    /**
     * Get the username and password of repository.
     *
     * @param string $url The unique name of repository
     *
     * @return array The 'username' and 'password'
     */

    public function getAuthentication($url)
    {
        if (isset($this->authentications[$url])) {
            return $this->authentications[$url];
        }

        return array('username' => null, 'password' => null);
    }

    /**
     * Set the authentication information for the repository.
     *
     * @param string $url The unique name of repository
     * @param string $username The username
     * @param string $password The password
     */
    public function setAuthentication($url, $username, $password = null)
    {
        $this->authentications[$url] = array('username' => $username, 'password' => $password);
    }

    /**
     * {@inheritDoc}
     */
    public function loadConfiguration(Config $config)
    {
        // reload http basic credentials from config if available
        if ($creds = $config->get('http-basic')) {
            foreach ($creds as $domain => $cred) {
                $this->setAuthentication($domain, $cred['username'], $cred['password']);
            }
        }
    }
}
