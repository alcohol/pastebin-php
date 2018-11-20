#
# For more information on some of the magic targets, variables and flags used, see:
#  - [1] https://www.gnu.org/software/make/manual/html_node/Special-Targets.html
#  - [2] https://www.gnu.org/software/make/manual/html_node/Secondary-Expansion.html
#  - [3] https://www.gnu.org/software/make/manual/html_node/Suffix-Rules.html
#  - [4] https://www.gnu.org/software/make/manual/html_node/Options-Summary.html
#  - [5] https://www.gnu.org/software/make/manual/html_node/Special-Variables.html
#  - [6] https://www.gnu.org/software/make/manual/html_node/Choosing-the-Shell.html
#

# Ensure (intermediate) targets are deleted when an error occurred executing a recipe, see [1]
.DELETE_ON_ERROR:

# Enable a second expansion of the prerequisites, see [2]
.SECONDEXPANSION:

# Disable built-in implicit rules and variables, see [3, 4]
.SUFFIXES:
MAKEFLAGS += --no-builtin-rules
MAKEFLAGS += --no-builtin-variables

# Disable printing of directory changes, see [4]
MAKEFLAGS += --no-print-directory

# Warn about undefined variables -- useful during development of makefiles, see [4]
MAKEFLAGS += --warn-undefined-variables

# Show an auto-generated help if no target is provided, see [5]
.DEFAULT_GOAL := help

# Default shell, see [6]
SHELL := /bin/bash

help:
	@echo
	@printf "%-20s %s\n" Target Description
	@echo
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
	@echo

#
# PROJECT TARGETS
#
# To learn more about automatic variables that can be used in target recipes, see:
#  https://www.gnu.org/software/make/manual/html_node/Automatic-Variables.html
#

# Target that makes sure containers are built
CONTAINERS = $(shell find docker/services -name Dockerfile | sed 's/Dockerfile/.build/')

# Runtime dependencies
RUNTIME-DEPENDENCIES = traefik-network vendor/composer/installed.json $(CONTAINERS)

# Passed from ENV by travis-ci, but if not available use HEAD (currently checked out commit)
TRAVIS_COMMIT ?= $(shell git rev-parse HEAD)

# Take the short hash as release version
RELEASE = $(shell git rev-parse --short $(TRAVIS_COMMIT))

# Docker permissions
DOCKER_UID = $(shell id -u)
DOCKER_GID = $(shell id -g)
DOCKER_USER = $(DOCKER_UID):$(DOCKER_GID)

export DOCKER_UID
export DOCKER_GID

.PHONY: traefik-network
traefik-network:
	-docker network create traefik_webgateway

.PHONY: containers
containers: $(CONTAINERS)
containers: ## build all containers
	@touch $(CONTAINERS)

.PHONY: fg
fg: $(RUNTIME-DEPENDENCIES)
fg: ## launch the docker-compose setup (foreground)
	docker-compose up --remove-orphans --abort-on-container-exit

.PHONY: up
up: $(RUNTIME-DEPENDENCIES)
up: ## launch the docker-compose setup (background)
	docker-compose up --remove-orphans --detach

.PHONY: down
down: ## terminate the docker-compose setup
	-docker-compose down --remove-orphans

.PHONY: test
test: export APP_ENV := test
test: $(RUNTIME-DEPENDENCIES)
test: ## run phpunit test suite
	docker-compose run --rm -e APP_ENV --user $(DOCKER_USER) --name pastebin-testsuite php-fpm \
		bin/console cache:warmup
	docker-compose run --rm -e APP_ENV --user $(DOCKER_USER) --name pastebin-testsuite php-fpm \
		phpdbg -qrr vendor/bin/phpunit --colors=always --stderr --coverage-text --coverage-clover clover.xml

.PHONY: logs
logs: $(RUNTIME-DEPENDENCIES)
logs: ## show logs
	docker-compose logs

.PHONY: tail
tail: $(RUNTIME-DEPENDENCIES)
tail: ## tail logs
	docker-compose logs -f

.PHONY: shell
shell: export APP_ENV := dev
shell: export COMPOSER_HOME := /tmp
shell: $(RUNTIME-DEPENDENCIES)
shell: ## spawn a shell inside a php-fpm container
	docker-compose run --rm -e APP_ENV -e COMPOSER_HOME --user $(DOCKER_USER) --name pastebin-shell php-fpm \
		sh

#
# PATH BASED TARGETS
#

docker/services/%/.build: $$(shell find $$(@D) -type f -not -name .build)
	docker-compose build $*
	@touch $@

var/cache:
	mkdir -p $@

var/log:
	mkdir -p $@

vendor:
	mkdir -p $@

vendor/composer/installed.json: export APP_ENV := dev
vendor/composer/installed.json: export COMPOSER_HOME := /tmp
vendor/composer/installed.json: composer.json composer.lock vendor var/cache var/log $(CONTAINERS)
	docker-compose run --rm --no-deps -e APP_ENV -e COMPOSER_HOME \
		--user $(DOCKER_USER) \
		--volume /etc/passwd:/etc/passwd:ro \
		--volume /etc/group:/etc/group:ro \
		--name pastebin-composer \
		php-fpm composer install --no-interaction --no-progress --no-suggest --prefer-dist
	@touch $@
