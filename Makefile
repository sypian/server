build:
	docker run --rm --volume $(pwd):/app composer install
	docker-compose build

lint:
	docker-compose run php-fpm phpcs

test:
	docker-compose run php-fpm phpunit

