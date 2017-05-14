# Eole API

[![Build Status](https://travis-ci.org/eole-io/eole-api.svg?branch=dev)](https://travis-ci.org/eole-io/eole-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eole-io/eole-api/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/eole-io/eole-api/?branch=dev)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8d79c694-4535-4302-a83b-11f55cedde04/mini.png)](https://insight.sensiolabs.com/projects/8d79c694-4535-4302-a83b-11f55cedde04)


Provides Eole domain, a RestAPI, and a websocket server.

Allows to plug new API endpoints and websocket topics easily for games.


## Installation

### Requirements

This application requires Docker and docker-compose.

Or, without docker, it requires PHP 5.5+, ZMQ and php-zmq extension.

[Install ZMQ and php-zmq on Linux](https://eole-io.github.io/sandstone/install-zmq-php-linux.html)


### Docker installation

``` bash
# Clone project
git clone git@github.com:eole-io/eole-api.git --branch=dev
cd eole-api

# Install Eole
make

# Sometimes needed
chmod -R 777 var/cache var/logs
chown -R GROUP:USER .
```

Youn should now access to:

 - http://0.0.0.0:8480/api-docker.php/api/games Eole Api
 - http://0.0.0.0:8480/api-docker.php/_profiler/ Symfony profiler
 - http://0.0.0.0:8481/ PHPMyAdmin (`root` / `root`)
 - http://0.0.0.0:8482/ Websocket server

Access to the Symfony console:

``` bash
make bash
bin/console --env=docker
```

:heavy_check_mark: The installation is done.


### Raw installation

``` bash
# Clone project
git clone git@github.com:eole-io/eole-api.git --branch=dev
cd eole-api

# Install dependencies
composer update

# Configuration files
cp config/environment.yml.dist config/environment.yml
cp config/environment_prod.yml.dist config/environment_prod.yml
```

 - Edit configuration files,
 - create a database which matches config.

``` bash
# Create database schema
php bin/console orm:schema-tool:create

# And finally, install natives games:
php bin/console eole:games:install
```

Run React server (push server and websocket server)

``` bash
php bin/react-server
```

:heavy_check_mark: The installation is done.


> **Note**: Redirect react server logs.
>
> The command `bin/react-server` is blocking.
> You may want to run:
>
> `php bin/react-server > var/logs/react-server.log &`


> **Another note**: Prod environment.
>
> All bin commands have a `prod` mode,
> you can do instead in a prod environment:
>
> `composer update --no-dev`
>
> `php bin/console --env=prod`
>
> `php bin/react-server --env=prod`


## Documentation

- [Testing and PSR2 check](doc/testing.md)
- [API documentation](doc/api-documentation.md)
- [Creating a game](doc/create-game/index.md)
    - [Initializing a game installation](doc/create-game/init-game.md)
    - [Add API endpoints](doc/create-game/controller-provider.md)
    - [Add websocket topic](doc/create-game/websocket-provider.md)
    - [Register services or listeners](doc/create-game/service-provider.md)


## License

This project is under [AGPL-v3 License](LICENSE).
