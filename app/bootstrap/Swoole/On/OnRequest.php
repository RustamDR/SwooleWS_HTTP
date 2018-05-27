<?php

namespace app\bootstrap\Swoole\On;

/**
 * Class OnRequest
 * @package app\bootstrap\Swoole\On
 */
class OnRequest extends OnBase
{
    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function __invoke(\swoole_http_request $request, \swoole_http_response $response): void
    {
        $app = $this->app;
        $this->requestUpperCase($request);
        $response->header('Content-Type', 'text/json;charset=utf-8');

        $method = $request->server['REQUEST_METHOD'];
        $uri = $request->server['REQUEST_URI'];
        $params = $this->getParams($request);

        $logCb = function ($result = null) use ($method, $uri, $params): void {
            $level = $result ? 'INFO' : 'ERROR';
            $this->log($method . ' ' . $uri . ' params ' . json_encode($params, JSON_PRETTY_PRINT) . ' => ' . json_encode($result, JSON_PRETTY_PRINT), $level);
        };
        $resolveCb = $this->resolveCb($response, $logCb);

        try {
            $app->handler->handle($method, $uri, $params, $request, $resolveCb);
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'ERROR', $request->fd);
            $resolveCb(500);
        }
    }

    /**
     * @param \swoole_http_response $response
     * @param callable $log
     * @return callable
     */
    public function resolveCb(\swoole_http_response $response, callable $log = null): callable
    {
        return function (int $status, $result = null, array $roomsData = []) use ($response, $log): void {
            $response->status($status);
            $response->end(json_encode($result));

            $this->broadcast($result);

            if ($log) {
                $log($result);
            }
        };
    }
}