## Build the docker container, install the dependencies
build:
	docker-compose build
	make vendors-install

## Install the composer dependencies
vendors-install:
	docker-compose run --rm --no-deps php-cli composer install

## Copy dist files to actual path (if not present yet)
copy-dist-configs:
	docker-compose run --rm --no-deps php-cli cp -n phpunit.xml.dist phpunit.xml
	docker-compose run --rm --no-deps php-cli cp -n phpstan.neon.dist phpstan.neon
	docker-compose run --rm --no-deps php-cli cp -n .php-cs-fixer.php.dist .php-cs-fixer.php

## Update composer autoload
dump-autoload:
	docker-compose run --rm --no-deps php-cli composer dump-autoload

## Run console application
run-input-csv:
	docker-compose run --rm --no-deps php-cli php src/index.php input.csv

## Run unit tests
unit-tests:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/phpunit --no-coverage --stop-on-error --stop-on-failure --testsuite Unit

## Run integration tests
integration-tests:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/phpunit --no-coverage --stop-on-error --stop-on-failure --testsuite Integration

## Run integration tests
all-tests:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/phpunit --no-coverage --stop-on-error --stop-on-failure

## Run static analysis
static-analysis:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/phpstan analyze

## Run unit tests
cs-fix:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/php-cs-fixer fix