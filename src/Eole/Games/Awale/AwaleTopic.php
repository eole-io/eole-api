<?php

namespace Eole\Games\Awale;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\WebSocket\Topic as BaseTopic;
use Eole\Games\Awale\Event\AwaleEvent;

class AwaleTopic extends BaseTopic implements EventSubscriberInterface
{
    /**
     * @param string $topicPath
     */
    public function __construct($topicPath)
    {
        parent::__construct($topicPath);
    }

    /**
     * @param AwaleEvent $event
     */
    public function onPlay(AwaleEvent $event)
    {
        $this->broadcast(array(
            'type' => 'played',
            'move' => $event->getAwaleParty()->getLastMove(),
            'current_player' => $event->getAwaleParty()->getCurrentPlayer(),
        ));
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AwaleEvent::PLAY => array(
                array('onPlay'),
            ),
        );
    }
}
