PHP ?= php
COMPOSER ?= composer
WORKFLOW = .github/workflows/ci.yml
ACT_IMAGE = ubuntu-latest=shivammathur/node:latest

.DEFAULT_GOAL := help
.PHONY: help install phpcs phpstan test ci demo act

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-10s %s\n", $$1, $$2}'

install: ## composer install
	$(COMPOSER) install --no-progress --no-ansi --prefer-dist

phpcs: ## PHP CodeSniffer
	$(PHP) vendor/bin/phpcs -s

phpstan: ## PHPStan
	$(PHP) vendor/bin/phpstan analyse

test: ## PHPUnit
	$(PHP) vendor/bin/phpunit

ci: phpcs phpstan test ## phpcs, phpstan, test in order

demo: ## Generate demo/output.pdf
	$(PHP) demo/run.php

act: ## Run the CI matrix in Docker (PHP_VERSION=8.4 for one cell)
	act workflow_dispatch -W $(WORKFLOW) -P $(ACT_IMAGE) $(if $(PHP_VERSION),--matrix php:$(PHP_VERSION))
