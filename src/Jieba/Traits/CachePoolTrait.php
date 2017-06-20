<?php

namespace Jieba\Traits;

use Cache\Adapter\Common\AbstractCachePool;

trait CachePoolTrait
{
    /**
     * @var AbstractCachePool
     */
    protected $cachePool;

    /**
     * @return AbstractCachePool
     */
    public function getCachePool()
    {
        return $this->cachePool;
    }

    /**
     * @param AbstractCachePool $cachePool
     * @return $this
     */
    public function setCachePool(AbstractCachePool $cachePool)
    {
        $this->cachePool = $cachePool;

        return $this;
    }
}
