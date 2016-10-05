<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Alcohol\Paste\Security;

final class HashGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \TypeError
     */
    public function itRequiresAnArgumentToInstantiate()
    {
        new HashGenerator();
    }

    /**
     * @test
     * @dataProvider invalidSecrets
     *
     * @param mixed $input
     * @param \Throwable $expectedException
     */
    public function itExplodesWhenArgumentGivenIsA($input, $expectedException)
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
            'object / class' => [new \stdClass, \TypeError::class],
            'empty string' => ['', \InvalidArgumentException::class],
        ];
    }

    /**
     * @test
     */
    public function itInstantiatesWhenGivenAValidArgument()
    {
        $generator = new HashGenerator('secret');

        $this->assertInstanceOf(HashGenerator::class, $generator);
    }

    /**
     * @test
     */
    public function itProducesTheSameHashWhenGivenTheSameInputRepeatedly()
    {
        $generator = new HashGenerator('secret');
        $generated = $generator->generateHash('abcd');

        $this->assertSame($generated, $generator->generateHash('abcd'));
        $this->assertSame($generated, $generator->generateHash('abcd'));
        $this->assertSame($generated, $generator->generateHash('abcd'));
    }

    /**
     * @test
     */
    public function itProducesADifferentHashWhenGivenDifferentInputs()
    {
        $generator = new HashGenerator('secret');
        $generated = $generator->generateHash('abcd');

        $this->assertNotSame($generated, $generator->generateHash('dcba'));
    }
}
