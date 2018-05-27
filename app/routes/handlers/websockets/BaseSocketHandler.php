<?php

namespace app\routes\handlers\websockets;

use app\core\Application\Container;
use app\core\Application\RequestParams;
use app\models\Sockets;

/**
 * Class BaseSocketHandler
 * @package app\routes\handlers\websockets
 */
abstract class BaseSocketHandler
{
    /** @var Sockets */
    protected $sockets;

    /**
     * @param RequestParams $params
     * @param array $message
     * @param callable|null $resolve
     * @return mixed
     */
    abstract public function __invoke(RequestParams $params, array $message, callable $resolve = null): void;

    /**
     * @return Sockets
     */
    protected function sockets(): Sockets
    {
        return $this->sockets ? $this->sockets : ($this->sockets = Container::get(Sockets::class));
    }
}