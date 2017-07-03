<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Closure;
use Jieba\Constants\JiebaConstant;
use Jieba\Constants\PosTagConstant;
use Jieba\Data\TopArrayElement;
use Jieba\Data\MultiByteString;
use Jieba\Data\TaggedWord;
use Jieba\Data\Viterbi;
use Jieba\Data\Words;
use Jieba\Factory\CacheFactory;
use Jieba\Helper\DictHelper;
use Jieba\Helper\Helper;
use Jieba\Helper\ModelSingleton;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\LoggerTrait;

/**
 * Class Posseg
 *
 * @package Jieba
 */
class Posseg
{
    use CachePoolTrait, LoggerTrait;

    /**
     * @var Jieba
     */
    protected $jieba;

    /**
     * @var array
     */
    public $word_tag = [];

    /**
     * Posseg constructor.
     *
     * @param Jieba $jieba
     * @param AbstractCachePool|null $cachePool
     */
    public function __construct(Jieba $jieba, AbstractCachePool $cachePool = null)
    {
        $this
            ->setJieba($jieba)
            ->setLogger($this->jieba->getLogger())
            ->setCachePool($cachePool ?: CacheFactory::getCachePool())
            ->init();
    }

    /**
     * @return Posseg
     */
    protected function init(): Posseg
    {
        // TODO: Here property \Jieba::$dictname was used.
        // TODO: performance improvement with cache
        // @see \Jieba::$dictname
        // @see https://bitbucket.org/deminy/jieba-php/src/005f8d6440fd55189f386ccfe438ec6ac41c53c4/src/Jieba/Posseg.php?at=old&fileviewer=file-view-default#Posseg.php-39
        DictHelper::addWordTags($this->getJieba()->getOptions()->getDict()->getDictFilePath(), $this->word_tag);

        // TODO: Here property \Jieba::$user_dictname was used.
        // TODO: performance improvement with cache
        // @see \Jieba::user_dictname
        // @see https://bitbucket.org/deminy/jieba-php/src/005f8d6440fd55189f386ccfe438ec6ac41c53c4/src/Jieba/Posseg.php?at=old&fileviewer=file-view-default#Posseg.php-52
        foreach (Helper::getUserDictNames() as $userDictName) {
            DictHelper::addWordTags($userDictName, $this->word_tag);
        }

        return $this;
    }

    /**
     * @param string $sentence
     * @return Viterbi
     */
    protected function viterbi(string $sentence): Viterbi
    {
        $probEmit  = ModelSingleton::singleton()->getPosProbEmit();
        $probStart = ModelSingleton::singleton()->getPosProbStart();
        $probTrans = ModelSingleton::singleton()->getPosProbTrans();
        $states    = ModelSingleton::singleton()->getPosCharState();

        $string      = new MultiByteString($sentence);
        $V           = [[]];
        $mem_path    = [[]];
        $all_states  = array_keys($probTrans);

        $c        = $string->get(0);
        $c_states = (!empty($states[$c]) ? $states[$c] : $all_states);

        foreach ($c_states as $state) {
            $c                   = $string->get(0);
            $prob_emit           = $probEmit[$state][$c] ?? JiebaConstant::MIN_FLOAT;
            $V[0][$state]        = $probStart[$state] + $prob_emit;
            $mem_path[0][$state] = '';
        }

        for ($t = 1; $t < $string->strlen(); $t++) {
            $c            = $string->get($t);
            $V[$t]        = [];
            $mem_path[$t] = [];

            $prev_states = [];
            foreach (array_keys($mem_path[$t - 1]) as $mem_path_state) {
                if (!empty($probTrans[$mem_path_state])) {
                    $prev_states[] = $mem_path_state;
                }
            }

            $prev_states_expect_next = [];

            foreach ($prev_states as $prev_state) {
                $prev_states_expect_next
                    = array_unique(
                        array_merge(
                            $prev_states_expect_next,
                            array_keys($probTrans[$prev_state])
                        )
                    );
            }

            $obs_states = $states[$c] ?? $all_states;
            $obs_states = array_intersect($obs_states, $prev_states_expect_next);
            $obs_states = $obs_states ?: $all_states;

            foreach ($obs_states as $y) {
                $temp_prob_array = [];
                foreach ($prev_states as $y0) {
                    $prob_trans           = ($probTrans[$y0][$y] ?? JiebaConstant::MIN_FLOAT);
                    $prob_emit            = ($probEmit[$y][$c] ?? JiebaConstant::MIN_FLOAT);
                    $temp_prob_array[$y0] = $V[$t-1][$y0] + $prob_trans + $prob_emit;
                }
                $top              = new TopArrayElement($temp_prob_array);
                $mem_path[$t][$y] = $top->getKey();
                $V[$t][$y]        = $top->getValue(); // maximum probability
            }
        }

        $end_array = end($V);
        $last      = [];
        foreach (array_keys(end($mem_path)) as $y) {
            $last[$y] = $end_array[$y];
        }

        $top             = new TopArrayElement($last);
        $return_prob_key = $top->getKey();
        $return_prob     = $top->getValue();

        $route = array_fill(0, $string->strlen(), JiebaConstant::NONE);
        for ($i = count($route) - 1; $i >= 0; $i--) {
            $route[$i]       = $return_prob_key;
            $return_prob_key = $mem_path[$i][$return_prob_key];
        }

        return new Viterbi($return_prob, $route);
    }

    /**
     * @param string $sentence # input sentence
     * @return Words
     * @todo make code easier to understand.
     */
    protected function __cutDetail(string $sentence): Words
    {
        return $this->cutSentence(
            $sentence,
            function (string $block) {
                return DictHelper::cutSentence(
                    $block,
                    TaggedWord::class,
                    function (string $sentence) {
                        // here \Jieba\Data\Viterbi::$positions is an array of combined characters (e.g, "('S', 'g')").
                        return $this->viterbi($sentence);
                    }
                );
            }
        );
    }

    /**
     * @param string $sentence
     * @return Words
     */
    protected function __cutDAG(string $sentence): Words
    {
        $N = mb_strlen($sentence);
        $this->getJieba()->calc($sentence, $this->getJieba()->getDAG($sentence));

        $x   = 0;
        $buf = '';

        $words = new Words();
        while ($x < $N) {
            $current_route_keys = array_keys($this->getJieba()->getRouteByKey($x));
            $y                  = $current_route_keys[0] + 1;
            $l_word             = mb_substr($sentence, $x, ($y - $x));

            if (($y - $x) == 1) {
                $buf .= $l_word;
            } else {
                if (!empty($buf)) {
                    if (mb_strlen($buf) == 1) {
                        $words->addWord(new TaggedWord($buf, ($this->word_tag[$buf] ?? PosTagConstant::X)));
                    } else {
                        foreach ($this->__cutDetail($buf)->getWords() as $word) {
                            $words->addWord($word);
                        }
                    }
                    $buf = '';
                }

                $words->addWord(new TaggedWord($l_word, ($this->word_tag[$l_word] ?? PosTagConstant::X)));
            }
            $x = $y;
        }

        if (!empty($buf)) {
            if (mb_strlen($buf) == 1) {
                $words->addWord(new TaggedWord($buf, ($this->word_tag[$buf] ?? PosTagConstant::X)));
            } else {
                foreach ($this->__cutDetail($buf)->getWords() as $word) {
                    $words->addWord($word);
                }
            }
        }

        return $words;
    }
    /**
     * @param string $sentence
     * @return Words List of \Jieba\Data\TaggedWord objects.
     */
    public function cut(string $sentence): Words
    {
        return $this->cutSentence(
            $sentence,
            function (string $block) {
                return $this->__cutDAG($block);
            }
        );
    }

    /**
     * @param string  $sentence
     * @param Closure $callback A callback function that returns a |Jieba\Words object back.
     * @return Words
     * @throws Exception
     */
    protected function cutSentence(string $sentence, Closure $callback): Words
    {
        preg_match_all(
            '/(' . JiebaConstant::REGEX_HAN . '|' . JiebaConstant::REGEX_SKIP . '|' . JiebaConstant::REGEX_PUNCTUATION .
            ')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        $seg_list = new Words();
        foreach ($blocks as $block) {
            if (preg_match('/' . JiebaConstant::REGEX_HAN . '/u', $block)) {
                /** @var Words $words */
                $words = $callback($block);
                foreach ($words->getWords() as $word) {
                    $seg_list->addWord($word);
                }
            } elseif (preg_match('/' . JiebaConstant::REGEX_SKIP . '/u', $block)) {
                if (preg_match('/' . JiebaConstant::REGEX_NUMBER . '/u', $block)) {
                    $seg_list->addWord(new TaggedWord($block, PosTagConstant::M));
                } elseif (preg_match('/' . JiebaConstant::REGEX_ENG . '/u', $block)) {
                    $seg_list->addWord(new TaggedWord($block, PosTagConstant::ENG));
                }
            } elseif (preg_match('/' . JiebaConstant::REGEX_PUNCTUATION . '/u', $block)) {
                $seg_list->addWord(new TaggedWord($block, PosTagConstant::W));
            } else {
                throw new Exception('unreachable case executed');
            }
        }

        return $seg_list;
    }

    /**
     * @param array $seg_list
     * @return array
     */
    public function posTagReadable(array $seg_list): array
    {
        foreach ($seg_list as $seg) {
            $seg['tag_readable'] = PosTagConstant::TAGS[$seg['tag']];
        }

        return $seg_list;
    }

    /**
     * @return Jieba
     */
    public function getJieba(): Jieba
    {
        return $this->jieba;
    }

    /**
     * @param Jieba $jieba
     * @return Posseg
     */
    public function setJieba(Jieba $jieba): Posseg
    {
        $this->jieba = $jieba;

        return $this;
    }
}
