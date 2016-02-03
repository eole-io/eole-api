# Creating a game

Creating a game in the RestAPI means extending it.

It can be easily extended by adding:

- new API routes, or endpoints,
- new websockets topics if needed.

This is a Silex application, so services and controllers will be added with providers.

Once providers are ready to be registered, just add them in configuration file, in `config/environment.yml`.

A game is defined by a name, an identifier you have to choose (lower case and '-'), that will be used in api urls, and in services definition.

- [Add API endpoints](controller-provider.md)
- [Add websocket topic](websocket-provider.md)
- [Register services or listeners](service-provider.md)
