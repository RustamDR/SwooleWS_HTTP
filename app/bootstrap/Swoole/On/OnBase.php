<?php

namespace app\bootstrap\Swoole\On;

use app\core\Application\Application;
use app\models\Sockets;

/**
 * Class OnBase
 * @package app\bootstrap\Swoole\On
 */
abstract class OnBase
{
    /** @var Application */
    protected $app;

    /**
     * OnClose constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $data
     * @param string $level
     * @param null $fd
     */
    protected function log(string $data, string $level = 'INFO', $fd = null): void
    {
        $this->app->logger->log(($fd ? "fd#${fd} " : '') . $data, $level);
    }

    /**
     * @param \swoole_http_request $request
     */
    protected function requestUpperCase(\swoole_http_request &$request): void
    {
        $request->server = array_change_key_case($request->server, CASE_UPPER);
        $request->server['SCRIPT_FILENAME'] = 'index.php';
        $request->server['SCRIPT_NAME'] = 'index.php';
        unset($request->server['PHP_SELF']);
    }

    /**
     * @param \swoole_http_request $request
     * @return array
     */
    protected function getParams(\swoole_http_request $request): array
    {
        $method = $request->server['REQUEST_METHOD'];
        $result = ($method === 'POST' ? $request->post : $request->get) ?? [];
        $result['ip'] = $request->server['REMOTE_ADDR'];
        return $result;
    }

    /**
     * @param $data
     */
    protected function broadcast($data): void
    {
        // REALIZE BROADCAST BY ANY WAY
    }

    /**
     * @return Sockets
     */
    protected function sockets(): Sockets
    {
        return $this->app->container->getByAlias(Sockets::class);
    }

    /**
     * @param \swoole_http_request $request
     * @return bool
     */
    protected function checkConnection(\swoole_http_request $request): bool
    {
        /** @var Sockets $sockets */
        $sockets = $this->sockets();
        $this->requestUpperCase($request);
        $method = $request->server['REQUEST_METHOD'];
        $uri = $request->server['REQUEST_URI'];

        try {
            $server = $this->app->swoole;
            $fd = $request->fd;
            $this->log("Server worker pid {$server->worker_pid}: handshake success " . json_encode($server->getClientInfo($fd)), 'INFO', $fd);

            $params = $this->getParams($request);
            $log = function ($data) use ($fd, $sockets, $uri) {
                $sockets->addUriToFd($fd, $uri);
                $this->log($data, 'INFO', $fd);
            };
            $this->app->handler->handle($method, $uri, $params, $request, $log);
        } catch (\Exception|\TypeError $e) {
            $sockets->deleteUriByFd($fd);
            $this->log($e->getMessage() . ' ' . $method . ' ' . $uri, 'ERROR', $fd);

            return false;
        }

        return true;
    }
}