DOCKER_COMPOSE ?= docker compose

help: ## Show this help message.
	@echo 'usage: make [target] ...'
	@echo
	@echo 'targets:'
	@egrep '^(.+)\:(.+)?\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'

install: ## Install dependencies
	$(DOCKER_COMPOSE) run --rm php composer install

test: ## Runs PHPUnit tests
	$(DOCKER_COMPOSE) up -d test-webserver
	$(DOCKER_COMPOSE) run --rm ready -wait tcp://test-webserver:9080 -timeout 5s
	$(DOCKER_COMPOSE) run --rm php composer run phpunit
	$(DOCKER_COMPOSE) run --rm php composer run behat
	$(DOCKER_COMPOSE) stop test-webserver

cs-fix: ## Fixes code standards
	$(DOCKER_COMPOSE) run --rm php composer run cs-fix

cs-check: ## Checks code standards
	$(DOCKER_COMPOSE) run --rm php composer run cs-check

phpstan: ## Checks phpstan
	$(DOCKER_COMPOSE) run --rm php composer run phpstan
