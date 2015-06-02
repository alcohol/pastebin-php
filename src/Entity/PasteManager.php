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
    protected $ttl;

    /**
     * @param Client $redis
     * @param HashUtils $hash
     * @param int $ttl
     */
    public function __construct(Client $redis, HashUtils $hash, $ttl)
    {
        $this->redis = $redis;
        $this->hash = $hash;

        $this->setTtl($ttl);
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param string $body
     * @throws StorageException when the paste cannot be persisted due to the conditional flag
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    public function create($body)
    {
        do {
            $code = $this->hash->generate();
        } while ($this->redis->exists('paste:'.$code));

        $token = $this->hash->generate(10);
        $paste = new Paste($code, $body, $token);

        return $this->persist($paste, 'NX');
    }

    /**
     * @param string $code
     * @throws StorageException when the paste cannot be found
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    public function read($code)
    {
        $paste = $this->redis->get('paste:'.$code);

        if (null === $paste) {
            throw new StorageException('Not found: '.$code);
        }

        $paste = unserialize($paste);

        return $paste;
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @throws TokenException when the token given does not match the token associated with the paste
     * @throws StorageException when the paste cannot be persisted due to the conditional flag
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    public function update(Paste $paste, $token)
    {
        if (!$this->hash->compare($token, $paste->getToken())) {
            throw new TokenException('Unable to persist to storage, invalid token');
        }

        return $this->persist($paste);
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

        if (!$this->redis->del(array('paste:'.$paste->getCode()))) {
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
     * @throws StorageException when the paste cannot be persisted due to the conditional flag
     * @throws ConnectionException when a connection with the redis server cannot be established
     * @return Paste
     */
    protected function persist(Paste $paste, $flag = 'XX')
    {
        if (!$this->redis->set('paste:'.$paste->getCode(), serialize($paste), 'EX', $this->getTtl(), $flag)) {
            throw new StorageException('Unable to persist to storage');
        }

        return $paste;
    }
}
