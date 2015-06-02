<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\EventListener;

use Predis\Connection\ConnectionException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class ExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $response = new Response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);

        if ($exception instanceof ConnectionException) {
            $exception = new ServiceUnavailableHttpException(300, $exception->getMessage(), $exception);
            $event->setException($exception);
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        }

        $event->setResponse($response);
    }
}
