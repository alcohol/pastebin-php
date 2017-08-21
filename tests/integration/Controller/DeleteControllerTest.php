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
    /**
     * @test
     */
    public function it_should_return_a_400_if_paste_exists_but_authentication_header_is_missing()
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        list($location, /* $token */) = $this->extractLocationAndToken($client->getResponse());
        $client->request('DELETE', $location);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_return_a_404_if_paste_exists_but_authentication_header_is_invalid()
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        list($location, /* $token */) = $this->extractLocationAndToken($client->getResponse());
        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => 'dummy-token']);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_return_a_404_if_paste_does_not_exist_but_authentication_header_is_given()
    {
        $client = static::createClient();
        $client->request('DELETE', '/dummy', [], [], ['HTTP_X-Paste-Token' => 'dummy-token']);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_return_a_204_if_paste_exists_and_valid_authentication_header_is_given()
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        list($location, $token) = $this->extractLocationAndToken($client->getResponse());
        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => $token]);

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
