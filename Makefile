composer := $(shell which composer.phar 2>/dev/null || which composer 2>/dev/null || echo false)
box := $(shell which box.phar 2>/dev/null || which box 2>/dev/null || echo false)
php := php

all:
	@echo 'Run "make eventum.phar" to build standalone eventum cli phar.'

eventum.phar:
	 $(composer) install --prefer-dist
	 $(php) -d phar.readonly=0 $(box) build -v

XMLRPC.md: Makefile
	$(php) eventum.php --no-ansi dump > $@.tmp && mv $@.tmp $@

clean:
	rm -vf *.phar

.PHONY: eventum.phar
