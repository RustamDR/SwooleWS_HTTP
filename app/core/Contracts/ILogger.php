<?php

namespace app\core\Contracts;

/**
 * Interface ILogger
 * @package app\core\Contracts
 */
interface ILogger
{
    /**
     * @param string $data
     * @param string $level
     */
    public function log(string $data, string $level = 'info'): void;

    /**
     * @return mixed
     */
    public function flush(): void;
}