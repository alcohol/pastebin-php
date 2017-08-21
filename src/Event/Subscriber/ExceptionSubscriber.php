<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Event\Subscriber;

use Predis\Connection\ConnectionException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['logException', 20],
                ['handleException', 10],
            ],
        ];
    }

    public function logException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $this->logger->error($exception->getMessage(), ['exception' => [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]]);
    }

    public function handleException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

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
