<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Tests\Integration;

use Alcohol\PasteBundle\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @medium
 */
class UpdateControllerTest extends WebTestCase
{
    /**
     * @inheritDoc
     */
    public static function createKernel(array $options = array())
    {
        return new Application(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    /**
     * @group functional
     */
    public function testPostRaw()
    {
        $original = 'Lorem ipsum';
        $modified = 'Ipsum lorem';

        $client = static::createClient();
        $client->request('POST', '/', [], [], [], $original);
        $token = $client->getResponse()->headers->get('X-Paste-Token');
        $location = $client->getResponse()->headers->get('Location');
        $client->request('GET', $location);

        $this->assertEquals(
            $original,
            $client->getResponse()->getContent(),
            '"GET /{id}" should return original content stored.'
        );

        $client->request('PUT', $location, [], [], ['HTTP_X-Paste-Token' => $token], $modified);

        $this->assertEquals(
            204,
            $client->getResponse()->getStatusCode(),
            '"PUT /{id}" should return a 204 No Content response.'
        );

        $client->request('GET', $location);

        $this->assertEquals(
            $modified,
            $client->getResponse()->getContent(),
            '"GET /{id}" should return modified content stored.'
        );
    }
}
