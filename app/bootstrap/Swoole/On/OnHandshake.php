<?php

namespace app\bootstrap\Swoole\On;

/**
 * Class OnHandshake
 * @package app\bootstrap\Swoole\On
 */
class OnHandshake extends OnBase
{
    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return bool
     */
    public function __invoke(\swoole_http_request $request, \swoole_http_response $response): bool
    {
        if (!isset($request->header['sec-websocket-key'])) {
            //'Bad protocol implementation: it is not RFC6455.'
            $response->end();
            return false;
        }

        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $request->header['sec-websocket-key'])
            || 16 !== strlen(base64_decode($request->header['sec-websocket-key']))
        ) {
            //Header Sec-WebSocket-Key is illegal;
            $response->end();
            return false;
        }

        if (!$this->checkConnection($request)) {
            $response->end();
            return false;
        }

        $key = base64_encode(sha1($request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $headers = array(
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
            'KeepAlive' => 'off',
        );
        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);
        $response->end();

        return true;
    }
}