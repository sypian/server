build:
	docker run --rm --user $(shell id -u):$(shell id -g) --volume /etc/passwd:/etc/passwd:ro --volume /etc/group:/etc/group:ro --volume $(shell pwd):/app composer install
	docker-compose build

lint:
	docker-compose run php-fpm phpcs

test:
	docker-compose run php-fpm phpunit --coverage-html coverage --coverage-clover clover.xml --coverage-text

neo4j:
	docker-compose up neo4j
