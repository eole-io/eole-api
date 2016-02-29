# Initializing a game installation

This requires to have an instance of Eole running locally.
Check [Installation instructions in Readme](../../README.md).

Then, you can clone a game skeleton in antoher directory
to have a base rest api controller and websocket topic:

```
git clone git@github.com:alcalyn/game-api-skeleton.git my-game
```

And replace all `my_game` occurences to your game name,
`MyGame` to your game name in studly cases,
and namespace `Acme\MyGame` occurences to your base namespace.
Also rename file `MyGame.php`.

A solution to make your game visible in your Eole installation is
to autoload it with composer, in Eole's `composer.json`:

``` json
    "autoload": {
        "psr-4": {
            "Eole\\": "src/Eole",
            "Acme\\MyGame\\": "../my-game",
        }
    },
```

With `"Acme\\MyGame\\"` depending on your base namespace,
and `"../my-game"` depending on your Eole installation path.

Now composer needs to update autoloader with:

``` bash
composer dump-autoload
```

Then, if you have added your game in `config/environment.yml`:

``` yaml
games:
    mygame:
        interface: Acme\MyGame\MyGame
```

and called:

``` bash
php bin/console eole:games:install`
```

to install your game instance in database,
you could access to the Rest Api test route: `/api/games/my-game/test/hello`
(declared in skeleton project).

Your Eole installation and game developpement environment are now ready.
