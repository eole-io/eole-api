<?php

namespace Eole\WebSocket\Service;

use JMS\Serializer\Serializer;

class Normalizer
{
    /**
     * Waiting for https://github.com/schmittjoh/serializer/issues/537 to use NormalizerInterface
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function normalize($data)
    {
        return $this->serializer->toArray($data);
    }
}
