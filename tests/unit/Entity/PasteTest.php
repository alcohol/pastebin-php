<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group(name: 'unit')]
final class PasteTest extends TestCase
{
    public function testItCanBeCreatedWithACodeAndBody(): Paste
    {
        $code = uniqid();
        $body = 'foo';

        $paste = new Paste($code, $body);

        static::assertSame($body, $paste->body);
        static::assertSame($body, (string) $paste);
        static::assertSame($code, $paste->code);

        return $paste;
    }

    #[Depends(methodName: 'testItCanBeCreatedWithACodeAndBody')]
    public function testItReturnsANewInstanceWithGivenBodyWhenCallingPersist(Paste $paste): void
    {
        $body = 'bar';

        $updated = $paste->withBody($body);

        static::assertSame($body, $updated->body);
        static::assertSame($paste->code, $updated->code);
        static::assertNotSame($updated, $paste);
    }
}
