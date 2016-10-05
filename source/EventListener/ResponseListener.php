<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Alcohol\Paste\EventListener;

use Alcohol\Paste\Security\HashGenerator;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

final class ResponseListener
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
     * @param FilterResponseEvent $event
     */
    public function handleEvent(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        if ($response->headers->has('X-Paste-Id') && !$response->headers->has('X-Paste-Token')) {
            $id = $response->headers->get('X-Paste-Id');
            $response->headers->add(['X-Paste-Token' => $this->generator->generateHash($id)]);
        }
    }
}
