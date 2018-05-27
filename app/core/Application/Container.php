<?php

namespace app\core\Application;

/**
 * Simplest container
 * Class Container
 * @package app\core\Application
 */
class Container
{
    /** @var null|self  */
    protected static $_instance = null;
    /** @var array  */
    protected $singletons = [];

    /**
     * Container constructor.
     */
    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * @return Container
     */
    public static function instance(): self
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new self());
    }

    /**
     * @param string $alias
     * @param $object
     */
    public function setSingleton(string $alias, $object): void
    {
        $hash = $this->hash($alias);
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Singleton must be object');
        }
        $this->singletons[$hash] = $object;
    }

    /**
     * @param string $alias
     * @return mixed
     */
    public static function get(string $alias)
    {
        return self::instance()->getByAlias($alias);
    }

    /**
     * @param string $alias
     * @return mixed
     */
    public function getByAlias(string $alias)
    {
        $hash = $this->hash($alias);

        return $this->singletons[$hash];
    }

    /**
     * @param string $alias
     * @return string
     */
    protected function hash(string $alias): string
    {
        return md5($alias);
    }
}