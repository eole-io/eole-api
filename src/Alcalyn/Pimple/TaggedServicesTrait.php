<?php

namespace Alcalyn\Pimple;

trait TaggedServicesTrait
{
    /**
     * @var string[][]
     */
    private $tags = array();

    /**
     * @param string $tagName
     * @param string $serviceId
     */
    public function tagService($tagName, $serviceId)
    {
        if (!isset($this->tags[$tagName])) {
            $this->tags[$tagName] = array();
        }

        $this->tags[$tagName] []= $serviceId;
    }

    /**
     * @param string $tagName
     *
     * @return string[]
     */
    public function findTaggedServiceIds($tagName)
    {
        if (!isset($this->tags[$tagName])) {
            return array();
        }

        return $this->tags[$tagName];
    }
}
