<?php

namespace app\models\repositories;

use app\models\repositories\driver\IStorage;

/**
 * IStorage of connected fd to uri
 * Class SocketUriRepository
 * @package app\models\repositories
 */
class SocketUriRepository
{
    /** @var IStorage */
    private $storage;

    /**
     * SocketsRepository constructor.
     * @param IStorage $storage
     */
    public function __construct(IStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Uri подключения по fd
     * @param $fd
     * @return null|string
     */
    public function get(int $fd): ?string
    {
        return $this->storage->get($fd);
    }

    /**
     * Запомнить uri подключения по fd
     * @param int $fd
     * @param string $uri
     * @return bool
     */
    public function set(int $fd, string $uri): bool
    {
        return $this->storage->set($fd, $uri);
    }

    /**
     * Удалить uri по fd
     * @param int $fd
     * @return bool
     */
    public function delete(int $fd): bool
    {
        return $this->storage->delete($fd);
    }
}