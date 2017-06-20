<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\SingletonTrait;

/**
 * Class JiebaAnalyse
 *
 * @package Jieba
 */
class JiebaAnalyse
{
    use CachePoolTrait, SingletonTrait;

    /**
     * @var array
     */
    protected $idfFreq = [];

    /**
     * @var int
     */
    protected $maxIdf  = 0;

    /**
     * JiebaAnalyse constructor.
     * @param AbstractCachePool|null $cachePool
     */
    protected function __construct(AbstractCachePool $cachePool = null)
    {
        $this->setCachePool($cachePool ?: CacheFactory::getCachePool());

        $this->idfFreq = CacheFactory::get(
            $this->getCachePool(),
            ('idf-' . md5(__CLASS__)),
            function () {
                return DictHelper::getIdfFreq('idf.txt');
            }
        );

        $this->maxIdf = max($this->idfFreq);
    }

    /**
     * @param array $words Return value of method call Jieba::cut($content).
     * @param int    $top_k   # top_k
     * @return array
     * @see \Jieba\Jieba::cut()
     */
    public function extractTags(array $words, int $top_k = 20): array
    {
        $freq  = [];
        $total = 0.0;

        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w, 'UTF-8') < 2) {
                continue;
            }
            if (isset($freq[$w])) {
                $freq[$w] = $freq[$w] + 1.0;
            } else {
                $freq[$w] = 0.0 + 1.0;
            }
            $total = $total + 1.0;
        }

        foreach ($freq as $k => $v) {
            $freq[$k] = $v/$total;
        }

        $tf_idf_list = [];

        foreach ($freq as $k => $v) {
            if (isset($this->idfFreq[$k])) {
                $idf_freq = $this->idfFreq[$k];
            } else {
                $idf_freq = $this->maxIdf;
            }
            $tf_idf_list[$k] = $v * $idf_freq;
        }

        arsort($tf_idf_list);

        $tags = array_slice($tf_idf_list, 0, $top_k, true);

        return $tags;
    }

    /**
     * @return array
     */
    public function getIdfFreq(): array
    {
        return $this->idfFreq;
    }

    /**
     * @param array $idfFreq
     * @return JiebaAnalyse
     */
    public function setIdfFreq(array $idfFreq): JiebaAnalyse
    {
        $this->idfFreq = $idfFreq;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxIdf(): int
    {
        return $this->maxIdf;
    }

    /**
     * @param int $maxIdf
     * @return JiebaAnalyse
     */
    public function setMaxIdf(int $maxIdf): JiebaAnalyse
    {
        $this->maxIdf = $maxIdf;

        return $this;
    }
}
