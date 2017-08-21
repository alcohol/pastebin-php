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
    /**
     * @test
     */
    public function it_should_return_a_404_if_a_paste_does_not_exist()
    {
        $client = static::createClient();
        $client->request('GET', '/dummy', [], [], ['HTTP_Accept' => 'text/plain']);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_return_a_200_with_correct_body_if_paste_exists()
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        list($location, /* $token */) = $this->extractLocationAndToken($client->getResponse());
        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals('Lorem ipsum', $client->getResponse()->getContent());
    }
}
