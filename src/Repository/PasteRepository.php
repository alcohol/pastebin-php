<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Repository;

use Paste\Entity\Paste;
use Paste\Exception\NotFoundException;
use Paste\Exception\StorageException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PasteRepository
{
    public function __construct(
        private readonly \Redis $storage,
        #[Autowire(param: 'redis.ttl')]
        private readonly int $defaultTtl
    ) {
    }

    /**
     * @throws \Paste\Exception\NotFoundException
     */
    public function find(string $code): Paste
    {
        $paste = $this->storage->get($code);

        if (false === $paste) {
            throw new NotFoundException();
        }

        $paste = unserialize(
            $paste,
            [
                'allowed_classes' => [
                    Paste::class,
                ],
                'max_depth' => 1,
            ]
        );

        if (!$paste instanceof Paste) {
            throw new NotFoundException();
        }

        return $paste;
    }

    public function delete(Paste $paste): void
    {
        $this->storage->unlink($paste->code);
    }

    public function generateIdentifier(): string
    {
        $retries = 10;

        do {
            if (0 === $retries--) {
                throw new StorageException('Failed to generate a unique nonexistent code within a reasonable amount of attempts.'); // @codeCoverageIgnore
            }

            $bytes = random_bytes(4);
            $code = bin2hex($bytes);
        } while ($this->storage->exists($code));

        return $code;
    }

    public function persist(Paste $paste, ?int $ttl = null): bool
    {
        $ttl ??= $this->defaultTtl;

        return $this->storage->set($paste->code, serialize($paste), ['nx', 'ex' => $ttl]);
    }

    public function update(Paste $paste, ?int $ttl = null): bool
    {
        $ttl ??= $this->defaultTtl;

        return $this->storage->set($paste->code, serialize($paste), ['xx', 'ex' => $ttl]);
    }
}
