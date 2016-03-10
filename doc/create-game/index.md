# Creating a game

Creating a game in the RestAPI means extending it.

It can be easily extended by adding:

- new API routes, or endpoints,
- new websockets topics if needed.

Eole is a Silex application, so services and controllers from your game
will be added to the application container through providers.

A game is defined by a name (an identifier you have to choose, lower case and '-'),
and a `GameInterface`, and is registered in `config/environment.yml` like that:

``` yaml
games:
    mygame:
        interface: Acme\MyGame\MyGame
```

And call `php bin/console eole:games:install` to install your game instance in database.

A `GameInterface` provides a game instance which represents a game in database,
and 3 providers:

 - A **controller provider**, which declare RestApi routes and controllers,
 - A **websocket provider**, which declare websocket topic used by your game,
 - A **service provider**, which extends Eole container by services used both in RestApi and Websocket topics.

See documentation about creating these providers:

 - [Add API endpoints](controller-provider.md)
 - [Add websocket topic](websocket-provider.md)
 - [Register services or listeners](service-provider.md)

But first, you may be interested by:

 - [Initializing a game installation](init-game.md)
