start:
	PHP_CLI_SERVER_WORKERS=1 php -S 0.0.0.0:8000 -t public

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public

install:
	composer install