PORT ?= 8000
start:
	php -S 0.0.0.0:$(PORT) 	-t public

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public src
	composer exec --verbose phpstan -- --level=8 --memory-limit=-1 --xdebug analyse ./phpstan.neon public src

install:
	composer install