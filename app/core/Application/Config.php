<?php

namespace app\core\Application;

/**
 * Application config
 * Class Config
 * @package app\core\Application
 */
class Config
{
    /** @var array */
    private $params = [];

    /**
     * Config constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }
}