<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Repository;

use Doctrine\Common\Cache\Cache;
use Paste\Entity\Paste;
use Paste\Exception\StorageException;

final class PasteRepository
{
    private $cache;
    private $default_ttl;

    public function __construct(Cache $cache, int $default_ttl)
    {
        $this->cache = $cache;
        $this->default_ttl = $default_ttl;
    }

    /**
     * @throws \Paste\Exception\StorageException
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
     * @throws \Paste\Exception\StorageException
     */
    public function delete(Paste $paste): bool
    {
        if (!$this->cache->delete($paste->getCode())) {
            throw new StorageException('Cannot delete from cache.'); // @codeCoverageIgnore
        }

        return true;
    }

    /**
     * @throws \Paste\Exception\StorageException
     */
    public function persist(Paste $paste, ?int $ttl = null): Paste
    {
        if (null === $ttl) {
            $ttl = $this->default_ttl;
        }

        if (null === $paste->getCode()) {
            $retries = 10;

            do {
                if (0 === $retries--) {
                    throw new StorageException('Failed to generate a unique code.'); // @codeCoverageIgnore
                }

                $bytes = random_bytes(4);
                $code = bin2hex($bytes);
            } while ($this->cache->contains($code));

            $paste = $paste->persist($code);
        }

        if (!$this->cache->save($paste->getCode(), serialize($paste), $ttl)) {
            throw new StorageException('Cannot persist to cache.'); // @codeCoverageIgnore
        }

        return $paste;
    }
}
