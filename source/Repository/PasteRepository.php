<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Alcohol\Paste\Repository;

use Alcohol\Paste\Entity\Paste;
use Alcohol\Paste\Exception\StorageException;
use Doctrine\Common\Cache\Cache;

final class PasteRepository
{
    /** @var Cache */
    private $cache;

    /** @var int */
    private $default_ttl;

    /**
     * @param Cache $cache
     * @param int $default_ttl
     */
    public function __construct(Cache $cache, int $default_ttl)
    {
        $this->cache = $cache;
        $this->default_ttl = $default_ttl;
    }

    /**
     * @param string $body
     *
     * @return Paste
     */
    public function create(string $body): Paste
    {
        $retries = 10;

        do {
            if (0 === $retries--) {
                throw new StorageException('Failed to generate a unique key.');
            }

            $bytes = random_bytes(4);
            $code = bin2hex($bytes);
        } while ($this->cache->contains($code));

        $paste = new Paste($code, $body);

        return $paste;
    }

    /**
     * @param string $code
     *
     * @throws StorageException
     *
     * @return Paste
     */
    public function find(string $code): Paste
    {
        $paste = $this->cache->fetch($code);

        if (false === $paste) {
            throw new StorageException('Cannot fetch from cache.');
        }

        return unserialize($paste);
    }

    /**
     * @param Paste $paste
     *
     * @throws StorageException
     *
     * @return bool
     */
    public function delete(Paste $paste): bool
    {
        if (!$this->cache->delete($paste->getCode())) {
            throw new StorageException('Cannot delete from cache.');
        }

        return true;
    }

    /**
     * @param Paste $paste
     * @param int $ttl
     *
     * @throws StorageException
     *
     * @return Paste
     */
    public function persist(Paste $paste, int $ttl = null): Paste
    {
        if (null === $ttl) {
            $ttl = $this->default_ttl;
        }

        if (!$this->cache->save($paste->getCode(), serialize($paste), $ttl)) {
            throw new StorageException('Cannot persist to cache.');
        }

        return $paste;
    }
}
