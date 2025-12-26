.PHONY: format phpcs phpunit test phpstan install up down

format:
	docker compose run --rm composer composer format

phpcs:
	docker compose run --rm composer vendor/bin/phpcs

phpunit test:
	docker compose run --rm composer vendor/bin/phpunit --testsuite 'Test Suite'

phpstan:
	docker compose run --rm composer vendor/bin/phpstan analyse

install:
	docker compose up composer
	cd node/functions && npm install

PROJECT_ROOT := $(CURDIR)
SERVE_LOG := $(PROJECT_ROOT)/logs/functions-serve.log
SERVE_PID := $(PROJECT_ROOT)/logs/functions-serve.pid

up:
	docker compose up -d db slim
	cd node/functions && nohup npm run serve -- --project apptan > $(SERVE_LOG) 2>&1 & echo $$! > $(SERVE_PID)

down:
	if [ -f $(SERVE_PID) ]; then kill "$$(cat $(SERVE_PID))"; rm -f $(SERVE_PID); fi
	docker compose down
