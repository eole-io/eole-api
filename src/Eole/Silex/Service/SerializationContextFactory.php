<?php

namespace Eole\Silex\Service;

use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;

class SerializationContextFactory implements SerializationContextFactoryInterface
{
    public function createSerializationContext()
    {
        return SerializationContext::create()
            ->setSerializeNull(true)
        ;
    }
}
