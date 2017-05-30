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
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseSubscriber implements EventSubscriberInterface
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
            KernelEvents::RESPONSE => [
                ['onResponse', 0],
            ]
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
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
