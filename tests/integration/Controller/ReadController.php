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
final class ReadController extends IntegrationSetup
{
    public function testItShouldReturnA404IfAPasteDoesNotExist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dummy', [], [], ['HTTP_Accept' => 'text/plain']);

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA200WithCorrectBodyIfPasteExists(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');

        [$location, /* $token */ ] = $this->extractLocationAndToken($client->getResponse());

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/html']);

        static::assertTrue($client->getResponse()->isOk());
        static::assertStringContainsString('Lorem ipsum', (string) $client->getResponse()->getContent());

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        static::assertTrue($client->getResponse()->isOk());
        static::assertSame('Lorem ipsum', $client->getResponse()->getContent());
    }
}
