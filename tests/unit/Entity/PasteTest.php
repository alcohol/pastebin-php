<?php declare(strict_types=1);

namespace Paste\Entity;

use PHPUnit\Framework\TestCase;

class PasteTest extends TestCase
{
    public function test_it_can_be_created_with_a_body(): Paste
    {
        $body = 'foo';
        $paste = Paste::create($body);

        $this->assertInstanceOf(Paste::class, $paste);
        $this->assertEquals($body, $paste->getBody());
        $this->assertEquals($body, (string) $paste);

        return $paste;
    }

    /**
     * @depends test_it_can_be_created_with_a_body
     */
    public function test_it_returns_a_new_instance_with_given_code_when_calling_persist(Paste $paste): Paste
    {
        $code = uniqid();

        $this->assertNull($paste->getCode());

        $paste = $paste->persist($code);

        $this->assertInstanceOf(Paste::class, $paste);
        $this->assertEquals($code, $paste->getCode());

        return $paste;
    }

    /**
     * @depends test_it_returns_a_new_instance_with_given_code_when_calling_persist
     */
    public function test_it_returns_a_new_instance_with_given_body_when_calling_persist(Paste $paste): void
    {
        $body = 'bar';

        $updated = $paste->update($body);

        $this->assertInstanceOf(Paste::class, $updated);
        $this->assertEquals($body, $updated->getBody());
        $this->assertEquals($paste->getCode(), $updated->getCode());
    }
}
