<?php

namespace Inanimatt\Silex;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Converts array responses to JsonResponse instances.
 */
class ArrayToJsonListener implements EventSubscriberInterface
{
    /**
     * Handles string responses.
     *
     * @param GetResponseForControllerResultEvent $event The event to handle
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();

        if (is_array($response)) {
            $statusCode = 200;

            if (isset($response['X-Status-Code']) && $response['X-Status-Code']) {
                $statusCode = $response['X-Status-Code'];
                unset($response['X-Status-Code']);
            }

            $event->setResponse(new JsonResponse($response, $statusCode));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array('onKernelView', 0),
        );
    }
}
