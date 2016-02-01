<?php

namespace Eole\Core\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Eole\Core\ApiResponse;
use Eole\Core\Service\ApiResponseFilter;

class ApiResponseFilterListener
{
    /**
     * @var ApiResponseFilter
     */
    private $apiResponseFilter;

    /**
     * @param ApiResponseFilter $apiResponseFilter
     */
    public function __construct(ApiResponseFilter $apiResponseFilter)
    {
        $this->apiResponseFilter = $apiResponseFilter;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $apiResponse = $event->getControllerResult();

        if (!($apiResponse instanceof ApiResponse)) {
            return;
        }

        $response = $this->apiResponseFilter->toSymfonyResponse($apiResponse, $event->getRequest());

        $event->setResponse($response);
    }
}
