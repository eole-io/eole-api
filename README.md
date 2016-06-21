# Eole API

[![Build Status](https://travis-ci.org/eole-io/eole-api.svg?branch=dev)](https://travis-ci.org/eole-io/eole-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eole-io/eole-api/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/eole-io/eole-api/?branch=dev)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8d79c694-4535-4302-a83b-11f55cedde04/mini.png)](https://insight.sensiolabs.com/projects/8d79c694-4535-4302-a83b-11f55cedde04)


Provides Eole domain, a RestAPI, and a websocket server.

Allows to plug new API endpoints and websocket topics easily for games.


## Installation

### Requirements

This application requires PHP 5.5+, ZMQ and php-zmq extension.


### Steps

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
# This command is blocking
```


## Documentation

- [Testing and PSR2 check](doc/testing.md)
- [API documentation](doc/api-documentation.md)
- [Creating a game](doc/create-game/index.md)
    - [Initializing a game installation](doc/create-game/init-game.md)
    - [Add API endpoints](doc/create-game/controller-provider.md)
    - [Add websocket topic](doc/create-game/websocket-provider.md)
    - [Register services or listeners](doc/create-game/service-provider.md)


## License

This project is under [GPL-v3 License](LICENSE).
