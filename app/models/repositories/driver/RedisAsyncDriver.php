<?php

namespace app\models\repositories\driver;

use app\components\async\redis\RedisClient;
use Swoole\Exception;

/**
 * Class RedisAsyncDriver
 * @package app\models\repositories\driver
 */
class RedisAsyncDriver implements IAsyncStorage
{
    /** @var RedisClient */
    private $redis;
    /** @var string */
    private $collectionName;

    /**
     * RedisAsyncDriver constructor.
     * @param RedisClient $redis
     * @param $collectionName
     */
    public function __construct(RedisClient $redis, string $collectionName = '')
    {
        $this->redis = $redis;
        $this->collectionName = $collectionName;
    }

    /**
     * @param string $hash
     * @param $object
     * @param callable $cb
     */
    public function set(string $hash, $object, callable $cb): void
    {
        $this->redis->set($this->collectionKey($hash), serialize($object), $cb);
    }

    /**
     * @param string $hash
     * @param string $val
     * @param callable $cb
     * @throws Exception
     */
    public function setAdd(string $hash, string $val, callable $cb): void
    {
        $redis = $this->redis;
        $key = $this->collectionKey($hash);
        $this->transaction($key, function ($a, bool $success) use ($redis, $key, $val, $cb) {
            if (!$success) {
                (new \swoole_http_response())->end();
                throw new Exception("Add transaction start error");
            }

            $redis->sAdd($key, $val, function ($a, bool $success) use ($key, $cb, $val) {
                if (!$success) {
                    (new \swoole_http_response())->end();
                    throw new Exception("Add to set error {$key}: {$val}");
                }

                $this->redis->sMembers($key, function ($fds, bool $success) use ($cb) {
                    if (!$success) {
                        (new \swoole_http_response())->end();
                        throw new Exception('sMember');
                    }
                    $cb($fds);
                });
            });
        });
    }

    /**
     * @param string $hash
     * @param string $val
     * @param callable $cb
     * @throws Exception
     */
    public function setRemove(string $hash, string $val, callable $cb): void
    {
        $key = $this->collectionKey($hash);
        $this->transaction($key, function ($a, bool $success) use ($key, $val, $cb) {
            if (!$success) {
                throw new Exception("Remove from set error {$key}: {$val}");
            }

            $this->redis->sRem($key, $val, function ($a, bool $success) use ($key, $cb) {
                if (!$success) {
                    throw new Exception("Remove from set {$key} error");
                }

                $this->redis->sMembers($key, function ($fds, bool $success) use ($cb) {
                    if (!$success) {
                        throw new Exception('sMember get error');
                    }
                    $cb($fds);
                });
            });
        });

    }

    /**
     * @param string $hash
     * @param callable $resolve
     */
    public function get(string $hash, callable $resolve): void
    {
        $key = $this->collectionKey($hash);
        $this->redis->get($key, function ($res, $success) use ($resolve) {
            $result = $res ? unserialize($res) : null;
            $resolve($result);
        });
    }

    /**
     * @param string $hash
     * @param callable $cb
     */
    public function delete(string $hash, callable $cb): void
    {
        $this->redis->del($this->collectionKey($hash), $cb);
    }

    /**
     * @param string $key
     * @return string
     */
    private function collectionKey(string $key): string
    {
        if ($key === '') {
            throw new \MemcachedException('Empty key');
        }

        return $this->collectionName . $key;
    }

    /**
     * @param string $key
     * @param callable $cb
     * @throws Exception
     */
    public function transaction(string $key, callable $cb): void
    {
        $this->redis->watch($key, function ($a, bool $success) use ($cb) {
            if (!$success) {
                throw new Exception('Watch error');
            }

            $this->redis->multi(function ($a, bool $success) use ($cb) {
                if (!$success) {
                    throw new Exception('Multi error');
                }

                $this->redis->exec($cb);
            });
        });
    }
}