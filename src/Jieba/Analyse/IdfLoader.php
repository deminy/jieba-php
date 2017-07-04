<?php

namespace Jieba\Analyse;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Factory\CacheFactory;
use Jieba\Helper\DictHelper;
use Jieba\Traits\CachePoolTrait;

/**
 * Class IdfLoader
 *
 * @package Jieba\Analyse
 * @see https://github.com/fxsjy/jieba/blob/v0.36/jieba/analyse/__init__.py
 */
class IdfLoader
{
    use CachePoolTrait;

    /**
     * @var array
     */
    protected $idfFreq;

    /**
     * @var float
     */
    protected $medianIdf;

    /**
     * @var float
     */
    protected $maxIdf;

    /**
     * Analyse constructor.
     * @param AbstractCachePool|null $cachePool
     */
    public function __construct(AbstractCachePool $cachePool = null)
    {
        $this
            ->setCachePool($cachePool ?: CacheFactory::getCachePool())
            ->setIdfFreq(
                CacheFactory::get(
                    $this->getCachePool(),
                    ('idf_' . md5(__CLASS__)),
                    function () {
                        return DictHelper::getIdfFreq();
                    }
                )
            );
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
     * @return IdfLoader
     */
    public function setIdfFreq(array $idfFreq): IdfLoader
    {
        $this->idfFreq = $idfFreq;
        $this->setMedianIdf()->setMaxIdf();

        return $this;
    }

    /**
     * @return float
     */
    public function getMedianIdf(): float
    {
        return $this->medianIdf;
    }

    /**
     * @return int
     */
    public function getMaxIdf(): int
    {
        return $this->maxIdf;
    }

    /**
     * @return IdfLoader
     * @todo add unit tests
     */
    protected function setMedianIdf(): IdfLoader
    {
        $count = count($this->idfFreq);
        switch ($count) {
            case 0:
                $this->medianIdf = 0.0;
                break;
            case 1:
                $this->medianIdf = reset($this->idfFreq);
                break;
            default:
                $idfFreq = $this->idfFreq;
                sort($idfFreq);
                $index = floor(($count - 1) / 2);
                if ($count % 2) {
                    $this->medianIdf = $this->idfFreq[$index];
                } else {
                    $this->medianIdf = (($this->idfFreq[$index] + $this->idfFreq[$index + 1]) / 2);
                }
                break;
        }

        return $this;
    }

    /**
     * @return IdfLoader
     */
    protected function setMaxIdf(): IdfLoader
    {
        $this->maxIdf = $this->getIdfFreq() ? max($this->getIdfFreq()) : 0.0;

        return $this;
    }
}
