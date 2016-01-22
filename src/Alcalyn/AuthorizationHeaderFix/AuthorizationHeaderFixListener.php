<?php

namespace Alcalyn\AuthorizationHeaderFix;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class AuthorizationHeaderFixListener
{
    /**
     * @param GetResponseEvent $event the event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->fixAuthHeader($event->getRequest()->headers);
    }

    /**
     * @param HeaderBag $headers
     */
    private function fixAuthHeader(HeaderBag $headers)
    {
        if (!$headers->has('Authorization') && function_exists('apache_request_headers')) {
            $all = apache_request_headers();
            if (isset($all['Authorization'])) {
                $headers->set('Authorization', $all['Authorization']);
            }
        }
    }
}
