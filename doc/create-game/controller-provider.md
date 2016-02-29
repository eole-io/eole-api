# Add API endpoints

The API can be extended with new API endpoints.

This can be done with a simple Silex ControllerProvider.


## Creating a ControllerProvider

``` php
namespace Acme\MyGame;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Eole\Core\ApiResponse;

class MyControllerProvider implements ControllerProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/something/{id}', function (Application $app, $id) {
            return new ApiResponse(array(
                'message' => 'ok',
                'id' => intval($id),
            ));
        });

        return $controllers;
    }
}
```

**Note**: By adding the route `/something/{id}`,
it will be accessible in Eole with `/api/games/my-game/something/42`
(underscores are replaces with `-`).
The prefix `/api/games/my-game` is added to avoid url collisions.

**Note**: The [ApiResponse](../../src/Eole/Core/ApiResponse.php)
is used to return a Symfony agnostic response object
that could be used elsewhere than from the Symfony HTTP loop (i.e in commands).


## Declare services in your controller provider

If you want to register services only for the RestApi stack,
note that if your ControllerProvider also implements `Pimple\ServiceProviderInterface`,
it will be registered before mounted.

It is usefull when you want to put your controller logic in another class.

Example:

``` php
namespace Acme\MyGame;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Eole\Core\ApiResponse;
use Acme\MyGame\MyController;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        // Register controller as a service
        $app['eole.games.my_game.controller'] = function () {
            return new MyController();
        };
    }

    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/something/{id}', 'eole.games.my_game.controller:someAction');

        return $controllers;
    }
}
```
