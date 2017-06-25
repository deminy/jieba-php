<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Closure;
use Jieba\Constants\JiebaConstant;
use Jieba\Constants\PosTagConstant;
use Jieba\Data\TopArrayElement;
use Jieba\Data\Viterbi;
use Jieba\Data\Word;
use Jieba\Data\Words;
use Jieba\Factory\CacheFactory;
use Jieba\Helper\DictHelper;
use Jieba\Helper\Helper;
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
     * @var array
     */
    public $prob_start = [];
    /**
     * @var array
     */
    public $prob_trans = [];
    /**
     * @var array
     */
    public $prob_emit  = [];
    /**
     * @var array
     */
    public $char_state = [];
    /**
     * @var array
     */
    public $word_tag   = [];

    /**
     * @var Jieba
     */
    protected $jieba;

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
        $this->prob_start = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_START);
        $this->prob_trans = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_TRANS);
        $this->prob_emit  = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_EMIT);
        $this->char_state = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_CHAR_STATE);

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
        $obs = $sentence;
        $states = $this->char_state;
        $V = [];
        $V[0] = [];
        $mem_path = [];
        $mem_path[0] = [];
        $all_states = array_keys($this->prob_trans);

        $c = mb_substr($obs, 0, 1);
        $c_states = (!empty($states[$c]) ? $states[$c] : $all_states);

        foreach ($c_states as $state) {
            $y = $state;
            $c = mb_substr($obs, 0, 1);
            if (isset($this->prob_emit[$y][$c])) {
                $prob_emit = $this->prob_emit[$y][$c];
            } else {
                $prob_emit = JiebaConstant::MIN_FLOAT;
            }
            $V[0][$y] = $this->prob_start[$y] + $prob_emit;
            $mem_path[0][$y] = '';
        }

        for ($t=1; $t<mb_strlen($obs); $t++) {
            $c = mb_substr($obs, $t, 1);
            $V[$t] = [];
            $mem_path[$t] = [];

            $prev_mem_path = array_keys($mem_path[$t-1]);

            $prev_states = [];

            foreach ($prev_mem_path as $mem_path_state) {
                if (count($this->prob_trans[$mem_path_state])>0) {
                    array_push($prev_states, $mem_path_state);
                }
            }

            $prev_states_expect_next = [];

            foreach ($prev_states as $prev_state) {
                $prev_states_expect_next
                    = array_unique(
                        array_merge(
                            $prev_states_expect_next,
                            array_keys($this->prob_trans[$prev_state])
                        )
                    );
            }

            if (isset($states[$c])) {
                $obs_states = $states[$c];
            } else {
                $obs_states = $all_states;
            }

            $obs_states = array_intersect($obs_states, $prev_states_expect_next);

            if (count($obs_states)==0) {
                $obs_states = $all_states;
            }


            foreach ($obs_states as $y) {
                $temp_prob_array = [];
                foreach ($prev_states as $y0) {
                    $prob_trans = ($this->prob_trans[$y0][$y] ?? JiebaConstant::MIN_FLOAT);
                    $prob_emit  = ($this->prob_emit[$y][$c] ?? JiebaConstant::MIN_FLOAT);
                    $temp_prob_array[$y0] = $V[$t-1][$y0] + $prob_trans + $prob_emit;
                }
                $top              = new TopArrayElement($temp_prob_array);
                $mem_path[$t][$y] = $top->getKey();
                $V[$t][$y]        = $top->getValue(); // maximum probability
            }
        }

        $last = [];
        $mem_path_end_keys = array_keys(end($mem_path));

        foreach ($mem_path_end_keys as $y) {
            $end_array = end($V);
            $last[$y] = $end_array[$y];
        }

        $top             = new TopArrayElement($last);
        $return_prob_key = $top->getKey();
        $return_prob     = $top->getValue();

        $obs_length = mb_strlen($obs);

        $route = [];
        for ($t=0; $t<$obs_length; $t++) {
            array_push($route, 'None');
        }

        $i = $obs_length-1;

        while ($i >= 0) {
            $route[$i] = $return_prob_key;
            $return_prob_key = $mem_path[$i][$return_prob_key];
            $i -= 1;
        }

        return new Viterbi($return_prob, $route);
    }

    /**
     * @param string $sentence # input sentence
     * @return Words
     * @todo make code easier to understand.
     */
    public function __cutDetail(string $sentence): Words
    {
        return $this->cutSentence(
            $sentence,
            function (string $block) {
                return DictHelper::cutSentence(
                    $block,
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
        $words = new Words();

        $N = mb_strlen($sentence);
        $this->getJieba()->calc($sentence, $this->getJieba()->getDAG($sentence));

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys($this->getJieba()->getRouteByKey($x));
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x));

            if (($y-$x)==1) {
                $buf = $buf.$l_word;
            } else {
                if (!empty($buf)) {
                    if (mb_strlen($buf) == 1) {
                        $words->addWord(new Word($buf, ($this->word_tag[$buf] ?? PosTagConstant::X)));
                        $buf = '';
                    } else {
                        $regognized = $this->__cutDetail($buf);
                        foreach ($regognized->getWords() as $word) {
                            $words->addWord($word);
                        }
                        $buf = '';
                    }
                }

                $words->addWord(new Word($l_word, ($this->word_tag[$l_word] ?? PosTagConstant::X)));
            }
            $x = $y;
        }

        if (!empty($buf)) {
            if (mb_strlen($buf) == 1) {
                $words->addWord(new Word($buf, ($this->word_tag[$buf] ?? PosTagConstant::X)));
            } else {
                $regognized = $this->__cutDetail($buf);
                foreach ($regognized->getWords() as $word) {
                    $words->addWord($word);
                }
            }
        }

        return $words;
    }
    /**
     * @param string $sentence
     * @return Words
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
        foreach ($blocks as $blk) {
            if (preg_match('/' . JiebaConstant::REGEX_HAN . '/u', $blk)) {
                /** @var Words $words */
                $words = $callback($blk);
                foreach ($words->getWords() as $word) {
                    $seg_list->addWord($word);
                }
            } elseif (preg_match('/' . JiebaConstant::REGEX_SKIP . '/u', $blk)) {
                if (preg_match('/' . JiebaConstant::REGEX_NUMBER . '/u', $blk)) {
                    $seg_list->addWord(new Word($blk, PosTagConstant::M));
                } elseif (preg_match('/' . JiebaConstant::REGEX_ENG . '/u', $blk)) {
                    $seg_list->addWord(new Word($blk, PosTagConstant::ENG));
                }
            } elseif (preg_match('/' . JiebaConstant::REGEX_PUNCTUATION . '/u', $blk)) {
                $seg_list->addWord(new Word($blk, PosTagConstant::W));
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
