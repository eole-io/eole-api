<?php

namespace Alcalyn\AuthorizationHeaderFix;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class AuthorizationHeaderFixListener
{
    /**
     * @param GetResponseEvent $event the event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->fixRequest($event->getRequest());
    }

    /**
     * @param Request $request
     */
    public function fixRequest(Request $request)
    {
        $this->fixHeaderBag($request->headers);
    }

    /**
     * @param HeaderBag $headers
     */
    public function fixHeaderBag(HeaderBag $headers)
    {
        if (!$headers->has('Authorization')) {
            $headers->set('Authorization', $this->getAuthorizationHeader());
        }
    }

    /**
     * Returns Authorization header from apache request.
     *
     * @return string|null
     */
    public function getAuthorizationHeader()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }

        return null;
    }
}
