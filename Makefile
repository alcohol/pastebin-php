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
# Traefik
#

.PHONY: traefik-network
traefik-network:
	@docker network ls | grep traefik &>/dev/null || docker network create traefik &>/dev/null

.PHONY: traefik
traefik: traefik-network
	@docker inspect -f {{.State.Running}} traefik &>/dev/null || docker run \
		--restart unless-stopped \
		--name traefik \
		--network traefik \
		--volume /var/run/docker.sock:/var/run/docker.sock \
		--publish 80:80 \
		--expose 80 \
		--expose 8080 \
		--health-cmd 'nc -z localhost 80' \
		--health-interval 5s \
		--label traefik.enable=true \
		--label 'traefik.http.routers.api.rule=Host(`traefik.localhost`)' \
		--label traefik.http.routers.api.service=api@internal \
		--detach \
		traefik:2.1 \
			--entrypoints.web.address=:80 \
			--api \
			--accesslog \
			--providers.docker=true \
			--providers.docker.network=traefik \
			--providers.docker.exposedbydefault=false

.PHONY: traefik-cleanup
traefik-cleanup:
	@docker stop traefik &>/dev/null
	@docker rm traefik &>/dev/null
	@-docker network rm traefik &>/dev/null

.PHONY: traefik-restart
traefik-restart: traefik-cleanup traefik
traefik-restart: ## restart traefik

#
# Docker-Compose Services & Containers
#

.PHONY: build
build: ## build containers
	docker-compose --project-name $(PROJECT) build --parallel --pull

.PHONY: fg
fg: traefik
fg: ## launch the docker-compose setup (foreground)
	docker-compose --project-name $(PROJECT) up --remove-orphans

.PHONY: up
up: traefik
up: ## launch the docker-compose setup (background)
	docker-compose --project-name $(PROJECT) up --remove-orphans --detach

.PHONY: down
down: ## terminate the docker-compose setup
	-docker-compose --project-name $(PROJECT) down --remove-orphans

.PHONY: logs
logs: ## show logs
	docker-compose --project-name $(PROJECT) logs

.PHONY: tail
tail: ## tail logs
	docker-compose --project-name $(PROJECT) logs --follow

.PHONY: shell
shell: ## spawn a shell inside a php-fpm container
	docker-compose --project-name $(PROJECT) run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps composer sh

#
# Application related targets
#

.PHONY: install
install: ## install dependencies (composer)
	docker-compose --project-name $(PROJECT) run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps composer \
		composer install --no-interaction --no-progress --no-suggest --prefer-dist

.PHONY: update
update: ## update dependencies (composer)
	docker-compose --project-name $(PROJECT) run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps composer \
		composer update --no-interaction --no-progress --no-suggest --prefer-dist

.PHONY: phpunit
phpunit: export APP_ENV := test
phpunit: ## run phpunit test suite
	docker-compose --project-name $(PROJECT) run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps fpm \
		bin/console cache:warmup
	docker-compose --project-name $(PROJECT) run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps fpm \
		phpdbg -qrr vendor/bin/phpunit --colors=always --stderr --coverage-text --coverage-clover clover.xml

.PHONY: phpstan
phpstan: export APP_ENV := dev
phpstan: ## run phpunit test suite
	docker-compose --project-name $(PROJECT) run --rm -e APP_ENV --user $(DOCKER_USER) --no-deps fpm \
		php vendor/bin/phpstan --level=4 analyse bin config public src tests
