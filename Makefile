PORT ?= 8000
start:
	export DATABASE_URL=postgresql://anatoliyyushkevich:password@localhost:5432/websites
	PHP_CLI_SERVER_WORKERS=1 php -S 0.0.0.0:$(PORT) -t public