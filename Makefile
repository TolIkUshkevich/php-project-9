PORT ?= 8000
start:
	export DATABASE_URL=postgresql://anatoliyyushkevich:password@localhost:5432/websites
	PHP_CLI_SERVER_WORKERS=1 php -S 0.0.0.0:$(PORT) -t public

lint:
	vendor/bin/phpstan analyse public

test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

install:
	composer install