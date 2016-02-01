<?php

namespace Eole\Core\Service;

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eole\Core\ApiResponse;

class ApiResponseFilter
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
     * @var string
     */
    private $defaultResponseFormat;

    /**
     * @param SerializerInterface $serializer
     * @param callable $contextFactory
     * @param string $defaultResponseFormat
     */
    public function __construct(
        SerializerInterface $serializer,
        callable $contextFactory,
        $defaultResponseFormat = 'json'
    ) {
        $this->serializer = $serializer;
        $this->contextFactory = $contextFactory;
        $this->defaultResponseFormat = $defaultResponseFormat;
    }

    /**
     * @param ApiResponse $apiResponse to convert to a Symfony Response.
     * @param Request $request that needs this Response.
     *
     * @return Response
     */
    public function toSymfonyResponse(ApiResponse $apiResponse, Request $request)
    {
        $format = $request->getRequestFormat($this->defaultResponseFormat);
        $serialized = $this->serializer->serialize($apiResponse->getData(), $format, $this->createContext());
        $response = new Response($serialized, $apiResponse->getStatusCode());

        $response->headers->set('Content-Type', $request->getMimeType($format));

        return $response;
    }

    /**
     * @return SerializationContext
     */
    private function createContext()
    {
        $contextFactory = $this->contextFactory;

        return $contextFactory();
    }
}
