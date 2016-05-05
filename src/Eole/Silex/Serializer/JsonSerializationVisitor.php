<?php

namespace Eole\Silex\Serializer;

use JMS\Serializer\JsonSerializationVisitor as BaseJsonSerializationVisitor;

class JsonSerializationVisitor extends BaseJsonSerializationVisitor
{
    public function getResult()
    {
        if ($this->getRoot() instanceof \ArrayObject) {
            $this->setRoot((array) $this->getRoot());
        }

        return parent::getResult();
    }
}
