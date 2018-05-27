<?php

namespace app\routes;

use app\routes\handlers\SayHelloHandler;
use app\routes\handlers\WSConnect;

return [

    'POST' => [
    ],

    'GET' => [
        '/{name}' => new SayHelloHandler(),
        '/ws/{name}' => new WSConnect(),
    ],

    'PUT' => [
    ],

];