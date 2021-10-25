<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @internal
 */
final class PasteTest extends TestCase
{
    public function testItCanBeCreatedWithABody(): Paste
    {
        $body = 'foo';
        $paste = Paste::create($body);

        static::assertSame($body, $paste->getBody());
        static::assertSame($body, (string) $paste);

        return $paste;
    }

    /**
     * @depends testItCanBeCreatedWithABody
     */
    public function testItReturnsANewInstanceWithGivenCodeWhenCallingPersist(Paste $paste): Paste
    {
        $code = uniqid();

        static::assertNull($paste->getCode());

        $paste = $paste->persist($code);

        static::assertSame($code, $paste->getCode());

        return $paste;
    }

    /**
     * @depends testItReturnsANewInstanceWithGivenCodeWhenCallingPersist
     */
    public function testItReturnsANewInstanceWithGivenBodyWhenCallingPersist(Paste $paste): void
    {
        $body = 'bar';

        $updated = $paste->update($body);

        static::assertSame($body, $updated->getBody());
        static::assertSame($paste->getCode(), $updated->getCode());
    }
}
