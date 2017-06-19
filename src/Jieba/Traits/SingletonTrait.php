<?php

namespace Jieba\Traits;

trait SingletonTrait
{
    /**
     * @var $this
     */
    protected static $instance;

    /**
     * The constructor.
     * Ideally we should not allow to create a singleton object from outside since there should be only one instance of
     * it there; however, we still need provide developers with enough flexibility to define construct methods in a best
     * way they want.
     */
    // protected function __construct()
    // {
    // }

    /**
     * Not allow to clone the object since there should be only one instance of it there.
     */
    protected function __clone()
    {
    }

    /**
     * @return $this
     */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
