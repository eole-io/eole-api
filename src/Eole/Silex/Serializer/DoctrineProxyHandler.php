<?php

namespace Eole\Silex\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\XmlSerializationVisitor;

class DoctrineProxyHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];

        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => SerializerProxyType::class,
                'method' => 'serializeTo' . ucfirst($format),
            ];
        }

        return $methods;
    }

    public function serializeToJson(VisitorInterface $visitor, $entity, array $type, Context $context)
    {
        $object = new \stdClass();
        $object->id = $type['params']['id'];

        return $object;
    }

    public function serializeToYml(VisitorInterface $visitor, $entity, array $type, Context $context)
    {
        $object = new \stdClass();
        $object->id = $type['params']['id'];

        return $object;
    }

    public function serializeToXml(XmlSerializationVisitor $visitor, $entity, array $type, Context $context)
    {
        $visitor->getCurrentNode()->appendChild(
            $formNode = $visitor->getDocument()->createElement('id', $type['params']['id'])
        );

        return $formNode;
    }
}
