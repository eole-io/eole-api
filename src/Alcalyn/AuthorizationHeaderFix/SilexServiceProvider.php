<?php

namespace Alcalyn\AuthorizationHeaderFix;

use Symfony\Component\HttpKernel\KernelEvents;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class SilexServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app->on(KernelEvents::REQUEST, array(new AuthorizationHeaderFixListener(), 'onKernelRequest'), 10);
    }
}
