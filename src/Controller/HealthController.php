<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class HealthController
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {

        $this->redis = $redis;
    }

    public function __invoke(): Response
    {
        $this->redis->ping();

        $response = new Response('OK', 200);
        $response->setPrivate();

        return $response;
    }
}
