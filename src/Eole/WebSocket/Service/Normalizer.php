<?php

namespace Eole\WebSocket\Service;

use JMS\Serializer\SerializerInterface;

class Normalizer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \Closure
     */
    private $contextFactory;

    /**
     * @param SerializerInterface $serializer
     * @param \Closure $contextFactory
     */
    public function __construct(SerializerInterface $serializer, \Closure $contextFactory)
    {
        $this->serializer = $serializer;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function normalize($data)
    {
        $contextFactory = $this->contextFactory;

        return json_decode($this->serializer->serialize($data, 'json', $contextFactory()));
    }
}
