<?php

namespace app\routes;

// WebSocket commands by path
use app\routes\handlers\websockets\WSHelloHandler;

return [

    '/ws/{name}' => [
        'sayHello' => new WSHelloHandler()
    ],

];