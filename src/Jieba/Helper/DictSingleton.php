<?php

namespace Jieba\Helper;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Factory\CacheFactory;
use Jieba\Options\Dict;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\SingletonTrait;

/**
 * Class DictSingleton
 *
 * @package Jieba\Helper
 */
class DictSingleton
{
    use CachePoolTrait, SingletonTrait;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * ModelHelper constructor.
     *
     * @param AbstractCachePool|null $cachePool
     */
    protected function __construct(AbstractCachePool $cachePool = null)
    {
        $this->setCachePool($cachePool ?: CacheFactory::getCachePool());
    }

    /**
     * @param Dict $dict
     * @param string|null $fileType
     * @return array
     * @todo use more meaningful cache key instead of md5 values.
     */
    public function getDict(Dict $dict, string $fileType = null): array
    {
        $key = md5($dict->getDictBaseName($fileType));
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = CacheFactory::get(
                $this->getCachePool(),
                $key,
                function () use ($dict, $fileType) {
                    return $dict->getDictFileContent($fileType);
                }
            );
        }

        return $this->data[$key];
    }
}
