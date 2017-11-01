lint:
	docker-compose run php-fpm phpcs

test:
	docker-compose run php-fpm phpunit

