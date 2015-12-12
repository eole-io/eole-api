<?php

namespace Eole\WebSocket;

use JMS\Serializer\SerializerInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\Topic as BaseTopic;

class Topic extends BaseTopic
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var callable
     */
    private $contextFactory;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param string $topicPath
     * @param array $arguments
     */
    public function __construct($topicPath, array $arguments = array())
    {
        parent::__construct($topicPath);

        $this->arguments = $arguments;
    }

    public function onSubscribe(WampConnection $conn, $topic)
    {
        $this->add($conn);
    }

    public function onPublish(WampConnection $conn, $topic, $event)
    {
    }

    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->remove($conn);
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @return self
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param callable $contextFactory
     *
     * @return self
     */
    public function setContextFactory(callable $contextFactory)
    {
        $this->contextFactory = $contextFactory;

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function normalize($data)
    {
        $contextFactory = $this->contextFactory;

        return json_decode($this->serializer->serialize($data, 'json', $contextFactory()));
    }
}
