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

up:
	docker compose up -d db slim

down:
	docker compose down
