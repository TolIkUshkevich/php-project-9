PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=1 php -S 0.0.0.0:$(PORT) -t public

lint:
	composer exec --verbose --memory-limit 256M phpstan analyse public

install:
	composer install