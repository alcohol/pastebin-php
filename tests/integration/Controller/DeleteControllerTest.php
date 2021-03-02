<?php

declare(strict_types=1);

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
 *
 * @internal
 */
final class DeleteControllerTest extends IntegrationTest
{
    public function testItShouldReturnA400IfPasteExistsButAuthenticationHeaderIsMissing(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, /* $token */ ] = $this->extractLocationAndToken($client->getResponse());
        $client->request('DELETE', $location);

        static::assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA404IfPasteExistsButAuthenticationHeaderIsInvalid(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, /* $token */ ] = $this->extractLocationAndToken($client->getResponse());
        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => 'dummy-token']);

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA404IfPasteDoesNotExistButAuthenticationHeaderIsGiven(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/dummy', [], [], ['HTTP_X-Paste-Token' => 'dummy-token']);

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA204IfPasteExistsAndValidAuthenticationHeaderIsGiven(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, $token] = $this->extractLocationAndToken($client->getResponse());
        $client->request('DELETE', $location, [], [], ['HTTP_X-Paste-Token' => $token]);

        static::assertSame(204, $client->getResponse()->getStatusCode());
    }
}
