<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
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
        return StringHelper::cut(
            $sentence,
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
        $words = [];

        $viterbi_array = $this->viterbi($sentence);
        $pos_list = $viterbi_array['pos_list'];

        $begin = 0;
        $next  = 0;
        $len   = mb_strlen($sentence);

        for ($i=0; $i<$len; $i++) {
            $char = mb_substr($sentence, $i, 1);
            switch ($pos_list[$i]) {
                case Constant::B:
                    $begin = $i;
                    break;
                case Constant::E:
                    array_push($words, mb_substr($sentence, $begin, (($i+1)-$begin)));
                    $next = $i+1;
                    break;
                case Constant::S:
                    array_push($words, $char);
                    $next = $i+1;
                    break;
                default:
                    break;
            }
        }

        if ($next < $len) {
            array_push($words, mb_substr($sentence, $next, null));
        }

        return $words;
    }

    /**
     * @param string $sentence
     * @return array
     */
    protected function viterbi(string $sentence): array
    {
        $obs  = $sentence;
        $V    = [];
        $V[0] = [];
        $path = [];

        foreach (Constant::BMES as $state) {
            $y = $state;
            $c = mb_substr($obs, 0, 1);
            if (isset($this->probEmit[$y][$c])) {
                $prob_emit = $this->probEmit[$y][$c];
            } else {
                $prob_emit = Constant::MIN_FLOAT;
            }
            $V[0][$y] = $this->probStart[$y] + $prob_emit;
            $path[$y] = $y;
        }

        for ($t=1; $t<mb_strlen($obs); $t++) {
            $c = mb_substr($obs, $t, 1);
            $V[$t] = [];
            $newpath = [];
            foreach (Constant::BMES as $state) {
                $y = $state;
                $temp_prob_array = [];
                foreach (Constant::BMES as $state0) {
                    $y0 = $state0;
                    if (isset($this->probTrans[$y0][$y])) {
                        $prob_trans = $this->probTrans[$y0][$y];
                    } else {
                        $prob_trans = Constant::MIN_FLOAT;
                    }
                    if (isset($this->probEmit[$y][$c])) {
                        $prob_emit = $this->probEmit[$y][$c];
                    } else {
                        $prob_emit = Constant::MIN_FLOAT;
                    }
                    $temp_prob_array[$y0] = $V[$t-1][$y0] + $prob_trans + $prob_emit;
                }
                arsort($temp_prob_array);
                $max_prob = reset($temp_prob_array);
                $max_key = key($temp_prob_array);
                $V[$t][$y] = $max_prob;
                if (is_array($path[$max_key])) {
                    $newpath[$y] = [];
                    foreach ($path[$max_key] as $path_value) {
                        array_push($newpath[$y], $path_value);
                    }
                    array_push($newpath[$y], $y);
                } else {
                    $newpath[$y] = array($path[$max_key], $y);
                }
            }
            $path = $newpath;
        }

        $es_states = [Constant::E, Constant::S];
        $temp_prob_array = [];
        $len = mb_strlen($obs);
        foreach ($es_states as $state) {
            $y = $state;
            $temp_prob_array[$y] = $V[$len-1][$y];
        }
        arsort($temp_prob_array);
        $prob = reset($temp_prob_array);
        $state = key($temp_prob_array);

        return [
            'prob'     => $prob,
            'pos_list' => $path[$state],
        ];
    }
}
