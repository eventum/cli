#!/usr/bin/php
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

use Eventum\Console\Application;

require_once __DIR__ . '/vendor/autoload.php';

$application = new Application('Eventum CLI', '@package_version@');
$application->run();
