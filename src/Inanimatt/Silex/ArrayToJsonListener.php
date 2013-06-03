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

            if (isset($response['httpStatus']) && $response['httpStatus']) {
                $statusCode = $response['httpStatus'];
                unset($response['httpStatus']);
            }

            $r = new JsonResponse(null, $statusCode);

            $r->headers->set('X-API-Version', $this->api_version);

            if (isset($response['httpHeaders']) && is_array($response['httpHeaders'])) {
                foreach ($response['httpHeaders'] as $key => $value) {
                    $r->headers->set($key, $value);
                }

                unset($response['httpHeaders']);
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
