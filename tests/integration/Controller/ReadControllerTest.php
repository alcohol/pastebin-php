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
class ReadControllerTest extends IntegrationTest
{
    public function testPostRaw()
    {
        $ttl = 2;

        $client = static::createClient();
        $client->disableReboot();

        $client->request('POST', '/', [], [], ['HTTP_X-Paste-Ttl' => $ttl], 'Lorem ipsum');

        $location = $client->getResponse()->headers->get('Location');

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        $this->assertEquals(
            'Lorem ipsum',
            $client->getResponse()->getContent(),
            '"GET /{id}" should return content stored.'
        );

        sleep($ttl + 1);

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode(),
            '"GET /{id}" should return a 404 after past has expired.'
        );
    }
}
