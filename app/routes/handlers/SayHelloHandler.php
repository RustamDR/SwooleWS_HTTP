<?php
namespace app\routes\handlers;

use app\core\Application\RequestParams;

/**
 * Class HelloHandler
 * @package app\routes\handlers
 */
class SayHelloHandler extends BaseHandler
{
    /**
     * @param RequestParams $params
     * @param \swoole_http_request $request
     * @param callable $resolve
     */
    public function __invoke(RequestParams $params, \swoole_http_request $request, callable $resolve)
    {
        $resolve(200, "Hello {$params->name} from swoole!");
    }
}