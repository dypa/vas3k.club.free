<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();

        $response->headers->set('Access-Control-Allow-Origin', '*');
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
