<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Data\MultiArray;
use Jieba\Data\MultiByteString;
use Jieba\Data\TopArrayElement;
use Jieba\Data\Word;
use Jieba\Data\Words;
use Jieba\Factory\CacheFactory;
use Jieba\Factory\LoggerFactory;
use Jieba\Helper\DictHelper;
use Jieba\Helper\DictSingleton;
use Jieba\Helper\Helper;
use Jieba\Options\Dict;
use Jieba\Options\Options;
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
        $this->__calculateFrequency();

        return $this;
    }

    /**
     * @return Jieba
     */
    protected function __calculateFrequency(): Jieba
    {
        $this->FREQ     = DictHelper::calculateFrequency($this->original_freq, $this->total) + $this->FREQ;
        $this->min_freq = min($this->FREQ);

        return $this;
    }

    /**
     * @param string $sentence
     * @param array $DAG
     * @return array
     */
    public function calc(string $sentence, array $DAG): array
    {
        $N = mb_strlen($sentence);
        $this->route = [
            $N => [$N => 1.0],
        ];
        for ($i = ($N - 1); $i >= 0; $i--) {
            $candidates = [];
            foreach ($DAG[$i] as $x) {
                $w_c            = mb_substr($sentence, $i, (($x + 1) - $i));
                $previous_freq  = current($this->route[$x + 1]);
                $current_freq   = (float) $previous_freq + ($this->FREQ[$w_c] ?? $this->min_freq);
                $candidates[$x] = $current_freq;
            }
            $top             = new TopArrayElement($candidates);
            $this->route[$i] = [$top->getKey() => $top->getValue()];
        }

        return $this->route;
    }

    /**
     * @param Dict $dict
     * @return MultiArray
     */
    public function genTrie(Dict $dict): MultiArray
    {
        $this->trie        = new MultiArray(DictSingleton::singleton()->getDict($dict, Dict::SERIALIZED));
        $this->trie->cache = new MultiArray(DictSingleton::singleton()->getDict($dict, Dict::SERIALIZED_AND_CACHED));

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
                $this->trie->set((new MultiByteString($word))->buildMultiArrayKey(), ['end' => '']);
            }
        );

        $this->__calculateFrequency();

        return $this->trie;
    }

    /**
     * @param string $sentence
     * @return Words
     */
    protected function __cutAll(string $sentence): Words
    {
        $old_j = -1;
        $words = new Words();
        foreach ($this->getDAG($sentence) as $k => $L) {
            if (count($L) == 1 && $k > $old_j) {
                $words->addWord(new Word(mb_substr($sentence, $k, (($L[0] - $k) + 1))));
                $old_j = $L[0];
            } else {
                foreach ($L as $j) {
                    if ($j > $k) {
                        $words->addWord(new Word(mb_substr($sentence, $k, ($j - $k) + 1)));
                        $old_j = $j;
                    }
                }
            }
        }

        return $words;
    }

    /**
     * @param string $sentence
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
            $c             = mb_substr($sentence, $j, 1);
            $next_word_key = ($word_c ? (implode('.', $word_c) . '.' . $c) : $c);

            if ($this->trie->exists($next_word_key)) {
                array_push($word_c, $c);
                $next_word_key_value = $this->trie->get($next_word_key);
                if ($next_word_key_value == ['end' => '']
                 || isset($next_word_key_value['end'])
                 || isset($next_word_key_value[0]['end'])
                ) {
                    $DAG[$i]   = $DAG[$i] ?? [];
                    $DAG[$i][] = $j;
                }
                $j++;
                if ($j >= $N) {
                    $word_c = [];
                    $j      = ++$i;
                }
            } else {
                $word_c = [];
                $j      = ++$i;
            }
        }

        for ($i = 0; $i < $N; $i++) {
            $DAG[$i] = $DAG[$i] ?? [$i];
        }

        return $DAG;
    }

    /**
     * @param string $sentence
     * @return Words
     */
    protected function __cutDAG(string $sentence): Words
    {
        $N = mb_strlen($sentence);
        $DAG = $this->getDAG($sentence);

        $this->calc($sentence, $DAG);

        $x   = 0;
        $buf = '';

        $words = new Words();
        while ($x < $N) {
            $current_route_keys = array_keys($this->route[$x]);
            $y = $current_route_keys[0] + 1;
            $l_word = mb_substr($sentence, $x, ($y - $x));

            if (($y - $x) == 1) {
                $buf .= $l_word;
            } else {
                if (!empty($buf)) {
                    if (mb_strlen($buf) == 1) {
                        $words->addWord(new Word($buf));
                    } else {
                        foreach (Finalseg::singleton()->cut($buf) as $word) {
                            $words->addWord(new Word($word));
                        }
                    }
                    $buf = '';
                }
                $words->addWord(new Word($l_word));
            }
            $x = $y;
        }

        if (!empty($buf)) {
            if (mb_strlen($buf) == 1) {
                $words->addWord(new Word($buf));
            } else {
                foreach (Finalseg::singleton()->cut($buf) as $word) {
                    $words->addWord(new Word($word));
                }
            }
        }

        return $words;
    }

    /**
     * @param string $sentence
     * @param boolean $cutAll
     * @return array
     */
    public function cut(string $sentence, bool $cutAll = false): array
    {
        return (new MultiByteString($sentence))->cut(
            function (string $blk) use ($cutAll) {
                return ($cutAll ? $this->__cutAll($blk) : $this->__cutDAG($blk));
            }
        );
    }

    /**
     * @param string $sentence
     * @return array
     */
    public function cutForSearch(string $sentence): array
    {
        $cut_seg_list = $this->cut($sentence);

        $seg_list = [];
        foreach ($cut_seg_list as $w) {
            $len = mb_strlen($w);

            if ($len > 2) {
                for ($i = 0; $i < ($len - 1); $i++) {
                    $gram2 = mb_substr($w, $i, 2);

                    if (isset($this->FREQ[$gram2])) {
                        $seg_list[] = $gram2;
                    }
                }
            }

            if ($len > 3) {
                for ($i = 0; $i < ($len - 2); $i++) {
                    $gram3 = mb_substr($w, $i, 3);

                    if (isset($this->FREQ[$gram3])) {
                        $seg_list[] = $gram3;
                    }
                }
            }

            $seg_list[] = $w;
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
