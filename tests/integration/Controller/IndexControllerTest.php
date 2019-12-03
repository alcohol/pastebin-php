<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\IntegrationTest;

/**
 * @group integration
 */
final class IndexControllerTest extends IntegrationTest
{
    public function testItShouldReturnA200Response(): void
    {
        $client = static::createClient();
        $client->request('GET', '/', [], [], ['HTTP_Accept' => 'text/html']);

        static::assertTrue($client->getResponse()->isOk());

        $client->request('GET', '/', [], [], ['HTTP_Accept' => 'text/plain']);

        static::assertTrue($client->getResponse()->isOk());
    }
}
