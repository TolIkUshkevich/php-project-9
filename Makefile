PORT ?= 8000
start:
	export DATABASE_URL=postgresql://anatoliyyushkevich:password@localhost:5432/websites
	PHP_CLI_SERVER_WORKERS=1 php -S 0.0.0.0:$(PORT) -t public

lint:
	vendor/bin/phpcs public
	vendor/bin/phpcs tests
	vendor/bin/phpstan analyse public tests

test:
	vendor/bin/phpunit tests

test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

install:
	composer install