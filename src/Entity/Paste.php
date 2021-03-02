<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

final class Paste implements \Serializable
{
    private ?string $code = null;
    private string $body;

    public function __toString(): string
    {
        return $this->body;
    }

    public static function create(string $body): self
    {
        $paste = new self();
        $paste->body = $body;

        return $paste;
    }

    public function persist(string $code): self
    {
        $paste = new self();
        $paste->body = $this->body;
        $paste->code = $code;

        return $paste;
    }

    public function update(string $body): self
    {
        $paste = new self();
        $paste->code = $this->code;
        $paste->body = $body;

        return $paste;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function serialize(): string
    {
        return serialize([$this->code, $this->body]);
    }

    /** @param string $serialized */
    public function unserialize($serialized): void
    {
        [$this->code, $this->body] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
