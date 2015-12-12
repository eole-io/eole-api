<?php

namespace Eole\WebSocket\Routing;

use Symfony\Component\Routing\Route;
use Eole\WebSocket\Topic;

class TopicRoute extends Route
{
    /**
     * @param string $topicPath
     * @param string|Topic $topic topic or topic class name.
     * @param array $defaults
     * @param array $requirements
     */
    public function __construct($topicPath, $topic, array $defaults = array(), array $requirements = array())
    {
        $defaults['_topic'] = $topic;

        parent::__construct($topicPath, $defaults, $requirements);
    }
}
