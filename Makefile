PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=1 php -S 0.0.0.0:$(PORT) -t public

lint:
	./vendor/bin/phpstan analyse public  --memory-limit 4048M

test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover -l8 build/logs/clover.xml

install:
	composer install