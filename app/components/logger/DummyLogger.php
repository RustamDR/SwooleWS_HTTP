<?php

namespace app\components\logger;

use app\core\Contracts\ILogger;

/**
 * Class DummyLogger
 * @package app\components\logger
 */
class DummyLogger implements ILogger
{
    /**
     * @inheritdoc
     */
    public function log(string $data, string $level = 'INFO'): void
    {
        echo $data . PHP_EOL;
    }

    public function flush(): void
    {
        // TODO: Implement flush() method.
    }
}