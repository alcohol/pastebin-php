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

fg: vendor/composer/installed.json
fg: ## Launch the docker-compose setup (foreground)
	docker-compose up --remove-orphans --abort-on-container-exit

up: vendor/composer/installed.json
up: ## Launch the docker-compose setup (background)
	docker-compose up --remove-orphans --detach

down: ## Terminate the docker-compose setup
	docker-compose down --remove-orphans

test: APP_ENV ?= test
test: vendor/composer/installed.json
test: ## Run phpunit test suite
	docker-compose run -u $(shell id -u):$(shell id -g) -e APP_ENV --rm --no-deps --name pastebin-testsuite php \
		phpdbg -qrr vendor/bin/phpunit --colors=always --stderr --coverage-text --coverage-clover coverage.xml

#
# PATH BASED TARGETS
#

vendor:
	mkdir vendor

vendor/composer/installed.json: composer.json composer.lock vendor
	docker run --rm -u $(shell id -u):$(shell id -g) \
		--volume /etc/passwd:/etc/passwd:ro \
		--volume /etc/group:/etc/group:ro \
		--volume "$(shell pwd)":/workdir \
		--workdir /workdir \
		composer install
