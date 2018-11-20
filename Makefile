#
# For more information on some of the magic targets, variables and flags used, see:
#  - [1] https://www.gnu.org/software/make/manual/html_node/Special-Targets.html
#  - [2] https://www.gnu.org/software/make/manual/html_node/Secondary-Expansion.html
#  - [3] https://www.gnu.org/software/make/manual/html_node/Suffix-Rules.html
#  - [4] https://www.gnu.org/software/make/manual/html_node/Options-Summary.html
#  - [5] https://www.gnu.org/software/make/manual/html_node/Special-Variables.html
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

containers = $(shell find docker/services -name Dockerfile | sed 's/Dockerfile/.build/')
runtime-dependencies = traefik-network vendor/composer/installed.json $(containers)

.PHONY: traefik-network
traefik-network:
	-docker network create traefik_webgateway

.PHONY: fg
fg: $(runtime-dependencies)
fg: ## launch the docker-compose setup (foreground)
	docker-compose up --remove-orphans --abort-on-container-exit

.PHONY: up
up: $(runtime-dependencies)
up: ## launch the docker-compose setup (background)
	docker-compose up --remove-orphans --detach

.PHONY: down
down: ## terminate the docker-compose setup
	-docker-compose down --remove-orphans

.PHONY: test
test: export APP_ENV := test
test: $(runtime-dependencies)
test: ## run phpunit test suite
	docker-compose run --rm -e APP_ENV --user $(shell id -u):$(shell id -g) --name pastebin-testsuite php-fpm \
		bin/console cache:warmup
	docker-compose run --rm -e APP_ENV --user $(shell id -u):$(shell id -g) --name pastebin-testsuite php-fpm \
		phpdbg -qrr vendor/bin/phpunit --colors=always --stderr --coverage-text --coverage-clover clover.xml

.PHONY: logs
logs: $(runtime-dependencies)
logs: ## show logs
	docker-compose logs

.PHONY: tail
tail: $(runtime-dependencies)
tail: ## tail logs
	docker-compose logs -f

shell: export APP_ENV := dev
shell: export COMPOSER_HOME := /tmp
shell: $(runtime-dependencies)
shell: ## spawn a shell inside a php-fpm container
	docker-compose run --rm -e APP_ENV -e COMPOSER_HOME --user $(shell id -u):$(shell id -g) --name pastebin-shell php-fpm \
		sh

deploy: $(runtime-dependencies)
	test -n "$(TRAVIS_COMMIT)" || $(error TRAVIS_COMMIT must be defined)
	test -n "$(DOCKERHUB_PASSWORD)" || $(error DOCKERHUB_PASSWORD must be defined)
	test -n "$(DOCKERHUB_USERNAME)" || $(error DOCKERHUB_USERNAME must be defined)
	docker build --file=docker/services/varnish/Dockerfile --tag=alcohol/pastebin-varnish:latest .
	docker build --file=docker/services/nginx/Dockerfile --tag=alcohol/pastebin-nginx:latest .
	docker build --file=docker/services/php-fpm/Dockerfile.dist --tag=alcohol/pastebin-fpm:latest --build-arg=RELEASE=$(shell git rev-parse --short $(TRAVIS_COMMIT)) .
	echo $(DOCKERHUB_PASSWORD) | docker login --username $(DOCKERHUB_USERNAME) --password-stdin
	docker push alcohol/pastebin-varnish:latest
	docker push alcohol/pastebin-nginx:latest
	docker push alcohol/pastebin-fpm:latest
	docker logout

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
vendor/composer/installed.json: composer.json composer.lock vendor var/cache var/log $(containers)
	docker-compose run --rm --no-deps -e APP_ENV -e COMPOSER_HOME \
		--user $(shell id -u):$(shell id -g) \
		--volume /etc/passwd:/etc/passwd:ro \
		--volume /etc/group:/etc/group:ro \
		--name pastebin-composer \
		php-fpm composer install --no-interaction --no-progress --no-suggest --prefer-dist
	@touch $@
