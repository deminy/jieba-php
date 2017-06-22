<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Option\Dict;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\LoggerTrait;
use Jieba\Traits\OptionsTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Jieba
 *
 * @package Jieba
 */
class Jieba
{
    use CachePoolTrait, LoggerTrait, OptionsTrait;

    /**
     * @var float
     */
    public $total = 0.0;
    /**
     * @var MultiArray
     */
    public $trie;
    /**
     * @var array
     */
    public $FREQ = [];
    /**
     * @var array
     */
    public $original_freq = [];
    /**
     * @var float
     */
    public $min_freq = 0.0;
    /**
     * @var array
     */
    protected $route = [];

    /**
     * Jieba constructor.
     *
     * @param Options $options
     * @param AbstractCachePool|null $cachePool
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Options $options = null,
        AbstractCachePool $cachePool = null,
        LoggerInterface $logger = null
    ) {
        $this->trie = new MultiArray();
        $this
            ->setOptions(($options ?: new Options()))
            ->setCachePool($cachePool ?: CacheFactory::getCachePool())
            ->setLogger($logger ?: LoggerFactory::getLogger())
            ->init();
    }

    /**
     * @return Jieba
     */
    public function init(): Jieba
    {
        $this->trie = $this->genTrie($this->options->getDict());
        $this->__calcFreq();

        return $this;
    }

    /**
     * @return Jieba
     */
    protected function __calcFreq(): Jieba
    {
        foreach ($this->original_freq as $key => $value) {
            $this->FREQ[$key] = log($value / $this->total);
        }
        $this->min_freq = min($this->FREQ);

        return $this;
    }

    /**
     * @param string $sentence # input sentence
     * @param array  $DAG      # DAG
     * @return array
     */
    public function calc(string $sentence, array $DAG): array
    {
        $N = mb_strlen($sentence);
        $this->route = [];
        $this->route[$N] = array($N => 1.0);
        for ($i=($N-1); $i>=0; $i--) {
            $candidates = [];
            foreach ($DAG[$i] as $x) {
                $w_c = mb_substr($sentence, $i, (($x+1)-$i));
                $previous_freq = current($this->route[$x+1]);
                if (isset($this->FREQ[$w_c])) {
                    $current_freq = (float) $previous_freq + $this->FREQ[$w_c];
                } else {
                    $current_freq = (float) $previous_freq + $this->min_freq;
                }
                $candidates[$x] = $current_freq;
            }
            arsort($candidates);
            $max_prob = reset($candidates);
            $max_key = key($candidates);
            $this->route[$i] = array($max_key => $max_prob);
        }

        return $this->route;
    }

    /**
     * @param Dict $dict
     * @return MultiArray
     */
    public function genTrie(Dict $dict): MultiArray
    {
        $this->trie        = new MultiArray(CacheFactory::getDict($this->getCachePool(), $dict, Dict::EXT_JSON));
        $this->trie->cache = new MultiArray(CacheFactory::getDict($this->getCachePool(), $dict, Dict::EXT_CACHE_JSON));

        Helper::readFile(
            $dict->getDictFilePath(),
            function (string $line) {
                DictHelper::readDictLine($line, $word, $this->original_freq, $this->total);
            }
        );

        return $this->trie;
    }

    /**
     * @param string $userDictName
     * @return MultiArray
     */
    public function loadUserDict(string $userDictName): MultiArray
    {
        Helper::addUserDictName($userDictName);

        Helper::readFile(
            $userDictName,
            function (string $line) {
                DictHelper::readDictLine($line, $word, $this->original_freq, $this->total);

                $l = mb_strlen($word);
                $word_c = [];
                for ($i = 0; $i < $l; $i++) {
                    $c = mb_substr($word, $i, 1);
                    array_push($word_c, $c);
                }
                $word_c_key = implode('.', $word_c);
                $this->trie->set($word_c_key, ['end' => '']);
            }
        );

        $this->__calcFreq();

        return $this->trie;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    protected function __cutAll(string $sentence): array
    {
        $words = [];

        $DAG = $this->getDAG($sentence);
        $old_j = -1;

        foreach ($DAG as $k => $L) {
            if (count($L) == 1 && $k > $old_j) {
                $word = mb_substr($sentence, $k, (($L[0]-$k)+1));
                array_push($words, $word);
                $old_j = $L[0];
            } else {
                foreach ($L as $j) {
                    if ($j > $k) {
                        $word = mb_substr($sentence, $k, ($j-$k)+1);
                        array_push($words, $word);
                        $old_j = $j;
                    }
                }
            }
        }

        return $words;
    }

    /**
     * Static method getDAG
     *
     * @param string $sentence # input sentence
     * @return array
     */
    public function getDAG(string $sentence): array
    {
        $N = mb_strlen($sentence);
        $i = 0;
        $j = 0;
        $DAG = [];
        $word_c = [];

        while ($i < $N) {
            $c = mb_substr($sentence, $j, 1);
            if (count($word_c)==0) {
                $next_word_key = $c;
            } else {
                $next_word_key = implode('.', $word_c).'.'.$c;
            }

            if ($this->trie->exists($next_word_key)) {
                array_push($word_c, $c);
                $next_word_key_value = $this->trie->get($next_word_key);
                if ($next_word_key_value == array("end"=>"")
                 || isset($next_word_key_value["end"])
                 || isset($next_word_key_value[0]["end"])
                ) {
                    if (!isset($DAG[$i])) {
                        $DAG[$i] = [];
                    }
                    array_push($DAG[$i], $j);
                }
                $j += 1;
                if ($j >= $N) {
                    $word_c = [];
                    $i += 1;
                    $j = $i;
                }
            } else {
                $word_c = [];
                $i += 1;
                $j = $i;
            }
        }

        for ($i=0; $i<$N; $i++) {
            if (!isset($DAG[$i])) {
                $DAG[$i] = array($i);
            }
        }

        return $DAG;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    protected function __cutDAG(string $sentence): array
    {
        $words = [];

        $N = mb_strlen($sentence);
        $DAG = $this->getDAG($sentence);

        $this->calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys($this->route[$x]);
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x));

            if (($y-$x)==1) {
                $buf = $buf.$l_word;
            } else {
                if (mb_strlen($buf)>0) {
                    if (mb_strlen($buf)==1) {
                        array_push($words, $buf);
                        $buf = '';
                    } else {
                        $regognized = Finalseg::singleton()->cut($buf);
                        foreach ($regognized as $key => $word) {
                            array_push($words, $word);
                        }
                        $buf = '';
                    }
                }
                array_push($words, $l_word);
            }
            $x = $y;
        }

        if (mb_strlen($buf)>0) {
            if (mb_strlen($buf)==1) {
                array_push($words, $buf);
            } else {
                $regognized = Finalseg::singleton()->cut($buf);
                foreach ($regognized as $key => $word) {
                    array_push($words, $word);
                }
            }
        }

        return $words;
    }

    /**
     * @param string  $sentence # input sentence
     * @param boolean $cut_all  # cut_all or not
     * @return array
     */
    public function cut(string $sentence, bool $cut_all = false): array
    {
        return StringHelper::cut(
            $sentence,
            function (string $blk) use ($cut_all) {
                return $cut_all ? $this->__cutAll($blk) : $this->__cutDAG($blk);
            }
        );
    }

    /**
     * @param string $sentence
     * @return array
     */
    public function cutForSearch(string $sentence): array
    {
        $seg_list = [];

        $cut_seg_list = $this->cut($sentence);

        foreach ($cut_seg_list as $w) {
            $len = mb_strlen($w);

            if ($len>2) {
                for ($i=0; $i<($len-1); $i++) {
                    $gram2 = mb_substr($w, $i, 2);

                    if (isset($this->FREQ[$gram2])) {
                        array_push($seg_list, $gram2);
                    }
                }
            }

            if (mb_strlen($w)>3) {
                for ($i=0; $i<($len-2); $i++) {
                    $gram3 = mb_substr($w, $i, 3);

                    if (isset($this->FREQ[$gram3])) {
                        array_push($seg_list, $gram3);
                    }
                }
            }

            array_push($seg_list, $w);
        }

        return $seg_list;
    }

    /**
     * @return array
     */
    public function getRoute(): array
    {
        return $this->route;
    }

    /**
     * @param int $key
     * @return array
     */
    public function getRouteByKey(int $key): array
    {
        return (array_key_exists($key, $this->route) ? $this->route[$key] : []);
    }

    /**
     * @param array $route
     * @return Jieba
     */
    public function setRoute(array $route): Jieba
    {
        $this->route = $route;

        return $this;
    }
}
