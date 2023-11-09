<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            $response = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];
        } else {
            $response = [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $exception->getMessage()
            ];
        }
        $event->setResponse(new JsonResponse($response));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelResponse',
        ];
    }
}
