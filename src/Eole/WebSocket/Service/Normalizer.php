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
     * @var \Closure
     */
    private $contextFactory;

    /**
     * @param Serializer $serializer
     * @param \Closure $contextFactory
     */
    public function __construct(Serializer $serializer, \Closure $contextFactory)
    {
        $this->serializer = $serializer;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function normalize($data)
    {
        $contextFactory = $this->contextFactory;

        return $this->serializer->toArray($data, $contextFactory());
    }
}
