<?php

namespace app\routes\handlers;

use app\core\Application\Container;
use app\core\Application\RequestParams;
use app\models\Sockets;

/**
 * Abstract class for all handlers
 * Class BaseHandler
 * @package app\routes\handlers
 */
abstract class BaseHandler
{
    /**
     * @return Sockets
     */
    protected function sockets(): Sockets
    {
        return Container::get(Sockets::class);
    }

    /**
     * Runs by closing socket connection only (no work by default)
     * @param int $fd
     * @param RequestParams $params
     * @param callable $resolve
     */
    public function close(int $fd, RequestParams $params, callable $resolve): void
    {
        return;
    }
}