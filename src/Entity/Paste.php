<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

/**
 * @phpstan-type SerializedPaste array{code: string, body: string}
 */
final readonly class Paste implements \Stringable
{
    public function __construct(
        public string $code,
        public string $body,
    ) {
    }

    public function withBody(string $body): self
    {
        return new self($this->code, $body);
    }

    public function __toString(): string
    {
        return $this->body;
    }

    /**
     * @phpstan-return SerializedPaste
     */
    public function __serialize(): array
    {
        return [
            'code' => $this->code,
            'body' => $this->body,
        ];
    }

    /**
     * @phpstan-param SerializedPaste $data
     */
    public function __unserialize(array $data): void
    {
        [
            'code' => $this->code,
            'body' => $this->body
        ] = $data;
    }
}
