<?php

namespace Eole\RestApi\EventListener;

use ZMQSocket;
use Eole\Core\Event\Event;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Event\SlotEvent;
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
     * @param ZMQSocket $pushServer
     * @param EventSerializer $eventSerializer
     */
    public function __construct(ZMQSocket $pushServer, EventSerializer $eventSerializer)
    {
        $this->pushServer = $pushServer;
        $this->eventSerializer = $eventSerializer;
    }

    /**
     * @param Event $event
     * @param string $name
     */
    public function sendEventToSocket(Event $event, $name)
    {
        $this->pushServer->send($this->eventSerializer->serializeEvent($name, $event));
    }
}
