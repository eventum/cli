define find_tool
$(shell PATH=$$PATH:. which $1.phar 2>/dev/null || which $1 2>/dev/null || echo false)
endef

composer := $(call find_tool, composer)
box := $(call find_tool, box)
php := $(call find_tool, php)

all:
	@echo 'Run "make eventum.phar" to build standalone eventum cli phar.'

composer.lock:
	 $(composer) install --prefer-dist

eventum.phar: composer.lock
	 $(php) -d phar.readonly=0 $(box) build -v

XMLRPC.md: Makefile composer.lock
	$(php) eventum.php --no-ansi dump > $@.tmp && mv $@.tmp $@

deps:
	$(box) --version || $(MAKE) box.phar
	$(composer) --version || $(MAKE) composer.phar

composer.phar:
	curl -sS https://getcomposer.org/installer | php

box.phar:
	curl -LSs https://box-project.github.io/box2/installer.php | php

clean:
	rm -vf eventum.phar

distclean: clean
	rm -rf composer.lock vendor *.phar

.PHONY: deps
