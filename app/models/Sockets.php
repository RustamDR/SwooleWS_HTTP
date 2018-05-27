<?php

namespace app\models;

use app\models\repositories\SocketUriRepository;

/**
 * Connected sockets registry by fd
 * Class Sockets
 * @package app\models
 */
class Sockets
{
    /**
     * URI по fd ([fd => uri])
     * @var SocketUriRepository
     */
    private $socketUri;

    /**
     * Sockets constructor.
     * @param SocketUriRepository $uri
     */
    public function __construct(SocketUriRepository $uri)
    {
        $this->socketUri = $uri;
    }

    /**
     * @param int $fd
     * @param string $uri
     */
    public function addUriToFd(int $fd, string $uri): void
    {
        $this->socketUri->set($fd, $uri);
    }

    /**
     * @param int $fd
     */
    public function deleteUriByFd(int $fd): void
    {
        $this->socketUri->delete($fd);
    }

    /**
     * @param int $fd
     * @return string|null
     */
    public function getUriByFd(int $fd): ?string
    {
        return $this->socketUri->get($fd);
    }
}