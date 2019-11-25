<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Security;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class HashGeneratorTest extends TestCase
{
    public function testItRequiresASecretToInstantiate(): void
    {
        $this->expectException(\TypeError::class);

        new HashGenerator();
    }

    /**
     * @dataProvider invalidSecrets
     *
     * @param mixed $input
     */
    public function testItExplodesWhenSecretGivenIsOfType($input, string $expectedException): void
    {
        $this->expectException($expectedException);

        new HashGenerator($input);
    }

    public function invalidSecrets(): array
    {
        return [
            'integer' => [123, \TypeError::class],
            'float' => [1.23, \TypeError::class],
            'array' => [[], \TypeError::class],
            'object / class' => [new \stdClass(), \TypeError::class],
            'empty string' => ['', \InvalidArgumentException::class],
            'null' => [null, \TypeError::class],
        ];
    }

    public function testItInstantiatesWhenGivenAValidSecret(): void
    {
        $generator = new HashGenerator('secret');

        static::assertInstanceOf(HashGenerator::class, $generator);
    }

    public function testItProducesTheSameHashWhenGivenTheSameInput(): void
    {
        $input = 'hash-me';
        $generator = new HashGenerator('secret');

        for ($i = 0; $i < 5; ++$i) {
            static::assertSame($generator->generateHash($input), $generator->generateHash($input));
        }
    }

    public function testItProducesADifferentHasWhenGivenDifferentInputs(): void
    {
        $input = 'hash-me';
        $generator = new HashGenerator('secret');

        static::assertNotSame($generator->generateHash($input), $generator->generateHash(strrev($input)));
    }

    public function testItProducesADifferentHashWhenInstantiatedWithADifferentSecretButGivenIdenticalInput(): void
    {
        $input = 'hash-me';
        $generator1 = new HashGenerator('secret-foo');
        $generator2 = new HashGenerator('secret-bar');

        static::assertNotSame($generator1->generateHash($input), $generator2->generateHash($input));
    }
}
