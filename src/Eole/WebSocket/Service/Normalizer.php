<?php

namespace Eole\WebSocket\Service;

use JMS\Serializer\NormalizerInterface;

class Normalizer
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function normalize($data)
    {
        return $this->normalizer->toArray($data);
    }
}
