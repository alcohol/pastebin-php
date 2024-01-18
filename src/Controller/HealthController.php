<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/health', name: 'paste.health', methods: [Request::METHOD_HEAD, Request::METHOD_GET], stateless: true)]
final readonly class HealthController
{
    public function __construct(
        private \Redis $redis
    ) {
    }

    public function __invoke(): Response
    {
        $this->redis->ping();

        $response = new Response('OK', 200);
        $response->setPrivate();

        return $response;
    }
}
