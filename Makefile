all: docker_update

docker_update:
	docker-compose up --no-deps -d php-fpm database

	docker exec -ti eole-php sh -c "composer update"

	# Initialize configuration files if not exits
	docker exec -ti eole-php sh -c "cp -n config/environment.yml.dist config/environment.yml"
	docker exec -ti eole-php sh -c "cp -n docker/php-fpm/config/* config/"

	# Update database
	docker exec -ti eole-database sh -c "mysql -u root -proot -e 'create database if not exists eole;'"
	docker exec -ti eole-php sh -c "bin/console --env=docker orm:schema-tool:update --dump-sql"
	docker exec -ti eole-php sh -c "bin/console --env=docker orm:schema-tool:update --force"

	# Update games
	docker exec -ti eole-php sh -c "bin/console --env=docker eole:games:install"

	docker-compose up -d

bash:
	docker exec -ti eole-php bash

logs:
	docker-compose logs -ft

test:
	docker-compose up --no-deps -d php-fpm database

	# Unit testing
	docker exec -ti eole-php sh -c "cp -n config/environment_test.yml.dist config/environment_test.yml"
	docker exec -ti eole-php sh -c "bin/console --env=test orm:schema-tool:update --force"
	docker exec -ti eole-php sh -c "vendor/bin/phpunit -c ."

	# Codestyle checking
	docker exec -ti eole-php sh -c "vendor/bin/phpcs --standard=phpcs.xml src/"

restart_websocket_server:
	docker restart eole-ws
