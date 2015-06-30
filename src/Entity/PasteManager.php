<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Entity;

use Alcohol\PasteBundle\Exception\StorageException;
use Alcohol\PasteBundle\Exception\TokenException;
use Alcohol\PasteBundle\Util\HashUtils;
use Predis\Client;
use Predis\Collection\Iterator\Keyspace;
use Predis\Connection\ConnectionException;

class PasteManager
{
    /** @var Client */
    protected $redis;

    /** @var HashUtils */
    protected $hash;

    /** @var int */
    protected $default_ttl;

    /**
     * @param Client $redis
     * @param HashUtils $hash
     * @param int $default_ttl
     */
    public function __construct(Client $redis, HashUtils $hash, $default_ttl)
    {
        $this->redis = $redis;
        $this->hash = $hash;

        $this->setDefaultTtl($default_ttl);
    }

    /**
     * @param int $default_ttl
     */
    public function setDefaultTtl($default_ttl)
    {
        $this->default_ttl = (int) $default_ttl;
    }

    /**
     * @return int
     */
    public function getDefaultTtl()
    {
        return $this->default_ttl;
    }

    /**
     * @param string $body
     * @param int $ttl
     * @throws StorageException when the paste cannot be persisted due to the conditional flag
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    public function create($body, $ttl = null)
    {
        do {
            $code = $this->hash->generate();
        } while ($this->redis->exists('paste:' . $code));

        $token = $this->hash->generate(10);
        $paste = new Paste($code, $body, $token);

        return $this->persist($paste, 'NX', $ttl);
    }

    /**
     * @param string $code
     * @throws StorageException when the paste cannot be found
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    public function read($code)
    {
        $paste = $this->redis->get('paste:' . $code);

        if (null === $paste) {
            throw new StorageException('Not found: ' . $code);
        }

        $paste = unserialize($paste);

        return $paste;
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @param int $ttl
     * @throws TokenException when the token given does not match the token associated with the paste
     * @throws StorageException when the paste cannot be persisted due to the conditional flag
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    public function update(Paste $paste, $token, $ttl = null)
    {
        if (!$this->hash->compare($token, $paste->getToken())) {
            throw new TokenException('Unable to persist to storage, invalid token');
        }

        return $this->persist($paste, 'XX', $ttl);
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @throws TokenException when the token given does not match the token associated with the paste
     * @throws StorageException when the paste cannot be removed from storage (possibly already removed/expired)
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return bool
     */
    public function delete(Paste $paste, $token)
    {
        if (!$this->hash->compare($token, $paste->getToken())) {
            throw new TokenException('Unable to delete from storage, invalid token');
        }

        if (!$this->redis->del(array('paste:' . $paste->getCode()))) {
            throw new StorageException('Unable to delete from storage');
        }

        return true;
    }

    /**
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return int
     */
    public function getCount()
    {
        $keys = $this->redis->keys('paste:*');

        return count($keys);
    }

    /**
     * @throws StorageException when the paste cannot be found
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return array
     */
    public function getList()
    {
        $pasties = [];
        foreach (new Keyspace($this->redis, 'paste:*') as $key) {
            list(/* $prefix */, $code) = explode(':', $key);
            $pasties[] = $this->read($code);
        }

        return $pasties;
    }

    /**
     * @param Paste $paste
     * @param string $flag
     * @param int $ttl
     * @throws StorageException when the paste cannot be persisted due to the conditional flag
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    protected function persist(Paste $paste, $flag = 'XX', $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->getDefaultTtl();
        }

        if ($ttl > 31556926) {
            throw new StorageException('Refusing to persist with a ttl larger than 1 year');
        }

        if (!$this->redis->set('paste:' . $paste->getCode(), serialize($paste), 'EX', (int) $ttl, $flag)) {
            throw new StorageException('Unable to persist to storage');
        }

        return $paste;
    }
}
