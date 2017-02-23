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
class DeleteControllerTest extends IntegrationTest
{
    public function testDelete()
    {
        $client = static::createClient();
        $client->disableReboot();

        $client->request('POST', '/', [], [], [], 'Lorem ipsum');

        $token = $client->getResponse()->headers->get('X-Paste-Token');
        $location = $client->getResponse()->headers->get('Location');

        $client->request('DELETE', $location);

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode(),
            '"DELETE /{id}" should return a 400 Bad Request if no token is provided.'
        );

        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => 'invalid-token']);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode(),
            '"DELETE /{id}" should return a 404 Not Found if invalid token is provided.'
        );

        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => $token]);

        $this->assertEquals(
            204,
            $client->getResponse()->getStatusCode(),
            '"DELETE /{id}" should return a 204 No Content response if correct token is provided.'
        );

        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => $token]);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode(),
            '"DELETE /{id}" should return a 404 Not Found when trying to delete a paste that does not exist.'
        );

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode(),
            '"GET /{id}" should return a 404 Not Found after deleting.'
        );
    }
}
