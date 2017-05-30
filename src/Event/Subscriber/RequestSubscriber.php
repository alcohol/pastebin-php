<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Event\Subscriber;

use Paste\Security\HashGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Paste\Security\HashGenerator
     */
    private $generator;

    /**
     * @param \Paste\Security\HashGenerator $generator
     */
    public function __construct(HashGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequest', 0],
            ]
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (false === in_array($request->attributes->get('_route'), ['paste.update', 'paste.delete'], true)) {
            return;
        }

        if (false === $request->headers->has('X-Paste-Token')) {
            throw new BadRequestHttpException();
        }

        $code = $request->attributes->get('id');

        if (false === hash_equals($request->headers->get('X-Paste-Token'), $this->generator->generateHash($code))) {
            throw new NotFoundHttpException();
        }
    }
}
