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

use RuntimeException;
use XdgBaseDir\Xdg;

class Config
{
    /** @var string */
    private $configFile;
    /** @var array */
    private $config;

    public static $defaultConfig = array(
        'store-auths' => 'prompt',
    );

    public function __construct()
    {
        $this->config = static::$defaultConfig;
        $this->configFile = static::getHomeConfigDir() . '/eventum.json';
        $this->load();
    }

    /**
     * Load optional config in JSON format
     */
    public function load()
    {
        if (!file_exists($this->configFile)) {
            return;
        }

        // refuse to read the file with bad permissions
        if (fileperms($this->configFile) & 0077) {
            throw new RuntimeException(
                sprintf('The "%s" file should not be accessible for other users', $this->configFile)
            );
        }

        $this->config = json_decode(file_get_contents($this->configFile), true);
    }

    /**
     * Save config in JSON format
     */
    public function save()
    {
        $options = 0;
        if (PHP_VERSION_ID >= 50400) {
            $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        }

        $contents = json_encode($this->config, $options);
        file_put_contents($this->configFile, $contents);
        chmod($this->configFile, 0600);
    }

    /**
     * Returns a setting
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        switch ($key) {
            case 'home':
                return rtrim($this->config[$key], '/\\');
        }

        if (!isset($this->config[$key])) {
            return null;
        }

        return $this->config[$key];
    }

    /**
     * @param string $key
     * @param mixed $val
     */
    public function set($key, $val)
    {
        list($topKey) = explode('.', $key, 2);

        if ($topKey === 'http-basic') {
            list($key, $host) = explode('.', $key, 2);
            $this->config[$key][$host] = $val;
        } else {
            $this->config[$key] = $val;
        }
    }

    /**
     * @throws RuntimeException
     * @return string
     */
    public static function getHomeConfigDir()
    {
        $xdg = new Xdg();
        $dir = $xdg->getHomeConfigDir();

        if (!is_dir($dir)) {
            throw new RuntimeException(
                sprintf('The "%s" directory must exist run correctly', $dir)
            );
        }

        return $dir;
    }
}
