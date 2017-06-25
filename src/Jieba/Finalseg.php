<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Constants\JiebaConstant;
use Jieba\Data\MultiByteString;
use Jieba\Data\TopArrayElement;
use Jieba\Data\Viterbi;
use Jieba\Factory\CacheFactory;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\SingletonTrait;

/**
 * Class Finalseg
 *
 * @package Jieba
 */
class Finalseg
{
    use CachePoolTrait, SingletonTrait;

    /**
     * @var array
     */
    protected $probStart = [];
    /**
     * @var array
     */
    protected $probTrans = [];
    /**
     * @var array
     */
    protected $probEmit  = [];

    /**
     * Finalseg constructor.
     * @param AbstractCachePool|null $cachePool
     */
    protected function __construct(AbstractCachePool $cachePool = null)
    {
        $this
            ->setCachePool($cachePool ?: CacheFactory::getCachePool())
            ->setProbStart(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_PROB_START))
            ->setProbTrans(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_PROB_TRANS))
            ->setProbEmit(CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_PROB_EMIT));
    }

    /**
     * Cut given sentence to an array of individual Chinese and non-Chinese characters.
     * @param string $sentence
     * @return array
     */
    public function cut(string $sentence): array
    {
        return (new MultiByteString($sentence))->cut(
            function (string $blk) {
                return $this->__cut($blk);
            }
        );
    }

    /**
     * @return array
     */
    public function getProbStart(): array
    {
        return $this->probStart;
    }

    /**
     * @param array $probStart
     * @return $this
     */
    public function setProbStart(array $probStart): Finalseg
    {
        $this->probStart = $probStart;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbTrans(): array
    {
        return $this->probTrans;
    }

    /**
     * @param array $probTrans
     * @return $this
     */
    public function setProbTrans(array $probTrans): Finalseg
    {
        $this->probTrans = $probTrans;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbEmit(): array
    {
        return $this->probEmit;
    }

    /**
     * @param array $probEmit
     * @return $this
     */
    public function setProbEmit(array $probEmit): Finalseg
    {
        $this->probEmit = $probEmit;

        return $this;
    }

    /**
     * @param string $sentence
     * @return array
     */
    protected function __cut(string $sentence): array
    {
        $viterbi = $this->viterbi($sentence);

        $begin = 0;
        $next  = 0;
        $len   = mb_strlen($sentence);
        $words = [];
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($sentence, $i, 1);
            switch ($viterbi->getPositionAt($i)) {
                case JiebaConstant::B:
                    $begin = $i;
                    break;
                case JiebaConstant::E:
                    $words[] = mb_substr($sentence, $begin, (($i + 1) - $begin));
                    $next    = $i + 1;
                    break;
                case JiebaConstant::S:
                    $words[] = $char;
                    $next    = $i + 1;
                    break;
                case JiebaConstant::M:
                default:
                    break;
            }
        }

        if ($next < $len) {
            $words[] = mb_substr($sentence, $next);
        }

        return $words;
    }

    /**
     * @param string $sentence
     * @return Viterbi
     */
    protected function viterbi(string $sentence): Viterbi
    {
        $obs  = $sentence;
        $V    = [];
        $V[0] = [];
        $path = [];

        foreach (JiebaConstant::BMES as $state) {
            $c            = mb_substr($obs, 0, 1);
            $prob_emit    = ($this->probEmit[$state][$c] ?? JiebaConstant::MIN_FLOAT);
            $V[0][$state] = $this->probStart[$state] + $prob_emit;
            $path[$state] = $state;
        }

        for ($t = 1; $t < mb_strlen($obs); $t++) {
            $c       = mb_substr($obs, $t, 1);
            $V[$t]   = [];
            $newPath = [];
            foreach (JiebaConstant::BMES as $state) {
                $temp_prob_array = [];
                foreach (JiebaConstant::BMES as $state0) {
                    $prob_trans = ($this->probTrans[$state0][$state] ?? JiebaConstant::MIN_FLOAT);
                    $prob_emit  = ($this->probEmit[$state][$c] ?? JiebaConstant::MIN_FLOAT);
                    $temp_prob_array[$state0] = $V[$t-1][$state0] + $prob_trans + $prob_emit;
                }
                $top               = new TopArrayElement($temp_prob_array);
                $maxKey            = $top->getKey();
                $V[$t][$state]     = $top->getValue(); // maximum probability
                $newPath[$state]   = (is_array($path[$maxKey]) ? array_values($path[$maxKey]) : [$path[$maxKey]]);
                $newPath[$state][] = $state;
            }
            $path = $newPath;
        }

        if ($V[mb_strlen($obs) - 1][JiebaConstant::E] >= $V[mb_strlen($obs) - 1][JiebaConstant::S]) {
            $state = JiebaConstant::E;
        } else {
            $state = JiebaConstant::S;
        }

        return new Viterbi($V[mb_strlen($obs) - 1][$state], $path[$state]);
    }
}
