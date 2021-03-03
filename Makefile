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

PROJECT := phpbin

# Environment variable(s) for Symfony
export APP_ENV ?= dev

# Docker permissions (for Linux)
export DOCKER_UID ?= $(shell id -u)
export DOCKER_GID ?= $(shell id -g)
export DOCKER_USER ?= $(DOCKER_UID):$(DOCKER_GID)

#
# Docker-Compose Services & Containers
#

.PHONY: build
build: ## build containers
	docker-compose --file docker-compose.yaml --file docker-compose.traefik.yaml --project-name $(PROJECT) \
	  build --pull

.PHONY: fg
fg: ## launch the docker-compose setup (foreground)
	docker-compose --file docker-compose.yaml --file docker-compose.traefik.yaml --project-name $(PROJECT) \
	  up --remove-orphans

.PHONY: up
up: ## launch the docker-compose setup (background)
	docker-compose --file docker-compose.yaml --file docker-compose.traefik.yaml --project-name $(PROJECT) \
	  up --remove-orphans --detach

.PHONY: down
down: ## terminate the docker-compose setup
	-docker-compose --file docker-compose.yaml --file docker-compose.traefik.yaml --project-name $(PROJECT) \
	  down --remove-orphans

.PHONY: logs
logs: ## show logs
	docker-compose --file docker-compose.yaml --file docker-compose.traefik.yaml --project-name $(PROJECT) \
	  logs

.PHONY: tail
tail: ## tail logs
	docker-compose --file docker-compose.yaml --file docker-compose.traefik.yaml --project-name $(PROJECT) \
	  logs --follow

.PHONY: shell
shell: ## spawn a shell inside a new php-fpm container
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps composer \
	    sh

.PHONY: enter
enter: ## spawn a shell inside a running php-fpm container
	docker-compose --project-name $(PROJECT) \
	  exec -e APP_ENV --user $(DOCKER_USER) fpm \
	    sh


#
# Application related targets
#

.PHONY: install
install: composer.json
install: ## install dependencies (composer)
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps composer \
	    composer install --no-interaction --no-progress --prefer-dist

.PHONY: update
update: composer.lock
update: ## update dependencies (composer)
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps composer \
	    composer update --no-interaction --no-progress --prefer-dist

.PHONY: phpunit
phpcsfixer: vendor/bin/php-cs-fixer
phpcsfixer: ## run php-cs-fixer
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps fpm \
	    vendor/bin/php-cs-fixer fix --allow-risky=yes

.PHONY: phpunit
phpunit: vendor/bin/phpunit
phpunit: export APP_ENV := test
phpunit: ## run phpunit test suite
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps fpm \
	    bin/console cache:warmup
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) fpm \
	    phpdbg -qrr vendor/bin/phpunit --colors=always --stderr --coverage-text --coverage-clover clover.xml

.PHONY: phpstan
phpstan: vendor/bin/phpstan
phpstan: export APP_ENV := dev
phpstan: LEVEL ?= 6
phpstan: ## run phpunit test suite
	docker-compose --project-name $(PROJECT) \
	  run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps fpm \
	    vendor/bin/phpstan --level=$(LEVEL) analyse bin config public src tests


#
# Path targets
#

composer.lock: composer.json
	make update

vendor: composer.lock
	make install

vendor/bin/php-cs-fixer: vendor
vendor/bin/phpstan: vendor
vendor/bin/phpunit: vendor
