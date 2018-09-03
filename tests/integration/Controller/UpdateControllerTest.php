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
class UpdateControllerTest extends IntegrationTest
{
    public function test_it_should_return_a_400_if_paste_exists_but_authentication_header_is_missing(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, /* $token */] = $this->extractLocationAndToken($client->getResponse());
        $client->request('PUT', $location, [], [], [], 'Ipsum lorem');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function test_it_should_return_a_404_if_paste_exists_but_authentication_header_is_invalid(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, /* $token */] = $this->extractLocationAndToken($client->getResponse());
        $client->request('PUT', $location, [], [], ['HTTP_X-Paste-Token' => 'dummy-token'], 'Ipsum lorem');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function test_it_should_return_a_404_if_paste_does_not_exist_but_authentication_header_is_given(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/dummy', [], [], ['HTTP_X-Paste-Token' => 'dummy-token'], 'Ipsum lorem');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function test_it_should_return_a_204_if_paste_exists_and_valid_authentication_header_is_given(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, $token] = $this->extractLocationAndToken($client->getResponse());
        $client->request('PUT', $location, [], [], ['HTTP_X-Paste-Token' => $token], 'Ipsum lorem');

        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        $this->assertEquals('Ipsum lorem', $client->getResponse()->getContent());
    }
}
