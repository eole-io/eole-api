<?php

namespace Eole\WebSocket\ServiceProvider;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Eole\WebSocket\Routing\TopicRouter;

class TopicRoutingProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['eole.websocket.routes'] = function () {
            return new RouteCollection();
        };

        $app['eole.websocket.url_matcher'] = function () use ($app) {
            return new UrlMatcher(
                $app['eole.websocket.routes'],
                new RequestContext()
            );
        };

        $app['eole.websocket.router'] = function () use ($app) {
            return new TopicRouter($app['eole.websocket.url_matcher']);
        };
    }
}
