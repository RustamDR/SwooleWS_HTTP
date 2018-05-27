<?php

namespace app\core\Application;

/**
 * Class RequestParams
 * @package app\routes\handlers
 * @property int $fd
 * @property string $name
 */
class RequestParams
{
    /** @var array */
    private $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    protected function getParamFromArgs(string $name)
    {
        if (isset($this->args[$name])) {
            $value = $this->args[$name];
            if (preg_match('#^\d+$#', $value)) {
                $value = (int)$value;
            }

            return $value;
        }

        throw new \Exception("{$name} param not found in request");
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        return $this->getParamFromArgs($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->args[$name] = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->args);
    }
}