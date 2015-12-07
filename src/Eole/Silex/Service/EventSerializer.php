<?php

namespace Eole\Silex\Service;

use JMS\Serializer\SerializerInterface;
use Eole\Core\Event\Event;

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
        return json_encode(array(
            'name' => $name,
            'class' => get_class($event),
            'event' => $this->serializer->serialize($event, 'json'),
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
