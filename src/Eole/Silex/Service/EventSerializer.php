<?php

namespace Eole\Silex\Service;

use Symfony\Component\EventDispatcher\Event;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class EventSerializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $name
     * @param Event $event
     *
     * @return string
     */
    public function serializeEvent($name, Event $event)
    {
        $context = SerializationContext::create()
            ->setSerializeNull(false)
            ->setGroups(array(
                'Default',
                'push_server',
            ))
        ;

        return json_encode(array(
            'name' => $name,
            'class' => get_class($event),
            'event' => $this->serializer->serialize($event, 'json', $context),
        ));
    }

    /**
     * @param string $serial
     *
     * @return array
     */
    public function deserializeEvent($serial)
    {
        $data = json_decode($serial, true);

        return array(
            'name' => $data['name'],
            'class' => $data['class'],
            'event' => $this->serializer->deserialize($data['event'], $data['class'], 'json'),
        );
    }
}
