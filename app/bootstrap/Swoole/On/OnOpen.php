<?php

namespace app\bootstrap\Swoole\On;

/**
 * Class OnOpen
 * @package app\bootstrap\Swoole\On
 */
class OnOpen extends OnBase
{
    /**
     * @param \swoole_websocket_server $server
     * @param \swoole_http_request $request
     * @throws \Exception
     */
    public function __invoke(\swoole_websocket_server $server, \swoole_http_request $request): void
    {
        if (!$this->checkConnection($request)) {
            (new \swoole_http_response())->end();
        }
    }
}