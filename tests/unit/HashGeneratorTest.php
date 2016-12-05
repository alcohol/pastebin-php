<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace AppBundle\Security;

final class HashGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \TypeError
     */
    public function itRequiresASecretToInstantiate()
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
    public function itExplodesWhenSecretGivenIsOfType($input, $expectedException)
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
        ];
    }

    /**
     * @test
     */
    public function itInstantiatesWhenGivenAValidSecret()
    {
        $generator = new HashGenerator('secret');

        $this->assertInstanceOf(HashGenerator::class, $generator);
    }

    /**
     * @test
     */
    public function itProducesTheSameHashWhenGivenTheSameInput()
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

    /**
     * @test
     */
    public function itProducesADifferentHashWhenGivenDifferentSecrets()
    {
        $generator = new HashGenerator('secret1');
        $generated = $generator->generateHash('abcd');
        $generator = new HashGenerator('secret2');

        $this->assertNotSame($generated, $generator->generateHash('abcd'));
    }
}
