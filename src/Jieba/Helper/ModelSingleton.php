<?php

namespace Jieba\Helper;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Factory\CacheFactory;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\SingletonTrait;

/**
 * Class ModelHelper
 *
 * @package Jieba\Helper
 */
class ModelSingleton
{
    use CachePoolTrait, SingletonTrait;

    /**
     * @var array
     */
    protected $posCharState;
    /**
     * @var array
     */
    protected $posProbEmit;
    /**
     * @var array
     */
    protected $posProbStart;
    /**
     * @var array
     */
    protected $posProbTrans;
    /**
     * @var array
     */
    protected $probEmit;
    /**
     * @var array
     */
    protected $probStart;
    /**
     * @var array
     */
    protected $probTrans;

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
     * @return array
     */
    public function getPosCharState(): array
    {
        if (empty($this->posCharState)) {
            $this->setPosCharState(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_CHAR_STATE));
        }

        return $this->posCharState;
    }

    /**
     * @param array $posCharState
     * @return ModelSingleton
     */
    public function setPosCharState(array $posCharState): ModelSingleton
    {
        $this->posCharState = $posCharState;

        return $this;
    }

    /**
     * @return array
     */
    public function getPosProbEmit(): array
    {
        if (empty($this->posProbEmit)) {
            $this->setPosProbEmit(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_EMIT));
        }

        return $this->posProbEmit;
    }

    /**
     * @param array $posProbEmit
     * @return ModelSingleton
     */
    public function setPosProbEmit(array $posProbEmit): ModelSingleton
    {
        $this->posProbEmit = $posProbEmit;

        return $this;
    }

    /**
     * @return array
     */
    public function getPosProbStart(): array
    {
        if (empty($this->posProbStart)) {
            $this->setPosProbStart(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_START));
        }

        return $this->posProbStart;
    }

    /**
     * @param array $posProbStart
     * @return ModelSingleton
     */
    public function setPosProbStart(array $posProbStart): ModelSingleton
    {
        $this->posProbStart = $posProbStart;

        return $this;
    }

    /**
     * @return array
     */
    public function getPosProbTrans(): array
    {
        if (empty($this->posProbTrans)) {
            $this->setPosProbTrans(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_TRANS));
        }

        return $this->posProbTrans;
    }

    /**
     * @param array $posProbTrans
     * @return ModelSingleton
     */
    public function setPosProbTrans(array $posProbTrans): ModelSingleton
    {
        $this->posProbTrans = $posProbTrans;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbEmit(): array
    {
        if (empty($this->probEmit)) {
            $this->setProbEmit(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_PROB_EMIT));
        }

        return $this->probEmit;
    }

    /**
     * @param array $probEmit
     * @return ModelSingleton
     */
    public function setProbEmit(array $probEmit): ModelSingleton
    {
        $this->probEmit = $probEmit;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbStart(): array
    {
        if (empty($this->probStart)) {
            $this->setProbStart(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_PROB_START));
        }

        return $this->probStart;
    }

    /**
     * @param array $probStart
     * @return ModelSingleton
     */
    public function setProbStart(array $probStart): ModelSingleton
    {
        $this->probStart = $probStart;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbTrans(): array
    {
        if (empty($this->probTrans)) {
            $this->setProbTrans(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_PROB_TRANS));
        }

        return $this->probTrans;
    }

    /**
     * @param array $probTrans
     * @return ModelSingleton
     */
    public function setProbTrans(array $probTrans): ModelSingleton
    {
        $this->probTrans = $probTrans;

        return $this;
    }
}
