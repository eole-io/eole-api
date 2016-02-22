# Add websocket topic

A **websocket server** is launched in parallel with the **Rest API server**, and is used to send data in real-time
from server to client.

Also, an internal **push server** allows **Rest API** to send **events** to **websocket server**,
and then forward these **events** to **subscribing clients**.

You can easily **register a topic** to the **websocket server**, and then allow clients
to subscribe to this topic, and notify them in real-time that API state changed.


## Creating your topic

A Topic is a class which implements `Eole\WebSocket\Topic`.

You can then override 3 methods to handle client upcoming notifications:

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

Example from `Eole\WebSocket\Topic\ChatTopic`:

``` php
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        // Notifying all subscribing clients that a new player is subscribing to the chat.
        $this->broadcast([
            'type' => 'join',
            'player' => $conn->player,
        ]);
    }
```


#### Notify after an API call

You will usually want to notify them after an API call which has changed the state of the API.

But the **websocket server** and **Rest server** are launched in separates processes.

That's why a **push server** allows them to communicate through a socket.

To simplify the process, all the workflow has been abstracted using the [Symfony EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher/introduction.html) component.

Just dispatch an event from your API controller,
*declare* that this event must be forwarded to websocket server through socket,
and listen this same event in your topic.

Example:

- Just dispatch an event from your API controller...

In your controller, add `$app['dispatcher']->dispatch('acme.my_game.my_event', new Event());`:

``` php
// using a simple Symfony event
use Symfony\Component\EventDispatcher\Event;

/* ... */

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/something/{id}', function (Application $app, $id) {

            // Dispatch an event
            $app['dispatcher']->dispatch('acme.my_game.my_event', new Event());

            return new ApiResponse(array(
                'message' => 'ok',
                'id' => intval($id),
            ));
        });

        return $controllers;
    }
```

- ...*declare* that this event must be forwarded to websocket server...

Use `$app->forwardEventToPushServer('acme.my_game.my_event');` to achieve it:

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

When the **websocket server** receives an serialized event from the **push server**,
it deserializes it and re-dispatch it through the WebsocketApplication, and topics.

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
        $this->broadcast([
            'type' => 'my-event',
            'data' => $event->data,
        ]);
    }
}
```
