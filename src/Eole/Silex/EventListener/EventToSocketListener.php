<?php

namespace Eole\Silex\EventListener;

use ZMQSocket;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\Event;
use Eole\Core\Event\PartyEvent;
use Eole\Silex\Service\EventSerializer;

class EventToSocketListener implements EventSubscriberInterface
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
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PartyEvent::CREATE => array(
                array('sendEventToSocket'),
            ),
        );
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
