<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\EventListener;

use Alcohol\Paste\Security\HashGenerator;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RequestListener
{
    /** @var HashGenerator */
    private $generator;

    /**
     * @param HashGenerator $generator
     */
    public function __construct(HashGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @throws BadRequestHttpException
     */
    public function handleEvent(GetResponseEvent $event)
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
