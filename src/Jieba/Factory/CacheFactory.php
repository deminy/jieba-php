<?php

namespace Jieba\Factory;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Closure;
use Jieba\Exception;
use Jieba\Helper\Helper;

class CacheFactory
{
    const FINALSEG_PROB_START = 'finalseg/prob_start';
    const FINALSEG_PROB_TRANS = 'finalseg/prob_trans';
    const FINALSEG_PROB_EMIT  = 'finalseg/prob_emit';
    const POSSEG_PROB_START   = 'posseg/prob_start';
    const POSSEG_PROB_TRANS   = 'posseg/prob_trans';
    const POSSEG_PROB_EMIT    = 'posseg/prob_emit';
    const POSSEG_CHAR_STATE   = 'posseg/char_state_tab';

    const FINALSEG_FILES = [
        self::FINALSEG_PROB_START => self::FINALSEG_PROB_START,
        self::FINALSEG_PROB_TRANS => self::FINALSEG_PROB_TRANS,
        self::FINALSEG_PROB_EMIT  => self::FINALSEG_PROB_EMIT,
    ];

    const POSSEG_FILES = [
        self::POSSEG_PROB_START   => self::POSSEG_PROB_START,
        self::POSSEG_PROB_TRANS   => self::POSSEG_PROB_TRANS,
        self::POSSEG_PROB_EMIT    => self::POSSEG_PROB_EMIT,
        self::POSSEG_CHAR_STATE   => self::POSSEG_CHAR_STATE,
    ];

    const MODEL_FILES = [
        self::FINALSEG_PROB_START => self::FINALSEG_PROB_START,
        self::FINALSEG_PROB_TRANS => self::FINALSEG_PROB_TRANS,
        self::FINALSEG_PROB_EMIT  => self::FINALSEG_PROB_EMIT,
        self::POSSEG_PROB_START   => self::POSSEG_PROB_START,
        self::POSSEG_PROB_TRANS   => self::POSSEG_PROB_TRANS,
        self::POSSEG_PROB_EMIT    => self::POSSEG_PROB_EMIT,
        self::POSSEG_CHAR_STATE   => self::POSSEG_CHAR_STATE,
    ];

    /**
     * @var AbstractCachePool
     */
    protected static $cachePool;

    /**
     * @param AbstractCachePool $cachePool
     * @param string $basename
     * @return mixed
     * @throws Exception
     * @todo use more meaningful cache key instead of md5 values.
     */
    public static function getModel(AbstractCachePool $cachePool, string $basename)
    {
        if (!array_key_exists($basename, self::MODEL_FILES)) {
            throw new Exception("undefined model file '{$basename}'");
        }

        return self::get(
            $cachePool,
            md5($basename),
            function () use ($basename) {
                return Helper::loadModel($basename);
            }
        );
    }

    /**
     * @param AbstractCachePool $cachePool
     * @param string $key
     * @param Closure $op
     * @param array ...$params
     * @return mixed
     * @throws Exception
     * @todo set expiry for cached data
     * @todo "$cachePool->getItem();" could fail after "$cachePool->hasItem();" since they are not atomic.
     */
    public static function get(AbstractCachePool $cachePool, string $key, Closure $op, ...$params)
    {
        if (!$cachePool->hasItem($key)) {
            $item = $cachePool->getItem($key);
            $value = $op(...$params);
            $item->set($value);
            $cachePool->save($item);

            return $value;
        }

        return $cachePool->getItem($key)->get();
    }

    /**
     * @return AbstractCachePool
     */
    public static function getCachePool(): AbstractCachePool
    {
        if (empty(self::$cachePool)) {
            self::$cachePool = new ArrayCachePool();
        }

        return self::$cachePool;
    }

    /**
     * @param AbstractCachePool $cachePool
     */
    public static function setCachePool(AbstractCachePool $cachePool)
    {
        self::$cachePool = $cachePool;
    }
}
