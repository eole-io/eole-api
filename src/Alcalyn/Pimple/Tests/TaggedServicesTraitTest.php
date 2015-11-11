<?php

namespace Alcalyn\Pimple\Tests;

use Alcalyn\Pimple\Tests\stub\PimpleContainerUsingTaggedServices;

class TaggedServicesTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testTagService()
    {
        $container = new PimpleContainerUsingTaggedServices();

        $container->tagService('tag.1', 'service.a');
        $container->tagService('tag.1', 'service.b');
        $container->tagService('tag.2', 'service.c');
        $container->tagService('tag.1', 'service.d');
        $container->tagService('tag.2', 'service.e');

        $servicesTaggedOne = $container->findTaggedServiceIds('tag.1');
        $servicesTaggedTwo = $container->findTaggedServiceIds('tag.2');

        $this->assertSame(['service.a', 'service.b', 'service.d'], $servicesTaggedOne);
        $this->assertSame(['service.c', 'service.e'], $servicesTaggedTwo);
    }
}
