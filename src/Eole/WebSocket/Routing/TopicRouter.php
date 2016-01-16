<?php

namespace Eole\WebSocket\Routing;

use Eole\WebSocket\Topic;
use Eole\Silex\Application;

class TopicRouter
{
    /**
     * @var Application
     */
    private $silexApp;

    /**
     * @param Application $silexApp
     */
    public function __construct(Application $silexApp)
    {
        $this->silexApp = $silexApp;
    }

    /**
     * @param string $topicPath
     *
     * @return Topic
     *
     * @throws \LogicException when cannot load topic.
     */
    public function loadTopic($topicPath)
    {
        $urlMatcher = $this->silexApp['eole.websocket.url_matcher'];
        $arguments = $urlMatcher->match('/'.$topicPath);
        $topic = $arguments['_topic'];
        $topicInstance = null;

        if ($topic instanceof Topic) {
            $topicInstance = $topic;
        } elseif (is_string($topic)) {
            $topicFactory = $this->silexApp[$topic];

            if (!is_callable($topicFactory)) {
                throw new \LogicException(sprintf('Service "%s" is not a factory callback.', $topic));
            }

            $topicInstance = $topicFactory($topicPath, $arguments);
        }

        if (null === $topicInstance) {
            throw new \LogicException('Expected Topic or topic factory in _topic.');
        }

        return $topicInstance;
    }
}
