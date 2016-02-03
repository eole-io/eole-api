# Add websocket topic

A websocket is launched with the Rest API, and is used to send data in real-time
from server to client.

Also, a push server allows Rest API to send event to websocket,
to be forwarded to subscribed clients.

You can easily register a topic to the websocket server, and then allow client
to subscribe to this topic, and then being notified in real-time from API changes.


## Creating your topic

A Topic is a class which implements `Eole\WebSocket\Topic`.


### Handle upcomming events

You can then override 3 methods to handle client notifications:

``` php
namespace Acme\MyGame;

use Ratchet\Wamp\WampConnection;
use Eole\WebSocket\Topic;

class MyTopic extends Topic
{
    /**
     * @param WampConnection $conn
     * @param string $topic
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        // Called when a client subscribe to your topic.
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     * @param string $event
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        // Called when a client publish a message ($event) on your topic.
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);

        // Called when a client leaves your topic.
    }
}
```

By default, the topic automatically stores connected client
(this is why you need to call `parent::onSubscribe($conn, $topic);` and `parent::onUnSubscribe($conn, $topic);`),
and nothing happens when a client try to send a message to your topic.

You can then handle client messages in `onPublish`. If a client send 'hello', `$event` will be equal to 'hello'.


### Notify clients

To notify clients, `Eole\WebSocket\Topic` provides a method, `broadcast($msg)`,
which will send a message to each subscribed clients.


#### Notify after an API call

You will usually want to notify them from an API call,
but the websocket server and Rest server are launched in separates processes.

That's why the websocket server also launch a push server and is listening to it,
and then we can send events from Rest API server to a server socket, and the websocket
will listen the event.

To simplify the process, all the workflow has been abstracted using the Symfony EventDispatcher component.

Just dispatch an event from your API controller, *say* that this event must be forwarded to websocket server,
and listen this same event in your topic.

Example:

- Just dispatch an event from your API controller...

From an example controller:

``` php
// using a simple Symfony event
use Symfony\Component\EventDispatcher\Event;

/* ... */

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/something/{id}', function (Application $app, $id) {

            // Dispatch an event
            $$app['dispatcher']->dispatch('acme.my_game.my_event', new Event());

            return new ApiResponse(array(
                'message' => 'ok',
                'id' => intval($id),
            ));
        });

        return $controllers;
    }
```

- ...*say* that this event must be forwarded to websocket server...

``` php
namespace Acme\MyGame;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Symfony\Component\EventDispatcher\Event;
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
        $app['eole.games.my_game.controller'] = function () {
            return new MyController();
        };

        // This call will listen this event and send a serial to the push server
        $app->forwardEventToPushServer('acme.my_game.my_event');
    }

    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/something/{id}', function (Application $app, $id) {

            $$app['dispatcher']->dispatch('acme.my_game.my_event', new Event());

            return new ApiResponse(array(
                'message' => 'ok',
                'id' => intval($id),
            ));
        });

        return $controllers;
    }
}
```

- ...and listen this same event in your topic.

When the websocket server receives an serialized event from the push server,
it deserialize it and re-dispatch it through the WebsocketApplication, and topics.

You now just have to complete your `MyTopic` class.

> Note:
>
> If your Topic class implements `Symfony\Component\EventDispatcher\EventSubscriberInterface`,
> it will be automatically subscribed to event dispatcher.

So let's implement the interface `Symfony\Component\EventDispatcher\EventSubscriberInterface`:

``` php
namespace Acme\MyGame;

use Ratchet\Wamp\WampConnection;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\WebSocket\Topic;

class MyTopic extends Topic implements EventSubscriberInterface
{
    /* ... */

    /**
     * Subscribe to your event
     */
    public static function getSubscribedEvents()
    {
        return array(
            'acme.my_game.my_event' => array(
                array('onEvent'),
            ),
        );
    }

    /**
     * @param Event $event
     */
    public function onEvent(Event $event)
    {
        // Do something on event, broadcast...
    }
}
```
