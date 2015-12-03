<?php

namespace Eole\RestApi\Service;

use Eole\Core\Event\Event;

class EventSerializer
{
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
            'event' => serialize($event),
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
            'event' => unserialize($data['event']),
        );
    }
}
