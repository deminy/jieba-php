<?php

namespace Jieba\Factory;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Closure;
use Jieba\Exception;
use Jieba\Helper\Helper;
use Jieba\Options\Dict;

class CacheFactory
{
    const MODEL_PROB_START     = 'prob_start.json';
    const MODEL_PROB_TRANS     = 'prob_trans.json';
    const MODEL_PROB_EMIT      = 'prob_emit.json';
    const MODEL_POS_PROB_START = 'pos/prob_start.json';
    const MODEL_POS_PROB_TRANS = 'pos/prob_trans.json';
    const MODEL_POS_PROB_EMIT  = 'pos/prob_emit.json';
    const MODEL_POS_CHAR_STATE = 'pos/char_state.json';

    const MODEL_FILES = [
        self::MODEL_PROB_START     => self::MODEL_PROB_START,
        self::MODEL_PROB_TRANS     => self::MODEL_PROB_TRANS,
        self::MODEL_PROB_EMIT      => self::MODEL_PROB_EMIT,
        self::MODEL_POS_PROB_START => self::MODEL_POS_PROB_START,
        self::MODEL_POS_PROB_TRANS => self::MODEL_POS_PROB_TRANS,
        self::MODEL_POS_PROB_EMIT  => self::MODEL_POS_PROB_EMIT,
        self::MODEL_POS_CHAR_STATE => self::MODEL_POS_CHAR_STATE,
    ];

    /**
     * @var AbstractCachePool
     */
    protected static $cachePool;

    /**
     * @param AbstractCachePool $cachePool
     * @param Dict $dict
     * @param string|null $fileType
     * @return mixed
     * @throws Exception
     * @todo use more meaningful cache key instead of md5 values.
     */
    public static function getDict(AbstractCachePool $cachePool, Dict $dict, $fileType = null)
    {
        return self::get(
            $cachePool,
            md5($dict->getDictBaseName($fileType)),
            function () use ($dict, $fileType) {
                return $dict->getDictFileContent($fileType);
            }
        );
    }

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
