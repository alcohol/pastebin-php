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

final class PasteRepository
{
    private \Redis $storage;
    private int $defaultTtl;

    public function __construct(\Redis $storage, int $defaultTtl)
    {
        $this->storage = $storage;
        $this->defaultTtl = $defaultTtl;
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

        return unserialize($paste);
    }

    public function delete(Paste $paste): void
    {
        if (null !== $paste->getCode()) {
            $this->storage->unlink($paste->getCode());
        }
    }

    /**
     * @throws \Paste\Exception\StorageException
     */
    public function persist(Paste $paste, ?int $ttl = null): Paste
    {
        if (null === $paste->getCode()) {
            $retries = 10;

            do {
                if (0 === $retries--) {
                    throw new StorageException('Failed to generate a unique nonexistent code within a reasonable amount of attempts.'); // @codeCoverageIgnore
                }

                $bytes = random_bytes(4);
                $code = bin2hex($bytes);
            } while ($this->storage->exists($code));

            $paste = $paste->persist($code);
        }

        if (null === $ttl) {
            $ttl = $this->defaultTtl;
        }

        if (!$this->storage->set($paste->getCode(), serialize($paste), $ttl)) {
            throw new StorageException('Cannot persist to cache.'); // @codeCoverageIgnore
        }

        return $paste;
    }
}
