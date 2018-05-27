<?php

namespace app\routes\handlers\websockets;

use app\core\Application\RequestParams;

/**
 * Class WSHelloHandler
 * @package app\routes\handlers\websockets
 */
class WSHelloHandler extends BaseSocketHandler
{
    /**
     * @param RequestParams $params
     * @param array $message
     * @param callable|null $resolve
     */
    public function __invoke(RequestParams $params, array $message, callable $resolve = null): void
    {
        $resolve("Hello {$params->name} from ws-swoole!");
    }
}