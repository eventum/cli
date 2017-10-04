define find_tool
$(shell PATH=$$PATH:. which $1.phar 2>/dev/null || which $1 2>/dev/null || echo false)
endef

define fetch_tool
curl -sSLf $1 -o $@.tmp && chmod +x $@.tmp && mv $@.tmp $@
endef

PHPCS_FIXER_VERSION := 2.5.0

box := $(call find_tool, box)
ifeq ($(PHP),)
php := $(call find_tool, php)
else
php := $(PHP)
endif
composer := $(call find_tool, composer)
composer_options :=

all:
	@echo 'Run "make eventum.phar" to build standalone eventum cli phar.'

composer.lock:
	 $(composer) install --prefer-dist $(composer_options)

eventum.phar: composer.lock box.json
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

php-cs-fixer.phar:
	$(call fetch_tool,https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v$(PHPCS_FIXER_VERSION)/php-cs-fixer.phar)

clean:
	rm -vf eventum.phar

dist: dist/.git
	rm -rf dist/build
	git clone . dist/build
	$(MAKE) -C dist/build eventum.phar composer_options="--no-dev --classmap-authoritative"
	mv dist/build/eventum.phar dist

manifest: dist
	./eventum.php create-manifest -o dist/manifest.json dist/eventum.phar

dist/.git:
	git_url=`git config remote.origin.url`; \
	git clone "$$git_url" dist -b gh-pages --depth=1

distclean: clean
	rm -rf composer.lock vendor *.phar

.PHONY: deps dist
