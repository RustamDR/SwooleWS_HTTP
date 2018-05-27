<?php

namespace app\routes\handlers;

use app\core\Application\RequestParams;

/**
 * Class WSConnect
 * @package app\routes\handlers
 */
class WSConnect extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function __invoke(RequestParams $params, \swoole_http_request $request, callable $log)
    {
        $log("{$request->fd} connected!");
    }

    /**
     * Закрытие сокет соединения для fd
     * @inheritdoc
     */
    public function close(int $fd, RequestParams $params, callable $log): void
    {
        $log("{$fd} closed");
    }
}