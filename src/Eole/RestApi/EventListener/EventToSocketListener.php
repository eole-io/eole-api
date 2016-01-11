<?php

namespace Eole\RestApi\EventListener;

use ZMQSocket;
use Eole\Core\Event\Event;
use Eole\Silex\Service\EventSerializer;

class EventToSocketListener
{
    /**
     * @var ZMQSocket
     */
    private $pushServer;

    /**
     * @var EventSerializer
     */
    private $eventSerializer;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param ZMQSocket $pushServer
     * @param EventSerializer $eventSerializer
     * @param bool $enabled
     */
    public function __construct(ZMQSocket $pushServer, EventSerializer $eventSerializer, $enabled = true)
    {
        $this->pushServer = $pushServer;
        $this->eventSerializer = $eventSerializer;
        $this->enabled = $enabled;
    }

    /**
     * @param Event $event
     * @param string $name
     */
    public function sendEventToSocket(Event $event, $name)
    {
        if (!$this->enabled) {
            return;
        }

        $this->pushServer->send($this->eventSerializer->serializeEvent($name, $event));
    }
}
