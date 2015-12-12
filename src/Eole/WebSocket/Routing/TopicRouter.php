<?php

namespace Eole\WebSocket\Routing;

use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Eole\WebSocket\Topic;

class TopicRouter
{
    /**
     * @var UrlMatcherInterface
     */
    private $urlMatcher;

    /**
     * @param UrlMatcherInterface $urlMatcher
     */
    public function __construct(UrlMatcherInterface $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
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
        $match = $this->urlMatcher->match('/'.$topicPath);
        $topic = $match['_topic'];
        $topicInstance = null;

        if ($topic instanceof Topic) {
            $topicInstance = $topic;
        } elseif (is_string($topic)) {
            $topicInstance = new $topic($topicPath, $match);
        }

        if (null === $topicInstance) {
            throw new \LogicException('Expected Topic or topic class name in _topic.');
        }

        return $topicInstance;
    }
}
