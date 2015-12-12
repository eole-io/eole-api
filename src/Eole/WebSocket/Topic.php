<?php

namespace Eole\WebSocket;

use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\Topic as BaseTopic;
use Eole\WebSocket\Service\Normalizer;

class Topic extends BaseTopic
{
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var Normalizer
     */
    protected $normalizer;

    /**
     * @param string $topicPath
     * @param array $arguments
     */
    public function __construct($topicPath, array $arguments = array())
    {
        parent::__construct($topicPath);

        $this->arguments = $arguments;
    }

    /**
     * @param Normalizer $normalizer
     *
     * @return self
     */
    public function setNormalizer(Normalizer $normalizer)
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        $this->add($conn);
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     * @param string $event
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->remove($conn);
    }
}
