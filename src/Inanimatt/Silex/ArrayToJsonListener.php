<?php

namespace Inanimatt\Silex;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Inanimatt\Silex\JsonResponse;

/**
 * Converts array responses to JsonResponse instances.
 */
class ArrayToJsonListener implements EventSubscriberInterface
{
    protected $api_version;

    public function __construct($api_version)
    {
        $this->api_version = $api_version;
    }

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

            $r = new JsonResponse(null, $statusCode);

            $r->headers->set('X-API-Version', $this->api_version);

            if (isset($response['X-Location']) && $response['X-Location']) {
                $r->headers->set('Location', $response['X-Location']);
                unset($response['X-Location']);
            }

            if (isset($response['X-Deprecated']) && $response['X-Deprecated']) {
                $r->headers->set('X-API-Warning', 'This method is deprecated');
                unset($response['X-Deprecated']);
            }

            $r->setData($response);
            $event->setResponse($r);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array('onKernelView', 0),
        );
    }
}
