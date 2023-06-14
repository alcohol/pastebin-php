<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\IntegrationSetup;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group(name: 'integration')]
final class HealthController extends IntegrationSetup
{
    public function testItShouldReturnA200ResponseForHEADRequests(): void
    {
        $client = static::createClient();
        $client->request('HEAD', '/health');

        static::assertTrue($client->getResponse()->isOk());
    }

    public function testItShouldReturnA200ResponseForGETRequests(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        static::assertTrue($client->getResponse()->isOk());
    }
}
