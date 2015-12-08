<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
	->in(__DIR__)
	->exclude('vendor')
;

# Levels are incremental:
# none->psr0->psr1->psr2->symfony
# We'll stick to PSR-2
# Symfony is nice, but some conflict with IDE formatting
# https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/1.12/Symfony/CS/FixerInterface.php#L19-L24
return Symfony\CS\Config\Config::create()
	->setUsingCache(true)
	->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
	->finder($finder)
;
