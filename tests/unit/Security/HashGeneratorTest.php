<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Security;

use PHPUnit\Framework\TestCase;

final class HashGeneratorTest extends TestCase
{
    /**
     * @expectedException \TypeError
     */
    public function test_it_requires_a_secret_to_instantiate()
    {
        new HashGenerator();
    }

    /**
     * @dataProvider invalidSecrets
     *
     * @param mixed $input
     * @param \Throwable $expectedException
     */
    public function test_it_explodes_when_secret_given_is_of_type($input, $expectedException)
    {
        $this->expectException($expectedException);

        new HashGenerator($input);
    }

    /**
     * @return array
     */
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

    public function test_it_instantiates_when_given_a_valid_secret()
    {
        $generator = new HashGenerator('secret');

        $this->assertInstanceOf(HashGenerator::class, $generator);
    }

    public function test_it_produces_the_same_hash_when_given_the_same_input()
    {
        $input = 'hash-me';
        $generator = new HashGenerator('secret');

        for ($i = 0; $i < 5; ++$i) {
            $this->assertSame($generator->generateHash($input), $generator->generateHash($input));
        }
    }

    public function test_it_produces_a_different_has_when_given_different_inputs()
    {
        $input = 'hash-me';
        $generator = new HashGenerator('secret');

        $this->assertNotSame($generator->generateHash($input), $generator->generateHash(strrev($input)));
    }

    public function test_it_produces_a_different_hash_when_instantiated_with_a_different_secret_but_given_identical_input()
    {
        $input = 'hash-me';
        $generator1 = new HashGenerator('secret-foo');
        $generator2 = new HashGenerator('secret-bar');

        $this->assertNotSame($generator1->generateHash($input), $generator2->generateHash($input));
    }
}
