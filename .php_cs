<?php

$header = <<<EOF
This file is part of the Eventum (Issue Tracking System) package.

@copyright (c) Eventum Team
@license GNU General Public License, version 2 or later (GPL-2+)

For the full copyright and license information,
please see the LICENSE and AUTHORS files
that were distributed with this source code.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$finder = Symfony\CS\Finder\DefaultFinder::create()
	->in(__DIR__)
	->exclude('vendor')
	->exclude('build')
;

# Levels are incremental:
# none->psr0->psr1->psr2->symfony
# We'll stick to PSR-2
# Symfony is nice, but some conflict with IDE formatting
# https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/1.12/Symfony/CS/FixerInterface.php#L19-L24
return Symfony\CS\Config\Config::create()
	->setUsingCache(true)
	->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
	->fixers(array(
		'header_comment',
	))
	->finder($finder)
;
