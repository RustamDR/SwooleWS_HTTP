<?php

namespace app\core\Contracts;

use app\core\Application\Application;

/**
 * Interface IBootstrap
 * @package app\core\Contracts
 */
interface IBootstrap
{
    /**
     * @param Application $app
     * @return mixed
     */
    public static function execute(Application $app);
}