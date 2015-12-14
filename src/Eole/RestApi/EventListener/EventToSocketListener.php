<?php

namespace Eole\RestApi\EventListener;

use ZMQSocket;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\Event;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Event\SlotEvent;
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
        $eventsToSerialize = array(
            PartyEvent::CREATE_AFTER,
            SlotEvent::JOIN_AFTER,
        );

        $subscribedEvents = array();

        foreach ($eventsToSerialize as $eventName) {
            $subscribedEvents[$eventName] = array(
                array('sendEventToSocket'),
            );
        }

        return $subscribedEvents;
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
