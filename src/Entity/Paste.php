<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

final class Paste implements \Serializable
{
    private $code;
    private $body;

    public static function create($body)
    {
        $paste = new self();
        $paste->body = $body;

        return $paste;
    }

    public function persist($code): self
    {
        $paste = new self();
        $paste->body = $this->body;
        $paste->code = $code;

        return $paste;
    }

    public function update($body): self
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
        return (string) $this->body;
    }

    public function __toString(): string
    {
        return (string) $this->body;
    }

    public function serialize(): string
    {
        return serialize([$this->code, $this->body]);
    }

    public function unserialize($serialized): void
    {
        list($this->code, $this->body) = unserialize($serialized);
    }
}
