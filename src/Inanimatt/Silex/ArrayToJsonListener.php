<?php

namespace Inanimatt\Silex;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Inanimatt\Api\ApiResponse;

/**
 * Converts array responses to JsonResponse instances.
 */
class ArrayToJsonListener implements EventSubscriberInterface
{
    protected $response_prototype;

    public function __construct(ApiResponse $response)
    {
        $this->response_prototype = $response;
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

            $r = clone $this->response_prototype;
            $r->setStatusCode($statusCode);

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
