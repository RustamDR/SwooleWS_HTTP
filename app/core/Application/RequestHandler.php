<?php

namespace app\core\Application;

use app\core\Contracts\IHandler;
use app\models\Sockets;
use app\routes\handlers\BaseHandler;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * Http request handler execute
 * Class RequestHandler
 * @package app\core\Application
 */
class RequestHandler implements IHandler
{
    /** @var Application */
    protected $app;

    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * RequestHandler constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $routes = require(__DIR__ . '/../../routes/http.php');
        $dispatchCallback = function (RouteCollector $r) use ($routes) {
            foreach ($routes as $method => $urls)
                foreach ((array)$urls as $route => $handler)
                    $r->addRoute($method, $route, $handler);
        };
        $this->dispatcher = simpleDispatcher($dispatchCallback);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param \swoole_http_request $request
     * @param callable|null $resolve
     * @throws \Exception
     */
    public function handle(string $method, string $uri, array $params = [], \swoole_http_request $request, callable $resolve = null): void
    {
        $route = $this->dispatcher->dispatch($method, $uri);
        switch ($route[0]) {

            case Dispatcher::NOT_FOUND:
                throw new \Exception('Bad request');
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new \Exception('Not allowed');
                break;

            case Dispatcher::FOUND:
                /** @var BaseHandler $handler */
                $handler = $route[1];
                $vars = array_merge($route[2], $params);
                $handler(new RequestParams($vars), $request, $resolve);
                break;
        }
    }

    /**
     * @param int $fd
     * @param array $params
     * @param callable|null $resolve
     */
    public function close(int $fd, array $params, callable $resolve = null): void
    {
        /** @var Sockets $sockets */
        $sockets = $this->app->container->getByAlias(Sockets::class);
        $uri = $sockets->getUriByFd($fd);
        $route = $this->dispatcher->dispatch('GET', $uri);
        switch ($route[0]) {
            case Dispatcher::FOUND:
                /** @var BaseHandler $handler */
                $handler = $route[1];
                $handler->close($fd, new RequestParams($route[2]), $resolve);
                break;
        }
    }
}