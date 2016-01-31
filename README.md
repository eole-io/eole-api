# Eole API

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alcalyn/eole-api/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/alcalyn/eole-api/?branch=dev)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8c0ab48f-4dbb-4b89-ab5d-b7acbc926e6d/mini.png)](https://insight.sensiolabs.com/projects/8c0ab48f-4dbb-4b89-ab5d-b7acbc926e6d)


Provides Eole domain, a RestAPI, and a websocket server.


## Installation

### Requirements

This application requires PHP 5.5+, ZMQ and php-zmq extension.


### Steps

``` bash
# Clone project
git clone git@github.com:alcalyn/eole-api.git --branch=dev
cd eole-api

# Install dependencies
composer update

# Configuration files
cp config/environment.yml.dist config/environment.yml
cp config/environment_test.yml.dist config/environment_test.yml
```

 - Edit configuration files,
 - create a database which matches config.

``` bash
# Create database schema
php bin/console-test orm:schema-tool:update --force
php bin/console orm:schema-tool:update --force
```

Run React server (push server and websocket server)

``` bash
php bin/react-server
# This command is blocking
```


## Testing

By default, tests are run in an SQLite database, located in `var/eole-test.sqlite`.

Run tests

``` bash
php vendor/bin/phpunit -c .
```

Check PSR2

``` bash
php vendor/bin/phpcs --standard=phpcs.xml src/
```


## License

This project is under [GPL-v3 License](LICENSE).
