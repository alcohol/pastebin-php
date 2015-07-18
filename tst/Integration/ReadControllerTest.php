<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Tests\Integration;

/**
 * @medium
 * @group integration
 */
class ReadControllerTest extends IntegrationTest
{
    public function testPostRaw()
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        $token = $client->getResponse()->headers->get('X-Paste-Token');
        $location = $client->getResponse()->headers->get('Location');
        $client->request('GET', $location);

        $this->assertEquals(
            'Lorem ipsum',
            $client->getResponse()->getContent(),
            '"GET /{id}" should return content stored.'
        );
    }
}
