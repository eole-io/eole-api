<?php

namespace Alcalyn\Pimple\Tests\stub;

use Alcalyn\Pimple\TaggedServicesTrait;

class PimpleContainerUsingTaggedServices extends \Pimple\Container
{
    use TaggedServicesTrait;
}
