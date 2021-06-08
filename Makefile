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
	docker-compose run --rm --no-deps php-cli cp -n src/Config/parameters.yaml.dist src/Config/parameters.yaml
	docker-compose run --rm --no-deps php-cli cp -n src/Config/services.yaml.dist src/Config/services.yaml
	docker-compose run --rm --no-deps php-cli cp -n src/Config/services_test.yaml.dist src/Config/services_test.yaml

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
all-phpunit-tests:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/phpunit --no-coverage --stop-on-error --stop-on-failure

# Run behavior tests via behat
behavior-tests:
	docker-compose run --rm --no-deps php-cli vendor/bin/behat

# Run all tests
all-tests:
	make all-phpunit-tests
	make behavior-tests

# Run all tests
all-tests-and-checks:
	make cs-check
	make all-phpunit-tests
	make all-phpunit-tests
	make behavior-tests

## Run static analysis
static-analysis:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/phpstan analyze

## Run unit tests
cs-fix:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/php-cs-fixer fix

cs-check:
	docker-compose run --rm --no-deps php-cli ./vendor/bin/php-cs-fixer fix --dry-run -v
